<?php 
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/admin
 */
include_once MWB_RWPR_DIR_PATH.'/admin/partials/settings/class-rewardeem-wocoommerce-points-rewards-settings.php';
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/admin
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class ultimate_woocommerce_Points_Rewards_Admin_settings Extends Rewardeem_woocommerce_Points_Rewards_Admin_settings{

	/**
	*This function is used for generating the settings
	*@name mwb_rwpr_generate_number_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_wpr_generate_searchSelect_html( $value,$general_settings )
	{
		$selectedvalue = isset($general_settings[$value['id']]) ? ($general_settings[$value['id']]) : array();
		if($selectedvalue == '') {
			$selectedvalue = '';
		}

		?>
		<label for="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
			<select name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>[]" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" 
			<?php if(array_key_exists('multiple', $value)):?>
			multiple = "<?php echo (array_key_exists('multiple', $value))? $value['multiple']:false; ?>"
		   <?php endif;?>
				class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"
				<?php
				if (array_key_exists('custom_attribute', $value)) {
					foreach ($value['custom_attribute'] as $attribute_name => $attribute_val) {
						echo  $attribute_name.'='.$attribute_val ;					
					}
				}
				if(is_array($value['options']) && !empty($value['options'])){
					foreach($value['options'] as $option){
						$select = 0;
						if( is_array($selectedvalue) && in_array($option['id'], $selectedvalue) && !empty( $selectedvalue )){
							$select = 1;
						}
						?>
						><option value="<?php echo $option['id'];?>" <?php echo selected(1, $select);?> ><?php echo $option['name']; ?></option>
						<?php
					}
				}	
				?>
			</select>
		</label>
		<?php	
	}

	/**
	*This function is used generating the option
	*@name mwb_wpr_get_category
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_wpr_get_category() {
		$args = array('taxonomy'=>'product_cat');
		$categories = get_terms($args);
		if(isset($categories) && !empty($categories)) {
			$category = array();
			foreach($categories as $cat) {
				$category[]=array(
					'id' => $cat->term_id,
					'name' => $cat->name
					);
			}
			return $category;	
		}
	}

	/**
	*This function is used generating the option
	*@name mwb_wpr_get_category
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_wpr_get_option_of_points() {
		$mwb_wpr_points_expiration = array(
			'days' => __('Days','ultimate-woocommerce-points-and-rewards'),
			'weeks' => __('Weeks','ultimate-woocommerce-points-and-rewards'),
			'months' => __('Months','ultimate-woocommerce-points-and-rewards'),
			'years' => __('Years','ultimate-woocommerce-points-and-rewards'),
			);
		$mwb_wpr_option = array();
		foreach ($mwb_wpr_points_expiration as $key => $value) {
			$mwb_wpr_option[] = array(
				'id' => $key,
				'name' => $value
				);
		}
		return $mwb_wpr_option;
	}
}