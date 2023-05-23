<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/includes/extra-template
 */

?>
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	global $pagenow;
if ( empty( $pagenow ) || 'plugins.php' != $pagenow ) {
	return false;
}

	$form_fields = apply_filters( 'wps_deactivation_form_fields', array() );
?>
<?php if ( ! empty( $form_fields ) ) : ?>
	<div style="display: none;" class="loading-style-bg" id="wps_wpr_loader">
		<img src="<?php echo esc_url( WPS_RWPR_DIR_URL ); ?>public/images/loading.gif">
	</div>
	<div class="wps-onboarding-section">
		<div class="wps-on-boarding-wrapper-background">
		<div class="wps-on-boarding-wrapper">
			<div class="wps-on-boarding-close-btn">
				<a href="javascript:void(0);">
					<span class="close-form">x</span>
				</a>
			</div>
			<h3 class="wps-on-boarding-heading"></h3>
			<p class="wps-on-boarding-desc"><?php esc_html_e( 'May we have a little info about why you are deactivating?', 'points-and-rewards-for-woocommerce' ); ?></p>
			<form action="#" method="post" class="wps-on-boarding-form">
				<?php foreach ( $form_fields as $key => $field_attr ) : ?>
					<?php $this->render_field_html( $field_attr, 'deactivating' ); ?>
				<?php endforeach; ?>
				<div class="wps-on-boarding-form-btn__wrapper">
					<div class="wps-on-boarding-form-submit wps-on-boarding-form-verify ">
					<input type="submit" class="wps-on-boarding-submit wps-on-boarding-verify " value="SUBMIT AND DEACTIVATE">
				</div>
				<div class="wps-on-boarding-form-no_thanks">
					<a href="javascript:void(0);" class="wps-deactivation-no_thanks"><?php esc_html_e( 'Skip and Deactivate Now', 'points-and-rewards-for-woocommerce' ); ?></a>
				</div>
				</div>
			</form>
		</div>
	</div>
	</div>
<?php endif; ?>
