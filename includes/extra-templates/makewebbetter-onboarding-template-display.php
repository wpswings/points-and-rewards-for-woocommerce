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

	$screen = get_current_screen();
	$is_valid = in_array( $screen->id, apply_filters( 'mwb_helper_valid_frontend_screens', array() ) );
if ( ! $is_valid ) {

	return false;
}

	$form_fields = apply_filters( 'mwb_on_boarding_form_fields', array() );

?>

<?php if ( ! empty( $form_fields ) ) : ?>
	<div style="display: none;" class="loading-style-bg" id="mwb_wpr_loader">
		<img src="<?php echo esc_url( MWB_RWPR_DIR_URL ); ?>public/images/loading.gif">
	</div>
	<div class="mwb-onboarding-section">
		<div class="mwb-on-boarding-wrapper-background">
		<div class="mwb-on-boarding-wrapper">
			<div class="mwb-on-boarding-close-btn">
				<a href="javascript:void(0);">
					<span class="close-form">x</span>
				</a>
			</div>
			<h3 class="mwb-on-boarding-heading"><?php esc_html_e( 'Welcome to MakeWebBetter', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<p class="mwb-on-boarding-desc"><?php esc_html_e( 'We love making new friends! Subscribe below and we promise to keep you up-to-date with our latest new plugins, updates, awesome deals and a few special offers.', 'points-and-rewards-for-woocommerce' ); ?></p>
			<form action="#" method="post" class="mwb-on-boarding-form">
				<?php foreach ( $form_fields as $key => $field_attr ) : ?>
					<?php $this->render_field_html( $field_attr ); ?>
				<?php endforeach; ?>
				<div class="mwb-on-boarding-form-btn__wrapper">
					<div class="mwb-on-boarding-form-submit mwb-on-boarding-form-verify ">
					<input type="submit" class="mwb-on-boarding-submit mwb-on-boarding-verify " value="<?php esc_attr_e( 'Send Us', 'points-and-rewards-for-woocommerce' ); ?>">
				</div>
				<div class="mwb-on-boarding-form-no_thanks">
					<a href="javascript:void(0);" class="mwb-on-boarding-no_thanks"><?php esc_html_e( 'Skip For Now', 'points-and-rewards-for-woocommerce' ); ?></a>
				</div>
				</div>
			</form>
		</div>
	</div>
	</div>
<?php endif; ?>
