<?php
/**
 * This is the setting template
 *
 * Order total points settings.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Check the allowed the html.
 *
 * @name wps_wpr_allowed_html
 * @since      1.0.0
 */
function wps_wpr_allowed_html() {
	$allowed_tags = array(
		'span' => array(
			'class'    => array(),
			'title'    => array(),
			'style'    => array(),
			'data-tip' => array(),
		),
	);
	return $allowed_tags;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

/*  This is the setting array */
$wps_wpr_order_total_points_settings = array(
	array(
		'title'    => __( 'Enable the settings for the orders', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_thankyouorder_enable',
		'desc_tip' => __( 'Check this box to enable order total points', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Enable Points on order total.', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
	),
	array(
		'type'  => 'create_order_total_points',
		'title' => __( 'Enter Points  within Order Range', 'points-and-rewards-for-woocommerce' ),
	),
);
if ( isset( $_POST['wps_wpr_save_order_totalsettings'] ) && isset( $_POST['wps-wpr-nonce'] ) ) {
	unset( $_POST['wps_wpr_save_order_totalsettings'] );

	$wps_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['wps-wpr-nonce'] ) );
	if ( wp_verify_nonce( $wps_wpr_nonce, 'wps-wpr-nonce' ) ) {

		$wps_wpr_order_total_points                  = array();
		$_POST['wps_wpr_thankyouorder_enable']       = isset( $_POST['wps_wpr_thankyouorder_enable'] ) ? 1 : 0;
		$_POST['wps_wpr_thankyouorder_minimum']      = ( isset( $_POST['wps_wpr_thankyouorder_minimum'] ) && ! empty( $_POST['wps_wpr_thankyouorder_minimum'] ) ) ? map_deep( wp_unslash( $_POST['wps_wpr_thankyouorder_minimum'] ), 'sanitize_text_field' ) : array();
		$_POST['wps_wpr_thankyouorder_maximum']      = ( isset( $_POST['wps_wpr_thankyouorder_maximum'] ) && ! empty( $_POST['wps_wpr_thankyouorder_maximum'] ) ) ? map_deep( wp_unslash( $_POST['wps_wpr_thankyouorder_maximum'] ), 'sanitize_text_field' ) : array();
		$_POST['wps_wpr_thankyouorder_current_type'] = ( isset( $_POST['wps_wpr_thankyouorder_current_type'] ) && ! empty( $_POST['wps_wpr_thankyouorder_current_type'] ) ) ? map_deep( wp_unslash( $_POST['wps_wpr_thankyouorder_current_type'] ), 'sanitize_text_field' ) : array();

		/* Save Order Total Points*/
		$_postdata = $_POST;
		foreach ( $_postdata as $key => $value ) {
			$wps_wpr_order_total_points[ $key ] = $value;
		}
		if ( is_array( $wps_wpr_order_total_points ) && ! empty( $wps_wpr_order_total_points ) ) {
			update_option( 'wps_wpr_order_total_settings', $wps_wpr_order_total_points );
		}
		$settings_obj->wps_wpr_settings_saved();
	}
}

/* Get Order Total Settings in the order*/
$wps_get_order_total_settings = get_option( 'wps_wpr_order_total_settings', array() );
$thankyouorder_min            = ( isset( $wps_get_order_total_settings['wps_wpr_thankyouorder_minimum'] ) && ! empty( $wps_get_order_total_settings['wps_wpr_thankyouorder_minimum'] ) ) ? $wps_get_order_total_settings['wps_wpr_thankyouorder_minimum'] : array();
$thankyouorder_max            = ( isset( $wps_get_order_total_settings['wps_wpr_thankyouorder_maximum'] ) && ! empty( $wps_get_order_total_settings['wps_wpr_thankyouorder_maximum'] ) ) ? $wps_get_order_total_settings['wps_wpr_thankyouorder_maximum'] : array();
$thankyouorder_value          = ( isset( $wps_get_order_total_settings['wps_wpr_thankyouorder_current_type'] ) && ! empty( $wps_get_order_total_settings['wps_wpr_thankyouorder_current_type'] ) ) ? $wps_get_order_total_settings['wps_wpr_thankyouorder_current_type'] : array();

$wps_wpr_order_total_points_settings = apply_filters( 'wps_wpr_order_total_points_settings', $wps_wpr_order_total_points_settings );
?>

<?php do_action( 'wps_wpr_add_notice' ); ?>
<div class="wps_wpr_wrap_table">
	<table class="form-table wps_wpr_general_setting mwp_wpr_settings">
	<input type="hidden" id="wps_order_ttol" value = 1 />
		<tbody>
			<?php
			foreach ( $wps_wpr_order_total_points_settings as $key => $value ) {
				?>
				<tr valign="top">
					<th scope="row" class="wps-wpr-titledesc">
						<?php $settings_obj->wps_wpr_generate_label_for_order_total_settings( $value ); ?>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags = wps_wpr_allowed_html();
						echo array_key_exists( 'desc_tip', $value ) ? wp_kses( wc_help_tip( $value['desc_tip'] ), $allowed_tags ) : '';
						if ( 'checkbox' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_checkbox_html( $value, $wps_get_order_total_settings );
						}
						if ( 'create_order_total_points' == $value['type'] ) {
							?>
							<?php
							do_action( 'wps_wpr_order_total_points', $thankyouorder_min, $thankyouorder_max, $thankyouorder_value );
							?>
							<input type="button" value="<?php esc_html_e( 'Add More', 'points-and-rewards-for-woocommerce' ); ?>" class="wps_wpr_add_more button" id="wps_wpr_add_more">
							<?php
						}
						do_action( 'wps_wpr_additional_order_total', $value, $wps_get_order_total_settings )
						?>
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
</div>
<p class="submit">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_save_order_totalsettings">
</p>
