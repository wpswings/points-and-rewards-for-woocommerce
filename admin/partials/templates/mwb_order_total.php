<?php
include_once MWB_RWPR_DIR_PATH . '/admin/partials/settings/class-rewardeem-wocoommerce-points-rewards-settings.php';
$settings_obj = new Rewardeem_woocommerce_Points_Rewards_Admin_settings();
/*  This is the setting array */
$mwb_wpr_order_total_points_settings = array(
	array(
		'title' => __( 'Enable the settings for the orders', MWB_RWPR_Domain ),
		'type'  => 'checkbox',
		'id' => 'mwb_wpr_thankyouorder_enable',
		'desc_tip' => __( 'Check this box to enable gift coupon for those customers who had placed orders in your site', MWB_RWPR_Domain ),
		'desc' => __( 'Enable Points on order total.', MWB_RWPR_Domain ),
		'class' => 'input-text',
	),
	array(
		'type' => 'create_order_total_points',
		'title' => __( 'Enter Points  within Order Range', MWB_RWPR_Domain ),
	),
);
if ( isset( $_POST['mwb_wpr_save_order_totalsettings'] ) ) {
	unset( $_POST['mwb_wpr_save_order_totalsettings'] );
	if ( wp_verify_nonce( $_POST['mwb-wpr-nonce'], 'mwb-wpr-nonce' ) ) {
		$mwb_wpr_order_total_points = array();
		$_POST['mwb_wpr_thankyouorder_enable'] = isset( $_POST['mwb_wpr_thankyouorder_enable'] ) ? 1 : 0;
		$_POST['mwb_wpr_thankyouorder_minimum'] = ( isset( $_POST['mwb_wpr_thankyouorder_minimum'] ) && ! empty( $_POST['mwb_wpr_thankyouorder_minimum'] ) ) ? $_POST['mwb_wpr_thankyouorder_minimum'] : array();
		$_POST['mwb_wpr_thankyouorder_maximum'] = ( isset( $_POST['mwb_wpr_thankyouorder_maximum'] ) && ! empty( $_POST['mwb_wpr_thankyouorder_maximum'] ) ) ? $_POST['mwb_wpr_thankyouorder_maximum'] : array();
		$_POST['mwb_wpr_thankyouorder_current_type'] = ( isset( $_POST['mwb_wpr_thankyouorder_current_type'] ) && ! empty( $_POST['mwb_wpr_thankyouorder_current_type'] ) ) ? $_POST['mwb_wpr_thankyouorder_current_type'] : array();
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
			   <th scope="row" class="titledesc">
				 <?php $settings_obj->mwb_wpr_generate_label_for_order_total_settings( $value ); ?>
			   </th>
			<td class="forminp forminp-text">
				   <?php
					echo array_key_exists( 'desc_tip', $value ) ? wc_help_tip( $value['desc_tip'] ) : '';
					if ( $value['type'] == 'checkbox' ) {
						$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $mwb_get_order_total_settings );
					}
					if ( $value['type'] == 'create_order_total_points' ) {
						?>
					
							<?php
							 do_action( 'mwb_wpr_order_total_points', $thankyouorder_min, $thankyouorder_max, $thankyouorder_value );
							?>
						
					<input type="button" value="<?php _e( 'Add More', MWB_RWPR_Domain ); ?>" class="mwb_wpr_add_more button" id="mwb_wpr_add_more">
						<?php
					}
					?>
			</td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<p class="submit">
	<input type="submit" value='<?php _e( 'Save changes', MWB_RWPR_Domain ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_order_totalsettings">
</p>
