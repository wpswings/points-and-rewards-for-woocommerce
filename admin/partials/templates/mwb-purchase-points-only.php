<?php
/**
 * Exit if accessed directly
 *
 * @package    Rewardeem_woocommerce_Points_Rewards
 * @subpackage Rewardeem_woocommerce_Points_Rewards/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Assign Points to Products Template
 */
$current_tab = 'mwb_wpr_purchase_points_only';

if ( isset( $_POST['mwb_wpr_select_all_products'] ) && isset( $_POST['mwb-wpr-nonce'] ) ) {
	$mwb_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['mwb-wpr-nonce'] ) );
	if ( wp_verify_nonce( $mwb_wpr_nonce, 'mwb-wpr-nonce' ) ) {
		if ( isset( $_POST['mwb_wpr_global_product_enable'] ) && ! empty( $_POST['mwb_wpr_global_product_enable'] ) ) {
			if ( isset( $_POST['mwb_wpr_pro_points_to_all'] ) && ! empty( $_POST['mwb_wpr_pro_points_to_all'] ) ) {
				$mwb_wpr_pro_points_to_all = sanitize_text_field( wp_unslash( $_POST['mwb_wpr_pro_points_to_all'] ) );
				update_option( 'mwb_wpr_global_product_enable', 'on' );
				update_option( 'mwb_wpr_pro_points_to_all', $mwb_wpr_pro_points_to_all );
				$args = array(
					'post_type' => 'product',
					'posts_per_page' => -1,
				);
				$loop = new WP_Query( $args );
				foreach ( $loop->posts as $key => $value ) {
					$product = wc_get_product( $value->ID );
					if ( $product->is_type( 'variable' ) && $product->has_child() ) {
						$parent_id = $product->get_id();
						$parent_product = wc_get_product( $parent_id );
						foreach ( $parent_product->get_children() as $child_id ) {
							update_post_meta( $parent_id, 'mwb_product_points_enable', 'yes' );
							update_post_meta( $child_id, 'mwb_wpr_variable_points', $mwb_wpr_pro_points_to_all );
						}
					} else {
						update_post_meta( $value->ID, 'mwb_product_points_enable', 'yes' );
						update_post_meta( $value->ID, 'mwb_points_product_value', $mwb_wpr_pro_points_to_all );
					}
				}
				wp_reset_query(); ?>
			<div class="notice notice-success is-dismissible">
				<p><strong>
					<?php
					echo esc_html( $mwb_wpr_pro_points_to_all );
					esc_html_e( ' Point Assigned Successfully to All Products', 'points-rewards-for-woocommerce' );
					?>
				</strong></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notices.', 'points-rewards-for-woocommerce' ); ?></span>
				</button>
			</div>
				<?php
			} else {
				?>
			<div class="notice notice-error is-dismissible">
				<p><strong><?php esc_html_e( ' Please enter some points !', 'points-rewards-for-woocommerce' ); ?></strong></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notices.', 'points-rewards-for-woocommerce' ); ?></span>
				</button>
			</div>
				<?php
			}
		} else {
			update_option( 'mwb_wpr_global_product_enable', 'off' );
			update_option( 'mwb_wpr_pro_points_to_all', '' );
			$args = array(
				'post_type' => 'product',
				'posts_per_page' => -1,
			);
			$loop = new WP_Query( $args );
			foreach ( $loop->posts as $key => $value ) {
				update_post_meta( $value->ID, 'mwb_product_points_enable', 'no' );
				update_post_meta( $value->ID, 'mwb_points_product_value', '' );
			}
			wp_reset_query();
			?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?php esc_html_e( 'Points are removed Successfully from All Products', 'points-rewards-for-woocommerce' ); ?></strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notices.', 'points-rewards-for-woocommerce' ); ?></span>
			</button>
		</div>
			<?php
		}
	}
}
$mwb_wpr_global_product_enable = get_option( 'mwb_wpr_global_product_enable', 'off' );
$mwb_wpr_pro_points_to_all = get_option( 'mwb_wpr_pro_points_to_all', '' );
?>

<!-- Category Listing -->

<div class="mwb_table">
	<p class="mwb_wpr_section_notice"><?php esc_html_e( 'This is the category wise setting for purchase product from points only, enter some valid points for assigning, leave blank fields for removing assigned points', 'points-rewards-for-woocommerce' ); ?></p>
	<div class="mwb_wpr_categ_details">
		<table class="form-table mwb_wpr_pro_points_setting mwp_wpr_settings">
			<tbody>
				<tr>
					<th class="titledesc"><?php esc_html_e( 'Categories', 'points-rewards-for-woocommerce' ); ?></th>
					<th class="titledesc"><?php esc_html_e( 'Enter Points', 'points-rewards-for-woocommerce' ); ?></th>
					<th class="titledesc"><?php esc_html_e( 'Assign/Remove', 'points-rewards-for-woocommerce' ); ?></th>
				</tr>
				<?php
				$args = array( 'taxonomy' => 'product_cat' );
				$categories = get_terms( $args );
				if ( isset( $categories ) && ! empty( $categories ) ) {
					foreach ( $categories as $category ) {
						$catid = $category->term_id;
						$catname = $category->name;
						$mwb_wpr_purchase_categ_point = get_option( 'mwb_wpr_purchase_points_cat' . $catid, '' );
						?>
						<tr>
							<td><?php echo esc_html( $catname ); ?></td>
							<td><input type="number" min="1" name="mwb_wpr_purchase_points_per_categ" id="mwb_wpr_purchase_points_cat<?php echo esc_html( $catid ); ?>" value="<?php echo esc_html( $mwb_wpr_purchase_categ_point ); ?>" class="input-text mwb_wpr_new_woo_ver_style_text"></td>
							<td><input type="button" value='<?php esc_html_e( 'Submit', 'points-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_submit_purchase_points_per_category" name="mwb_wpr_submit_purchase_points_per_category" id="<?php echo esc_html( $catid ); ?>"></td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</div>
</div>
