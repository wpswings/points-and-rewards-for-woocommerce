<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
 * Membership Settings Template
 */
$current_tab = 'mwb_wpr_membership_tab';
include_once MWB_RWPR_DIR_PATH . '/admin/partials/settings/class-rewardeem-wocoommerce-points-rewards-settings.php';
$settings_obj = new Rewardeem_woocommerce_Points_Rewards_Admin_settings();
if ( isset( $_POST['mwb_wpr_save_membership'] ) ) {
	unset( $_POST['mwb_wpr_save_membership'] );
	if ( wp_verify_nonce( $_POST['mwb-wpr-nonce'], 'mwb-wpr-nonce' ) ) {
		if ( 'mwb_wpr_membership_tab' == $current_tab ) {
			$membership_settings_array = array();
			$membership_roles_list = array();
			$mwb_wpr_no_of_section = isset( $_POST['hidden_count'] ) ? $_POST['hidden_count'] : 0;
			$mwb_wpr_mem_enable = isset( $_POST['mwb_wpr_membership_setting_enable'] ) ? 1 : 0;
			$exclude_sale_product = isset( $_POST['exclude_sale_product'] ) ? 1 : 0;
			if ( isset( $mwb_wpr_no_of_section ) ) {
				$count = $mwb_wpr_no_of_section;
				$mwb_wpr_membersip_roles = isset( $_POST[ 'mwb_wpr_membership_level_name_' . $count ] ) ? $_POST[ 'mwb_wpr_membership_level_name_' . $count ] : '';
				$mwb_wpr_membersip_roles = preg_replace( '/\s+/', '', $mwb_wpr_membersip_roles );
				$mwb_wpr_membersip_points = isset( $_POST[ 'mwb_wpr_membership_level_value_' . $count ] ) ? $_POST[ 'mwb_wpr_membership_level_value_' . $count ] : '';
				$mwb_wpr_categ_list = ( isset( $_POST[ 'mwb_wpr_membership_category_list_' . $count ] ) && ! empty( $_POST[ 'mwb_wpr_membership_category_list_' . $count ] ) ) ? $_POST[ 'mwb_wpr_membership_category_list_' . $count ] : '';
				$mwb_wpr_prod_list = ( isset( $_POST[ 'mwb_wpr_membership_product_list_' . $count ] ) && ! empty( $_POST[ 'mwb_wpr_membership_product_list_' . $count ] ) ) ? $_POST[ 'mwb_wpr_membership_product_list_' . $count ] : '';
				$mwb_wpr_discount = ( isset( $_POST[ 'mwb_wpr_membership_discount_' . $count ] ) && ! empty( $_POST[ 'mwb_wpr_membership_discount_' . $count ] ) ) ? $_POST[ 'mwb_wpr_membership_discount_' . $count ] : '';
				$mwb_wpr_expnum = isset( $_POST[ 'mwb_wpr_membership_expiration_' . $count ] ) ? $_POST[ 'mwb_wpr_membership_expiration_' . $count ] : '';
				$mwb_wpr_expdays = isset( $_POST[ 'mwb_wpr_membership_expiration_days_' . $count ] ) ? $_POST[ 'mwb_wpr_membership_expiration_days_' . $count ] : '';

				if ( isset( $mwb_wpr_membersip_roles ) && ! empty( $mwb_wpr_membersip_roles ) ) {
					$membership_roles_list[ $mwb_wpr_membersip_roles ] = array(
						'Points' => $mwb_wpr_membersip_points,
						'Prod_Categ' => $mwb_wpr_categ_list,
						'Product' => $mwb_wpr_prod_list,
						'Discount' => $mwb_wpr_discount,
						'Exp_Number' => $mwb_wpr_expnum,
						'Exp_Days' => $mwb_wpr_expdays,
						);
				}
			}
			$membership_settings_array['mwb_wpr_membership_setting_enable'] = $mwb_wpr_mem_enable;
			$membership_settings_array['membership_roles'] = $membership_roles_list;
			$membership_settings_array['exclude_sale_product'] = $exclude_sale_product;
			if ( is_array( $membership_settings_array ) ) {
				update_option( 'mwb_wpr_membership_settings', $membership_settings_array );
			}
			do_action( 'mwb_wpr_save_membership_settings', $mwb_wpr_no_of_section );
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
}
if ( isset( $_GET['action'] ) && 'view_membership_log' == $_GET['action'] ) {
	include_once MWB_RWPR_DIR_PATH . '/admin/partials/templates/mwb_wpr_membership_log_table.php';

} else {
	$membership_settings_array = get_option( 'mwb_wpr_membership_settings', true );
	$mwb_wpr_membership_roles = array();
	?>
	<?php
	if ( ! is_array( $membership_settings_array ) ) :
		$membership_settings_array = array();
	endif;
	$mwbsetexpiry = get_option( 'mwb_wpr_set_expiry_for_old_users', false );
	$mwb_wpr_membership_roles = ( isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ) ? $membership_settings_array['membership_roles'] : array();
	?>
	
	<?php

	$mwb_wpr_settings = array(
		array(
			'title' => __( 'Enable Membership', 'rewardeem-woocommerce-points-rewards' ),
			'id'  => 'mwb_wpr_membership_setting_enable',
			'type' => 'checkbox',
			'class' => 'input-text',
			'desc' => __( 'Enable Membership', 'rewardeem-woocommerce-points-rewards' ),
			'desc_tip' => __( 'Check this box to enable the Membership Feature', 'rewardeem-woocommerce-points-rewards' ),
			'memebrship_log' => true,
			),
		array(
			'title' => __( 'Exclude Sale Products', 'rewardeem-woocommerce-points-rewards' ),
			'id'  => 'exclude_sale_product',
			'type' => 'checkbox',
			'class' => 'input-text',
			'desc' => __( 'Exclude Sale Products for Membership Discount', 'rewardeem-woocommerce-points-rewards' ),
			'desc_tip' => __( 'Check this box to do not apply the membership discount on sale products', 'rewardeem-woocommerce-points-rewards' ),
			),
		array(
			'title' => __( 'Create Member', 'rewardeem-woocommerce-points-rewards' ),
			'id'  => 'mwb_wpr_membership_create_section',
			'type' => 'create_member',
			'class' => 'parent_of_div',
			'create_member' => array(),
			),
		);

		?>
		<div class="mwb_wpr_wrap_table">
			<table class="form-table mwb_wpr_membership_setting mwp_wpr_settings">
				<tbody>
					<?php foreach ( $mwb_wpr_settings as $key => $value ) { ?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<?php
							$settings_obj->mwb_rwpr_generate_label( $value );
							?>
						</th>
						<td class="forminp forminp-text">
							<?php
							echo array_key_exists( 'desc_tip', $value ) ? wc_help_tip( $value['desc_tip'] ) : '';//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							if ( 'checkbox' == $value['type'] ) {
								$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $membership_settings_array );
							}
							if ( array_key_exists( 'memebrship_log', $value ) ) {
								?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mwb-rwpr-setting&tab=membership&action=view_membership_log' ) ); ?>" class="mwb_wpr_membership_log"><?php esc_html_e( 'Membership Log', 'rewardeem-woocommerce-points-rewards' ); ?></a>
								<?php
							}
							if ( 'create_member' == $value['type'] ) {
								do_action( 'mwb_wpr_add_membership_rule', $mwb_wpr_membership_roles );
								?>
								<input type="button" value='<?php esc_html_e( 'Add Another', 'rewardeem-woocommerce-points-rewards' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_repeat_button">
								<p class= "description"><?php esc_html_e( 'Please do not change the "Level Name" once it will be saved, as it become the key for the Membership User', 'rewardeem-woocommerce-points-rewards' ); ?></p>
								<?php
							}
							?>
							
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<p class="submit">
			<input type="submit" value='<?php esc_attr_e( 'Save changes', 'rewardeem-woocommerce-points-rewards' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_membership">
		</p>
		<?php
	}
