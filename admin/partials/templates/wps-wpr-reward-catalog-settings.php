<?php
/**
 * This is the setting template
 *
 * Reward catalog settings.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

// Save reward catalog settings.
if ( isset( $_POST['wps_wpr_save_reward_catalog'], $_POST['wps_wpr_reward_catalog_nonce'] ) && current_user_can( 'manage_options' ) ) {
	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_wpr_reward_catalog_nonce'] ) ), 'wps-wpr-reward-catalog' ) ) {
		$wps_wpr_rc_enable  = ! empty( $_POST['wps_wpr_reward_catalog_enable'] ) ? 'yes' : 'no';
		$wps_wpr_rc_rewards = isset( $_POST['wps_wpr_rewards'] ) && is_array( $_POST['wps_wpr_rewards'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_rewards'] ), 'sanitize_text_field' ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field in wps_wpr_save_settings().
		// Keep line breaks in descriptions.
		if ( isset( $_POST['wps_wpr_rewards'] ) && is_array( $_POST['wps_wpr_rewards'] ) ) {
			foreach ( wp_unslash( $_POST['wps_wpr_rewards'] ) as $wps_wpr_rc_key => $wps_wpr_rc_raw ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( isset( $wps_wpr_rc_raw['description'], $wps_wpr_rc_rewards[ $wps_wpr_rc_key ] ) ) {
					$wps_wpr_rc_rewards[ $wps_wpr_rc_key ]['description'] = sanitize_textarea_field( $wps_wpr_rc_raw['description'] );
				}
			}
		}
		WPS_WPR_Reward_Catalog::wps_wpr_save_settings( $wps_wpr_rc_enable, $wps_wpr_rc_rewards );
		$settings_obj->wps_wpr_settings_saved();
		foreach ( WPS_WPR_Reward_Catalog::wps_wpr_get_save_errors() as $wps_wpr_rc_error ) {
			?>
			<div class="notice notice-error wps-wpr-rc-error"><p><?php echo esc_html( $wps_wpr_rc_error ); ?></p></div>
			<?php
		}
	}
}

$wps_wpr_rc_settings = WPS_WPR_Reward_Catalog::wps_wpr_get_settings();
$wps_wpr_rc_types    = WPS_WPR_Reward_Catalog::wps_wpr_get_reward_types();

/**
 * Render one reward row.
 *
 * @param string $index  row index.
 * @param array  $reward reward values.
 * @param array  $types  reward types.
 */
$wps_wpr_rc_row = static function ( $index, $reward, $types ) {
	$name    = 'wps_wpr_rewards[' . $index . ']';
	$product = ! empty( $reward['product_id'] ) ? wc_get_product( $reward['product_id'] ) : false;
	?>
	<tr class="wps-wpr-rc-row" data-type="<?php echo esc_attr( $reward['type'] ); ?>">
		<td>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[id]" value="<?php echo esc_attr( $reward['id'] ); ?>">
			<input type="text" class="wps-wpr-rc-title" name="<?php echo esc_attr( $name ); ?>[title]" value="<?php echo esc_attr( $reward['title'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Free T-shirt', 'points-and-rewards-for-woocommerce' ); ?>">
			<textarea class="wps-wpr-rc-description" name="<?php echo esc_attr( $name ); ?>[description]" rows="2" placeholder="<?php esc_attr_e( 'Short description (optional)', 'points-and-rewards-for-woocommerce' ); ?>"><?php echo esc_textarea( $reward['description'] ); ?></textarea>
		</td>
		<td><input type="number" min="1" class="wps-wpr-rc-points" name="<?php echo esc_attr( $name ); ?>[points]" value="<?php echo esc_attr( $reward['points'] ); ?>"></td>
		<td>
			<select class="wps-wpr-rc-type" name="<?php echo esc_attr( $name ); ?>[type]">
				<?php foreach ( $types as $type_key => $type_label ) : ?>
					<option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $reward['type'], $type_key ); ?>><?php echo esc_html( $type_label ); ?></option>
				<?php endforeach; ?>
			</select>
			<div class="wps-wpr-rc-amount-wrap">
				<label><?php esc_html_e( 'Discount value', 'points-and-rewards-for-woocommerce' ); ?>
					<input type="number" min="0" step="0.01" class="wps-wpr-rc-amount" name="<?php echo esc_attr( $name ); ?>[amount]" value="<?php echo esc_attr( $reward['amount'] ); ?>">
				</label>
			</div>
			<div class="wps-wpr-rc-product-wrap">
				<label><?php esc_html_e( 'Product', 'points-and-rewards-for-woocommerce' ); ?>
					<select class="wps-wpr-rc-product" name="<?php echo esc_attr( $name ); ?>[product_id]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'points-and-rewards-for-woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude_type="<?php echo esc_attr( implode( ',', WPS_WPR_Reward_Catalog::EXCLUDED_PRODUCT_TYPES ) ); ?>" data-allow_clear="true" style="width:100%;">
						<?php if ( $product ) : ?>
							<option value="<?php echo esc_attr( $product->get_id() ); ?>" selected="selected"><?php echo esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ); ?></option>
						<?php endif; ?>
					</select>
				</label>
				<span class="description"><?php esc_html_e( 'Simple products and single variations only.', 'points-and-rewards-for-woocommerce' ); ?></span>
			</div>
			<div class="wps-wpr-rc-shipping-wrap">
				<span class="description"><?php esc_html_e( 'Needs a Free shipping method set to "A valid free shipping coupon" in your shipping zones, otherwise the coupon does nothing.', 'points-and-rewards-for-woocommerce' ); ?></span>
			</div>
		</td>
		<td>
			<label><?php esc_html_e( 'Min. cart subtotal', 'points-and-rewards-for-woocommerce' ); ?>
				<input type="number" min="0" step="0.01" class="wps-wpr-rc-min" name="<?php echo esc_attr( $name ); ?>[min_amount]" value="<?php echo esc_attr( $reward['min_amount'] ); ?>">
			</label>
			<label><?php esc_html_e( 'Coupon expires after (days)', 'points-and-rewards-for-woocommerce' ); ?>
				<input type="number" min="0" class="wps-wpr-rc-expiry" name="<?php echo esc_attr( $name ); ?>[expiry_days]" value="<?php echo esc_attr( $reward['expiry_days'] ); ?>">
			</label>
			<label><?php esc_html_e( 'Redemptions per customer', 'points-and-rewards-for-woocommerce' ); ?>
				<input type="number" min="0" class="wps-wpr-rc-limit" name="<?php echo esc_attr( $name ); ?>[user_limit]" value="<?php echo esc_attr( $reward['user_limit'] ); ?>">
			</label>
		</td>
		<td>
			<label><input type="checkbox" class="wps-wpr-rc-active" name="<?php echo esc_attr( $name ); ?>[active]" value="yes" <?php checked( $reward['active'], 'yes' ); ?>> <?php esc_html_e( 'Active', 'points-and-rewards-for-woocommerce' ); ?></label>
			<button type="button" class="button-link button-link-delete wps-wpr-rc-remove"><?php esc_html_e( 'Remove', 'points-and-rewards-for-woocommerce' ); ?></button>
		</td>
	</tr>
	<?php
};
?>
<style>
	.wps-wpr-rc-table td { vertical-align: top; }
	.wps-wpr-rc-table label { display: block; margin-bottom: 6px; }
	.wps-wpr-rc-table input[type="text"], .wps-wpr-rc-table textarea { width: 100%; }
	.wps-wpr-rc-table input[type="number"] { max-width: 120px; }
	.wps-wpr-rc-row:not([data-type="fixed_cart"]):not([data-type="percent"]) .wps-wpr-rc-amount-wrap { display: none; }
	.wps-wpr-rc-row:not([data-type="free_product"]) .wps-wpr-rc-product-wrap { display: none; }
	.wps-wpr-rc-row:not([data-type="free_shipping"]) .wps-wpr-rc-shipping-wrap { display: none; }
