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

		// Previous global settings: used to tell products that only carry the old global value apart from products with their own points.
		$wps_wpr_prev_assign_points = get_option( 'wps_wpr_assign_products_points', array() );
		$wps_wpr_prev_assign_points = is_array( $wps_wpr_prev_assign_points ) ? $wps_wpr_prev_assign_points : array();
		$wps_wpr_prev_global_points = ! empty( $wps_wpr_prev_assign_points['wps_wpr_global_product_enable'] ) && isset( $wps_wpr_prev_assign_points['wps_wpr_pro_points_to_all'] ) ? (string) $wps_wpr_prev_assign_points['wps_wpr_pro_points_to_all'] : '';
		$wps_wpr_overwrite_points   = ! empty( $_POST['wps_wpr_overwrite_existing_product_points'] );
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

				$wps_wpr_skipped_products = 0;
				foreach ( $loop->posts as $key => $value ) {
					$product = wc_get_product( $value->ID );
					if ( ! $product ) {
						continue;
					}

					if ( apply_filters( 'wps_wpr_is_variable_product', false, $product ) ) {

						$parent_id      = $product->get_id();
						$parent_product = wc_get_product( $parent_id );
						foreach ( $parent_product->get_children() as $child_id ) {

							// Without the overwrite flag, keep variations that have their own points (anything other than the previous global value).
							$wps_wpr_own_points = (string) wps_wpr_hpos_get_meta_data( $child_id, 'wps_wpr_variable_points', true );
							if ( ! $wps_wpr_overwrite_points && '' !== $wps_wpr_own_points && $wps_wpr_own_points !== $wps_wpr_prev_global_points ) {
								++$wps_wpr_skipped_products;
								continue;
							}
							wps_wpr_hpos_update_meta_data( $parent_id, 'wps_product_points_enable', 'yes' );
							wps_wpr_hpos_update_meta_data( $child_id, 'wps_wpr_variable_points', $wps_wpr_pro_points_to_all );
						}
					} else {
						// Without the overwrite flag, keep products that have their own points (anything other than the previous global value).
						$wps_wpr_own_points = (string) wps_wpr_hpos_get_meta_data( $value->ID, 'wps_points_product_value', true );
						if ( ! $wps_wpr_overwrite_points && '' !== $wps_wpr_own_points && $wps_wpr_own_points !== $wps_wpr_prev_global_points && 'yes' === wps_wpr_hpos_get_meta_data( $value->ID, 'wps_product_points_enable', true ) ) {
							++$wps_wpr_skipped_products;
							continue;
						}
						wps_wpr_hpos_update_meta_data( $value->ID, 'wps_product_points_enable', 'yes' );
						wps_wpr_hpos_update_meta_data( $value->ID, 'wps_points_product_value', $wps_wpr_pro_points_to_all );
					}
				}
				wp_reset_postdata(); ?>
				<div class="notice notice-success is-dismissible">
					<p><strong>
					<?php
					echo esc_html( $wps_wpr_pro_points_to_all );
					esc_html_e( ' Point Assigned Successfully to All Products', 'points-and-rewards-for-woocommerce' );
					if ( $wps_wpr_skipped_products > 0 ) {
						/* translators: %d: number of products/variations kept. */
						echo ' ' . esc_html( sprintf( __( '(%d products/variations with their own points were kept; tick "Overwrite existing product points" to replace them.)', 'points-and-rewards-for-woocommerce' ), $wps_wpr_skipped_products ) );
					}
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
		} elseif ( ! empty( $wps_wpr_prev_assign_points['wps_wpr_global_product_enable'] ) ) {
			// Only clear product points when global points were enabled before (i.e. the admin is turning it off), never on a plain save.
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
				if ( ! $product ) {
					continue;
				}

				if ( $product->is_type( 'variable' ) && $product->has_child() ) {

					$parent_id      = $product->get_id();
					$parent_product = wc_get_product( $parent_id );
					foreach ( $parent_product->get_children() as $child_id ) {
						// Keep variations with their own points unless overwrite is ticked.
						$wps_wpr_own_points = (string) wps_wpr_hpos_get_meta_data( $child_id, 'wps_wpr_variable_points', true );
						if ( ! $wps_wpr_overwrite_points && '' !== $wps_wpr_own_points && $wps_wpr_own_points !== $wps_wpr_prev_global_points ) {
							continue;
						}
						wps_wpr_hpos_update_meta_data( $parent_id, 'wps_product_points_enable', 'no' );
						wps_wpr_hpos_update_meta_data( $child_id, 'wps_wpr_variable_points', '' );
					}
				} else {
					// Keep products with their own points unless overwrite is ticked.
					$wps_wpr_own_points = (string) wps_wpr_hpos_get_meta_data( $value->ID, 'wps_points_product_value', true );
					if ( ! $wps_wpr_overwrite_points && '' !== $wps_wpr_own_points && $wps_wpr_own_points !== $wps_wpr_prev_global_points ) {
						continue;
					}
					wps_wpr_hpos_update_meta_data( $value->ID, 'wps_product_points_enable', 'no' );
					wps_wpr_hpos_update_meta_data( $value->ID, 'wps_points_product_value', '' );
				}
			}
			wp_reset_postdata();
			?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e( 'Points are removed Successfully from All Products', 'points-and-rewards-for-woocommerce' ); ?></strong></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'points-and-rewards-for-woocommerce' ); ?></span>
				</button>
			</div>
			<?php
		} else {
			$wps_wpr_prev_assign_points['wps_wpr_global_product_enable']           = 0;
			$wps_wpr_prev_assign_points['wps_wpr_show_assign_points_on_shop_page'] = ! empty( $_POST['wps_wpr_show_assign_points_on_shop_page'] ) ? '1' : 0;
			update_option( 'wps_wpr_assign_products_points', $wps_wpr_prev_assign_points );
			$settings_obj->wps_wpr_settings_saved();
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
	array(
		'title'    => __( 'Overwrite existing product points', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Also replace points that were set individually on products/variations', 'points-and-rewards-for-woocommerce' ),
		'id'       => 'wps_wpr_overwrite_existing_product_points',
		'desc_tip' => __( 'When unchecked, the global points are only applied to products that do not have their own points.', 'points-and-rewards-for-woocommerce' ),
	),
);
$wps_wpr_assign_product_table_settings = apply_filters( 'wps_wpr_assign_product_points_settings', $wps_wpr_assign_product_table_settings );
?>
<?php do_action( 'wps_wpr_add_notice' ); ?>
<div class="wps_wpr_table wps_wpr_assign_points_panel">
	<div class="wps_wpr_general_wrapper">
	<?php
	$wps_section_open = false;
	foreach ( $wps_wpr_assign_product_table_settings as $key => $value ) {
		if ( 'title' == $value['type'] ) {
			if ( $wps_section_open ) {
				?>
				</div></div>
				<?php
			}
			?>
			<div class="wps_wpr_general_row_wrap">
				<?php $settings_obj->wps_rwpr_generate_heading( $value ); ?>
				<div class="wps_wpr_section_content">
			<?php
			$wps_section_open = true;
			} ?>
				<?php if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) { ?>
				<div class="wps_wpr_general_row wps_wpr_assign_row wps_wpr_assign_row_type_<?php echo isset( $value['type'] ) ? esc_attr( $value['type'] ) : 'default'; ?>">
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
			<?php if ( 'sectionend' == $value['type'] && $wps_section_open ) : ?>
				</div>
				</div>
				<?php
				$wps_section_open = false;
				endif; ?>
		<?php } ?>
		<?php if ( $wps_section_open ) : ?>
			</div></div>
		<?php endif; ?>
	</div>

</div>
<div class="clear"></div>
<p class="submit">
	<input type="hidden" name="wps-wpr-nonce" value="<?php echo esc_html( wp_create_nonce( 'wps-wpr-nonce' ) ); ?>">
	<input type="submit" value='<?php esc_attr_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_select_all_products">
</p>
<script type="text/javascript">
	( function () {
		var btn = document.querySelector( 'input[name="wps_wpr_select_all_products"]' );
		if ( ! btn ) {
			return;
		}
		var wasEnabled = <?php echo ! empty( $wps_wpr_assing_product_points['wps_wpr_global_product_enable'] ) ? 'true' : 'false'; ?>;
		btn.addEventListener( 'click', function ( e ) {
			var enable    = document.getElementById( 'wps_wpr_global_product_enable' );
			var overwrite = document.getElementById( 'wps_wpr_overwrite_existing_product_points' );
			var msg       = '';
			if ( enable && enable.checked ) {
				msg = ( overwrite && overwrite.checked ) ? <?php echo wp_json_encode( __( 'This will replace the points of EVERY product, including products with their own points. Continue?', 'points-and-rewards-for-woocommerce' ) ); ?> : <?php echo wp_json_encode( __( 'This will assign the global points to all products that do not have their own points. Continue?', 'points-and-rewards-for-woocommerce' ) ); ?>;
			} else if ( enable && wasEnabled ) {
				msg = <?php echo wp_json_encode( __( 'Disabling global product points will remove the global points from all products (and also individually set points if "Overwrite existing product points" is ticked). Continue?', 'points-and-rewards-for-woocommerce' ) ); ?>;
			}
			if ( msg && ! window.confirm( msg ) ) {
				e.preventDefault();
			}
		} );
	} )();
</script>
<?php do_action( 'wps_wpr_product_assign_points' ); ?>
