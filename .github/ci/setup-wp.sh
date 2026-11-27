#!/usr/bin/env bash
# Build a throw-away WordPress + WooCommerce site for the unit and e2e tests on
# a self-hosted runner. Needs no root: WordPress runs on PHP's built-in server.
#
# Database (pick one):
#   - WPR_DB_HOST / WPR_DB_USER / WPR_DB_PASSWORD set: a MySQL/MariaDB the runner
#     can reach. A database named wpr_ci_<run>_<attempt>_<job> is created and
#     dropped again by teardown-wp.sh, so the user needs CREATE/DROP rights.
#   - otherwise Docker on the runner: a mysql:8.0 container is started.
#
# Inputs (env):
#   WPR_PLUGINS   "source_dir:slug" pairs, one per line, to install and activate in that order
#   WC_VERSION    WooCommerce version from wordpress.org (default: latest)
#   WP_VERSION    WordPress version (default: latest)
#   WPR_WP_CLI_PHAR  optional path of a wp-cli.phar already on the runner
#   WPR_ENABLE_POINTS  1 to switch the points program on (General > Enable Points and Rewards)
#
# Writes to $GITHUB_ENV: WP_DIR, WP_BASE_URL, WP_CLI, WP_LOAD_PATH,
# WP_USERNAME, WP_PASSWORD, WPR_CI_DIR and what teardown-wp.sh needs.
set -euo pipefail

CI_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
WORK="${RUNNER_TEMP:-/tmp}/wpr-ci-${GITHUB_RUN_ID:-local}-${GITHUB_JOB:-job}"
WP_DIR="$WORK/wordpress"
BIN="$WORK/bin"
ADMIN_PASS="ci-$( head -c 12 /dev/urandom | od -An -tx1 | tr -d ' \n' )"
rm -rf "$WORK"
mkdir -p "$BIN" "$WP_DIR"

env_out() { echo "$1=$2" >> "${GITHUB_ENV:-/dev/null}"; export "$1=$2"; }
php_free_port() { php -r '$s = stream_socket_server( "tcp://127.0.0.1:0" ); $n = explode( ":", stream_socket_get_name( $s, false ) ); echo end( $n );'; }

php -m | grep -qi '^mysqli$' || { echo "::error::PHP on the runner has no mysqli extension."; exit 1; }

# ---------- Database ----------
if [ -n "${WPR_DB_HOST:-}" ]; then
	DB_HOST="$WPR_DB_HOST"
	DB_USER="${WPR_DB_USER:-root}"
	DB_PASS="${WPR_DB_PASSWORD:-}"
	DB_NAME="wpr_ci_${GITHUB_RUN_ID:-0}_${GITHUB_RUN_ATTEMPT:-1}_${GITHUB_JOB:-job}"
	DB_NAME="${DB_NAME//-/_}"
	env_out WPR_DB_DROP "$DB_NAME"
elif command -v docker >/dev/null 2>&1; then
	NAME="wpr-ci-mysql-${GITHUB_RUN_ID:-local}-${GITHUB_RUN_ATTEMPT:-1}-${GITHUB_JOB:-job}"
	docker rm -f "$NAME" >/dev/null 2>&1 || true
	docker run -d --rm --name "$NAME" -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=wordpress \
		-p 127.0.0.1::3306 mysql:8.0 --default-authentication-plugin=mysql_native_password >/dev/null
	env_out WPR_DB_CONTAINER "$NAME"
	PORT="$( docker port "$NAME" 3306/tcp | head -1 | sed 's/.*://' )"
	DB_HOST="127.0.0.1:$PORT"
	DB_USER=root
	DB_PASS=root
	DB_NAME=wordpress
else
	echo "::error::No database: set the WPR_DB_HOST / WPR_DB_USER variables and WPR_DB_PASSWORD secret, or install Docker on the runner."
	exit 1
fi
env_out WPR_DB_HOST_USED "$DB_HOST"
env_out WPR_DB_USER_USED "$DB_USER"
env_out WPR_DB_PASS_USED "$DB_PASS"

echo "Waiting for MySQL at $DB_HOST ..."
for i in $( seq 1 90 ); do
	if WPR_DB_HOST="$DB_HOST" WPR_DB_USER="$DB_USER" WPR_DB_PASSWORD="$DB_PASS" WPR_DB_NAME="$DB_NAME" php "$CI_DIR/db.php" create 2>/dev/null; then
		break
	fi
	[ "$i" = 90 ] && { echo "::error::MySQL at $DB_HOST did not answer."; exit 1; }
	sleep 2
done

