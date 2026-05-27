<?php
/**
 * Exit if accessed directly
 *
 * @since      1.0.0
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wps_overview_feature_cards = array(
	array(
		'icon'  => 'dashicons-awards',
		'title' => __( 'Reward actions automatically', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Grant points for signup, referrals, purchases, and selected order milestones from one central configuration.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'icon'  => 'dashicons-chart-line',
		'title' => __( 'Tier and badge journeys', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Build membership levels and badge-driven progression to keep customers engaged and returning to your store.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'icon'  => 'dashicons-controls-repeat',
		'title' => __( 'Flexible point conversion', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Set earning and redemption values per currency, per order range, and per product strategy.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'icon'  => 'dashicons-email-alt',
		'title' => __( 'Notification workflows', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Keep users informed with point credit, redemption, and expiry notifications using customizable email templates.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'icon'  => 'dashicons-chart-bar',
		'title' => __( 'Points log and reporting', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Track every points transaction from the points table and maintain full visibility into customer activity.', 'points-and-rewards-for-woocommerce' ),
	),
);

$wps_overview_compatibilities = array(
	array(
		'label' => __( 'Subscriptions For WooCommerce', 'points-and-rewards-for-woocommerce' ),
		'url'   => 'https://wpswings.com/product/subscriptions-for-woocommerce-pro/?utm_source=wpswings-subs-pro&utm_medium=par-org-backend&utm_campaign=subscription',
	),
	array(
		'label' => __( 'Wallet System For WooCommerce', 'points-and-rewards-for-woocommerce' ),
		'url'   => 'https://wpswings.com/product/wallet-system-for-woocommerce-pro/?utm_source=wpswings-wallet-pro&utm_medium=par-org-backend&utm_campaign=wallet-system',
	),
	array(
		'label' => __( 'Ultimate Gift Cards For WooCommerce', 'points-and-rewards-for-woocommerce' ),
		'url'   => 'https://wpswings.com/product/gift-cards-for-woocommerce-pro/?utm_source=wpswings-giftcards-pro&utm_medium=par-org-backend&utm_campaign=giftcards',
	),
	array(
		'label' => __( 'WooCommerce PayPal Payments', 'points-and-rewards-for-woocommerce' ),
		'url'   => 'https://wordpress.org/plugins/woocommerce-paypal-payments/',
	),
	array(
		'label' => __( 'Advanced Dynamic Pricing for WooCommerce', 'points-and-rewards-for-woocommerce' ),
		'url'   => 'https://wordpress.org/plugins/advanced-dynamic-pricing-for-woocommerce/',
	),
	array(
		'label' => __( 'Elementor', 'points-and-rewards-for-woocommerce' ),
		'url'   => 'https://wordpress.org/plugins/elementor/',
	),
	array(
		'label' => __( 'FOX - Currency Switcher Professional for WooCommerce', 'points-and-rewards-for-woocommerce' ),
		'url'   => 'https://wordpress.org/plugins/woocommerce-currency-switcher/',
	),
);
?>
<div class="wps-overview__wrapper wps-overview--modern">
	<section class="wps-overview__surface">
		<header class="wps-overview__hero">
			<div class="wps-overview__badge">PAR</div>
			<span class="wps-overview__kicker"><?php esc_html_e( 'Overview', 'points-and-rewards-for-woocommerce' ); ?></span>
			<h2><?php esc_html_e( 'Customer loyalty and rewards workflows built for WooCommerce teams', 'points-and-rewards-for-woocommerce' ); ?></h2>
			<p><?php esc_html_e( 'Points And Rewards for WooCommerce centralizes earning rules, redemptions, memberships, and engagement automation so your team can launch and optimize loyalty programs from one place.', 'points-and-rewards-for-woocommerce' ); ?></p>
		</header>

		<div class="wps-overview__section-title-wrap">
			<h3><?php esc_html_e( 'Top features of this plugin', 'points-and-rewards-for-woocommerce' ); ?></h3>
		</div>

		<div class="wps-overview__feature-grid">
			<?php foreach ( $wps_overview_feature_cards as $feature ) { ?>
				<article class="wps-overview__feature-card">
					<div class="wps-overview__feature-icon"><span class="dashicons <?php echo esc_attr( $feature['icon'] ); ?>"></span></div>
					<h4><?php echo esc_html( $feature['title'] ); ?></h4>
					<p><?php echo esc_html( $feature['desc'] ); ?></p>
				</article>
			<?php } ?>
		</div>

		<div class="wps-overview__compatibility-block">
			<h4><?php esc_html_e( 'Plugin Compatibilities', 'points-and-rewards-for-woocommerce' ); ?></h4>
			<div class="wps-overview__compat-grid">
				<?php foreach ( $wps_overview_compatibilities as $compatibility ) { ?>
					<a class="wps_wpr_overview_remove_anchor_underline" href="<?php echo esc_url( $compatibility['url'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $compatibility['label'] ); ?>
					</a>
				<?php } ?>
			</div>
		</div>

		<div class="wps-overview__support-strip">
			<div class="wps-overview__support-content">
				<h4><?php esc_html_e( 'Facing issues?', 'points-and-rewards-for-woocommerce' ); ?></h4>
				<p><?php esc_html_e( 'Our support team can help you align earning rules, redemption strategy, and advanced loyalty workflows.', 'points-and-rewards-for-woocommerce' ); ?></p>
			</div>
			<a class="wps-overview__support-button" href="https://wpswings.com/submit-query/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Contact Support', 'points-and-rewards-for-woocommerce' ); ?></a>
		</div>
	</section>
</div>
