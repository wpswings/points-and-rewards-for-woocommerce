<?php
/**
 * This file is for license panel. Include this file if license is not validated.
 * If license is validated then show you setting page.
 * Otherwise show the same file.
 */
global $wp_version;
global $current_user; ?>
<h3><?php _e( 'Woocommerce Ultimate Points and Rewards', MWB_WPR_Domain ); ?> </h3>
<hr/>
<div style="text-align: justify; float: left; width: 66%; font-size: 16px; line-height: 25px; padding-right: 4%;">
<?php
 _e( 'This is the License Activation Panel. After purchasing plugin from Codecanyon you will get the purchase code of this plugin. Please verify your purchase below so that you can use feature of this plugin before 30 days of Activation.', MWB_WPR_Domain );
?>
 </div>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th class="titledesc" scope="row">
				<label><?php _e( 'Enter Purchase Code', MWB_WPR_Domain ); ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<input type="text" id="mwb_wpr_license_key" class="input-text regular-input" placeholder="Enter your Purchase code here...">
					<input type="button" value="Validate" class="button-primary" id="mwb_wpr_license_save">
					<img class="loading_image" src="<?php echo MWB_WPR_URL; ?>assets/images/loading.gif" style="height: 28px;vertical-align: middle;display:none;">
					<b class="licennse_notification"></b>
				</fieldset>
			</td>
		</tr>
	</tbody>
</table>
