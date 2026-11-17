<?php
/**
 * Points and rewards email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/wps-wpr-email-notification-template.php.
 *
 * @package    points-and-rewards-for-wooCommerce
 * @author  WP Swings <webmaster@wpswings.com>
 * @since      1.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
echo wp_strip_all_tags( $email_heading ) . "\n\n"; // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain-text email.
$email_content = apply_filters( 'wps_wpr_preview_email_content', $email_content );
?>

<?php
// Plain-text body: keep link targets, turn line breaks into new lines and drop all markup.
$email_text = preg_replace( '/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', '$2 ($1)', (string) $email_content );
$email_text = preg_replace( '/<br\s*\/?>|<\/p>|<\/div>|<\/li>|<\/h[1-6]>/i', "\n", $email_text );
$email_text = wp_strip_all_tags( $email_text );
$email_text = html_entity_decode( $email_text, ENT_QUOTES, get_bloginfo( 'charset' ) );
$email_text = preg_replace( "/\n{3,}/", "\n\n", $email_text );

echo trim( $email_text ) . "\n\n"; // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain-text email, markup already stripped.

echo wp_strip_all_tags( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain-text email.
