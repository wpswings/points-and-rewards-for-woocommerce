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
 * @name mwb_wpr_allowed_html
 * @since      1.0.0
 */
function mwb_wpr_allowed_html() {
	$allowed_tags = array(
		'span' => array(
			'class' => array(),
			'title' => array(),
			'style' => array(),
			'data-tip' => array(),
		),
	);
	return $allowed_tags;
}

include_once MWB_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();
/*  This is the setting array */
$mwb_wpr_order_total_points_settings = array(
	array(
		'title' => __( 'Enable the settings for the orders', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'checkbox',
		'id' => 'mwb_wpr_thankyouorder_enable',
		'desc_tip' => __( 'Check this box to enable gift coupon for those customers who had placed orders in your site', 'points-and-rewards-for-woocommerce' ),
		'desc' => __( 'Enable Points on order total.', 'points-and-rewards-for-woocommerce' ),
		'class' => 'input-text',
	),
	array(
		'type' => 'create_order_total_points',
		'title' => __( 'Enter Points  within Order Range', 'points-and-rewards-for-woocommerce' ),
	),
);
if ( isset( $_POST['mwb_wpr_save_order_totalsettings'] ) && isset( $_POST['mwb-wpr-nonce'] ) ) {
	unset( $_POST['mwb_wpr_save_order_totalsettings'] );
	$mwb_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['mwb-wpr-nonce'] ) );
	if ( wp_verify_nonce( $mwb_wpr_nonce, 'mwb-wpr-nonce' ) ) {
		$mwb_wpr_order_total_points = array();
		$_POST['mwb_wpr_thankyouorder_enable'] = isset( $_POST['mwb_wpr_thankyouorder_enable'] ) ? 1 : 0;
		$_POST['mwb_wpr_thankyouorder_minimum'] = ( isset( $_POST['mwb_wpr_thankyouorder_minimum'] ) && ! empty( $_POST['mwb_wpr_thankyouorder_minimum'] ) ) ? map_deep( wp_unslash( $_POST['mwb_wpr_thankyouorder_minimum'] ), 'sanitize_text_field' ) : array();
		$_POST['mwb_wpr_thankyouorder_maximum'] = ( isset( $_POST['mwb_wpr_thankyouorder_maximum'] ) && ! empty( $_POST['mwb_wpr_thankyouorder_maximum'] ) ) ? map_deep( wp_unslash( $_POST['mwb_wpr_thankyouorder_maximum'] ), 'sanitize_text_field' ) : array();
		$_POST['mwb_wpr_thankyouorder_current_type'] = ( isset( $_POST['mwb_wpr_thankyouorder_current_type'] ) && ! empty( $_POST['mwb_wpr_thankyouorder_current_type'] ) ) ? map_deep( wp_unslash( $_POST['mwb_wpr_thankyouorder_current_type'] ), 'sanitize_text_field' ) : array();
		/* Save Order Total Points*/
		$_postdata = $_POST;
		foreach ( $_postdata as $key => $value ) {
			$mwb_wpr_order_total_points[ $key ] = $value;
		}
		if ( is_array( $mwb_wpr_order_total_points ) && ! empty( $mwb_wpr_order_total_points ) ) {
			update_option( 'mwb_wpr_order_total_settings', $mwb_wpr_order_total_points );
		}
		$settings_obj->mwb_wpr_settings_saved();
	}
}
	/* Get Order Total Settings in the order*/

	$mwb_get_order_total_settings = get_option( 'mwb_wpr_order_total_settings', array() );



	$thankyouorder_min = ( isset( $mwb_get_order_total_settings['mwb_wpr_thankyouorder_minimum'] ) && ! empty( $mwb_get_order_total_settings['mwb_wpr_thankyouorder_minimum'] ) ) ? $mwb_get_order_total_settings['mwb_wpr_thankyouorder_minimum'] : array();
	$thankyouorder_max = ( isset( $mwb_get_order_total_settings['mwb_wpr_thankyouorder_maximum'] ) && ! empty( $mwb_get_order_total_settings['mwb_wpr_thankyouorder_maximum'] ) ) ? $mwb_get_order_total_settings['mwb_wpr_thankyouorder_maximum'] : array();
	$thankyouorder_value = ( isset( $mwb_get_order_total_settings['mwb_wpr_thankyouorder_current_type'] ) && ! empty( $mwb_get_order_total_settings['mwb_wpr_thankyouorder_current_type'] ) ) ? $mwb_get_order_total_settings['mwb_wpr_thankyouorder_current_type'] : array();
?>
<?php

	$mwb_wpr_order_total_points_settings = apply_filters( 'mwb_wpr_order_total_points_settings', $mwb_wpr_order_total_points_settings );
?>
<div class="mwb_wpr_wrap_table">
	<table class="form-table mwb_wpr_general_setting mwp_wpr_settings">
		<tbody>
			<?php
			foreach ( $mwb_wpr_order_total_points_settings as $key => $value ) {
				?>
			<tr valign="top">
			   <th scope="row" class="mwb-wpr-titledesc">
				 <?php $settings_obj->mwb_wpr_generate_label_for_order_total_settings( $value ); ?>
			   </th>
			<td class="forminp forminp-text">
				   <?php
					$allowed_tags = mwb_wpr_allowed_html();
					echo array_key_exists( 'desc_tip', $value ) ? wp_kses( wc_help_tip( $value['desc_tip'] ), $allowed_tags ) : '';
					if ( 'checkbox' == $value['type'] ) {
						$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $mwb_get_order_total_settings );
					}
					if ( 'create_order_total_points' == $value['type'] ) {
						?>
					
							<?php
							 do_action( 'mwb_wpr_order_total_points', $thankyouorder_min, $thankyouorder_max, $thankyouorder_value );
							?>
						
					<input type="button" value="<?php esc_html_e( 'Add More', 'points-and-rewards-for-woocommerce' ); ?>" class="mwb_wpr_add_more button" id="mwb_wpr_add_more">
						<?php
					}
					do_action( 'mwb_wpr_additional_order_total', $value, $mwb_get_order_total_settings )
					?>
			</td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<p class="submit">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_order_totalsettings">
</p>
