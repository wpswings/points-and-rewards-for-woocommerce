<?php 
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Rewardeem_woocommerce_Points_Rewards
 * @subpackage Rewardeem_woocommerce_Points_Rewards/admin
 */

/**This class is for generating the html for the settings.
 *
 * 
 * This file use to display the function fot the html
 *
 * @package    Rewardeem_woocommerce_Points_Rewards
 * @subpackage Rewardeem_woocommerce_Points_Rewards/admin
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Rewardeem_woocommerce_Points_Rewards_Admin_settings {

	/**
	*This function is for generating for the checkbox for the Settings
	*@name mwb_rwpr_generate_checkbox_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rwpr_generate_checkbox_html($value,$general_settings) {
		 	$enable_mwb_wpr = isset($general_settings[$value['id']]) ? intval($general_settings[$value['id']]) : 0;
		?>
		<label for="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
			<input type="checkbox" name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" <?php checked($enable_mwb_wpr,1);?> id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"> <?php echo (array_key_exists('desc', $value))?$value['desc']:'';?>
		</label>
		<?php
	}

	/**
	*This function is for generating for the checkbox for the Settings
	*@name mwb_rwpr_generate_checkbox_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_wpr_genrate_label_for_shortcode($value) {
		?>
		<p class="description"><?php _e('Use shortcode [MYCURRENTUSERLEVEL] for displaying current Membership Level of Users',MWB_RWPR_Domain);?></p>
		<p class="description"><?php _e('Use shortcode [MYCURRENTPOINT] for displaying current Points of Users',MWB_RWPR_Domain);?></p>
		<p class="description"><?php _e('Use shortcode [SIGNUPNOTIFICATION] for displaying notification anywhere on site',MWB_RWPR_Domain);?></p>	
		<?php

	}

	/**
	*This function is for generating for the number for the Settings
	*@name mwb_rwpr_generate_number_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rwpr_generate_number_html($value,$general_settings) {
		$default_val =array_key_exists('default',$value)?$value['default']:1;
		$mwb_signup_value = isset($general_settings[$value['id']]) ? intval($general_settings[$value['id']]) : $default_val;
		?>
		<label for="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
			<input type="number" <?php if (array_key_exists('custom_attributes', $value)) {
					
					foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
						echo  $attribute_name ;
						echo  "=$attribute_val"; 
						
					}
				}?> value="<?php echo $mwb_signup_value;?>" name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>"
			class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"><?php echo (array_key_exists('desc', $value))?$value['desc']:'';?>
		</label>
		<?php
	}

	/**
	*This function is for generating for the wp_editor for the Settings
	*@name mwb_rwpr_generate_label
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rwpr_generate_wp_editor($value,$notification_settings) {

		if(isset($value['id']) && !empty($value['id'])) {
			$defaut_text = isset($value['default'])?$value['default']:'';
			$mwb_wpr_content = isset($notification_settings[$value['id']]) && !empty($notification_settings[$value['id']]) ?$notification_settings[$value['id']] : $defaut_text;
			$value_id = (array_key_exists('id', $value))?$value['id']:'';
			?>
			<label for="<?php echo $value_id; ?>">
				<?php 
				$content=stripcslashes($mwb_wpr_content);
				$editor_id= $value_id;
				$settings = array(
					'media_buttons'    => false,
					'drag_drop_upload' => true,
					'dfw'              => true,
					'teeny'            => true,
					'editor_height'    => 200,
					'editor_class'       => 'mwb_wpr_new_woo_ver_style_textarea',
					'textarea_name'    => $value_id,
					);
					wp_editor($content,$editor_id,$settings); ?>
				</label>	
				<?php
			}
		}

	/**
	*This function is for generating for the Label for the Settings
	*@name mwb_rwpr_generate_label
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rwpr_generate_label($value) {
		?>
		<div class="mwb_wpr_general_label">
			<label for="<?php echo (array_key_exists('id', $value))?$value['id']:'';?>"><?php echo (array_key_exists('title', $value))?$value['title']:''; ?></label>
			<?php if(array_key_exists('pro',$value)) {?>
			<span class="mwb_wpr_general_pro">Pro</span>
			<?php }?>
		</div>
		<?php
	}

	/**
	*This function is used for the generating the order total label settings
	*@name mwb_wpr_generate_label_for_order_total_settings
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_wpr_generate_label_for_order_total_settings($value) {
		if(!empty($value) && is_array($value)) {
			?>
			<label for="<?php echo (array_key_exists('id', $value))?$value['id']:'';?>"><?php echo (array_key_exists('title', $value))?$value['title']:''; ?></label>
			<?php
		}
	}


	/**
	*This function is for generating for the heading for the Settings
	*@name mwb_rwpr_generate_heading
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rwpr_generate_heading($value) {
		if(array_key_exists('title',$value)) {?>
			<div class="mwb_wpr_general_sign_title">
				<?php echo $value['title'];?>
			</div>
			<?php 
		}
	}

	/**
	*This function is for generating for the Tool tip for the Settings
	*@name mwb_rwpr_generate_tool_tip
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rwpr_generate_tool_tip($value) {
		if(array_key_exists('desc_tip',$value)) {
			echo wc_help_tip($value['desc_tip']);
		}
	}

	/**
	*This function is for generating for the text html
	*@name mwb_rwpr_generate_text_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rwpr_generate_text_html($value,$general_settings) {
		$mwb_signup_value = isset($general_settings[$value['id']]) ? ($general_settings[$value['id']]) : '';
		if(empty($mwb_signup_value)) {
			$mwb_signup_value = array_key_exists('default',$value)?$value['default']:'';
		}
		?>
		<label for="
			<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
			<input type="text" <?php 
			if (array_key_exists('custom_attributes', $value)) {
					foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
						echo  $attribute_name ;
						echo  "=$attribute_val"; 
					}
				}?> 
				style ="<?php echo (array_key_exists('style', $value))?$value['style']:''; ?>"
				value="<?php echo $mwb_signup_value;?>" name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>"
				class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"><?php echo (array_key_exists('desc', $value))?$value['desc']:'';?>
		</label>
			<?php
	}

	/**
	*This function is for generating for the color
	*@name mwb_rwpr_generate_text_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rwpr_generate_color_box($value,$general_settings) {
		$mwb_color_value = isset($general_settings[$value['id']]) ? ($general_settings[$value['id']]) : '';
		if(empty($mwb_color_value)) {
			$mwb_color_value = array_key_exists('default',$value)?$value['default']:'';
		}
		?>
		<label for="
			<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
			<input <?php 
			if (array_key_exists('custom_attributes', $value)) {
					foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
						echo  $attribute_name ;
						echo  "=$attribute_val"; 
					}
				}?> 
				style ="<?php echo (array_key_exists('style', $value))?$value['style']:''; ?>"
				name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" 
				 id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>"
			type="color" 
			value="<?php echo $mwb_color_value;?>">
		</label>
		<?
	}
	/**
	*This function is for generating for the text html
	*@name mwb_rwpr_generate_textarea_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rwpr_generate_textarea_html($value,$general_settings) {
		$mwb_signup_value = isset($general_settings[$value['id']]) ? ($general_settings[$value['id']]) : '';
		if(empty($mwb_signup_value)) {
			$mwb_signup_value = array_key_exists('default',$value)?$value['default']:'';
		}
		?>
		<span class="description"><?php echo array_key_exists('desc', $value)?$value['desc']:'';?></span>	
		<label for="mwb_wpr_general_text_points" class="mwb_wpr_label">
			<textarea 
				<?php if (array_key_exists('custom_attributes', $value)) {
				foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
						echo  $attribute_name ;
						echo  "=$attribute_val"; 
						
					}
				}?>  name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>"
				class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"><?php echo $mwb_signup_value;?>
			</textarea>
		</label>
		<p class="description"><?php echo $value['desc2']; ?></p>
		<?php
	}

	/**
	*This function is for generating the notice of the save settings
	*@name mwb_rwpr_generate_textarea_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_wpr_settings_saved() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?php _e('Settings saved.',MWB_RWPR_Domain); ?></strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php _e('Dismiss this notices.',MWB_RWPR_Domain); ?></span>
			</button>
		</div>
		<?php 
	}

	/**
	*This function is used for the saving and filtering the input.
	*@name mwb_rwpr_save_notification_settings
	*@param $value
	*@since 1.0.0 
	*/
	public  function mwb_rwpr_filter_checkbox_notification_settings($POST,$name) {
		$_POST[$name] = isset($_POST[$name]) ? 1 : 0;
	}

	/**
	*This function is used for the saving and filtering the input.
	*@name mwb_rwpr_save_notification_settings
	*@param $value
	*@since 1.0.0 
	*/
	public function  mwb_rwpr_filter_subj_email_notification_settings($POST,$name) {
		$_POST[$name] = (isset($_POST[$name])&& !empty($_POST[$name])) ? $_POST[$name] :'';
		return $_POST[$name];
	}

	/**
	*This function is used for generating the label for the membership settings.
	*@name mwb_wpr_generate_label_for_membership
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_wpr_generate_label_for_membership($value,$count) {
		$mwb_wpr_id = array_key_exists('id',$value)?$value['id']:'';
		$mwb_wpr_title = array_key_exists('title',$value)?$value['title']:'';
		?>
		<label for="<?php  echo $mwb_wpr_id; ?>_$count">
		 <?php  echo $mwb_wpr_title; ?>
		</label>
		<?php
	}


	/**
	*This function is used for generating the shortcode.
	*@name mwb_wpr_generate_label_for_membership
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_wpr_generate_shortcode($value) {
		if (array_key_exists('desc',$value)) {
			foreach ($value['desc'] as $k => $val) {
				?>
				<p class="description"><?php echo $val;?></p>
				<?
			}
		}

	}


	/**
	*This function used for checking is checkbox is empty or not.
	*@name mwb_wpr_check_checkbox
	*@param $value
	*@since 1.0.0 
	*/

	public function mwb_wpr_check_checkbox($value,$postdata) {
		$postdata[$value['id']] = isset($postdata[$value['id']])?1:0;
		return $postdata[$value['id']];
	} 

	/**
	*This function used for checking is checkbox is empty or not.
	*@name mwb_wpr_check_numberbox
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_wpr_check_numberbox($value,$postdata) {

		$postdata[$value['id']] = (isset($postdata[$value['id']]) && !empty($postdata[$value['id']]))?sanitize_post($_POST[$value['id']]):1;
		return $postdata[$value['id']];
	} 


	/**
	*This function used for checking is textbox is empty or not.
	*@name mwb_wpr_check_textbox
	*@param $value,$postdata
	*@since 1.0.0 
	*/
	public function mwb_wpr_check_textbox($value,$postdata) {
		$mwb_textarea_text = '';
		$mwb_textarea_text = (isset($postdata[$value['id']]) && !empty($postdata[$value['id']]))?sanitize_post($postdata[$value['id']]):$value['default'];
		return $mwb_textarea_text;
	} 

	/**
	*This function used for checking is textarea is empty or not.
	*@name mwb_wpr_check_textarea
	*@param $value,$postdata
	*@since 1.0.0 
	*/
	public function mwb_wpr_check_textarea($value,$postdata) {
		$postdata[$value['id']] = (isset($postdata[$value['id']]) && !empty($postdata[$value['id']]))?stripcslashes($postdata[$value['id']]):$value['default'];
		return $postdata[$value['id']];
	} 

	/**
	*This function used for checking is color filed is empty or not.
	*@name mwb_wpr_check_input_color
	*@param $value,$postdata
	*@since 1.0.0 
	*/
	public function mwb_wpr_check_input_color($value,$postdata) {
		$postdata[$value['id']] = (isset($postdata[$value['id']]) && !empty($postdata[$value['id']]))?$postdata[$value['id']]:$value['default'];
		return $postdata[$value['id']];	
	}

	/**
	*This settings is used for checking is setting is empty or not.
	*@name check_is_settings_is_not_empty
	*@param $value,$postdata
	*@since 1.0.0 
	*/
	public function check_is_settings_is_not_empty($mwb_wpr_general_settings,$_postdata) {
		foreach ($mwb_wpr_general_settings as $key => $value) {
			if ($value['type'] == 'checkbox') {
				$_postdata[$value['id']] = $this->mwb_wpr_check_checkbox($value,$_postdata);
			}
			if ($value['type'] == 'number') {
				$_postdata[$value['id']] = $this->mwb_wpr_check_numberbox($value,$_postdata);
			}
			if ($value['type'] == 'text') {
				$_postdata[$value['id']] = $this->mwb_wpr_check_textbox($value,$_postdata);
			}
			if ($value['type'] == 'textarea') {
				$_postdata[$value['id']] = $this->mwb_wpr_check_textarea($value,$_postdata);
			}
			if ($value['type'] == "multiple_checkbox") {
				foreach ($value['multiple_checkbox'] as $k => $val) {
					$_postdata[$val['id']] = $this->mwb_wpr_check_checkbox($val,$_postdata);
				}
			}
			if ($value['type'] == "number_text") {
				foreach ($value['number_text'] as $k => $val) {
					if ($val['type'] == 'text') {
						$_postdata[$val['id']] = $this->mwb_wpr_check_textbox($val,$_postdata);

					}
					if ($val['type'] == 'number') {
						$_postdata[$val['id']] = $this->mwb_wpr_check_numberbox($val,$_postdata);
					}
				}
			}
			if ($value['type'] == "color") {
				$_postdata[$val['id']] = $this->mwb_wpr_check_input_color($value,$_postdata);
			}
			do_action('mwb_wpr_add_custom_type_settings',$value,$mwb_wpr_general_settings,$_postdata);
		}
		return $_postdata;
	}	
}