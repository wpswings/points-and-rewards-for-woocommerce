<?php
/**
 * This is the setting template
 *
 * Earn-points messages settings.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

// Save Earn Messages Settings.
if ( isset( $_POST['wps_wpr_earn_messages_nonce'] ) && current_user_can( 'manage_options' ) ) {
	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_wpr_earn_messages_nonce'] ) ), 'earn-messages-setting-nonce' ) ) {
		if ( isset( $_POST['wps_wpr_save_earn_messages_settings'] ) ) {
			$wps_wpr_em_input = array();
			foreach ( array_keys( WPS_WPR_Earn_Messages::wps_wpr_get_default_settings() ) as $wps_wpr_em_key ) {
				$wps_wpr_em_input[ $wps_wpr_em_key ] = isset( $_POST[ $wps_wpr_em_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $wps_wpr_em_key ] ) ) : '';
			}
			update_option( WPS_WPR_Earn_Messages::OPTION, WPS_WPR_Earn_Messages::wps_wpr_sanitize_settings( $wps_wpr_em_input ) );
		}
	}

	// Show saved msg.
	$settings_obj->wps_wpr_settings_saved();
}

$wps_wpr_em_settings = WPS_WPR_Earn_Messages::wps_wpr_get_settings();
$wps_wpr_em_toggles  = array(
	'wps_wpr_em_enable_product' => array(
		'label' => __( 'Show on Product Page', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Show the points a customer earns for the product on the single product page. Variable products show a range that updates when a variation is selected. Replaces the older "Product Points" badge.', 'points-and-rewards-for-woocommerce' ),
	),
	'wps_wpr_em_enable_shop'    => array(
		'label' => __( 'Show on Shop and Category Pages', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Show the points earned under each product in the shop, category and other product listings.', 'points-and-rewards-for-woocommerce' ),
	),
	'wps_wpr_em_enable_cart'    => array(
		'label' => __( 'Show on Cart and Checkout', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Add a row to the cart and checkout totals with the points the order will earn.', 'points-and-rewards-for-woocommerce' ),
	),
	'wps_wpr_em_show_guests'    => array(
		'label' => __( 'Show to Guests', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Also show messages to visitors who are not logged in, as a reason to sign up.', 'points-and-rewards-for-woocommerce' ),
	),
);
$wps_wpr_em_texts = array(
	'wps_wpr_em_product_text' => array(
		'label' => __( 'Product Page Message', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Use [POINTS] for the number of points.', 'points-and-rewards-for-woocommerce' ),
	),
	'wps_wpr_em_shop_text'    => array(
		'label' => __( 'Shop Page Message', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Use [POINTS] for the number of points.', 'points-and-rewards-for-woocommerce' ),
	),
	'wps_wpr_em_cart_text'    => array(
		'label' => __( 'Cart and Checkout Label', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Label of the totals row on the cart and checkout.', 'points-and-rewards-for-woocommerce' ),
	),
);
?>

<div class="wps_wpr_earn_messages_main_wrappers">
	<main class="wps_wpr_main_earn_messages_wrapper">
		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Earn-Points Messages', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content">
				<input type="hidden" name="wps_wpr_earn_messages_nonce" id="wps_wpr_earn_messages_nonce" value="<?php echo esc_attr( wp_create_nonce( 'earn-messages-setting-nonce' ) ); ?>">
				<p class="wps_wpr_label_notice"><?php esc_html_e( 'Points are worked out with your earning rules: product points plus per-currency points (including membership rates).', 'points-and-rewards-for-woocommerce' ); ?></p>
				<?php foreach ( $wps_wpr_em_toggles as $wps_wpr_em_key => $wps_wpr_em_field ) : ?>
					<article class="wps_wpr_general_row">
						<label for="<?php echo esc_attr( $wps_wpr_em_key ); ?>" class="wps_wpr_general_label"><?php echo esc_html( $wps_wpr_em_field['label'] ); ?></label>
						<div class="wps_wpr_general_content">
							<input type="checkbox" name="<?php echo esc_attr( $wps_wpr_em_key ); ?>" id="<?php echo esc_attr( $wps_wpr_em_key ); ?>" value="yes" <?php checked( $wps_wpr_em_settings[ $wps_wpr_em_key ], 'yes' ); ?>>
							<span class="wps_wpr_label_notice"><?php echo esc_html( $wps_wpr_em_field['desc'] ); ?></span>
						</div>
					</article>
				<?php endforeach; ?>
				<?php foreach ( $wps_wpr_em_texts as $wps_wpr_em_key => $wps_wpr_em_field ) : ?>
					<article class="wps_wpr_general_row">
						<label for="<?php echo esc_attr( $wps_wpr_em_key ); ?>" class="wps_wpr_general_label"><?php echo esc_html( $wps_wpr_em_field['label'] ); ?></label>
						<div class="wps_wpr_general_content">
							<input type="text" class="input-text" name="<?php echo esc_attr( $wps_wpr_em_key ); ?>" id="<?php echo esc_attr( $wps_wpr_em_key ); ?>" value="<?php echo esc_attr( $wps_wpr_em_settings[ $wps_wpr_em_key ] ); ?>">
							<span class="wps_wpr_label_notice"><?php echo esc_html( $wps_wpr_em_field['desc'] ); ?></span>
						</div>
					</article>
				<?php endforeach; ?>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_em_text_color" class="wps_wpr_general_label"><?php esc_html_e( 'Text Colour', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="color" name="wps_wpr_em_text_color" id="wps_wpr_em_text_color" value="<?php echo esc_attr( $wps_wpr_em_settings['wps_wpr_em_text_color'] ); ?>">
					</div>
				</article>
			</div>
		</section>
	</main>
	<input type="submit" name="wps_wpr_save_earn_messages_settings" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_save_earn_messages_settings" value="<?php esc_attr_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
</div>