# ---------- WP-CLI ----------
if [ -n "${WPR_WP_CLI_PHAR:-}" ] && [ -f "$WPR_WP_CLI_PHAR" ]; then
	cp "$WPR_WP_CLI_PHAR" "$BIN/wp-cli.phar"
else
	curl -fsSL -o "$BIN/wp-cli.phar" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
fi
printf '#!/usr/bin/env bash\nexec php -d memory_limit=1G "%s" --path="%s" "$@"\n' "$BIN/wp-cli.phar" "$WP_DIR" > "$BIN/wp"
chmod +x "$BIN/wp"
WP="$BIN/wp"

# ---------- WordPress + WooCommerce ----------
PORT_WEB="$( php_free_port )"
URL="http://127.0.0.1:$PORT_WEB"

$WP core download --version="${WP_VERSION:-latest}" --quiet
$WP config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost="$DB_HOST" --skip-check --quiet --extra-php <<'PHP'
define( 'DISABLE_WP_CRON', true );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_ENVIRONMENT_TYPE', 'local' );
PHP
$WP core install --url="$URL" --title="WPR CI" --admin_user=admin --admin_password="$ADMIN_PASS" --admin_email=admin@example.test --skip-email --quiet
$WP rewrite structure '/%postname%/' --quiet

if [ "${WC_VERSION:-latest}" = "latest" ]; then
	$WP plugin install woocommerce --activate --quiet
else
	$WP plugin install woocommerce --version="$WC_VERSION" --activate --quiet
fi
$WP eval 'WC_Install::create_pages();'
$WP option update woocommerce_coming_soon no --quiet
$WP option update woocommerce_default_country 'IN:DL' --quiet
$WP option update woocommerce_currency INR --quiet
$WP option update woocommerce_onboarding_profile '{"skipped":true}' --format=json --quiet

while IFS= read -r pair; do
	pair="$( echo "$pair" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//' )"
	[ -z "$pair" ] && continue
	src="${pair%:*}"
	slug="${pair##*:}"
	mkdir -p "$WP_DIR/wp-content/plugins/$slug"
	# Copy without VCS and dependency folders.
	tar -C "$src" --exclude=.git --exclude=node_modules --exclude=test-results --exclude=playwright-report -cf - . | tar -C "$WP_DIR/wp-content/plugins/$slug" -xf -
	$WP plugin activate "$slug" --quiet
done <<< "${WPR_PLUGINS:-}"
# Treat the WP Swings onboarding popup as done (nothing is sent) so it does not cover the admin pages.
$WP option update 'wps_wpr_onboarding-data-sent' sent --quiet
$WP rewrite flush --quiet

if [ "${WPR_ENABLE_POINTS:-}" = "1" ]; then
	# General > Enable Points and Rewards.
	$WP eval '$g = get_option( "wps_wpr_settings_gallery", array() ); $g = is_array( $g ) ? $g : array(); $g["wps_wpr_general_setting_enable"] = 1; update_option( "wps_wpr_settings_gallery", $g );'
fi

# ---------- Web server ----------
LOG="$WORK/php-server.log"
# Fully detached (own session, no inherited stdin/stdout) so the CI step does not wait on it.
# The shell writes its own PID (setsid may fork) and execs PHP, so the PID file
# names the session / process-group leader that teardown-wp.sh kills.
PHP_CLI_SERVER_WORKERS=4 setsid sh -c 'echo $$ > "$1"; shift; exec "$@"' sh "$WORK/php-server.pid" \
	php -d memory_limit=512M -S "127.0.0.1:$PORT_WEB" -t "$WP_DIR" "$CI_DIR/router.php" </dev/null >"$LOG" 2>&1 &
for i in $( seq 1 50 ); do [ -s "$WORK/php-server.pid" ] && break; sleep 0.1; done
for i in $( seq 1 30 ); do
	curl -fsS --max-time 20 -o /dev/null "$URL/wp-login.php" 2>/dev/null && break
	[ "$i" = 30 ] && { echo "::error::The PHP web server did not start."; cat "$LOG"; exit 1; }
	sleep 1
done

env_out WPR_CI_WORK "$WORK"
env_out WPR_CI_DIR "$CI_DIR"
env_out WP_DIR "$WP_DIR"
env_out WP_BASE_URL "$URL"
env_out WP_CLI "$WP"
env_out WP_LOAD_PATH "$WP_DIR/wp-load.php"
env_out WP_USERNAME admin
env_out WP_PASSWORD "$ADMIN_PASS"
echo "::add-mask::$ADMIN_PASS"
echo "WordPress $( $WP core version ), WooCommerce $( $WP plugin get woocommerce --field=version ) at $URL"
$WP plugin list --status=active --fields=name,version
