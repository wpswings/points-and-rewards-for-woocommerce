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

				$wps_wpr_assing_product_points                                            = array();
				$wps_wpr_pro_points_to_all                                                = sanitize_text_field( wp_unslash( $_POST['wps_wpr_pro_points_to_all'] ) );
				$wps_wpr_assing_product_points['wps_wpr_global_product_enable']           = 1;
				$wps_wpr_assing_product_points['wps_wpr_pro_points_to_all']               = $wps_wpr_pro_points_to_all;
				$wps_wpr_assing_product_points['wps_wpr_show_assign_points_on_shop_page'] = ! empty( $_POST['wps_wpr_show_assign_points_on_shop_page'] ) ? '1' : 0;

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

							wps_wpr_hpos_update_meta_data( $parent_id, 'wps_product_points_enable', 'yes' );
							wps_wpr_hpos_update_meta_data( $child_id, 'wps_wpr_variable_points', $wps_wpr_pro_points_to_all );
						}
					} else {
						wps_wpr_hpos_update_meta_data( $value->ID, 'wps_product_points_enable', 'yes' );
						wps_wpr_hpos_update_meta_data( $value->ID, 'wps_points_product_value', $wps_wpr_pro_points_to_all );
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
			$wps_wpr_assing_product_points['wps_wpr_global_product_enable']           = 0;
			$wps_wpr_assing_product_points['wps_wpr_pro_points_to_all']               = '';
			$wps_wpr_assing_product_points['wps_wpr_show_assign_points_on_shop_page'] = ! empty( $_POST['wps_wpr_show_assign_points_on_shop_page'] ) ? '1' : 0;
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
						wps_wpr_hpos_update_meta_data( $parent_id, 'wps_product_points_enable', 'no' );
						wps_wpr_hpos_update_meta_data( $child_id, 'wps_wpr_variable_points', '' );
					}
				} else {
					wps_wpr_hpos_update_meta_data( $value->ID, 'wps_product_points_enable', 'no' );
					wps_wpr_hpos_update_meta_data( $value->ID, 'wps_points_product_value', '' );
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

$wps_wpr_assing_product_points         = get_option( 'wps_wpr_assign_products_points', array() );
$wps_wpr_assign_product_table_settings = array(
	array(
		'title' => __( 'Global setting for assigning points to all products at once', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Global Assign Product Points', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Enable this to assign Global Product Points', 'points-and-rewards-for-woocommerce' ),
		'id'       => 'wps_wpr_global_product_enable',
		'desc_tip' => __( 'Toggle This to Assign Points to All Products at Once. Untoggle to Remove Assigned Points to All Products at Once.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'             => __( 'Enter Assign Global Product Points', 'points-and-rewards-for-woocommerce' ),
		'type'              => 'number',
		'desc'              => __( 'Enter Global Product Points', 'points-and-rewards-for-woocommerce' ),
		'id'                => 'wps_wpr_pro_points_to_all',
		'desc_tip'          => __( 'Entered Points are assigned to All Products.', 'points-and-rewards-for-woocommerce' ),
		'custom_attributes' => array( 'min' => '"0"' ),
		'class'             => 'input-text wps_wpr_common_width',
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
	<input type="hidden" name="wps-wpr-nonce" value="<?php echo esc_html( wp_create_nonce( 'wps-wpr-nonce' ) ); ?>">
	<input type="submit" value='<?php esc_attr_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_select_all_products">
</p>
<?php do_action( 'wps_wpr_product_assign_points' ); ?>
