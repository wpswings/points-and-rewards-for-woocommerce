<?php
/**
 * Points and rewards email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/mwb-wpr-email-notification-template.php.
 *
 * @package    points-and-rewards-for-wooCommerce
 * @author  makewebbetter<ticket@makewebbetter.com>
 * @since      1.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
echo esc_html( $email_heading ) . "\n\n"; // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php
$template = '<table class="mwb_wuc_email_template" style="width: 100%!important; max-width: 600px; text-align: center; font-size: 20px;" role="presentation" border="0" width="600" cellspacing="0" cellpadding="0" align="center">
	<tbody>
		<tr>
			<td style="background: #fff;">
				<table style="border: 2px dashed #b9aca1;" border="0" width="100%" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td colspan="2">
								<div style="text-align: center;"><span style="display: inline-block;padding: 5px 15px; margin-bottom: 10px; background-color: rgba(241, 225, 225, 0.12); font-weight: bold;">' . esc_html( $email_content ) . '</span></div>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>';

echo wp_kses_post( html_entity_decode( $template ) ); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped


echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
