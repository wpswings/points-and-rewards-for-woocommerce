<?php
/**
 * Campaign AJAX Handler
 *
 * Handles AJAX requests for campaign template library and settings.
 *
 * @link       https://wpswings.com/
 * @since      2.10.0
 *
 * @package    Points_Rewards_For_WooCommerce
 * @subpackage Points_Rewards_For_WooCommerce/admin/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPS_WPR_Campaign_AJAX
 *
 * Handles AJAX operations for campaign management.
 */
class WPS_WPR_Campaign_AJAX {

	/**
	 * Initialize AJAX hooks.
	 *
	 * @since 2.10.0
	 */
	public static function init() {
		// Template library AJAX actions.
		add_action( 'wp_ajax_wps_wpr_get_campaign_templates', array( __CLASS__, 'get_campaign_templates' ) );
		add_action( 'wp_ajax_wps_wpr_get_template_categories', array( __CLASS__, 'get_template_categories' ) );
	}

	/**
	 * Get campaign templates via AJAX.
	 *
	 * @since 2.10.0
	 */
	public static function get_campaign_templates() {
		// Verify nonce.
		check_ajax_referer( 'wps_wpr_campaign_nonce', 'security' );

		// Check user capabilities.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'points-and-rewards-for-woocommerce' ) ) );
		}

		// Get request parameters.
		$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : 'all';
		$search   = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		// Require template class.
		require_once WPS_RWPR_DIR_PATH . 'admin/includes/class-wps-wpr-campaign-templates.php';

		$templates = array();

		// Search mode.
		if ( ! empty( $search ) ) {
			$search_results = WPS_WPR_Campaign_Templates::search_templates( $search );

			foreach ( $search_results as $result ) {
				$templates[] = array(
					'category'    => $result['category'],
					'template_id' => $result['template_id'],
					'metadata'    => $result['metadata'],
				);
			}
		} elseif ( 'all' === $category ) {
			// Get all templates.
			$all_templates = WPS_WPR_Campaign_Templates::get_all_templates();

			foreach ( $all_templates as $category_key => $category_data ) {
				foreach ( $category_data['templates'] as $template_id => $template ) {
					$templates[] = array(
						'category'    => $category_key,
						'template_id' => $template_id,
						'metadata'    => WPS_WPR_Campaign_Templates::get_template_metadata( $category_key, $template_id ),
					);
				}
			}
		} else {
			// Get templates by category.
			$category_templates = WPS_WPR_Campaign_Templates::get_templates_by_category( $category );

			foreach ( $category_templates as $template_id => $template ) {
				$templates[] = array(
					'category'    => $category,
					'template_id' => $template_id,
					'metadata'    => WPS_WPR_Campaign_Templates::get_template_metadata( $category, $template_id ),
				);
			}
		}

		if ( empty( $templates ) ) {
			wp_send_json_error( array( 'message' => __( 'No templates found.', 'points-and-rewards-for-woocommerce' ) ) );
		}

		wp_send_json_success( $templates );
	}

	/**
	 * Get template categories via AJAX.
	 *
	 * @since 2.10.0
	 */
	public static function get_template_categories() {
		// Verify nonce.
		check_ajax_referer( 'wps_wpr_campaign_nonce', 'security' );

		// Check user capabilities.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'points-and-rewards-for-woocommerce' ) ) );
		}

		// Require template class.
		require_once WPS_RWPR_DIR_PATH . 'admin/includes/class-wps-wpr-campaign-templates.php';

		$categories = WPS_WPR_Campaign_Templates::get_template_categories();

		if ( empty( $categories ) ) {
			wp_send_json_error( array( 'message' => __( 'No categories found.', 'points-and-rewards-for-woocommerce' ) ) );
		}

		wp_send_json_success( $categories );
	}
}

// Initialize AJAX handlers.
WPS_WPR_Campaign_AJAX::init();
