<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
 * Points Expiration Template
 */
$current_tab = 'mwb_wpr_point_expiration_tab';

if ( isset( $_GET['tab'] ) ) {
	$current_tab = $_GET['tab'];
}
if ( isset( $_POST['mwb_wpr_save_point_expiration'] ) ) {
	if ( isset( $_POST['mwb_wpr_points_expiration_enable'] ) ) {
		$_POST['mwb_wpr_points_expiration_enable'] = 'on';
	} else {
		$_POST['mwb_wpr_points_expiration_enable'] = 'off';
	}
	if ( isset( $_POST['mwb_wpr_points_exp_onmyaccount'] ) ) {
		$_POST['mwb_wpr_points_exp_onmyaccount'] = 'on';
	} else {
		$_POST['mwb_wpr_points_exp_onmyaccount'] = 'off';
	}
	$postdata = $_POST;
	foreach ( $postdata as $key => $value ) {
		$value = stripcslashes( $value );
		$value = sanitize_text_field( $value );
		update_option( $key, $value );
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php _e( 'Settings saved.', MWB_WPR_Domain ); ?></strong></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php _e( 'Dismiss this notices.', MWB_WPR_Domain ); ?></span>
		</button>
	</div>
	<?php
}
$mwb_wpr_points_expiration_enable = get_option( 'mwb_wpr_points_expiration_enable', 'off' );
$mwb_wpr_points_exp_onmyaccount = get_option( 'mwb_wpr_points_exp_onmyaccount', 'off' );
$mwb_wpr_points_expiration_threshold = get_option( 'mwb_wpr_points_expiration_threshold', '' );
$mwb_wpr_points_expiration_time_num = get_option( 'mwb_wpr_points_expiration_time_num', '' );
$mwb_wpr_points_expiration_time_drop = get_option( 'mwb_wpr_points_expiration_time_drop', 'days' );
$mwb_wpr_points_expiration_email = get_option( 'mwb_wpr_points_expiration_email', 7 );
$mwb_wpr_threshold_notif = get_option( 'mwb_wpr_threshold_notif', 'You have reached your Threshold and your Total Point is: [TOTALPOINT], which will get expired on [EXPIRYDATE]' );
$mwb_wpr_re_notification = get_option( 'mwb_wpr_re_notification', 'Do not forget to redeem your points([TOTALPOINT]) before it will get expired on [EXPIRYDATE]' );
$mwb_wpr_expired_notification = get_option( 'mwb_wpr_expired_notification', 'Your Points has been expired, you may earn more Points and use the benefit more' );
?>
<div class="mwb_table">
	<table class="form-table mwb_wpr_points_expiration mwp_wpr_settings">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_enable"><?php _e( 'Enable Points Expiration ', MWB_WPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this, If you want to set the expiration period for the Rewarded Points', MWB_WPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_points_expiration_enable">
						<input type="checkbox" <?php echo ( $mwb_wpr_points_expiration_enable == 'on' ) ? "checked='checked'" : ''; ?> name="mwb_wpr_points_expiration_enable" id="mwb_wpr_points_expiration_enable" class="input-text"> <?php _e( 'Enable Point Expiration', MWB_WPR_Domain ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_exp_onmyaccount"><?php _e( 'Show Points expiration on Myaccount Page', MWB_WPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this, If you want to show the expiration period for the Rewarded Points on Myaccount Page', MWB_WPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_points_exp_onmyaccount">
						<input type="checkbox" <?php echo ( $mwb_wpr_points_exp_onmyaccount == 'on' ) ? "checked='checked'" : ''; ?> name="mwb_wpr_points_exp_onmyaccount" id="mwb_wpr_points_exp_onmyaccount" class="input-text"> <?php _e( 'Expiartion willl get displayed just below the Total Point', MWB_WPR_Domain ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_threshold"><?php _e( 'Set the Required Threshold', MWB_WPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Set the threshold for points expiration, The expiration period will be calculated when the threshold has been reached', MWB_WPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_points_expiration_threshold">
						<input type="number" min="1" name="mwb_wpr_points_expiration_threshold" id="mwb_wpr_points_expiration_threshold" value="<?php echo $mwb_wpr_points_expiration_threshold; ?>" class="input-text mwb_wpr_common_width">
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_time_num"><?php _e( 'Set Expiration Time', MWB_WPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Set the Time-Period for "When the points needs to get expired?" It will calculated over the above Threshold Time', MWB_WPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
						<input type="number" min="1" value="<?php echo $mwb_wpr_points_expiration_time_num; ?>" name="mwb_wpr_points_expiration_time_num" id="mwb_wpr_points_expiration_time_num" class="input-text mwb_wpr_common_width">
						<select id="mwb_wpr_points_expiration_time_drop" name="mwb_wpr_points_expiration_time_drop" style="width: 10%;">
							<option value="days"
							<?php
							if ( isset( $mwb_wpr_points_expiration_time_drop ) ) {
								if ( $mwb_wpr_points_expiration_time_drop == 'days' ) {
									?>
								selected="selected"
									<?php
								}
							}
							?>
												><?php _e( 'Days', MWB_WPR_Domain ); ?></option>
							<option value="weeks"
							<?php
							if ( isset( $mwb_wpr_points_expiration_time_drop ) ) {
								if ( $mwb_wpr_points_expiration_time_drop == 'weeks' ) {
									?>
								selected="selected"
									<?php
								}
							}
							?>
													><?php _e( 'Weeks', MWB_WPR_Domain ); ?></option>
							<option value="months"
							<?php
							if ( isset( $mwb_wpr_points_expiration_time_drop ) ) {
								if ( $mwb_wpr_points_expiration_time_drop == 'months' ) {
									?>
								selected="selected"
									<?php
								}
							}
							?>
													><?php _e( 'Months', MWB_WPR_Domain ); ?></option>
							<option value="years"
							<?php
							if ( isset( $mwb_wpr_points_expiration_time_drop ) ) {
								if ( $mwb_wpr_points_expiration_time_drop == 'years' ) {
									?>
								selected="selected"
									<?php
								}
							}
							?>
													><?php _e( 'Years', MWB_WPR_Domain ); ?></option>	
						</select>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_email"><?php _e( 'Email Notification(Re-Notify Days)', MWB_WPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Set the number of days beofre the Email will get sent out', MWB_WPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_points_expiration_email">
						<input type="number" min="1" name="mwb_wpr_points_expiration_email" id="mwb_wpr_points_expiration_email" value="<?php echo $mwb_wpr_points_expiration_email; ?>" class="input-text mwb_wpr_common_width">  Days
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_threshold_notif"><?php _e( 'Enter the Message for notifying the user about they have reached their Threshold', MWB_WPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears inside the Email Template for notifying the Customer that they have reached the Threshold now they should redeem their Points before it will get expired', MWB_WPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_threshold_notif" id="mwb_wpr_threshold_notif" class="input-text" ><?php echo $mwb_wpr_threshold_notif; ?></textarea>
					<p class="description"><?php _e( 'Use these shortcodes for providing an appropriate message for your customers ', MWB_WPR_Domain ); ?>
														<?php
														echo '[TOTALPOINT]';
														_e( ' for their Total Points ', MWB_WPR_Domain );
														echo ' [EXPIRYDATE]';
														_e( ' for the Expiration Date ', MWB_WPR_Domain );
														?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_re_notification"><?php _e( 'Re-notify Message before some days', MWB_WPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears inside the Email Template for notifying the Customer that they have left just some days more before the expiration', MWB_WPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_re_notification" id="mwb_wpr_re_notification" class="input-text" ><?php echo $mwb_wpr_re_notification; ?></textarea>
					<p class="description"><?php _e( 'Use these shortcodes for providing an appropriate message for your customers ', MWB_WPR_Domain ); ?>
														<?php
														echo '[TOTALPOINT]';
														_e( ' for their Total Points ', MWB_WPR_Domain );
														echo ' [EXPIRYDATE]';
														_e( ' for the Expiration Date ', MWB_WPR_Domain );
														?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_expired_notification"><?php _e( 'Message when Points has been Expired', MWB_WPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears inside the Email Template for notifying the Customer that the Points has been expired', MWB_WPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_expired_notification" id="mwb_wpr_expired_notification" class="input-text" ><?php echo $mwb_wpr_expired_notification; ?></textarea>
				</td>
			</tr>
		</tbody>
	</table>	
</div>
<div class="clear"></div>
<p class="submit">
	<input type="submit" value='<?php _e( 'Save changes', MWB_WPR_Domain ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_point_expiration">
</p>