</style>
<div class="wps_wpr_reward_catalog_main_wrappers">
	<main class="wps_wpr_main_reward_catalog_wrapper">
		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Reward Catalog', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content">
				<input type="hidden" name="wps_wpr_reward_catalog_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wps-wpr-reward-catalog' ) ); ?>">
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_reward_catalog_enable" class="wps_wpr_general_label"><?php esc_html_e( 'Enable Reward Catalog', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_reward_catalog_enable" id="wps_wpr_reward_catalog_enable" value="yes" <?php checked( $wps_wpr_rc_settings['enable'], 'yes' ); ?>>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Show a Rewards section on the My Account points page where customers spend points on the rewards below. Each redemption creates a single-use coupon for that customer.', 'points-and-rewards-for-woocommerce' ); ?></span>
						<p class="description"><?php esc_html_e( 'Coupons are locked to the customer\'s account email at the time of redemption. Coupons already issued stay valid when a reward is deactivated or removed; to cancel one, delete it under Marketing > Coupons (points are not refunded automatically). Removing a reward and adding it again resets its per-customer limit.', 'points-and-rewards-for-woocommerce' ); ?></p>
					</div>
				</article>
				<table class="widefat striped wps-wpr-rc-table" id="wps-wpr-rc-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Reward', 'points-and-rewards-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Points cost', 'points-and-rewards-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Type', 'points-and-rewards-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Rules', 'points-and-rewards-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Status', 'points-and-rewards-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$wps_wpr_rc_index = 0;
						foreach ( $wps_wpr_rc_settings['rewards'] as $wps_wpr_rc_reward ) {
							$wps_wpr_rc_row( $wps_wpr_rc_index, $wps_wpr_rc_reward, $wps_wpr_rc_types );
							++$wps_wpr_rc_index;
						}
						?>
					</tbody>
				</table>
				<p><button type="button" class="button" id="wps-wpr-rc-add" data-next-index="<?php echo esc_attr( $wps_wpr_rc_index ); ?>"><?php esc_html_e( 'Add reward', 'points-and-rewards-for-woocommerce' ); ?></button></p>
				<script type="text/template" id="wps-wpr-rc-row-template">
					<?php $wps_wpr_rc_row( '__INDEX__', WPS_WPR_Reward_Catalog::wps_wpr_get_reward_defaults(), $wps_wpr_rc_types ); ?>
				</script>
			</div>
		</section>
	</main>
	<input type="submit" name="wps_wpr_save_reward_catalog" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_save_reward_catalog" value="<?php esc_attr_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
</div>
