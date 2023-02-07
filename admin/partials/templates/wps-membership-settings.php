<?php
/**
 * Membership Settings Template for creating the setting
 *
 * Membership Settings Template
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Check the allowed the html in for the html.
 *
 * @name wps_wpr_allowed_html
 * @since      1.0.0
 */
function wps_wpr_allowed_html() {
	$allowed_tags = array(
		'span' => array(
			'class'    => array(),
			'title'    => array(),
			'style'    => array(),
			'data-tip' => array(),
		),
	);
	return $allowed_tags;
}

$current_tab = 'wps_wpr_membership_tab';
include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

if ( isset( $_POST['wps_wpr_save_membership'] ) && isset( $_POST['wps-wpr-nonce'] ) ) {
	$wps_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['wps-wpr-nonce'] ) );
	unset( $_POST['wps_wpr_save_membership'] );

	if ( wp_verify_nonce( $wps_wpr_nonce, 'wps-wpr-nonce' ) ) {
		if ( 'wps_wpr_membership_tab' == $current_tab ) {

			$membership_settings_array = array();
			$membership_roles_list     = array();
			$wps_wpr_no_of_section     = isset( $_POST['hidden_count'] ) ? sanitize_text_field( wp_unslash( $_POST['hidden_count'] ) ) : 0;
			$wps_wpr_mem_enable        = isset( $_POST['wps_wpr_membership_setting_enable'] ) ? 1 : 0;
			$exclude_sale_product      = isset( $_POST['exclude_sale_product'] ) ? 1 : 0;
			if ( isset( $wps_wpr_no_of_section ) ) {

				$count                    = $wps_wpr_no_of_section;
				$wps_wpr_membersip_roles  = isset( $_POST[ 'wps_wpr_membership_level_name_' . $count ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wps_wpr_membership_level_name_' . $count ] ) ) : '';
				$wps_wpr_membersip_points = isset( $_POST[ 'wps_wpr_membership_level_value_' . $count ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wps_wpr_membership_level_value_' . $count ] ) ) : '';
				$wps_wpr_categ_list       = ( isset( $_POST[ 'wps_wpr_membership_category_list_' . $count ] ) && ! empty( $_POST[ 'wps_wpr_membership_category_list_' . $count ] ) ) ? map_deep( wp_unslash( $_POST[ 'wps_wpr_membership_category_list_' . $count ] ), 'sanitize_text_field' ) : '';
				$wps_wpr_prod_list        = ( isset( $_POST[ 'wps_wpr_membership_product_list_' . $count ] ) && ! empty( $_POST[ 'wps_wpr_membership_product_list_' . $count ] ) ) ? map_deep( wp_unslash( $_POST[ 'wps_wpr_membership_product_list_' . $count ] ), 'sanitize_text_field' ) : '';
				$wps_wpr_discount         = ( isset( $_POST[ 'wps_wpr_membership_discount_' . $count ] ) && ! empty( $_POST[ 'wps_wpr_membership_discount_' . $count ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'wps_wpr_membership_discount_' . $count ] ) ) : '';
				$wps_wpr_expnum           = isset( $_POST[ 'wps_wpr_membership_expiration_' . $count ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wps_wpr_membership_expiration_' . $count ] ) ) : '';
				$wps_wpr_expdays          = isset( $_POST[ 'wps_wpr_membership_expiration_days_' . $count ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wps_wpr_membership_expiration_days_' . $count ] ) ) : '';

				if ( isset( $wps_wpr_membersip_roles ) && ! empty( $wps_wpr_membersip_roles ) ) {
					$membership_roles_list[ $wps_wpr_membersip_roles ] = array(
						'Points'     => $wps_wpr_membersip_points,
						'Prod_Categ' => $wps_wpr_categ_list,
						'Product'    => $wps_wpr_prod_list,
						'Discount'   => $wps_wpr_discount,
						'Exp_Number' => $wps_wpr_expnum,
						'Exp_Days'   => $wps_wpr_expdays,
					);
				}
			}
			$membership_settings_array['wps_wpr_membership_setting_enable'] = $wps_wpr_mem_enable;
			$membership_settings_array['membership_roles']                  = $membership_roles_list;
			$membership_settings_array['exclude_sale_product']              = $exclude_sale_product;
			if ( is_array( $membership_settings_array ) ) {
				update_option( 'wps_wpr_membership_settings', $membership_settings_array );
			}
			do_action( 'wps_wpr_save_membership_settings', $wps_wpr_no_of_section );
		}
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?php esc_html_e( 'Settings saved.', 'points-and-rewards-for-woocommerce' ); ?></strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'points-and-rewards-for-woocommerce' ); ?></span>
			</button>
		</div>
		<?php
	}
}
if ( isset( $_GET['action'] ) && 'view_membership_log' == $_GET['action'] ) {

	include_once WPS_RWPR_DIR_PATH . '/admin/partials/templates/class-membership-log-list-table.php';
} else {

	$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
	$wps_wpr_membership_roles  = array();
	?>
	<?php
	if ( ! is_array( $membership_settings_array ) ) :
		$membership_settings_array = array();
	endif;
	$wpssetexpiry             = get_option( 'wps_wpr_set_expiry_for_old_users', false );
	$wps_wpr_membership_roles = ( isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ) ? $membership_settings_array['membership_roles'] : array();
	?>
	<?php

	$wps_wpr_settings = array(
		array(
			'title'          => __( 'Enable Membership', 'points-and-rewards-for-woocommerce' ),
			'id'             => 'wps_wpr_membership_setting_enable',
			'type'           => 'checkbox',
			'class'          => 'input-text',
			'desc'           => __( 'Enable Membership', 'points-and-rewards-for-woocommerce' ),
			'desc_tip'       => __( 'Check this box to enable the Membership Feature', 'points-and-rewards-for-woocommerce' ),
			'memebrship_log' => true,
		),
		array(
			'title'    => __( 'Exclude Sale Products', 'points-and-rewards-for-woocommerce' ),
			'id'       => 'exclude_sale_product',
			'type'     => 'checkbox',
			'class'    => 'input-text',
			'desc'     => __( 'Exclude Sale Products for Membership Discount', 'points-and-rewards-for-woocommerce' ),
			'desc_tip' => __( 'Check this box to do not apply the membership discount on sale products', 'points-and-rewards-for-woocommerce' ),
		),
		array(
			'title'         => __( 'Create Member', 'points-and-rewards-for-woocommerce' ),
			'id'            => 'wps_wpr_membership_create_section',
			'type'          => 'create_member',
			'class'         => 'parent_of_div',
			'create_member' => array(),
		),
	);

	?>
	<?php do_action( 'wps_wpr_add_notice' ); ?>
	<div class="wps_wpr_wrap_table">
		<table class="form-table wps_wpr_membership_setting mwp_wpr_settings">
			<tbody>
				<?php foreach ( $wps_wpr_settings as $key => $value ) { ?>
				<tr valign="top">
					<th scope="row" class="wps-wpr-titledesc">
						<?php
						$settings_obj->wps_rwpr_generate_label( $value );
						?>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags = wps_wpr_allowed_html();
						echo array_key_exists( 'desc_tip', $value ) ? wp_kses( wc_help_tip( $value['desc_tip'] ), $allowed_tags ) : '';//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						if ( 'checkbox' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_checkbox_html( $value, $membership_settings_array );
						}
						if ( array_key_exists( 'memebrship_log', $value ) ) {
							?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-rwpr-setting&tab=membership&action=view_membership_log' ) ); ?>" class="wps_wpr_membership_log"><?php esc_html_e( 'Membership Log', 'points-and-rewards-for-woocommerce' ); ?></a>
							<?php
						}
						if ( 'create_member' == $value['type'] ) {
							do_action( 'wps_wpr_add_membership_rule', $wps_wpr_membership_roles );
							?>
							<p class= "description"><?php esc_html_e( 'Please do not change the "Level Name" once it will be saved, as it became the key for the Membership User', 'points-and-rewards-for-woocommerce' ); ?></p>
							<p class="wps_wpr_repeat_button_wrap"><input type="button" value='<?php esc_html_e( 'Add Another Level', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_repeat_button"></p>

							<?php
						}
						do_action( 'wps_wpr_additional_membership_settings', $value, $membership_settings_array );
						?>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<p class="submit">
		<input type="submit" value='<?php esc_attr_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_save_membership">
	</p>
	<?php
}
