<?php 
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
include_once MWB_RWPR_DIR_PATH.'/admin/partials/settings/class-rewardeem-wocoommerce-points-rewards-settings.php';
include_once ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_PATH.'/admin/partials/settings/class-ultimate-wocoommerce-points-rewards-settings_pro.php';
$settings_obj = new ultimate_woocommerce_Points_Rewards_Admin_settings();
/*
* Points Expiration Template
*/
$mwb_points_exipration_array = array(
	 array(
		'title' => __('Points Expiration',"ultimate-woocommerce-points-and-rewards"), 
		'type'	=> 'title',
		),
	array(
		'title' => __('Enable Points Expiration', "ultimate-woocommerce-points-and-rewards"),
		'type'	=> "checkbox",
		'desc'  =>	__('Enable Point Expiration.',"ultimate-woocommerce-points-and-rewards"),
		'id'	=> 'mwb_wpr_points_expiration_enable',
		'desc_tip'=> __('Check this, If you want to set the expiration period for the Rewarded Points.', "ultimate-woocommerce-points-and-rewards"),
		'default'	=> 0,
		),
	array(
		'title' => __('Show Points expiration on Myaccount Page',"ultimate-woocommerce-points-and-rewards"),
		'type'	=> "checkbox",
		'id'	=>	"mwb_wpr_points_exp_onmyaccount",
		'class'	  => 'input-text',
		'desc_tip'=> __('Check this, If you want to show the expiration period for the Rewarded Points on Myaccount Page.', "ultimate-woocommerce-points-and-rewards"),
		'default'	=> 0,
		'desc'	  => __('Expiartion willl get displayed just below the Total Point.',"ultimate-woocommerce-points-and-rewards"),
		),
	array(
		'title' => __('Set the Required Threshold',"ultimate-woocommerce-points-and-rewards"),
		'type'	=> "number",
		'default'	=> 1,
		'id'	=> "mwb_wpr_points_expiration_threshold",
		'custom_attributes'   => array('min'=>'"1"'),
		'class'	  => 'input-text mwb_wpr_common_width',
		'desc_tip'=> __('Set the threshold for points expiration, The expiration period will be calculated when the threshold has been reached.', "ultimate-woocommerce-points-and-rewards"),
		),
	array(
		'title' => __('Set Expiration Time',"ultimate-woocommerce-points-and-rewards"), 
		'type'	=> 'number_text',
		'desc_tip' => __('Set the Time-Period for "When the points needs to get expired?" It will calculated over the above Threshold Time',"ultimate-woocommerce-points-and-rewards"),
		'number_text' => array(
			array(
				'type'	=> "number",
				'id'	=> "mwb_wpr_points_expiration_time_num",
				'class'	  => 'input-text mwb_wpr_common_width',
				'custom_attributes'=> array('min'=>'"1"'),
				'desc_tip'=> __('Set the Time-Period for "When the points needs to get expired?" It will calculated over the above Threshold Time', 
					"ultimate-woocommerce-points-and-rewards"),
				),
			array( 
				'id' => 'mwb_wpr_points_expiration_time_drop',
				'type' => 'search&select',
				'desc_tip' =>  __('Select those categories which you want to allow to customers for purchase that product through points.', 'ultimate-woocommerce-points-and-rewards'),
				'options' => $settings_obj->mwb_wpr_get_option_of_points(),
				),
			),
		),
	array(
		'title' => __('Email Notification(Re-Notify Days)',"ultimate-woocommerce-points-and-rewards"), 
		'type'	=> 'text',
		'id'	=> 'mwb_wpr_points_expiration_email',
		'class'	=> 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'	=>	__('Days.',"ultimate-woocommerce-points-and-rewards"),
		'desc_tip'=>__('Set the number of days beofre the Email will get sent out.', "ultimate-woocommerce-points-and-rewards"),
		),
	array(
		'type' 	=> 'sectionend',
		),
	 array(
		'title' => __('ThankYou Page Message',"ultimate-woocommerce-points-and-rewards"), 
		'type'	=> 'title',
		),
	array(
		'title' => __('Enter the Message for notifying the user about they have reached their Threshold',"ultimate-woocommerce-points-and-rewards"), 
		'type'	=> 'textarea',
		'custom_attributes'=> array('cols'=>'"35"', 'rows'=>'"5"'),
		'id'	=> 'mwb_wpr_threshold_notif',
		'class'	=> 'input-text',
		'desc_tip'=>__('Entered Message will appears inside the Email Template for notifying the Customer that they have reached the Threshold now they should redeem their Points before it will get expired', "ultimate-woocommerce-points-and-rewards"),
		'default' =>  __('You have reached your Threshold and your Total Point is:',"ultimate-woocommerce-points-and-rewards").' [TOTALPOINT]'.__(',which will get expired on',"ultimate-woocommerce-points-and-rewards").'[EXPIRYDATE]',
		'desc2'	=>	__('Use these shortcodes for providing an appropriate message for your customers',"ultimate-woocommerce-points-and-rewards").__('for their Total Points [EXPIRYDATE] for the Expiration Date ',"ultimate-woocommerce-points-and-rewards"),
		),
	array(
		'title' => __('Re-notify Message before some days',"ultimate-woocommerce-points-and-rewards"), 
		'type'	=> 'textarea',
		'custom_attributes'=> array('cols'=>'"35"', 'rows'=>'"5"'),
		'id'	=> 'mwb_wpr_re_notification',
		'class'	=> 'input-text',
		'desc_tip'=>__('Entered Message will appears inside the Email Template for notifying the Customer that they have left just some days more before the expiration', "ultimate-woocommerce-points-and-rewards"),
		'default' =>  __('Do not forget to redeem your points',"ultimate-woocommerce-points-and-rewards").' [TOTALPOINT]'.__('before it will get expired on',"ultimate-woocommerce-points-and-rewards").'[EXPIRYDATE]',
		'desc2'	=>	__('Use these shortcodes for providing an appropriate message for your customers',"ultimate-woocommerce-points-and-rewards").__('for their Total Points [EXPIRYDATE] for the Expiration Date ',"ultimate-woocommerce-points-and-rewards"),
		),
	array(
		'title' => __('Message when Points has been Expired',"ultimate-woocommerce-points-and-rewards"), 
		'type'	=> 'textarea',
		'custom_attributes'=> array('cols'=>'"35"', 'rows'=>'"5"'),
		'id'	=> 'mwb_wpr_expired_notification',
		'class'	=> 'input-text',
		'desc_tip'=>__('Entered Message will appears inside the Email Template for notifying the Customer that the Points has been expired', "ultimate-woocommerce-points-and-rewards"),
		'default' =>  __('Your Points has been expired, you may earn more Points and use the benefit more',"ultimate-woocommerce-points-and-rewards"),
		'desc2'	=>	__('This mail will send when your points will get expired',"ultimate-woocommerce-points-and-rewards"),
		),
	array(
		'type' 	=> 'sectionend',
		),
	);
 	$mwb_points_exipration_array = apply_filters('mwb_wpr_points_exprition_settings',$mwb_points_exipration_array);
 	/*Save Settings*/
 	if(isset($_POST['mwb_wpr_save_point_expiration'])) {
 		if(wp_verify_nonce( $_POST['mwb-wpr-nonce'], 'mwb-wpr-nonce' )) {
 			?>
 			<?php
 			/* Save Settings and check is not empty*/
 			$postdata = $settings_obj->check_is_settings_is_not_empty($mwb_points_exipration_array,$_POST);
 			/* End of the save Settings and check is not empty*/
 			$general_settings_array = array();

 			foreach($postdata as $key=>$value){	
 				$general_settings_array[$key] = $value;
 			}
 			if (is_array($general_settings_array) && !empty($general_settings_array)) {
 				update_option('mwb_wpr_points_expiration_settings',$general_settings_array);
 			}
 			$settings_obj->mwb_wpr_settings_saved();
 			do_action('mwb_wpr_points_expiration_settings_save_option',$general_settings_array);
 		}
 	}
 	/*End of the save settings*/
	 $general_settings = get_option('mwb_wpr_points_expiration_settings',true);
	  ?>
	<?php if(!is_array($general_settings)): $general_settings = array(); endif;?>
	<div class="mwb_table">
		<div class="mwb_wpr_general_wrapper">
			 <?php 
			foreach ($mwb_points_exipration_array as $key => $value) {
				if ($value['type'] == "title") {
					?>
					<div class="mwb_wpr_general_row_wrap">
						<?php $settings_obj->mwb_rwpr_generate_heading($value);?>
				<?php }?>
				<?php if($value['type'] != "title" && $value['type'] != "sectionend") { ?>
				<div class="mwb_wpr_general_row">
					<?php $settings_obj->mwb_rwpr_generate_label($value);?>
					<div class="mwb_wpr_general_content">
						<?php 
						$settings_obj->mwb_rwpr_generate_tool_tip($value);
						if($value['type'] == "checkbox") {
							$settings_obj->mwb_rwpr_generate_checkbox_html($value,$general_settings);
						}
						if ($value['type'] == "number") {
							$settings_obj->mwb_rwpr_generate_number_html($value,$general_settings);
						}
						if ($value['type'] == "multiple_checkbox") {
							foreach ($value['multiple_checkbox'] as $k => $val) {
								$settings_obj->mwb_rwpr_generate_checkbox_html($val,$general_settings);
							}
						}
						if ($value['type'] == "text") {
							$settings_obj->mwb_rwpr_generate_text_html($value,$general_settings);
						}
						if ($value['type'] == "textarea") {
							$settings_obj->mwb_rwpr_generate_textarea_html($value,$general_settings);
						}
						if ($value['type'] == "number_text") {
							foreach ($value['number_text'] as $k => $val) {
								if ($val['type'] == 'number') {
									$settings_obj->mwb_rwpr_generate_number_html($val,$general_settings);
								}
								if ($val['type'] == 'search&select') {
									$settings_obj->mwb_wpr_generate_searchSelect_html($val,$general_settings);

								}
							}
						}
						?>
					</div>
				</div>
				<?php }?>
			<?php if ($value['type'] == "sectionend"):?>
				 </div>	
				<?php endif;?>
		<?php } ?> 		
		</div>
	</div>
	<div class="clear"></div>
	<p class="submit">
		<input type="submit" value='<?php _e("Save changes",MWB_RWPR_Domain); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_point_expiration">
	</p>