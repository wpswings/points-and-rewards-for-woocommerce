<?php
/**
 * Points Expiration Template
 *
 * Points Expiration Template Template
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Rewardeem_woocommerce_Points_Rewards
 * @subpackage Rewardeem_woocommerce_Points_Rewards/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_tab = 'mwb_wpr_point_expiration_tab';

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
		<p><strong><?php esc_html_e( 'Settings saved.', 'points-rewards-for-woocommerce' ); ?></strong></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notices.', 'points-rewards-for-woocommerce' ); ?></span>
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
					<label for="mwb_wpr_points_expiration_enable"><?php esc_html_e( 'Enable Points Expiration ', 'points-rewards-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this, If you want to set the expiration period for the Rewarded Points', 'points-rewards-for-woocommerce' );
					echo wc_help_tip( $attribute_description ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<label for="mwb_wpr_points_expiration_enable">
						<input type="checkbox" <?php echo ( 'on' == $mwb_wpr_points_expiration_enable ) ? "checked='checked'" : ''; ?> name="mwb_wpr_points_expiration_enable" id="mwb_wpr_points_expiration_enable" class="input-text"> <?php esc_html_e( 'Enable Point Expiration', 'points-rewards-for-woocommerce' ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_exp_onmyaccount"><?php esc_html_e( 'Show Points expiration on Myaccount Page', 'points-rewards-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this, If you want to show the expiration period for the Rewarded Points on Myaccount Page', 'points-rewards-for-woocommerce' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<label for="mwb_wpr_points_exp_onmyaccount">
						<input type="checkbox" <?php echo ( 'on' == $mwb_wpr_points_exp_onmyaccount ) ? "checked='checked'" : ''; ?> name="mwb_wpr_points_exp_onmyaccount" id="mwb_wpr_points_exp_onmyaccount" class="input-text"> <?php esc_html_e( 'Expiartion willl get displayed just below the Total Point', 'points-rewards-for-woocommerce' ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_threshold"><?php esc_html_e( 'Set the Required Threshold', 'points-rewards-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = esc_html__( 'Set the threshold for points expiration, The expiration period will be calculated when the threshold has been reached', 'points-rewards-for-woocommerce' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<label for="mwb_wpr_points_expiration_threshold">
						<input type="number" min="1" name="mwb_wpr_points_expiration_threshold" id="mwb_wpr_points_expiration_threshold" value="<?php echo esc_html( $mwb_wpr_points_expiration_threshold ); ?>" class="input-text mwb_wpr_common_width">
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_time_num"><?php esc_html_e( 'Set Expiration Time', 'points-rewards-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Set the Time-Period for "When the points needs to get expired?" It will calculated over the above Threshold Time', 'points-rewards-for-woocommerce' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
						<input type="number" min="1" value="<?php echo esc_html( $mwb_wpr_points_expiration_time_num ); ?>" name="mwb_wpr_points_expiration_time_num" id="mwb_wpr_points_expiration_time_num" class="input-text mwb_wpr_common_width">
						<select id="mwb_wpr_points_expiration_time_drop" name="mwb_wpr_points_expiration_time_drop" style="width: 10%;">
							<option value="days"
							<?php
							if ( isset( $mwb_wpr_points_expiration_time_drop ) ) {
								if ( 'days' == $mwb_wpr_points_expiration_time_drop ) {
									?>
								selected="selected"
									<?php
								}
							}
							?>
												><?php esc_html_e( 'Days', 'points-rewards-for-woocommerce' ); ?></option>
							<option value="weeks"
							<?php
							if ( isset( $mwb_wpr_points_expiration_time_drop ) ) {
								if ( 'weeks' == $mwb_wpr_points_expiration_time_drop ) {
									?>
								selected="selected"
									<?php
								}
							}
							?>
													><?php esc_html_e( 'Weeks', 'points-rewards-for-woocommerce' ); ?></option>
							<option value="months"
							<?php
							if ( isset( $mwb_wpr_points_expiration_time_drop ) ) {
								if ( 'months' == $mwb_wpr_points_expiration_time_drop ) {
									?>
								selected="selected"
									<?php
								}
							}
							?>
													><?php esc_html_e( 'Months', 'points-rewards-for-woocommerce' ); ?></option>
							<option value="years"
							<?php
							if ( isset( $mwb_wpr_points_expiration_time_drop ) ) {
								if ( 'years' == $mwb_wpr_points_expiration_time_drop ) {
									?>
								selected="selected"
									<?php
								}
							}
							?>
													><?php esc_html_e( 'Years', 'points-rewards-for-woocommerce' ); ?></option>	
						</select>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_points_expiration_email"><?php esc_html_e( 'Email Notification(Re-Notify Days)', 'points-rewards-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Set the number of days beofre the Email will get sent out', 'points-rewards-for-woocommerce' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<label for="mwb_wpr_points_expiration_email">
						<input type="number" min="1" name="mwb_wpr_points_expiration_email" id="mwb_wpr_points_expiration_email" value="<?php echo esc_html( $mwb_wpr_points_expiration_email ); ?>" class="input-text mwb_wpr_common_width">  Days
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_threshold_notif"><?php esc_html_e( 'Enter the Message for notifying the user about they have reached their Threshold', 'points-rewards-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears inside the Email Template for notifying the Customer that they have reached the Threshold now they should redeem their Points before it will get expired', 'points-rewards-for-woocommerce' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_threshold_notif" id="mwb_wpr_threshold_notif" class="input-text" ><?php echo esc_html( $mwb_wpr_threshold_notif ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Use these shortcodes for providing an appropriate message for your customers ', 'points-rewards-for-woocommerce' ); ?>
														<?php
														echo '[TOTALPOINT]';
														esc_html_e( ' for their Total Points ', 'points-rewards-for-woocommerce' );
														echo ' [EXPIRYDATE]';
														esc_html_e( ' for the Expiration Date ', 'points-rewards-for-woocommerce' );
														?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_re_notification"><?php esc_html_e( 'Re-notify Message before some days', 'points-rewards-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears inside the Email Template for notifying the Customer that they have left just some days more before the expiration', 'points-rewards-for-woocommerce' );
					echo wc_help_tip( $attribute_description );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_re_notification" id="mwb_wpr_re_notification" class="input-text" ><?php echo esc_html( $mwb_wpr_re_notification ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Use these shortcodes for providing an appropriate message for your customers ', 'points-rewards-for-woocommerce' ); ?>
														<?php
														echo '[TOTALPOINT]';
														esc_html_e( ' for their Total Points ', 'points-rewards-for-woocommerce' );
														echo ' [EXPIRYDATE]';
														esc_html_e( ' for the Expiration Date ', 'points-rewards-for-woocommerce' );
														?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_expired_notification"><?php esc_html_e( 'Message when Points has been Expired', 'points-rewards-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears inside the Email Template for notifying the Customer that the Points has been expired', 'points-rewards-for-woocommerce' );
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
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_point_expiration">
</p>
