<?php
/**
 * Assign Points to Products Template
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Points_And_Rewards_For_Woocommerce_Pro
 * @subpackage Points_And_Rewards_For_Woocommerce_Pro/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . 'admin/class-points-rewards-for-woocommerce-dummy-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Dummy_Settings( '', '' );

$wps_product_purchase_points = array(
	array(
		'title' => __( 'Purchase through Points', 'ultimate-woocommerce-points-and-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Purchase through Points', 'ultimate-woocommerce-points-and-rewards' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Purchase Products through Points', 'ultimate-woocommerce-points-and-rewards' ),
		'id'       => 'wps_wpr_product_purchase_points',
		'class'    => 'wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Check this box to enable purchasing products through points', 'ultimate-woocommerce-points-and-rewards' ),
		'default'  => 0,
	),
	array(
		'title'    => __( 'Restrictions for above setting', 'ultimate-woocommerce-points-and-rewards' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Allow some of the products for purchasing through points', 'ultimate-woocommerce-points-and-rewards' ),
		'id'       => 'wps_wpr_restrict_pro_by_points',
		'class'    => 'wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Toggle This to Allow Some Products to Be Purchased Through Points', 'ultimate-woocommerce-points-and-rewards' ),
		'default'  => 0,
	),
	array(
		'title'    => __( 'Select Product Category', 'ultimate-woocommerce-points-and-rewards' ),
		'id'       => 'wps_wpr_restrictions_for_purchasing_cat',
		'class'    => 'wps_wpr_pro_plugin_settings',
		'type'     => 'search&select',
		'multiple' => 'multiple',
		'desc_tip' => __( 'Select those categories which you want to allow to customers for purchase that product through points.', 'ultimate-woocommerce-points-and-rewards' ),
		'options'  => $settings_obj->wps_wpr_get_dummy_category(),
	),
	array(
		'title'    => __( 'Enter Text', 'ultimate-woocommerce-points-and-rewards' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_purchase_product_text',
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
		'desc'     => esc_html__( 'The entered text will get displayed on the Single Product Page', 'ultimate-woocommerce-points-and-rewards' ),
		'desc_tip' => __( 'The entered text will get displayed on the Single Product Page', 'ultimate-woocommerce-points-and-rewards' ),
		'default'  => __( 'Use your Points for purchasing this Product', 'ultimate-woocommerce-points-and-rewards' ),
	),
	array(
		'title'       => __( 'Purchase Points Conversion', 'ultimate-woocommerce-points-and-rewards' ),
		'type'        => 'number_text',
		'class'       => 'wps_wpr_pro_plugin_settings',
		'number_text' => apply_filters(
			'wps_wpr_currency_pro_filter',
			array(

				array(
					'type'              => 'text',
					'id'                => 'wps_wpr_product_purchase_price',
					'class'             => 'input-text wps_wpr_new_woo_ver_style_text wc_input_price wps_wpr_pro_plugin_settings',
					'custom_attributes' => array( 'min' => '"1"' ),
					'desc_tip'          => __(
						'Entered points will be converted to price. (i.e., how many points will be equivalent to the product price)',
						'ultimate-woocommerce-points-and-rewards'
					),
					'desc'              => __( '=', 'ultimate-woocommerce-points-and-rewards' ),
					'default'           => '1',
					'curr'              => get_woocommerce_currency_symbol(),
				),
				array(
					'type'              => 'number',
					'id'                => 'wps_wpr_purchase_points',
					'class'             => 'input-text wc_input_price wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
					'custom_attributes' => array( 'min' => '"1"' ),
					'desc_tip'          => __(
						'Entered points will be converted to price.(i.e., how many points will be equivalent to the product price)',
						'ultimate-woocommerce-points-and-rewards'
					),
					'desc'              => __( 'Points', 'ultimate-woocommerce-points-and-rewards' ),
					'curr'              => '',
				),
			)
		),
	),
	array(
		'title'    => __( 'Make "Per Product Redemption" Readonly', 'ultimate-woocommerce-points-and-rewards' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Readonly for entering Number of Points for Redemption ', 'ultimate-woocommerce-points-and-rewards' ),
		'id'       => 'wps_wpr_make_readonly',
		'class'    => 'wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Check this box if you want to make the redemption box read-only(where the end-user can enter the number of points they want to redeem)', 'ultimate-woocommerce-points-and-rewards' ),
		'default'  => 0,
	),
	array(
		'type' => 'sectionend',
	),
);

$wps_product_purchase_points = apply_filters( 'wps_wpr_add_product_purchase_points', $wps_product_purchase_points );
$general_settings            = get_option( 'wps_wpr_product_purchase_settings', array() );
$general_settings            = ! empty( $general_settings ) && is_array( $general_settings ) ? $general_settings : array();
?>
<div class="wps_table">
	<div class="wps_wpr_general_wrapper wps_wpr_pro_plugin_settings">
			<?php
			foreach ( $wps_product_purchase_points as $key => $value ) {
				if ( 'title' == $value['type'] ) {
					?>
				<div class="wps_wpr_general_row_wrap">
					<?php $settings_obj->wps_rwpr_generate_dummy_heading( $value ); ?>
				<?php } ?>
				<?php if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) { ?>
			<div class="wps_wpr_general_row">
					<?php $settings_obj->wps_rwpr_generate_dummy_label( $value ); ?>
				<div class="wps_wpr_general_content">
					<?php
					$settings_obj->wps_rwpr_generate_dummy_tool_tip( $value );
					if ( 'checkbox' == $value['type'] ) {
						$settings_obj->wps_rwpr_generate_dummy_checkbox_html( $value, $general_settings );
					}
					if ( 'number' == $value['type'] ) {
						$settings_obj->wps_rwpr_generate_dummy_number_html( $value, $general_settings );
					}
					if ( 'multiple_checkbox' == $value['type'] ) {
						foreach ( $value['multiple_checkbox'] as $k => $val ) {
							$settings_obj->wps_rwpr_generate_dummy_checkbox_html( $val, $general_settings );
						}
					}
					if ( 'text' == $value['type'] ) {
						$settings_obj->wps_rwpr_generate_dummy_text_html( $value, $general_settings );
					}
					if ( 'textarea' == $value['type'] ) {
						$settings_obj->wps_rwpr_generate_dummy_textarea_html( $value, $general_settings );
					}
					if ( 'number_text' == $value['type'] ) {
						foreach ( $value['number_text'] as $k => $val ) {
							if ( 'text' == $val['type'] ) {
								echo esc_html( isset( $val['curr'] ) ? $val['curr'] : '' );
								$settings_obj->wps_rwpr_generate_dummy_text_html( $val, $general_settings );

							}
							if ( 'number' == $val['type'] ) {
								$settings_obj->wps_rwpr_generate_dummy_number_html( $val, $general_settings );
								echo '<br>';

							}
						}
					}
					if ( 'search&select' == $value['type'] ) {
						$settings_obj->wps_wpr_generate_dummy_search_select_html( $value, $general_settings );
					}
					if ( 'select' == $value['type'] ) {
						$settings_obj->wps_wpr__select_dummy_html( $value, $general_settings );
					}
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
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'ultimate-woocommerce-points-and-rewards' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes wps_wpr_disabled_pro_plugin" name="wps_wpr_save_product_purchase">
</p>

<!-- Category Listing -->

<div class="wps_table">
	<h4><?php esc_html_e( 'Purchase Product Through Points Only', 'ultimate-woocommerce-points-and-rewards' ); ?></h4>
	<p class="wps_wpr_section_notice"><?php esc_html_e( 'This is a category-wise setting to assign points to a product of categories. Enter some valid points for assigning, and leave blank fields to remove assigned points.', 'ultimate-woocommerce-points-and-rewards' ); ?></p>
	<div class="wps_wpr_categ_details">
		<table class="form-table wps_wpr_pro_points_setting mwp_wpr_settings wps_wpr_pro_plugin_settings">
			<tbody>
				<tr>
					<th class="titledesc"><?php esc_html_e( 'Categories', 'ultimate-woocommerce-points-and-rewards' ); ?></th>
					<th class="titledesc"><?php esc_html_e( 'Enter Points', 'ultimate-woocommerce-points-and-rewards' ); ?></th>
					<th class="titledesc"><?php esc_html_e( 'Assign/Remove', 'ultimate-woocommerce-points-and-rewards' ); ?></th>
				</tr>
				<?php
				$args = array( 'taxonomy' => 'product_cat' );
				$categories = get_terms( $args );
				if ( isset( $categories ) && ! empty( $categories ) ) {
					foreach ( $categories as $category ) {
						$catid                        = $category->term_id;
						$catname                      = $category->name;
						$wps_wpr_purchase_categ_point = get_option( 'wps_wpr_purchase_points_cat' . $catid, '' );
						?>
						<tr>
							<td><?php echo esc_html( $catname ); ?></td>
							<td><input type="number" min="1" name="wps_wpr_purchase_points_per_categ" id="wps_wpr_purchase_points_cat<?php echo esc_html( $catid ); ?>" value="<?php echo esc_html( $wps_wpr_purchase_categ_point ); ?>" class="input-text wps_wpr_new_woo_ver_style_text"></td>
							<td><input type="button" value='<?php esc_html_e( 'Submit', 'ultimate-woocommerce-points-and-rewards' ); ?>' class="button-primary woocommerce-save-button wps_wpr_submit_purchase_points_per_category wps_wpr_disabled_pro_plugin" name="wps_wpr_submit_purchase_points_per_category" id="<?php echo esc_html( $catid ); ?>"></td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</div>
</div>
