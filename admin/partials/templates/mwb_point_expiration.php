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
if ( isset( $_POST['mwb_wpr_save_point_expiration'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( isset( $_POST['mwb_wpr_points_expiration_enable'] ) ) {//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$_POST['mwb_wpr_points_expiration_enable'] = 'on';
	} else {
		$_POST['mwb_wpr_points_expiration_enable'] = 'off';
	}
	if ( isset( $_POST['mwb_wpr_points_exp_onmyaccount'] ) ) {//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$_POST['mwb_wpr_points_exp_onmyaccount'] = 'on';
	} else {
		$_POST['mwb_wpr_points_exp_onmyaccount'] = 'off';
	}
	$postdata = $_POST;//phpcs:ignore WordPress.Security.NonceVerification.Missing
	foreach ( $postdata as $key => $value ) {
		$value = stripcslashes( $value );
		$value = sanitize_text_field( $value );
		update_option( $key, $value );
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php esc_html_e( 'Settings saved.', 'rewardeem-woocommerce-points-rewards' ); ?></strong></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notices.', 'rewardeem-woocommerce-points-rewards' ); ?></span>
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
					<label for="mwb_wpr_points_expiration_enable"><?php esc_html_e( 'Enable Points Expiration ', 'rewardeem-woocommerce-points-rewards' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this, If you want to set the expiration period for the Rewarded Points', 'rewardeem-woocommerce-points-rewards' );
					echo wc_help_tip( $attribute_description ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<label for="mwb_wpr_points_expiration_enable">
						<input type="checkbox" <?php echo ( 'on' == $mwb_wpr_points_expiration_enable ) ? "checked='checked'" : ''; ?> name="mwb_wpr_points_expiration_enable" id="mwb_wpr_points_expiration_enable" class="input-text"> <?php esc_html_e( 'Enable Point Expiration', 'rewardeem-woocommerce-points-rewards' ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_exp_onmyaccount"><?php esc_html_e( 'Show Points expiration on Myaccount Page', 'rewardeem-woocommerce-points-rewards' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this, If you want to show the expiration period for the Rewarded Points on Myaccount Page', 'rewardeem-woocommerce-points-rewards' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<label for="mwb_wpr_points_exp_onmyaccount">
						<input type="checkbox" <?php echo ( 'on' == $mwb_wpr_points_exp_onmyaccount ) ? "checked='checked'" : ''; ?> name="mwb_wpr_points_exp_onmyaccount" id="mwb_wpr_points_exp_onmyaccount" class="input-text"> <?php _e( 'Expiartion willl get displayed just below the Total Point', 'rewardeem-woocommerce-points-rewards' ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_threshold"><?php esc_html_e( 'Set the Required Threshold', 'rewardeem-woocommerce-points-rewards' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = esc_html__( 'Set the threshold for points expiration, The expiration period will be calculated when the threshold has been reached', 'rewardeem-woocommerce-points-rewards' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<label for="mwb_wpr_points_expiration_threshold">
						<input type="number" min="1" name="mwb_wpr_points_expiration_threshold" id="mwb_wpr_points_expiration_threshold" value="<?php echo esc_html( $mwb_wpr_points_expiration_threshold ); ?>" class="input-text mwb_wpr_common_width">
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_time_num"><?php esc_html_e( 'Set Expiration Time', 'rewardeem-woocommerce-points-rewards' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Set the Time-Period for "When the points needs to get expired?" It will calculated over the above Threshold Time', 'rewardeem-woocommerce-points-rewards' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
						<input type="number" min="1" value="<?php echo esc_html( $mwb_wpr_points_expiration_time_num ); ?>" name="mwb_wpr_points_expiration_time_num" id="mwb_wpr_points_expiration_time_num" class="input-text mwb_wpr_common_width">
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
												><?php esc_html_e( 'Days', 'rewardeem-woocommerce-points-rewards' ); ?></option>
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
													><?php esc_html_e( 'Weeks', 'rewardeem-woocommerce-points-rewards' ); ?></option>
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
													><?php esc_html_e( 'Months', 'rewardeem-woocommerce-points-rewards' ); ?></option>
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
													><?php esc_html_e( 'Years', 'rewardeem-woocommerce-points-rewards' ); ?></option>	
						</select>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_email"><?php esc_html_e( 'Email Notification(Re-Notify Days)', 'rewardeem-woocommerce-points-rewards' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Set the number of days beofre the Email will get sent out', 'rewardeem-woocommerce-points-rewards' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<label for="mwb_wpr_points_expiration_email">
						<input type="number" min="1" name="mwb_wpr_points_expiration_email" id="mwb_wpr_points_expiration_email" value="<?php echo esc_html( $mwb_wpr_points_expiration_email ); ?>" class="input-text mwb_wpr_common_width">  Days
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_threshold_notif"><?php esc_html_e( 'Enter the Message for notifying the user about they have reached their Threshold', 'rewardeem-woocommerce-points-rewards' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears inside the Email Template for notifying the Customer that they have reached the Threshold now they should redeem their Points before it will get expired', 'rewardeem-woocommerce-points-rewards' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_threshold_notif" id="mwb_wpr_threshold_notif" class="input-text" ><?php echo esc_html( $mwb_wpr_threshold_notif ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Use these shortcodes for providing an appropriate message for your customers ', 'rewardeem-woocommerce-points-rewards' ); ?>
														<?php
														echo '[TOTALPOINT]';
														esc_html_e( ' for their Total Points ', 'rewardeem-woocommerce-points-rewards' );
														echo ' [EXPIRYDATE]';
														esc_html_e( ' for the Expiration Date ', 'rewardeem-woocommerce-points-rewards' );
														?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_re_notification"><?php esc_html_e( 'Re-notify Message before some days', 'rewardeem-woocommerce-points-rewards' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears inside the Email Template for notifying the Customer that they have left just some days more before the expiration', 'rewardeem-woocommerce-points-rewards' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_re_notification" id="mwb_wpr_re_notification" class="input-text" ><?php echo $mwb_wpr_re_notification; ?></textarea>
					<p class="description"><?php esc_html_e( 'Use these shortcodes for providing an appropriate message for your customers ', 'rewardeem-woocommerce-points-rewards' ); ?>
														<?php
														echo '[TOTALPOINT]';
														esc_html_e( ' for their Total Points ', 'rewardeem-woocommerce-points-rewards' );
														echo ' [EXPIRYDATE]';
														esc_html_e( ' for the Expiration Date ', 'rewardeem-woocommerce-points-rewards' );
														?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_expired_notification"><?php esc_html_e( 'Message when Points has been Expired', 'rewardeem-woocommerce-points-rewards' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears inside the Email Template for notifying the Customer that the Points has been expired', 'rewardeem-woocommerce-points-rewards' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_expired_notification" id="mwb_wpr_expired_notification" class="input-text" ><?php echo esc_html( $mwb_wpr_expired_notification ); ?></textarea>
				</td>
			</tr>
		</tbody>
	</table>	
</div>
<div class="clear"></div>
<p class="submit">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'rewardeem-woocommerce-points-rewards' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_point_expiration">
</p>
