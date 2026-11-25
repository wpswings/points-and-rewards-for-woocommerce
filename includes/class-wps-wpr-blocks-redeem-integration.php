<?php
/**
 * Cart / Checkout block integration for the "Redeem points" panel.
 *
 * Loaded only when WooCommerce Blocks' IntegrationInterface is available.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Registers the frontend script that renders the redeem panel in the blocks.
 */
class WPS_WPR_Blocks_Redeem_Integration implements IntegrationInterface {

	/**
	 * Script / style handle.
	 */
	const HANDLE = 'wps-wpr-blocks-redeem';

	/**
	 * Integration name.
	 *
	 * @return string
	 */
	public function get_name() {
		return WPS_WPR_Blocks_Redeem::INTEGRATION_NAME;
	}

	/**
	 * Asset version based on file modification time.
	 *
	 * @param string $relative path relative to the plugin root.
	 * @return string
	 */
	private function asset_version( $relative ) {
		$path = WPS_RWPR_DIR_PATH . $relative;
		return file_exists( $path ) ? (string) filemtime( $path ) : REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION;
	}

	/**
	 * Register the script and style.
	 *
	 * @return void
	 */
	public function initialize() {
		wp_register_script(
			self::HANDLE,
			WPS_RWPR_DIR_URL . 'public/js/wps-wpr-blocks-redeem.js',
			array( 'wp-element', 'wp-plugins', 'wc-blocks-checkout', 'wc-settings' ),
			$this->asset_version( 'public/js/wps-wpr-blocks-redeem.js' ),
			true
		);
		wp_register_style(
			self::HANDLE,
			WPS_RWPR_DIR_URL . 'public/css/wps-wpr-blocks-redeem.css',
			array(),
			$this->asset_version( 'public/css/wps-wpr-blocks-redeem.css' )
		);
		if ( ! is_admin() ) {
			wp_enqueue_style( self::HANDLE );
		}
	}

	/**
	 * Frontend script handles.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( self::HANDLE );
	}

	/**
	 * Editor script handles (nothing to render in the editor).
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array();
	}

	/**
	 * Data exposed to the script as wc.wcSettings.getSetting( '<name>_data' ).
	 *
	 * @return array
	 */
	public function get_script_data() {
		$labels = array(
			'title'        => __( 'Redeem points', 'points-and-rewards-for-woocommerce' ),
			/* translators: %s: points balance. */
			'balance'      => __( 'You have %s points.', 'points-and-rewards-for-woocommerce' ),
			/* translators: %s: maximum points usable on this order. */
			'max'          => __( 'Use up to %s points on this order.', 'points-and-rewards-for-woocommerce' ),
			/* translators: %s: points still needed. */
			'need_more'    => __( 'Earn %s more points to start redeeming.', 'points-and-rewards-for-woocommerce' ),
			/* translators: 1: applied points, 2: discount amount. */
			'applied'      => __( '%1$s points applied (%2$s off).', 'points-and-rewards-for-woocommerce' ),
			'placeholder'  => __( 'Points to redeem', 'points-and-rewards-for-woocommerce' ),
			'apply'        => __( 'Apply', 'points-and-rewards-for-woocommerce' ),
			'remove'       => __( 'Remove', 'points-and-rewards-for-woocommerce' ),
			'invalid'      => __( 'Please enter some valid points!', 'points-and-rewards-for-woocommerce' ),
			'login'        => __( 'Log in to redeem your reward points.', 'points-and-rewards-for-woocommerce' ),
			'login_link'   => __( 'Log in', 'points-and-rewards-for-woocommerce' ),
			'generic_fail' => __( 'Can not redeem!', 'points-and-rewards-for-woocommerce' ),
		);

		return array(
			'namespace'         => WPS_WPR_Blocks_Redeem::STORE_API_NAMESPACE,
			'login_url'         => wc_get_page_permalink( 'myaccount' ),
			'show_guest_notice' => (bool) apply_filters( 'wps_wpr_block_redeem_show_guest_notice', true ),
			'labels'            => apply_filters( 'wps_wpr_block_redeem_labels', $labels ),
		);
	}
}
