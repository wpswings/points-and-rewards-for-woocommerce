#!/usr/bin/env bash
# Stop the CI web server and remove the test database / container. Safe to run twice.
set -uo pipefail

CI_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

if [ -n "${WPR_CI_WORK:-}" ] && [ -f "$WPR_CI_WORK/php-server.pid" ]; then
	pid="$( cat "$WPR_CI_WORK/php-server.pid" )"
	# Workers are children of the server process.
	pkill -P "$pid" 2>/dev/null || true
	kill "$pid" 2>/dev/null || true
fi
if [ -n "${WPR_DB_CONTAINER:-}" ]; then
	docker rm -f "$WPR_DB_CONTAINER" >/dev/null 2>&1 || true
fi
if [ -n "${WPR_DB_DROP:-}" ]; then
	WPR_DB_HOST="$WPR_DB_HOST_USED" WPR_DB_USER="$WPR_DB_USER_USED" WPR_DB_PASSWORD="$WPR_DB_PASS_USED" WPR_DB_NAME="$WPR_DB_DROP" php "$CI_DIR/db.php" drop || true
fi
if [ -n "${WPR_CI_WORK:-}" ]; then
	rm -rf "$WPR_CI_WORK"
fi
exit 0
