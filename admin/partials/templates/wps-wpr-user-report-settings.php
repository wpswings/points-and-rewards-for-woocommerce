<?php
/**
 * Exit if accessed directly
 *
 * @since      1.0.0
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_GET['wps_reports_userid'] ) ) {

	$user_id   = ! empty( $_GET['wps_reports_userid'] ) ? sanitize_text_field( wp_unslash( $_GET['wps_reports_userid'] ) ) : '';
}
?>
<div class="wps-wpg-gen-section-form-container">
	<div class="wpg-secion-wrap">
		<h3><?php esc_html_e( 'Points Report', 'points-and-rewards-for-woocommerce' ); ?></h3>
		<div id="react-app"></div>
	</div>
	<input type="hidden" id="wps_reports_userid" name="wps_reports_userid" value="<?php echo esc_attr( $user_id ); ?>"  >     

</div>  


