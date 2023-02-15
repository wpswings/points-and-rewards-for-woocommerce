<?php
/**
 * This is setttings array for the Assign Product Points settings.
 *
 * Assign Product Points Template
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

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();
$current_tab  = 'wps_wpr_pro_points_tab';

if ( isset( $_POST['wps_wpr_select_all_products'] ) && isset( $_POST['wps-wpr-nonce'] ) ) {
	$wps_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['wps-wpr-nonce'] ) );
	if ( wp_verify_nonce( $wps_wpr_nonce, 'wps-wpr-nonce' ) ) {

		if ( isset( $_POST['wps_wpr_global_product_enable'] ) && ! empty( $_POST['wps_wpr_global_product_enable'] ) ) {
			if ( isset( $_POST['wps_wpr_pro_points_to_all'] ) && ! empty( $_POST['wps_wpr_pro_points_to_all'] ) ) {

				$wps_wpr_assing_product_points                                  = array();
				$wps_wpr_pro_points_to_all                                      = sanitize_text_field( wp_unslash( $_POST['wps_wpr_pro_points_to_all'] ) );
				$wps_wpr_assing_product_points['wps_wpr_global_product_enable'] = 1;
				$wps_wpr_assing_product_points['wps_wpr_pro_points_to_all']     = $wps_wpr_pro_points_to_all;
				if ( is_array( $wps_wpr_assing_product_points ) && ! empty( $wps_wpr_assing_product_points ) ) {
					update_option( 'wps_wpr_assign_products_points', $wps_wpr_assing_product_points );
				}
				$args = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
				);
				$loop = new WP_Query( $args );
				foreach ( $loop->posts as $key => $value ) {
					$product = wc_get_product( $value->ID );

					if ( apply_filters( 'wps_wpr_is_variable_product', false, $product ) ) {

						$parent_id      = $product->get_id();
						$parent_product = wc_get_product( $parent_id );
						foreach ( $parent_product->get_children() as $child_id ) {

							update_post_meta( $parent_id, 'wps_product_points_enable', 'yes' );
							update_post_meta( $child_id, 'wps_wpr_variable_points', $wps_wpr_pro_points_to_all );
						}
					} else {
						update_post_meta( $value->ID, 'wps_product_points_enable', 'yes' );
						update_post_meta( $value->ID, 'wps_points_product_value', $wps_wpr_pro_points_to_all );
					}
				}
				wp_reset_query(); ?>
				<div class="notice notice-success is-dismissible">
					<p><strong>
					<?php
					echo esc_html( $wps_wpr_pro_points_to_all );
					esc_html_e( ' Point Assigned Successfully to All Products', 'points-and-rewards-for-woocommerce' );
					?>
					</strong></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</button>
				</div>
				<?php
			} else {
				?>
				<div class="notice notice-error is-dismissible">
					<p><strong><?php esc_html_e( ' Please enter some points !', 'points-and-rewards-for-woocommerce' ); ?></strong></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</button>
				</div>
				<?php
			}
		} else {
			$wps_wpr_assing_product_points['wps_wpr_global_product_enable'] = 0;
			$wps_wpr_assing_product_points['wps_wpr_pro_points_to_all'] = '';
			if ( is_array( $wps_wpr_assing_product_points ) && ! empty( $wps_wpr_assing_product_points ) ) {

				update_option( 'wps_wpr_assign_products_points', $wps_wpr_assing_product_points );
			}
			$args = array(
				'post_type' => 'product',
				'posts_per_page' => -1,
			);
			$loop = new WP_Query( $args );
			foreach ( $loop->posts as $key => $value ) {
				$product = wc_get_product( $value->ID );

				if ( $product->is_type( 'variable' ) && $product->has_child() ) {

					$parent_id      = $product->get_id();
					$parent_product = wc_get_product( $parent_id );
					foreach ( $parent_product->get_children() as $child_id ) {
						update_post_meta( $parent_id, 'wps_product_points_enable', 'no' );
						update_post_meta( $child_id, 'wps_wpr_variable_points', '' );
					}
				} else {
					update_post_meta( $value->ID, 'wps_product_points_enable', 'no' );
					update_post_meta( $value->ID, 'wps_points_product_value', '' );
				}
			}
			wp_reset_query();
			?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e( 'Points are removed Successfully from All Products', 'points-and-rewards-for-woocommerce' ); ?></strong></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'points-and-rewards-for-woocommerce' ); ?></span>
				</button>
			</div>
			<?php
		}
	}
}

$wps_wpr_assing_product_points = get_option( 'wps_wpr_assign_products_points', array() );

$wps_wpr_assign_product_table_settings = array(
	array(
		'title' => __( 'Global setting for assigning points to all products at once', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Global Assign Product Points', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Enable Assign Global Product Points', 'points-and-rewards-for-woocommerce' ),
		'id'       => 'wps_wpr_global_product_enable',
		'desc_tip' => __( 'This is the global setting for Product Purchase Points, check this if you want to assign points to all products at once or uncheck if you want to remove assigned points from all products at once.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'            => __( 'Enter Assign Global Product Points', 'points-and-rewards-for-woocommerce' ),
		'type'             => 'number',
		'desc'             => __( 'Enter Global Product Points', 'points-and-rewards-for-woocommerce' ),
		'id'               => 'wps_wpr_pro_points_to_all',
		'desc_tip'         => __( 'Entered Points are assigned to All Products.', 'points-and-rewards-for-woocommerce' ),
		'custom_attribute' => array( 'min' => '"1"' ),
		'class'            => 'input-text wps_wpr_common_width',
	),
	array(
		'type' => 'sectionend',
	),
);
$wps_wpr_assign_product_table_settings = apply_filters( 'wps_wpr_assign_product_points_settings', $wps_wpr_assign_product_table_settings );
?>
<?php do_action( 'wps_wpr_add_notice' ); ?>
<div class="wps_wpr_table">
	<div class="wps_wpr_general_wrapper">
	<?php
	foreach ( $wps_wpr_assign_product_table_settings as $key => $value ) {
		if ( 'title' == $value['type'] ) {
			?>
			<div class="wps_wpr_general_row_wrap">
				<?php $settings_obj->wps_rwpr_generate_heading( $value ); ?>
				<?php } ?>
				<?php if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) { ?>
				<div class="wps_wpr_general_row">
					<?php $settings_obj->wps_rwpr_generate_label( $value ); ?>
					<div class="wps_wpr_general_content">
						<?php
						$settings_obj->wps_rwpr_generate_tool_tip( $value );
						if ( 'checkbox' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_checkbox_html( $value, $wps_wpr_assing_product_points );
						}
						if ( 'number' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_number_html( $value, $wps_wpr_assing_product_points );
						}
						if ( 'multiple_checkbox' == $value['type'] ) {
							foreach ( $value['multiple_checkbox'] as $k => $val ) {
								$settings_obj->wps_rwpr_generate_checkbox_html( $val, $wps_wpr_assing_product_points );
							}
						}
						if ( 'text' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_text_html( $value, $wps_wpr_assing_product_points );
						}
						if ( 'textarea' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_textarea_html( $value, $wps_wpr_assing_product_points );
						}
						if ( 'number_text' == $value['type'] ) {
							foreach ( $value['number_text'] as $k => $val ) {
								if ( 'text' == $val['type'] ) {
									$settings_obj->wps_rwpr_generate_text_html( $val, $wps_wpr_assing_product_points );

								}
								if ( 'number' == $val['type'] ) {
									$settings_obj->wps_rwpr_generate_number_html( $val, $wps_wpr_assing_product_points );
									echo esc_html( get_woocommerce_currency_symbol() );
								}
							}
						}
						do_action( 'wps_wpr_additional_assign_product_points', $value, $wps_wpr_assing_product_points );
						?>
					</div>
				</div>
				<?php } ?>
			<?php if ( 'sectionend' == $value['type'] ) : ?>
				</div>	
				<?php endif; ?>
		<?php } ?> 
	</div>

</div>
<div class="clear"></div>
<p class="submit">
	<input type="submit" value='<?php esc_attr_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_select_all_products">
</p>
<?php do_action( 'wps_wpr_product_assign_points' ); ?>
