<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/public
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Ultimate_Woocommerce_Points_And_Rewards_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
     private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_URL . 'public/css/ultimate-woocommerce-points-and-rewards-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_URL . 'public/js/ultimate-woocommerce-points-and-rewards-public.js', array( 'jquery' ), $this->version, false );
		/*Get the settings of the products*/
		$mwb_wpr_make_readonly = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_make_readonly');
		/*Array of the*/
		$mwb_wpr_array = array(
			'make_readonly' => $mwb_wpr_make_readonly,
			);
		wp_localize_script('mwb_wpr_clipboard', 'mwb_wpr_pro',$mwb_wpr_array);

	}

	/**
	* This function is used for getting the product purchase points
	* 
	* @name mwb_wpr_get_general_settings
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	* @param string $id for key of the settings.
	*/
	public function mwb_wpr_get_product_purchase_settings_num($id) {
		$mwb_wpr_value = 0;
		$general_settings = get_option('mwb_wpr_product_purchase_settings',true);
		if(!empty($general_settings[$id])) {
			$mwb_wpr_value = $general_settings[$id];
		}
		return $mwb_wpr_value;
	}

	/**
	* This function is used for getting the product purchase points
	* 
	* @name mwb_wpr_get_general_settings
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_get_product_purchase_settings($id) {
		$mwb_wpr_value ='';
		$general_settings = get_option('mwb_wpr_product_purchase_settings',true);
		if(!empty($general_settings[$id])) {
			$mwb_wpr_value = $general_settings[$id];
		}
		return $mwb_wpr_value;
	}

	/**
	* Add the referral link parameter in the woocommerce.
	* @name mwb_wpr_add_referral_section 
	* @since    1.0.0
	* @param $user_id
	*/
	public function mwb_wpr_add_referral_section($user_id) {
		$general_settings = get_option('mwb_wpr_settings_gallery',true);
		/* Get the Refer Minimum Value*/
		$mwb_refer_min = isset($general_settings['mwb_wpr_general_refer_minimum']) ? intval($general_settings['mwb_wpr_general_refer_minimum']) : 1;
		$mwb_wpr_referral_link_permanent = isset($general_settings['mwb_wpr_referral_link_permanent']) ? intval($general_settings['mwb_wpr_referral_link_permanent']) : 0;
		$get_referral = get_user_meta($user_id, 'mwb_points_referral', true);
		$get_referral_invite = get_user_meta($user_id, 'mwb_points_referral_invite', true);
		if(isset($get_referral) && $get_referral !=null && isset($get_referral_invite) && $get_referral_invite !=null && $get_referral_invite >= $mwb_refer_min )
		{	
			/* check whether the Links are needs to be permanent or not */
			if($mwb_wpr_referral_link_permanent == 0){
				$referral_key = mwb_wpr_create_referral_code();
				update_user_meta($user_id, 'mwb_points_referral', $referral_key);
			}
			/* update the invites as soon as user got the referral rewards */
			$referral_invite = 0;
			update_user_meta($user_id, 'mwb_points_referral_invite', $referral_invite);
		}
	}

	/**
	* Add the text below the referral link.
	* @name mwb_wpr_add_invite_text 
	* @since    1.0.0
	* @param $user_id
	*/
	public function mwb_wpr_add_invite_text($user_id) {
		$general_settings = get_option('mwb_wpr_settings_gallery',true);
		$mwb_refer_min = isset($general_settings['mwb_wpr_general_refer_minimum']) ? intval($general_settings['mwb_wpr_general_refer_minimum']) : 1;
		$get_referral_invite = get_user_meta($user_id, 'mwb_points_referral_invite', true);
		$mwb_refer_value = isset($general_settings['mwb_wpr_general_refer_value']) ? intval($general_settings['mwb_wpr_general_refer_value']) : 1;
		$mwb_refer_value_disable = isset($general_settings['mwb_wpr_general_refer_value_disable']) ? intval($general_settings['mwb_wpr_general_refer_value_disable']) : 1;
		if(!$mwb_refer_value_disable){ ?>
			<p class="mwb_wpr_message">
				<?php
				_e( 'Minimum ','ultimate-woocommerce-points-and-rewards');echo $mwb_refer_min; _e(' invites are required to get a reward of ','ultimate-woocommerce-points-and-rewards');echo $mwb_refer_value; _e(' points','ultimate-woocommerce-points-and-rewards'); ?>
			</p>
			<p> 
				<?php if($mwb_refer_min > 1){
					echo __( 'Current Invites: ', 'ultimate-woocommerce-points-and-rewards' ).$get_referral_invite;
				} ?>
			</p>
			<?php
		}
		else { ?>
			<p><?php echo __( 'Invite Users to get some reward points on their purchasing', 'ultimate-woocommerce-points-and-rewards' );?>
		<?php
		}
	}

	/**
	* Referrals points rescrtion.
	* @name mwb_wpr_add_referral_resctrictions 
	* @since    1.0.0
	*/
	public function generate_public_obj() {
		$public_obj = new Rewardeem_woocommerce_Points_Rewards_Public('rewardeem-woocommerce-points-rewards','1.0.0');
		return $public_obj;
	}

	/**
	* Referrals points Restriction.
	* @name mwb_wpr_add_referral_resctrictions 
	* @since    1.0.0
	* @param bool
	*/
	public function mwb_wpr_add_referral_resctrictions($is_referral_true,$customer_id,$refere_id) {
		$user_id = $refere_id;
		$get_referral = get_user_meta($user_id, 'mwb_points_referral', true);
		$get_referral_invite = get_user_meta($user_id, 'mwb_points_referral_invite', true);
		/*Generate public obj*/
		$public_obj = $this->generate_public_obj();
		/*Get the minimum referral required for giving the signup points*/
		$mwb_refer_min = $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_refer_minimum');
		$mwb_refer_value_disable = $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_refer_value_disable');
		/*Custom Work*/
		$custom_ref_pnt = get_user_meta($user_id,'mwb_custom_points_referral_invite',true);
		/*Check the condition of the minimum referral requred*/
		if($get_referral_invite < $mwb_refer_min) {
			$get_referral_invite = (int)$get_referral_invite;
			update_user_meta($user_id, 'mwb_points_referral_invite', $get_referral_invite+1);
			update_user_meta($customer_id, 'user_visit_through_link', $user_id);
			$custom_ref_pnt = (int)$custom_ref_pnt;
			update_user_meta($user_id,'mwb_custom_points_referral_invite',$custom_ref_pnt+1);
			$public_obj->mwb_wpr_destroy_cookie();
			$is_referral_true = false;
		}
		$get_referral_invite = get_user_meta($user_id, 'mwb_points_referral_invite', true);
		if ($get_referral_invite == $mwb_refer_min) {
			update_user_meta($user_id, 'mwb_points_referral_invite', 0);
			/*Check Assign product points is not enable*/
			if(!$mwb_refer_value_disable) {
				$is_referral_true = true;
			}
		}
		return $is_referral_true;
	}

	/**
	* This function is used to edit comment template for points
	* 
	* @name mwb_wpr_woocommerce_comment_point
	* @return array
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_comment_point($comment_data) {
		
		$user_id = get_current_user_ID();
		$public_obj = $this->generate_public_obj();
		$mwb_wpr_comment_enable = $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_comment_enable');
		if(isset($mwb_wpr_comment_enable) && $mwb_wpr_comment_enable==1 && isset($user_id) && !empty($user_id)) {
			$mwb_wpr_comment_value = $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_comment_value');
			$mwb_wpr_comment_value = ($mwb_wpr_comment_value == 0)?1:$mwb_wpr_comment_value;
			$comment_data['comment_field'].='<p class="comment-mwb-wpr-points-comment"><label>' . __( 'You will get ','ultimate-woocommerce-points-and-rewards' ) .$mwb_wpr_comment_value.__(' points for product review','ultimate-woocommerce-points-and-rewards').'</p>';
		}
		return $comment_data;
		
	}

	/**
	* This function is used to give product points to user if order status of Product is complete and processing.
	* 
	* @name mwb_wpr_woocommerce_order_status_changed
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_pro_woocommerce_order_status_changed($order_id, $old_status, $new_status) {
		if($old_status != $new_status) {

			/*Generate object of the public class*/
			$public_obj = $this->generate_public_obj();
			/*Check is referral purchase is enable*/
			$mwb_referral_purchase_enable = $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_referal_purchase_enable');
			/*Get the referral purchase value*/
			$mwb_referral_purchase_value = $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_referal_purchase_value');
			/*Assign Default value to 1*/
			$mwb_referral_purchase_value = ($mwb_referral_purchase_value == 0)?1:$mwb_referral_purchase_value;
			$mwb_referral_purchase_limit = $public_obj->mwb_wpr_get_general_settings_num('mwb_referral_purchase_limit');

			$mwb_wpr_general_referal_order_limit = $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_referal_order_limit');
			$mwb_referral_purchase_limit = ($mwb_referral_purchase_limit == 0)?1:$mwb_referral_purchase_limit;
			if($new_status == 'completed') {
				$order = wc_get_order($order_id);
				/*Referral Purchase*/
				if($mwb_referral_purchase_enable) {
					$user_id = $order->get_user_id();
					$mwb_wpr_ref_noof_order = get_user_meta($user_id,'mwb_wpr_no_of_orders',true);
					$refer_id = get_user_meta($user_id,'user_visit_through_link',true);
					$refer_user=get_user_by('ID',$refer_id);
					/*Check that Refer is not empty*/
					if(!empty($refer_user)) {
						$refer_user_email=$refer_user->user_email;
						$referee_user_name = $refer_user->user_firstname;
					}
					if($mwb_referral_purchase_limit == 0) {
						if(isset($refer_id) && !empty($refer_id)) {
							/*Get total points of the referred user*/
							$prev_points_of_ref_userid = (int)get_user_meta($refer_id , 'mwb_wpr_points', true);
							$update_points = $prev_points_of_ref_userid + $mwb_referral_purchase_value;
							/*Update users Total points*/
							update_user_meta( $refer_id  , 'mwb_wpr_points' , $update_points );
							/*Update points details*/
							$data = array(
									'referr_id' => $user_id,
									);
							$public_obj->mwb_wpr_update_points_details($refer_id,'ref_product_detail',$mwb_referral_purchase_value,$data);
							/*Shortcode Array*/
							$mwb_wpr_shortcode = array(
								'[Points]' => $mwb_referral_purchase_value,
								'[Total Points]' => $update_points,
								'[Refer Points]' => $mwb_refer_value,
								"[Comment Points]" => $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_comment_value'),
								"[Per Currency Spent Points]" => $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_coupon_conversion_points'),
								"[USERNAME]" => $referee_user_name,	
								);

							/*Insert id of the subject and email subjects*/
							$mwb_wpr_subject_content = array(
								'mwb_wpr_subject' => 'mwb_wpr_referral_purchase_email_subject',
								'mwb_wpr_content' => 'mwb_wpr_referral_purchase_email_discription_custom_id',
								);

							/*Send mail to client regarding product purchase*/
							$public_obj->mwb_wpr_send_notification_mail_product($refer_id,$mwb_referral_purchase_value,$mwb_wpr_shortcode,$mwb_wpr_subject_content);
						}
					}
					else {
						if(isset($mwb_wpr_ref_noof_order) && !empty($mwb_wpr_ref_noof_order) && $mwb_wpr_ref_noof_order <= $mwb_wpr_general_referal_order_limit) {
							/*Check Refer is is not empty*/
							if(isset($refer_id) && !empty($refer_id)) {
								$prev_points_of_ref_userid = (int)get_user_meta($refer_id , 'mwb_wpr_points', true);
								
								$update_points = $prev_points_of_ref_userid + $mwb_referral_purchase_value;

								/*Update users Total points*/
								update_user_meta( $refer_id  , 'mwb_wpr_points' , $update_points );
								/*Update points details*/
								$data = array(
									'referr_id' => $user_id,
									);
								$public_obj->mwb_wpr_update_points_details($refer_id,'ref_product_detail',$mwb_referral_purchase_value,$data);
								/*Insert id of the subject and email subjects*/
								$mwb_wpr_subject_content = array(
									'mwb_wpr_subject' => 'mwb_wpr_referral_purchase_email_subject',
									'mwb_wpr_content' => 'mwb_wpr_referral_purchase_email_discription_custom_id',
									);
								/*Shortcode Array*/
								$mwb_wpr_shortcode = array(
									'[Points]' => $mwb_referral_purchase_value,
									'[Total Points]' => $update_points,
									'[Refer Points]' => $mwb_refer_value,
									"[Comment Points]" => $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_comment_value'),
									"[Per Currency Spent Points]" => $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_coupon_conversion_points'),
									"[USERNAME]" => $referee_user_name,	
									);
								/*Send mail to client regarding product purchase*/
								$public_obj->mwb_wpr_send_notification_mail_product($refer_id,$mwb_referral_purchase_value,$mwb_wpr_shortcode,$mwb_wpr_subject_content);
							}
						}
					}
				}
			}
		}
	}

	/**
	* This function use to add coupon generation 
	* 
	* @name mwb_wpr_woocommerce_order_status_changed
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_add_coupon_conversion_settings($user_id) {
		$public_obj = $this->generate_public_obj();
		/*Check is checkbox is enable*/
		$mwb_wpr_disable_coupon_generation = $public_obj->mwb_wpr_get_coupon_settings_num("mwb_wpr_enable_coupon_generation");
		/*Get Coupon Redeem Points*/
		$coupon_redeem_points = $public_obj->mwb_wpr_get_coupon_settings_num("mwb_wpr_coupon_redeem_points");
		$coupon_redeem_points = ($coupon_redeem_points)?1:$coupon_redeem_points;
		/*Check Coupon Redeem Price*/
		$coupon_redeem_price = $public_obj->mwb_wpr_get_coupon_settings_num("mwb_wpr_coupon_redeem_price");
		$coupon_redeem_price = ($coupon_redeem_price)?1:$coupon_redeem_price;
		/*Get total points of the users*/
		$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);
		/*Get minimum pints value for coupon generation*/
		$mwb_minimum_points_value = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_general_minimum_value');
		$coupon_redeem_price = ($coupon_redeem_price)?50:$coupon_redeem_price;
		/*Check Enable Custom Convert Points*/
		$enable_custom_convert_point = $public_obj->mwb_wpr_get_coupon_settings_num("mwb_wpr_general_custom_convert_enable");
		if($mwb_wpr_disable_coupon_generation == 1) {
			?>
			<p class="mwb_wpr_heading"><?php echo __( 'Points Conversion', 'ultimate-woocommerce-points-and-rewards' ); ?></p>
			<fieldset class="mwb_wpr_each_section">
				<p>
					<?php echo __( 'Points Conversion: ', 'ultimate-woocommerce-points-and-rewards' ); ?>
					<?php echo $coupon_redeem_points.__(' points = ','ultimate-woocommerce-points-and-rewards').get_woocommerce_currency_symbol().$coupon_redeem_price; ?>
				</p>
				<form id="points_form" enctype="multipart/form-data" action="" method="post">
					<?php 
					if(is_numeric($mwb_minimum_points_value)) {
						if($mwb_minimum_points_value <= $get_points) {
							if($enable_custom_convert_point == 1) {
								?>
								<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
									<label for="mwb_custom_text">
										<?php _e('Enter your points:','ultimate-woocommerce-points-and-rewards'); ?>
									</label>
									<p id="mwb_wpr_points_notification"></p>
									<input type="number" class="woocommerce-Input woocommerce-Input--number input-number" name="mwb_custom_number" min="1" id="mwb_custom_point_num" style="width: 160px;">

									<input type="button" name="mwb_wpr_custom_coupon" class="mwb_wpr_custom_coupon button" value="<?php _e('Generate Coupon','ultimate-woocommerce-points-and-rewards');?>" data-id="<?php echo $user_id; ?>">
								</p>
								<?php
							}
							else {
								_e('Convert Points To Coupon','ultimate-woocommerce-points-and-rewards');
								?>
								<p id="mwb_wpr_points_notification"></p>
								<input type="button" name="mwb_wpr_generate_coupon" class="mwb_wpr_generate_coupon button" value="<?php _e('Generate Coupon','ultimate-woocommerce-points-and-rewards');?>" data-id="<?php echo $user_id; ?>">
								<?php
							}
						}
						else {	
							printf(__('Minimum points required to convert points to coupon is %u', 'ultimate-woocommerce-points-and-rewards'),$mwb_minimum_points_value);
						}
					}
					?>
				</form>
			</fieldset>
			<?php 
		}
	}

	/**
	* This is used for listing of the Coupons
	* 
	* @name mwb_wpr_list_coupons_generation
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	* @param $user_id
	*/
	public function mwb_wpr_list_coupons_generation($user_id) {
		$user_log = get_user_meta( $user_id, 'mwb_wpr_user_log', true);
		if(isset($user_log) && is_array($user_log) && !empty($user_log)) {
			?>	
			<p class="mwb_wpr_heading"><?php echo __( 'Coupon Details', 'ultimate-woocommerce-points-and-rewards' ); ?></p>
			<div class="points_log">
				<table class="woocommerce-MyAccount-points shop_table my_account_points account-points-table mwb_wpr_coupon_details">
					<thead>
						<tr>
							<th class="points-points">
								<span class="nobr"><?php echo __( 'Points', 'ultimate-woocommerce-points-and-rewards' ); ?></span>
							</th>
							<th class="points-code">
								<span class="nobr"><?php echo __( 'Coupon Code', 'ultimate-woocommerce-points-and-rewards' ); ?></span>
							</th>
							<th class="points-amount">
								<span class="nobr"><?php echo __( 'Coupon Amount', 'ultimate-woocommerce-points-and-rewards' ); ?></span>
							</th>
							<th class="points-left">
								<span class="nobr"><?php echo __( 'Amount Left', 'ultimate-woocommerce-points-and-rewards' ); ?></span>
							</th>
							<th class="points-expiry">
								<span class="nobr"><?php echo __( 'Expiry', 'ultimate-woocommerce-points-and-rewards' ); ?></span>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php  foreach ( $user_log as $key => $mwb_user_log ) : ?>
							<tr class="points">
								<?php foreach ( $mwb_user_log as $column_id => $column_name ) : ?>
									<td class="points-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_id ); ?>" >
										<?php

										$mwb_split = explode("#",$key);
										if($column_id == 'left'){
											$column_name = get_post_meta( $mwb_split[1], 'coupon_amount',true);
											echo get_woocommerce_currency_symbol().$column_name;
										}
										elseif($column_id == 'expiry'){
											if(WC()->version < "3.0.6") {

												$column_name = get_post_meta( $mwb_split[1], 'expiry_date',true );
												echo $column_name;
											}
											else {
												$column_name = get_post_meta( $mwb_split[1], 'date_expires',true );
												$dt = new DateTime("@$column_name");
												echo $dt->format('Y-m-d');
												
											}
										}
										else
										{
											echo $column_name;
										}
										?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>			
			<?php
		}
		else { ?>
			<div class="points_log" style="display: none"></div>
			<?php
		}
	}

	/**
	* This function is used to generate coupon of total points.
	* 
	* @name mwb_wpr_generate_original_coupon
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_generate_original_coupon() {
		/*Check Ajax Nonce*/
		check_ajax_referer( 'mwb-wpr-verify-nonce', 'mwb_nonce' );
		/*Create object of the public object*/
		$public_obj = $this->generate_public_obj();
		$response['result'] = false;
		$response['message'] = __("Coupon generation error.",'ultimate-woocommerce-points-and-rewards');
		if(isset($_POST['user_id']) && !empty($_POST['user_id'])) {
			/*Get the the user id*/
			$user_id = sanitize_post($_POST['user_id']);
			/*Get all user points*/
			$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);
            /*Get the coupon length*/
			$mwb_coupon_length = $public_obj->mwb_wpr_get_coupon_settings_num("mwb_wpr_points_coupon_length");
			
			$mwb_coupon_length = ($mwb_coupon_length = 0)?1:$mwb_coupon_length;

			$tot_points = (isset($get_points) && $get_points != null)? (int)$get_points: 0;
			if($tot_points) {
				/*Generate the coupon number*/
				$couponnumber = mwb_wpr_coupon_generator($mwb_coupon_length);
				/*Get the Redeem Price*/
				$coupon_redeem_price = $public_obj->mwb_wpr_get_coupon_settings_num("mwb_wpr_coupon_redeem_points");
				$coupon_redeem_price = ($coupon_redeem_price == 0)?1:$coupon_redeem_price;
				/*Get the coupon Redeem Points*/
				$coupon_redeem_points = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_coupon_redeem_price');
				$coupon_redeem_points = ($coupon_redeem_points == 0)?1:$coupon_redeem_points;

				$coupon_redeem_price = str_replace( wc_get_price_decimal_separator(), '.', strval( $coupon_redeem_price ) );

				$couponamont = ($get_points * $coupon_redeem_price)/$coupon_redeem_points;


				$couponamont = str_replace( '.',wc_get_price_decimal_separator(), strval( $couponamont ) );


				if($this->mwb_wpr_create_points_coupon($couponnumber, $couponamont, $user_id, $get_points))
				{
					$user_log = get_user_meta( $user_id, 'mwb_wpr_user_log', true);
					$response['html'] = '<table class="woocommerce-MyAccount-points shop_table shop_table_responsive my_account_points account-points-table">
					<thead>
						<tr>
							<th class="points-points">
								<span class="nobr">'.__( "Points", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
							<th class="points-code">
								<span class="nobr">'.__( "Coupon Code", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
							<th class="points-amount">
								<span class="nobr">'.__( "Coupon Amount", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
							<th class="points-left">
								<span class="nobr">'.__( "Amount Left", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
							<th class="points-expiry">
								<span class="nobr">'.__( "Expiry", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
						</tr>
					</thead>
					<tbody>';

						foreach ( $user_log as $key => $mwb_user_log )
						{
							$response['html'].='<tr class="points">';
							foreach ( $mwb_user_log as $column_id => $column_name )
							{
								$response['html'].= '<td class="points-'.esc_attr( $column_id ).'" >';
								if($column_id == 'left')
								{
									$mwb_split = explode("#",$key);
									$column_name = get_post_meta( $mwb_split[1], 'coupon_amount',true);
									$response['html'].=get_woocommerce_currency_symbol().$column_name;
								}
								elseif($column_id == 'expiry'){
									if(WC()->version < "3.0.6") {

										$column_name = get_post_meta( $mwb_split[1], 'expiry_date',true );
										$response['html'].= $column_name;
									}
									else {
										$column_name = get_post_meta( $mwb_split[1], 'date_expires',true );
										$dt = new DateTime("@$column_name");
										$response['html'].= $dt->format('Y-m-d');

									}
								}
								else {
									$response['html'].=$column_name;
								}
								$response['html'].='</td>';
							}
							$response['html'].='</tr>';
						}
						$response['html'].='</tbody>
					</table>';
					$response['result'] = true;
					$response['message'] = __('Your points are converted to coupon', 'ultimate-woocommerce-points-and-rewards');
					$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);
					$response['points'] = $get_points;
				}
			}
		}
		echo json_encode($response);
		wp_die();
		
	}

	/**
	* This function is used to generate coupon according to points.
	* 
	* @name mwb_wpr_create_points_coupon
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_create_points_coupon($couponnumber, $couponamont, $user_id, $points) {
		/*Create object of the public object*/
		$public_obj = $this->generate_public_obj();
		/*Coupon Code*/
		$coupon_code = $couponnumber; // Code
		$amount = $couponamont; // Amount
		$woo_ver = WC()->version;//Version
		$discount_type = 'fixed_cart'; 
		$coupon_description =  __("Points And Reward - User ID#",'ultimate-woocommerce-points-and-rewards').$user_id;

		$coupon = array(
			'post_title' => $coupon_code,
			'post_content' => $coupon_description,
			'post_excerpt' => $coupon_description,
			'post_status' => 'publish',
			'post_author' => $user_id,
			'post_type'		=> 'shop_coupon'
			);

		$new_coupon_id = wp_insert_post( $coupon );
		/*Get the settings of the Individual Coupon Settings*/
		$individual_use = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_coupon_individual_use');

		if($individual_use) {
			$individual_use = "yes";
		}
		else {
			$individual_use = "no";
		}
		/*Get the value of the shipping from the coupon settings*/
		$free_shipping = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_points_freeshipping');
		if($free_shipping) {
			$free_shipping = "yes";
		}
		else {
			$free_shipping = "no";
		}
		/*Get the coupon length*/
		$mwb_coupon_length = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_points_coupon_length');
		$mwb_coupon_length = ($mwb_coupon_length == 0)?5:$mwb_coupon_length;
		/*Get the expriy date of the coupons*/
		$expiry_date = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_coupon_expiry');
		/*Get the coupon minimum expend*/
		$minimum_amount = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_coupon_minspend');
		/*Get the max expend of the coupon*/
		$maximum_amount = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_coupon_maxspend');

		$usage_limit = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_coupon_use');
		/*Get the current date*/
		$todaydate = date_i18n("Y-m-d");
		if($expiry_date > 0 ) {
			$expirydate = date_i18n( "Y-m-d", strtotime( "$todaydate +$expiry_date day" ) );
		}
		else {
			$expirydate = "";
		}
		/*Get the user data*/
		$user = get_user_by('ID',$user_id);
		/*Get the user mail*/
		$user_email=$user->user_email;
		/*update post meta of the coupon*/
		update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
		update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
		update_post_meta( $new_coupon_id, 'individual_use', $individual_use );
		update_post_meta( $new_coupon_id, 'usage_limit', $usage_limit );
		/*Coupons Expriry date*/
		if(!empty($expirydate)) {
			if($woo_ver < "3.6.0") {
				update_post_meta( $new_coupon_id, 'expiry_date', $expirydate );
			}
			else {
				$expirydate = strtotime($expirydate);
				update_post_meta( $new_coupon_id, 'date_expires', $expirydate );
			}
		}
		update_post_meta( $new_coupon_id, 'free_shipping', $free_shipping );
		update_post_meta( $new_coupon_id, 'minimum_amount', $minimum_amount );
		update_post_meta( $new_coupon_id, 'maximum_amount', $maximum_amount );
		update_post_meta( $new_coupon_id, 'customer_email', $user_email);
		update_post_meta( $new_coupon_id, 'mwb_wpr_points_coupon', $user_id );
		if( empty($expirydate ) ){
			$expirydate = __('No Expiry', 'ultimate-woocommerce-points-and-rewards');
		}

		$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);

		$available_points = $get_points - $points;
		$coupon_point_detail = get_user_meta($user_id, 'points_details',true);

		$today_date = date_i18n("Y-m-d h:i:sa");

		if(isset($coupon_point_detail['Coupon_details']) && !empty($coupon_point_detail['Coupon_details'])) {
			$coupon_array = array(
				'Coupon_details'=>$points,
				'date'=>$today_date);
			$coupon_point_detail['Coupon_details'][] = $coupon_array;
		}
		else {
			if(!is_array($coupon_point_detail)) {
				$coupon_point_detail = array();
			}
			$coupon_array = array(
				'Coupon_details'=>$points,
				'date'=>$today_date);
			$coupon_point_detail['Coupon_details'][] = $coupon_array;
		}
		update_user_meta( $user_id , 'mwb_wpr_points' , $available_points );
		update_user_meta( $user_id, 'points_details', $coupon_point_detail );
		$user_log = get_user_meta( $user_id, 'mwb_wpr_user_log',true);
		if(empty($user_log)) {
			$user_log = array();
			$user_log['mwb_wpr_'.$coupon_code."#".$new_coupon_id] = array(
				'points'=> $points,
				'code'=> $coupon_code,
				'camount' => get_woocommerce_currency_symbol().$amount,
				'left'=> get_woocommerce_currency_symbol().$amount,
				'expiry' => $expirydate);
		}
		else {
			$user_log['mwb_wpr_'.$coupon_code."#".$new_coupon_id] = array(
				'points'=> $points,
				'code'=> $coupon_code,
				'camount' => get_woocommerce_currency_symbol().$amount,
				'left'=> get_woocommerce_currency_symbol().$amount,
				'expiry' => $expirydate
				);
		}
		update_user_meta( $user_id ,'mwb_wpr_user_log', $user_log );
		return true;
	}

	/**
	* This function is used to generate coupon for custom points.
	* 
	* @name mwb_wpr_generate_custom_coupon
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_generate_custom_coupon() {
		/*Create object of the public object*/
		$public_obj = $this->generate_public_obj();
		/*Check Ajax Referer*/
		check_ajax_referer( 'mwb-wpr-verify-nonce', 'mwb_nonce' );
		$response['result'] = false;
		$response['message'] = __("Coupon generation error.",'ultimate-woocommerce-points-and-rewards');
		if(isset($_POST['points']) && !empty($_POST['points']))
		{
			$user_id = sanitize_post($_POST['user_id']);
			$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);
			$custom_points = sanitize_post($_POST['points']);

			/*Get the coupon length*/
			$mwb_coupon_length = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_points_coupon_length');
			$mwb_coupon_length = ($mwb_coupon_length == 0)?5:$mwb_coupon_length;

			if( $custom_points <= $get_points )
			{

				/*Generate the coupon number*/
				$couponnumber = mwb_wpr_coupon_generator($mwb_coupon_length);
				/*Get the Redeem Price*/
				$coupon_redeem_price = $public_obj->mwb_wpr_get_coupon_settings_num("mwb_wpr_coupon_redeem_points");
				$coupon_redeem_price = ($coupon_redeem_price == 0)?1:$coupon_redeem_price;
				/*Get the coupon Redeem Points*/
				$coupon_redeem_points = $public_obj->mwb_wpr_get_coupon_settings_num('mwb_wpr_coupon_redeem_price');
				$coupon_redeem_points = ($coupon_redeem_points == 0)?1:$coupon_redeem_points;


				$coupon_redeem_price = str_replace( wc_get_price_decimal_separator(), '.', strval( $coupon_redeem_price ) );

				$couponamont = ($custom_points * $coupon_redeem_price)/$coupon_redeem_points;


				$couponamont = str_replace( '.',wc_get_price_decimal_separator(), strval( $couponamont ) );
				if($this->mwb_wpr_create_points_coupon($couponnumber, $couponamont, $user_id, $custom_points))
				{
					$user_log = get_user_meta( $user_id, 'mwb_wpr_user_log', true);
					$response['html'] = '<table class="woocommerce-MyAccount-points shop_table shop_table_responsive my_account_points account-points-table">
					<thead>
						<tr>
							<th class="points-points">
								<span class="nobr">'.__( "Points", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
							<th class="points-code">
								<span class="nobr">'.__( "Coupon Code", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
							<th class="points-amount">
								<span class="nobr">'.__( "Coupon Amount", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
							<th class="points-left">
								<span class="nobr">'.__( "Amount Left", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
							<th class="points-expiry">
								<span class="nobr">'.__( "Expiry", 'ultimate-woocommerce-points-and-rewards' ).'</span>
							</th>
						</tr>
					</thead>
					<tbody>';		
						foreach ( $user_log as $key => $mwb_user_log )
						{
							$response['html'].='<tr class="points">';
							foreach ( $mwb_user_log as $column_id => $column_name )
							{
								$response['html'].= '<td class="points-'.esc_attr( $column_id ).'" >';
								if($column_id == 'left')
								{
									$mwb_split = explode("#",$key);
									$column_name = get_post_meta( $mwb_split[1], 'coupon_amount',true);
									$response['html'].=get_woocommerce_currency_symbol().$column_name;
								}
								elseif($column_id == 'expiry'){
									if(WC()->version < "3.0.6") {

										$column_name = get_post_meta( $mwb_split[1], 'expiry_date',true );
										$response['html'].=  $column_name;
									}
									else {
										$column_name = get_post_meta( $mwb_split[1], 'date_expires',true );
										$dt = new DateTime("@$column_name");
										$response['html'].= $dt->format('Y-m-d');

									}
								}
								else
								{
									$response['html'].=$column_name;
								}
								$response['html'].='</td>';
							}
							$response['html'].='</tr>';
						}
						$response['html'].='</tbody>
					</table>';
					$response['result'] = true;
					$response['message'] = __('Your points are converted to coupon', 'ultimate-woocommerce-points-and-rewards');
					$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);
					$response['points'] = $get_points;
				}
			}
			else
			{
				$response['result'] = false;
				$response['message'] = __('Points cannot be greater than your current points', 'ultimate-woocommerce-points-and-rewards');
			}
		}
		echo json_encode($response);
		wp_die();
	}

	/**
	* This function is used to maintain coupon value of latest version of woocommerce.
	* 
	* @name mwb_wpr_woocommerce_order_add_coupon_woo_latest_version
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_order_add_coupon_woo_latest_version($item_id,$item) {
		if(get_class($item)=='WC_Order_Item_Coupon') {
			$mwp_wpr_coupon_code=$item->get_code();
			$the_coupon = new WC_Coupon($mwp_wpr_coupon_code);
			if(isset($the_coupon)) {
				$mwp_wpr_discount_amount=$item->get_discount();
				$mwp_wpr_discount_amount_tax=$item->get_discount_tax();
				$mwp_wpr_coupon_id = $the_coupon->get_id();
				$pointscoupon = get_post_meta( $mwp_wpr_coupon_id, 'mwb_wpr_points_coupon', true );
				if(!empty($pointscoupon)) {
					$amount = get_post_meta( $mwp_wpr_coupon_id, 'coupon_amount', true );
					$total_discount =$mwp_wpr_discount_amount+$mwp_wpr_discount_amount_tax;
					if( $amount < $total_discount ) {
						$remaining_amount = 0;
					}
					else {
						$remaining_amount = $amount - $total_discount;
					}	
					if(!empty($amount)) {

						update_post_meta( $mwp_wpr_coupon_id, 'coupon_amount_before_use',$amount);
					}		
					update_post_meta( $mwp_wpr_coupon_id, 'coupon_amount', $remaining_amount );
				}
			}
		}
	}

	/**
	* This function is used to add the share points section in the myaccount page
	* 
	* @name mwb_wpr_share_points_section
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_share_points_section($user_id) {
		/*Create object of the public object*/
		$mwb_wpr_user_can_send_point = 0;
		$other_settings = get_option('mwb_wpr_other_settings',true);
		if(!empty($other_settings['mwb_wpr_user_can_send_point'])) {
			$mwb_wpr_user_can_send_point = (int)$other_settings['mwb_wpr_user_can_send_point'];
		}
		if($mwb_wpr_user_can_send_point){
			?>
			<p class="mwb_wpr_heading"><?php echo __( 'Share Points', 'ultimate-woocommerce-points-and-rewards' ); ?></p>
			<fieldset class="mwb_wpr_each_section">
				<p id="mwb_wpr_shared_points_notification"></p>
				<input type="email" style="width: 45%;" id="mwb_wpr_enter_email" placeholder="<?php _e('Enter Email','ultimate-woocommerce-points-and-rewards');?>">
				<input type="number" id="mwb_wpr_enter_point" placeholder="<?php _e('Points','ultimate-woocommerce-points-and-rewards');?>" style="width: 20%;">
				<input id="mwb_wpr_share_point" data-id="<?php echo $user_id; ?>"type="button" name="mwb_wpr_share_point" value="<?php _e('GO','ultimate-woocommerce-points-and-rewards');?>">
			</fieldset>	
			<div id="mwb_wpr_loader" style="display: none;">
				<img src="<?php echo plugin_dir_url( __FILE__ );?>/images/loading.gif">
			</div>
			<?php
		}
	}

	/**
	* The function is for share the points to other member for same site
	* 
	* @name mwb_wpr_sharing_point_to_other
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_sharing_point_to_other() {
		check_ajax_referer( 'mwb-wpr-verify-nonce', 'mwb_nonce' );
		$response['result'] = false;
		/*Create object of the public object*/
		$public_obj = $this->generate_public_obj();
		$response['message'] = __("Error during Point Sharing. Try Again!",'ultimate-woocommerce-points-and-rewards');
		/*Get the points to be send*/
		$mwb_wpr_shared_point = (int)sanitize_post($_POST['shared_point']);
		/*Get the user id of the user*/
		$user_id = sanitize_post($_POST['user_id']);
		$user=get_user_by('ID',$user_id);
		$sender_email=$user->user_email;
		$mwb_wpr_email = sanitize_post($_POST['email_id']);
		if(isset($user_id) && !empty($user_id)  && isset($mwb_wpr_shared_point) && !empty($mwb_wpr_shared_point)) {
			/*Check isset email*/
			if(isset($mwb_wpr_email) && !empty($mwb_wpr_email)) {
				/*get the providers points*/
				$providers_points = (int)get_user_meta($user_id,'mwb_wpr_points',true);
				$mwb_wpr_receiver = get_user_by( 'email', $mwb_wpr_email );
				$mwb_wpr_receiver_id = $mwb_wpr_receiver->data->ID;
				if(isset($mwb_wpr_receiver) && !empty($mwb_wpr_receiver)){
					if($providers_points >= $mwb_wpr_shared_point){

						$receivers_points = (int)get_user_meta($mwb_wpr_receiver_id,'mwb_wpr_points',true);
						$receivers_updated_point = $receivers_points + $mwb_wpr_shared_point;
						/*Update points logs*/
						$data = array(
								'type' => 'received_by',
								'user_id' => $user_id
							);
						$public_obj->mwb_wpr_update_points_details($mwb_wpr_receiver_id,'Receiver_point_details',$mwb_wpr_shared_point,$data);
					    /*Update user points*/
						update_user_meta($mwb_wpr_receiver_id,'mwb_wpr_points',$receivers_updated_point);
						$providers_updated_point = $providers_points - $mwb_wpr_shared_point;
						/*Update points logs*/
						$data = array(
								'type' => 'give_to',
								'user_id' => $mwb_wpr_receiver_id
							);
						$public_obj->mwb_wpr_update_points_details($user_id,'Receiver_point_details',$mwb_wpr_shared_point,$data);
						/*Update the total points*/
						update_user_meta($user_id,'mwb_wpr_points',$providers_updated_point);
						$available_points = get_user_meta($user_id,'mwb_wpr_points',true);
						$mwb_wpr_shortcode = array(
							'[Total Points]' => $receivers_updated_point,
							"[USERNAME]" => $mwb_wpr_receiver->user_firstname,
							'[RECEIVEDPOINT]' => $mwb_wpr_shared_point,
							'[SENDEREMAIL]'  => $sender_email,
							);
						$mwb_wpr_subject_content = array(
							'mwb_wpr_subject' => 'mwb_wpr_point_sharing_subject',
							'mwb_wpr_content' => 'mwb_wpr_point_sharing_description',
							);
						/*Send mail to client regarding product purchase*/
						$public_obj->mwb_wpr_send_notification_mail_product($mwb_wpr_receiver_id,$mwb_wpr_shared_point,$mwb_wpr_shortcode,$mwb_wpr_subject_content);
						$response['result'] = true;
						$response['message'] = __("Points assigned succesfuly",'ultimate-woocommerce-points-and-rewards');
						$response['available_points'] = $available_points;

					}else{

						$response['result'] = false;
						$response['message'] = __("Entered Point should be less than your Total Point",'ultimate-woocommerce-points-and-rewards');
					}
				}else{

					$response['result'] = false;
					$response['message'] = __("Please Enter Valid Email",'ultimate-woocommerce-points-and-rewards');
				}
			}
			else{

				$response['result'] = false;
				$response['message'] = __("Please Enter Email",'ultimate-woocommerce-points-and-rewards');
			}
		}
		else {
				
				$response['result'] = false;
				$response['message'] = __("Please fill Required feilds",'ultimate-woocommerce-points-and-rewards');
		}
		echo json_encode($response);
		wp_die();
	}

	/**
	 * This function will add checkbox for purchase the products through points
	 * 
	 * @name mwb_wpr_woocommerce_before_add_to_cart_button
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function mwb_wpr_woocommerce_before_add_to_cart_button($product) {
		global $product;
		$product_id = $product->get_id();
		$today_date = date_i18n("Y-m-d");
		/*Create object of the public object*/
		$public_obj = $this->generate_public_obj();
		$check_disbale = get_post_meta($product_id, 'mwb_product_purchase_through_point_disable', 'no');
		if(empty($check_disbale)) {
			$check_disbale = 'no';
		}
		$_product = wc_get_product( $product_id );
		$product_is_variable = $public_obj->mwb_wpr_check_whether_product_is_variable($_product);	
		$price = $_product->get_price();
		$user_ID = get_current_user_ID();
		$get_points = (int)get_user_meta($user_ID, 'mwb_wpr_points', true);
		$user_level = get_user_meta($user_ID,'membership_level',true);
		$mwb_wpr_mem_expr = get_user_meta($user_ID,'membership_expiration',true);
		/*Get the settings of the purchase points*/
		$enable_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_points');
		/*Check the restrction */
		$mwb_wpr_restrict_pro_by_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_restrict_pro_by_points');
		/*Product purchase product text*/
		$mwb_wpr_purchase_product_text = $this->mwb_wpr_get_product_purchase_settings('mwb_wpr_purchase_product_text');
		/*Check not product text should not be empty*/
		if (empty($mwb_wpr_purchase_product_text)) {
			$mwb_wpr_purchase_product_text = __('Use your Points for purchasing this Product','ultimate-woocommerce-points-and-rewards');
		}
		$mwb_wpr_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_purchase_points');
		$mwb_wpr_purchase_points = (0 == $mwb_wpr_purchase_points)?1:$mwb_wpr_purchase_points;
		$new_price = 1;
		/*Get the price eqivalent to the purchase*/
		$mwb_wpr_product_purchase_price = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_price');
		$mwb_wpr_product_purchase_price = (0 == $mwb_wpr_product_purchase_price)?1:$mwb_wpr_product_purchase_price;
		/*Get the membership role*/
		$membership_settings_array = get_option('mwb_wpr_membership_settings',true);
		$mwb_wpr_membership_roles = isset($membership_settings_array['membership_roles']) && !empty($membership_settings_array['membership_roles']) ? $membership_settings_array['membership_roles'] : array();
		$mwb_wpr_categ_list = $this->mwb_wpr_get_product_purchase_settings('mwb_wpr_restrictions_for_purchasing_cat');
		if(empty($mwb_wpr_categ_list)) {
			$mwb_wpr_categ_list = array();
		}
		if( $enable_purchase_points && !$product_is_variable) {	
			if(!$mwb_wpr_restrict_pro_by_points &&  'no' == $check_disbale){
				if(isset($user_level) && !empty($user_level)) {	
					if( isset( $mwb_wpr_mem_expr ) && !empty( $mwb_wpr_mem_expr ) && $today_date <= $mwb_wpr_mem_expr ){
						if(is_array($mwb_wpr_membership_roles[$user_level]) && !empty($mwb_wpr_membership_roles[$user_level])){	
							if(is_array($mwb_wpr_membership_roles[$user_level]['Product']) && !empty($mwb_wpr_membership_roles[$user_level]['Product'])){
								if(in_array($product_id, $mwb_wpr_membership_roles[$user_level]['Product']) && !$public_obj->check_exclude_sale_products($_product)){	
									$new_price = $price - ($price * $mwb_wpr_membership_roles[$user_level]['Discount'])/100;
								}
								else{	
									$new_price = $_product->get_price();
								}
							}
							else
							{
								$terms = get_the_terms ( $product_id, 'product_cat' );
								if(is_array($terms) && !empty($terms)){
									foreach ( $terms as $term ) {
										$cat_id = $term->term_id;
										$parent_cat = $term->parent;
										if( (in_array( $cat_id, $mwb_wpr_membership_roles[$user_level]['Prod_Categ'] ) || in_array( $parent_cat, $mwb_wpr_membership_roles[$user_level]['Prod_Categ'] )) && !$public_obj->check_exclude_sale_products($_product) ){	
											$new_price = $price - ($price * $mwb_wpr_membership_roles[$user_level]['Discount'])/100;
											break;
										}
										else{
											$new_price = $_product->get_price();
										}
									}
								}
							}
							$points_calculation =ceil(($new_price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);
							if($points_calculation <= $get_points){
								?>	
								<label for="mwb_wpr_pro_cost_to_points">
									<input type="checkbox" name="mwb_wpr_pro_cost_to_points" id="mwb_wpr_pro_cost_to_points" class="input-text" value="<?php echo $points_calculation;?>"> <?php echo $mwb_wpr_purchase_product_text;?>
								</label>
								<input type="hidden" name="mwb_wpr_hidden_points" class="mwb_wpr_hidden_points" value="<?php echo $points_calculation;?>">
								<p class="mwb_wpr_purchase_pro_point"><?php _e('Spend ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$points_calculation.'</span>'; _e(' Points for Purchasing this Product for Single Quantity','ultimate-woocommerce-points-and-rewards')?></p>
								<span class="mwb_wpr_notice"></span>
								<div class="mwb_wpr_enter_some_points" style="display: none;">
									<input type="number" name="mwb_wpr_some_custom_points" id="mwb_wpr_some_custom_points" value="<?php echo $points_calculation;?>">
								</div>
								<?php
							}
							else{
								$extra_need = $points_calculation - $get_points; ?>

								<p class="mwb_wpr_purchase_pro_point"><?php _e('You need extra ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$extra_need.'</span>'; _e(' Points for get this product for free','ultimate-woocommerce-points-and-rewards')?></p>
								<?php	
							}		
						}
					}
					else{
						$points_calculation =ceil(($price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);
						if($points_calculation <= $get_points){
							?>
							<label for="mwb_wpr_pro_cost_to_points">
								<input type="checkbox" name="mwb_wpr_pro_cost_to_points" id="mwb_wpr_pro_cost_to_points" class="input-text" value="<?php echo $points_calculation;?>"> <?php echo $mwb_wpr_purchase_product_text;?>
							</label>
							<p class="mwb_wpr_purchase_pro_point"><?php _e('Spend ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$points_calculation.'</span>'; _e(' Points for Purchasing this Product for Single Quantity','ultimate-woocommerce-points-and-rewards')?></p>
							<input type="hidden" name="mwb_wpr_hidden_points" class="mwb_wpr_hidden_points" value="<?php echo $points_calculation;?>">
							<span class="mwb_wpr_notice"></span>
							<div class="mwb_wpr_enter_some_points" style="display: none;">
								<input type="number" name="mwb_wpr_some_custom_points" id="mwb_wpr_some_custom_points" value="<?php echo $points_calculation;?>">
							</div>
							<?php
						}
						else{
							$extra_need = $points_calculation - $get_points; ?>
							<p class="mwb_wpr_purchase_pro_point"><?php _e('You need extra  ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$extra_need.'</span>'; _e(' Points for get this product for free','ultimate-woocommerce-points-and-rewards')?></p>
							<?php	
						}
					}
				}
				else{
					$points_calculation =ceil(($price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);
					if($points_calculation <= $get_points){
						?>
						<label for="mwb_wpr_pro_cost_to_points">
							<input type="checkbox" name="mwb_wpr_pro_cost_to_points" id="mwb_wpr_pro_cost_to_points" class="input-text" value="<?php echo $points_calculation;?>"> <?php echo $mwb_wpr_purchase_product_text;?>
						</label>
						<p class="mwb_wpr_purchase_pro_point"><?php _e('Spend ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$points_calculation.'</span>'; _e(' Points for Purchasing this Product for Single Quantity','ultimate-woocommerce-points-and-rewards')?></p>
						<input type="hidden" name="mwb_wpr_hidden_points" class="mwb_wpr_hidden_points" value="<?php echo $points_calculation;?>">
						<span class="mwb_wpr_notice"></span>
						<div class="mwb_wpr_enter_some_points" style="display: none;">
							<input type="number" name="mwb_wpr_some_custom_points" id="mwb_wpr_some_custom_points" value="<?php echo $points_calculation;?>">
						</div>
						<?php
					}
					else{
						$extra_need = $points_calculation - $get_points; ?>
						<p class="mwb_wpr_purchase_pro_point"><?php _e('You need extra  ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$extra_need.'</span>'; _e(' Points for get this product for free','ultimate-woocommerce-points-and-rewards')?></p>
						<?php	
					}
				}
			}
			else{
				if($check_disbale == 'no'){
					$terms = get_the_terms ( $product_id, 'product_cat' );
					if(is_array($terms) && !empty($terms)){
						foreach ( $terms as $term ) {
							$cat_id = $term->term_id;
							$parent_cat = $term->parent;
							if(in_array($cat_id, $mwb_wpr_categ_list) || in_array($parent_cat, $mwb_wpr_categ_list)){
								if(isset($user_level) && !empty($user_level)){	
									if( isset( $mwb_wpr_mem_expr ) && !empty( $mwb_wpr_mem_expr ) && $today_date <= $mwb_wpr_mem_expr ){
										if(is_array($mwb_wpr_membership_roles[$user_level]) && !empty($mwb_wpr_membership_roles[$user_level])){	
											if(is_array($mwb_wpr_membership_roles[$user_level]['Product']) && !empty($mwb_wpr_membership_roles[$user_level]['Product'])){
												if(in_array($product_id, $mwb_wpr_membership_roles[$user_level]['Product']) && !$public_obj->check_exclude_sale_products($_product)){	

													$new_price = $price - ($price * $mwb_wpr_membership_roles[$user_level]['Discount'])/100;
												}
												else{	
													$new_price = $_product->get_price();
												}
											}
											else {
												$terms = get_the_terms ( $product_id, 'product_cat' );
												if(is_array($terms) && !empty($terms))
												{
														
													foreach ( $terms as $term ) 
													{
														$cat_id = $term->term_id;
														$parent_cat = $term->parent;
														if( (in_array($cat_id, $mwb_wpr_membership_roles[$user_level]['Prod_Categ']) || in_array($parent_cat, $mwb_wpr_membership_roles[$user_level]['Prod_Categ'])) && !$public_obj->check_exclude_sale_products($_product) ){	
															$new_price = $price - ($price * $mwb_wpr_membership_roles[$user_level]['Discount'])/100;
															break;
														}
														else{
															$new_price = $_product->get_price();
														}
													}
												}
											}
											$points_calculation =ceil(($new_price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);

											if($points_calculation <= $get_points)
											{

												?>
												<label for="mwb_wpr_pro_cost_to_points">
													<input type="checkbox" name="mwb_wpr_pro_cost_to_points" id="mwb_wpr_pro_cost_to_points" class="input-text" value="<?php echo $points_calculation;?>"> <?php echo $mwb_wpr_purchase_product_text;?>
												</label>
												<p class="mwb_wpr_purchase_pro_point"><?php _e('Spend ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$points_calculation.'</span>'; _e(' Points for Purchasing this Product for Single Quantity','ultimate-woocommerce-points-and-rewards')?></p>
												<input type="hidden" name="mwb_wpr_hidden_points" class="mwb_wpr_hidden_points" value="<?php echo $points_calculation;?>">
												<span class="mwb_wpr_notice"></span>
												<div class="mwb_wpr_enter_some_points" style="display: none;">
													<input type="number" name="mwb_wpr_some_custom_points" id="mwb_wpr_some_custom_points" value="<?php echo $points_calculation;?>">
												</div>
												<?php
											}
											else
											{
												$extra_need = $points_calculation - $get_points; ?>

												<p class="mwb_wpr_purchase_pro_point"><?php _e('You need extra ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$extra_need.'</span>'; _e(' Points for get this product for free','ultimate-woocommerce-points-and-rewards')?></p>
												<?php	
											}		
										}
									}
									else
									{
										$points_calculation =ceil(($price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);
										if($points_calculation <= $get_points)
										{
											?>
											<label for="mwb_wpr_pro_cost_to_points">
												<input type="checkbox" name="mwb_wpr_pro_cost_to_points" id="mwb_wpr_pro_cost_to_points" class="input-text" value="<?php echo $points_calculation;?>"> <?php echo $mwb_wpr_purchase_product_text;?>
											</label>
											<p class="mwb_wpr_purchase_pro_point"><?php _e('Spend ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$points_calculation.'</span>'; _e(' Points for Purchasing this Product for Single Quantity','ultimate-woocommerce-points-and-rewards')?></p>
											<input type="hidden" name="mwb_wpr_hidden_points" class="mwb_wpr_hidden_points" value="<?php echo $points_calculation;?>">
											<span class="mwb_wpr_notice"></span>
											<div class="mwb_wpr_enter_some_points" style="display: none;">
												<input type="number" name="mwb_wpr_some_custom_points" id="mwb_wpr_some_custom_points" value="<?php echo $points_calculation;?>">
											</div>
											<?php
										}
										else
										{
											$extra_need = $points_calculation - $get_points; ?>
											<p class="mwb_wpr_purchase_pro_point"><?php _e('You need extra ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$extra_need.'</span>'; _e(' Points for get this product for free','ultimate-woocommerce-points-and-rewards')?></p>
											<?php	
										}
									}

								}
								else
								{
									$points_calculation =ceil(($price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);
									if($points_calculation <= $get_points)
									{
										?>
										<label for="mwb_wpr_pro_cost_to_points">
											<input type="checkbox" name="mwb_wpr_pro_cost_to_points" id="mwb_wpr_pro_cost_to_points" class="input-text" value="<?php echo $points_calculation;?>"> <?php echo $mwb_wpr_purchase_product_text;?>
										</label>
										<p class="mwb_wpr_purchase_pro_point"><?php _e('Spend ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$points_calculation.'</span>'; _e(' Points for Purchasing this Product for Single Quantity','ultimate-woocommerce-points-and-rewards')?></p>
										<input type="hidden" name="mwb_wpr_hidden_points" class="mwb_wpr_hidden_points" value="<?php echo $points_calculation;?>">
										<span class="mwb_wpr_notice"></span>
										<div class="mwb_wpr_enter_some_points" style="display: none;">
											<input type="number" name="mwb_wpr_some_custom_points" id="mwb_wpr_some_custom_points" value="<?php echo $points_calculation;?>">
										</div>
										<?php
									}
									else
									{
										$extra_need = $points_calculation - $get_points; ?>
										<p class="mwb_wpr_purchase_pro_point"><?php _e('You need extra ','ultimate-woocommerce-points-and-rewards'); echo '<span class=mwb_wpr_when_variable_pro>'.$extra_need.'</span>'; _e(' Points for get this product for free','ultimate-woocommerce-points-and-rewards')?></p>
										<?php	
									}
								}
								break;
							}
						}
					}
				}
			}
		}
		elseif($enable_purchase_points && $product_is_variable) {
			echo '<div class="mwb_wpr_variable_pro_pur_using_point" style="display: none;"></div>';
		}	
	}

	/**
	* This function is used to save points in add to cart session0.
	* 
	* @name mwb_wpr_woocommerce_add_cart_item_data_pro
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_add_cart_item_data_pro($the_cart_data, $product_id, $variation_id,$quantity) {
		$public_obj = $this->generate_public_obj();
		$enable_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_points');
		/*Get the current user Id*/
		$user_id = get_current_user_ID();
		/*Get the total points of the user*/
		$get_points = (int)get_user_meta($user_id,'mwb_wpr_points',true);
		if( $enable_purchase_points ) {
			if(isset($_POST['mwb_wpr_pro_cost_to_points']) && !empty($_POST['mwb_wpr_pro_cost_to_points']) && $_POST['mwb_wpr_pro_cost_to_points']) {	
				if( $_POST['mwb_wpr_pro_cost_to_points'] > $get_points ) {
					$item_meta['pro_purchase_by_points'] = $get_points;
				}
				else{
					$item_meta['pro_purchase_by_points'] = $_POST['mwb_wpr_pro_cost_to_points'];
				}
				$the_cart_data ['product_meta'] = array('meta_data' => $item_meta);
			}
		}
		//MWB Custom Work
		$_product = wc_get_product($product_id);
		$enable_product_purchase_points = get_post_meta($product_id, 'mwb_product_purchase_points_only', true);
		$mwb_product_purchase_value = get_post_meta($product_id, 'mwb_points_product_purchase_value', true);
		$prod_type = $_product->get_type();        		
		if(isset($enable_product_purchase_points) && $enable_product_purchase_points == 'yes') {
			if(isset($mwb_product_purchase_value) && !empty($mwb_product_purchase_value ) && ($prod_type == 'simple')) {
				if ($mwb_product_purchase_value < $get_points) {	
					$item_meta['mwb_wpr_purchase_point_only'] = $mwb_product_purchase_value*(int)$quantity;
					$the_cart_data ['product_meta'] = array('meta_data' => $item_meta);       				
				}

			}
		}
       	/*Custom Work for Variable Product*/
		if ($public_obj->mwb_wpr_check_whether_product_is_variable($_product)) {
			/*Get the parent id of the post*/
			$mwb_wpr_parent_id = wp_get_post_parent_id($variation_id);
			$enable_product_purchase_points = get_post_meta($mwb_wpr_parent_id, 'mwb_product_purchase_points_only',true);
			$mwb_product_purchase_value = get_post_meta($variation_id, 'mwb_wpr_variable_points_purchase',true);
			if(isset($enable_product_purchase_points) && $enable_product_purchase_points == 'yes'){

				if(isset($mwb_product_purchase_value) && !empty($mwb_product_purchase_value))
				{
					if (is_user_logged_in())
					{
						if ($mwb_product_purchase_value < $get_points)
						{	
							$item_meta['mwb_wpr_purchase_point_only'] = $mwb_product_purchase_value*(int)$quantity;			       				
							$the_cart_data ['product_meta'] = array('meta_data' => $item_meta);       				
						}
					}
				}

			}
		}

		/*End of Custom Work*/
		return $the_cart_data;
	}

	/**
	* This function is used to show item poits in time of order .
	* 
	* @name mwb_wpr_woocommerce_get_item_data
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_get_item_data_pro($item_meta, $existing_item_meta) {
		if(isset($existing_item_meta ['product_meta']['meta_data'])) {
			if ($existing_item_meta ['product_meta']['meta_data']) {
				foreach ($existing_item_meta['product_meta'] ['meta_data'] as $key => $val ) {
					if($key == 'mwb_wpr_purchase_point_only') {
						$item_meta [] = array (
							'name' => __('Purchased By Points','ultimate-woocommerce-points-and-rewards'),
							'value' => stripslashes( $val ),
							);
					}
				}
			}
		}
		return $item_meta;
	}

	/**
	* The function is convert the points and add this to in the ofrm of Fee(add_fee)
	* 
	* @name mwb_wpr_woocommerce_cart_calculate_fees_pro
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_cart_calculate_fees_pro($cart) {
		/*Get the enable setting value*/
		$enable_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_points');
		/*Get the purchase points of the product*/
		$mwb_wpr_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_purchase_points');
		$mwb_wpr_purchase_points = (0 == $mwb_wpr_purchase_points)?1:$mwb_wpr_purchase_points;
		$new_price = 1;
		$mwb_wpr_discount_bcz_pnt = 0;
		$mwb_wpr_pnt_fee_added = false;
		$points_calculation = 0;
		/*Get the price eqivalent to the purchase*/
		$mwb_wpr_product_purchase_price = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_price');
		$mwb_wpr_product_purchase_price = (0 == $mwb_wpr_product_purchase_price)?1:$mwb_wpr_product_purchase_price;
		$user_id = get_current_user_ID();
		$mwb_wpr_mem_expr = get_user_meta($user_id,'membership_expiration',true);
		$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);
		if(!empty($cart)) {	
			foreach ( $cart->cart_contents as $key => $value ){
				if(!empty($value)) {
					$new_price = '';
					$today_date = date_i18n("Y-m-d");
					$product_id = $value['product_id'];
					$pro_quant = $value['quantity'];
					$_product = wc_get_product( $product_id );
					$reg_price = $_product->get_price();
					/*check is product purchase is enable or not*/
					if($enable_purchase_points) {
						if(isset($value['product_meta']['meta_data']['pro_purchase_by_points']) && !empty($value['product_meta']['meta_data']['pro_purchase_by_points'])) {	

							$original_price = $_product->get_price();
							$original_price = $pro_quant * $original_price;
							$points_calculation +=ceil(($original_price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);
							$mwb_wpr_about_to_pay = ($value['product_meta']['meta_data']['pro_purchase_by_points']/$mwb_wpr_purchase_points*$mwb_wpr_product_purchase_price);
							$mwb_wpr_discount_bcz_pnt = $mwb_wpr_discount_bcz_pnt + $mwb_wpr_about_to_pay;
							$mwb_wpr_pnt_fee_added = true;
						}
					}
				}

			}
			if($get_points > 0 && $mwb_wpr_pnt_fee_added) {	
				$convert_in_point = ($mwb_wpr_discount_bcz_pnt * $mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price;
				if($convert_in_point > $get_points){
					$mwb_wpr_about_to_pay = (int)($get_points/$mwb_wpr_purchase_points*$mwb_wpr_product_purchase_price);
					$cart->add_fee( 'Point Discount', -$mwb_wpr_about_to_pay, true, '' );
				}
				else{
					$cart->add_fee( 'Point Discount', -$mwb_wpr_discount_bcz_pnt, true, '' );
				}
			}
		}
	}

	/**
	* This function used to update points of the purchased products.
	* 
	* @name mwb_update_cart_points_pro
	* @return array
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_update_cart_points_pro( $cart_updated ) {

		if($cart_updated) {  
			$public_obj = $this->generate_public_obj();  
			$cart = WC()->session->get('cart');
			$user_id = get_current_user_ID();
			$get_points = (int)get_user_meta($user_id,'mwb_wpr_points',true);
			if(isset($_POST['cart']) && $_POST['cart'] != null && isset($cart) && $cart !=null) {
				$cart_update = sanitize_post($_POST['cart']);

				foreach ($cart_update as $key => $value) {
					if(isset(WC()->cart->cart_contents[$key]['product_meta'])) {
						if(isset(WC()->cart->cart_contents[$key]['product_meta']['meta_data']['mwb_wpr_purchase_point_only'])) {

							$product = wc_get_product($cart[$key]['product_id']);
							if($public_obj->mwb_wpr_check_whether_product_is_variable($product)){

								if(isset($cart[$key]['variation_id']) && !empty($cart[$key]['variation_id'])){

									$mwb_variable_purchase_value = get_post_meta($cart[$key]['variation_id'], 'mwb_wpr_variable_points_purchase',true);
								}
								$total_pro_pnt = (int)$mwb_variable_purchase_value * (int)$value['qty'];

								if(isset($total_pro_pnt) && !empty($total_pro_pnt) && $get_points >= $total_pro_pnt){
									WC()->cart->cart_contents[$key]['product_meta']['meta_data']['mwb_wpr_purchase_point_only'] = $total_pro_pnt;
								}
								else{

									wc_add_notice(__('You cant purchase that much quantity for Free','ultimate-woocommerce-points-and-rewards'),'error');
									WC()->cart->cart_contents[$key]['product_meta']['meta_data']['mwb_wpr_purchase_point_only'] = 0;
								}
							}
							else{

								if(isset($cart[$key]['product_id']) && !empty($cart[$key]['product_id'])){
									$get_product_points = get_post_meta($cart[$key]['product_id'], 'mwb_points_product_purchase_value', true);
								}
								$total_pro_pnt = (int)$get_product_points * (int)$value['qty'];
								if(isset($total_pro_pnt) && !empty($total_pro_pnt) && $get_points >= $total_pro_pnt){
									WC()->cart->cart_contents[$key]['product_meta']['meta_data']['mwb_wpr_purchase_point_only'] = (int)$get_product_points * (int)$value['qty'];
								}else{

									wc_add_notice(__('You cant purchase that much quantity for Free','ultimate-woocommerce-points-and-rewards'),'error');
									WC()->cart->cart_contents[$key]['product_meta']['meta_data']['mwb_wpr_purchase_point_only'] = 0;
								}
							}
						}
					}
				}                
			}                
		}
		return $cart_updated;
	}

	/**
	* This function will add discounted price for selected products in any 	Membership Level.
	* 
	* @name mwb_wpr_user_level_discount_on_price
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_user_level_discount_on_price_pro($price, $product_data) {
		
		$product_id = $product_data->get_id();
		$_product = wc_get_product( $product_id );
		$reg_price = $_product->get_price();
		$prod_type = $_product->get_type();
		$enable_product_purchase_points = get_post_meta($product_id, 'mwb_product_purchase_points_only',true);
		$mwb_product_purchase_value = get_post_meta($product_id, 'mwb_points_product_purchase_value',true);
		if(isset($enable_product_purchase_points) && $enable_product_purchase_points == 'yes'){
			if(isset($mwb_product_purchase_value) && !empty($mwb_product_purchase_value) && ($prod_type == 'simple') ) {	
				$price = $mwb_product_purchase_value.' Points';
			}
		}
		return $price;
	}

	/**
	* This function will add discounted price in cart page.
	* 
	* @name mwb_wpr_woocommerce_before_calculate_totals
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_before_calculate_totals_pro($cart) {
		/*Get the settings of the purchase points*/
		$public_obj = $this->generate_public_obj();  
		$enable_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_points');
		/*Check the restrction */
		$mwb_wpr_restrict_pro_by_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_restrict_pro_by_points');
		/*Product purchase product text*/
		$mwb_wpr_purchase_product_text = $this->mwb_wpr_get_product_purchase_settings('mwb_wpr_purchase_product_text');
		/*Check not product text should not be empty*/
		if (empty($mwb_wpr_purchase_product_text)) {
			$mwb_wpr_purchase_product_text = __('Use your Points for purchasing this Product','ultimate-woocommerce-points-and-rewards');
		}
		$mwb_wpr_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_purchase_points');
		$mwb_wpr_purchase_points = (0 == $mwb_wpr_purchase_points)?1:$mwb_wpr_purchase_points;
		$new_price = 1;
		/*Get the price eqivalent to the purchase*/
		$mwb_wpr_product_purchase_price = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_price');
		$mwb_wpr_product_purchase_price = (0 == $mwb_wpr_product_purchase_price)?1:$mwb_wpr_product_purchase_price;
		$user = wp_get_current_user();
		$user_id = $user->ID;
		$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);
		if (isset($cart)) {
			foreach ( $cart->cart_contents as $key => $value ) {
				if(isset($value)) {
					$product_id = $value['product_id'];
					$pro_quant = $value['quantity'];
					$_product = wc_get_product( $product_id );
					//===== Custom work==========
					$enable_product_purchase_points = get_post_meta($product_id, 'mwb_product_purchase_points_only', true);
					$mwb_product_purchase_value = get_post_meta($product_id, 'mwb_points_product_purchase_value', true);
					$product_type = $_product->get_type();
					if(isset($enable_product_purchase_points) && $enable_product_purchase_points == 'yes') {
						if(isset($mwb_product_purchase_value) && !empty($mwb_product_purchase_value ) && ($product_type == 'simple')) {
							if ($mwb_product_purchase_value < $get_points) {	
								$cart->cart_contents[$key]['product_meta']["meta_data"]['mwb_wpr_purchase_point_only'] = $mwb_product_purchase_value*(int)$pro_quant;  				
							}
						}
					}
					if ($public_obj->mwb_wpr_check_whether_product_is_variable($_product)) {		       		    				
						$mwb_wpr_parent_id = wp_get_post_parent_id($value['variation_id']);
						$enable_product_purchase_points = get_post_meta($mwb_wpr_parent_id, 'mwb_product_purchase_points_only',true);
						$mwb_product_purchase_value = get_post_meta($value['variation_id'], 'mwb_wpr_variable_points_purchase',true);
						if(isset($enable_product_purchase_points) && $enable_product_purchase_points == 'yes') {

							if(isset($mwb_product_purchase_value) && !empty($mwb_product_purchase_value)) {
								if (is_user_logged_in()) {
									if ($mwb_product_purchase_value < $get_points) {	
										$cart->cart_contents[$key]['product_meta']["meta_data"]['mwb_wpr_purchase_point_only'] = $mwb_product_purchase_value*(int)$pro_quant;       				
									}
								}
							}
						}

					}
					/*Product purchase through point only*/
					if(isset($enable_product_purchase_points) 
						&& $enable_product_purchase_points == 'yes' 
						&& !empty($enable_product_purchase_points) ) { 
						if(isset($mwb_product_purchase_value) 
							&& !empty($mwb_product_purchase_value) 
							&& ($product_type == 'simple') ) {	
							if (is_user_logged_in()) {	
								if (($mwb_product_purchase_value*$pro_quant) < $get_points) {	
									$value['data']->set_price(0);	
								}			 
							}
						}
					}
					/*Variable product purchase through points*/
					if ($public_obj->mwb_wpr_check_whether_product_is_variable($_product)) {
						$variation_id = $value['variation_id'];
						$mwb_wpr_parent_id = wp_get_post_parent_id($variation_id);
						$enable_product_purchase_points = get_post_meta($mwb_wpr_parent_id, 'mwb_product_purchase_points_only',true);
						$mwb_product_purchase_value = get_post_meta($variation_id, 'mwb_wpr_variable_points_purchase',true);
						if(isset($enable_product_purchase_points) && $enable_product_purchase_points == 'yes') {
							if(isset($mwb_product_purchase_value) && !empty($mwb_product_purchase_value)) {
								if (is_user_logged_in()) {
									if (($mwb_product_purchase_value*$pro_quant) < $get_points) {  
										$value['data']->set_price(0); 
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	* This function is used to save item points in time of order.
	* 
	* @name mwb_wpr_woocommerce_add_order_item_meta_version_3
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_add_order_item_meta_version_3($item,$cart_key,$values,$order) {

		if (isset ( $values['product_meta'] )) {
			foreach ($values['product_meta'] ['meta_data'] as $key => $val ) {
				$order_val = stripslashes( $val );
				if($val) {
					if($key == 'pro_purchase_by_points') {
						$item->add_meta_data('Purchasing Option',$order_val);
					}
					if($key == 'mwb_wpr_purchase_point_only') {
						$item->add_meta_data('Purchased By Points',$order_val);
					}
				}
			}
		}
	}

	/**
	* The function is for let the meta keys translatable
	* 
	* @name mwb_wpr_woocommerce_order_item_display_meta_key
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_order_item_display_meta_key_pro($display_key){
		if($display_key == 'Purchasing Option') {
				$display_key = __('Purchasing Option','ultimate-woocommerce-points-and-rewards');
		}
		return $display_key;
	}

	/**
	* This function will update the user points as they purchased products through points
	* 
	* @name mwb_wpr_woocommerce_checkout_update_order_meta_pro.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/	
	public function mwb_wpr_woocommerce_checkout_update_order_meta_pro($order_id,$data) {
		/*Get the settings of the purchase points*/
		$public_obj = $this->generate_public_obj(); 
		$user_id = get_current_user_id();
		$user=get_user_by('ID',$user_id);
		$user_email=$user->user_email;
		$deduct_point = '';
		$points_deduct = 0;
		$mwb_wpr_is_pnt_fee_applied = false;
		/*Get the purchase points*/
		$mwb_wpr_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_purchase_points');
		$mwb_wpr_purchase_points = (0 == $mwb_wpr_purchase_points)?1:$mwb_wpr_purchase_points;
		$new_price = 1;
		/*Get the price eqivalent to the purchase*/
		$mwb_wpr_product_purchase_price = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_price');
		$mwb_wpr_product_purchase_price = (0 == $mwb_wpr_product_purchase_price)?1:$mwb_wpr_product_purchase_price;

		/*Get the order from the order id */
		$get_points = get_user_meta($user_id,'mwb_wpr_points',true);
		$order = wc_get_order( $order_id );
		$line_items_fee = $order->get_items( 'fee' );
		if(!empty($line_items_fee)){
			foreach ($line_items_fee as $item_id => $item) {
				$mwb_wpr_fee_name = $item->get_name();
				if($mwb_wpr_fee_name == 'Point Discount'){
					$mwb_wpr_is_pnt_fee_applied = true;
					$fee_amount = $item->get_amount();
				}
			}
		}
		if($mwb_wpr_is_pnt_fee_applied) {
			$fee_amount = -($fee_amount);
			$fee_to_point = ceil(($mwb_wpr_purchase_points * $fee_amount)/$mwb_wpr_product_purchase_price);
			$points_deduct = $fee_to_point;
			$total_points = $get_points - $points_deduct;
			$data = array();
			/*update points detais of the customer*/
			$this->mwb_wpr_update_points_details_pro($user_id,'pur_by_points',$points_deduct,$data);
			/*update users points*/
			update_user_meta($user_id,'mwb_wpr_points',$total_points);
			$mwb_wpr_shortcode = array(
				'[Total Points]' => $total_points,
				"[USERNAME]" => $user->user_firstname,
				'[PROPURPOINTS]' => $points_deduct,
				);
			$mwb_wpr_subject_content = array(
				'mwb_wpr_subject' => 'mwb_wpr_pro_pur_by_points_email_subject',
				'mwb_wpr_content' => 'mwb_wpr_pro_pur_by_points_discription_custom_id',
				);
			/*Send mail to client regarding product purchase through points*/
			$public_obj->mwb_wpr_send_notification_mail_product($user_id,$points_deduct,$mwb_wpr_shortcode,$mwb_wpr_subject_content);
		}
		$product_points = 0;
		$product_purchased_pnt_only = false;
		foreach( $order->get_items() as $item_id => $item ) {
			$mwb_wpr_items=$item->get_meta_data();
			foreach ($mwb_wpr_items as $key => $mwb_wpr_value) {
				if(isset($mwb_wpr_value->key) && !empty($mwb_wpr_value->key) && ($mwb_wpr_value->key=='Purchased By Points') ){
					$product_points += (int)$mwb_wpr_value->value;
					$product_purchased_pnt_only = true;
				}
			}
		}
		 $get_points = get_user_meta($user_id,'mwb_wpr_points',true);
		/*Product purchase through points*/
		if($product_purchased_pnt_only && isset($user_id) && $user_id > 0) {
			if($get_points >= $product_points) {
				$total_points_only = $get_points - $product_points;
				/*Update user points*/
				update_user_meta($user_id, 'mwb_wpr_points', $total_points_only);
				$data = array();
				/*update points detais of the customer*/
				$this->mwb_wpr_update_points_details_pro($user_id,'pur_pro_pnt_only',$product_points,$data);
			}
		}
	}

	/**
	* Update points details in the public section
	* 
	* @name mwb_wpr_update_points_details
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	* @param $user_id,$type,$points
	*/
	public  function mwb_wpr_update_points_details_pro($user_id,$type,$points,$data) {
		$today_date = date_i18n("Y-m-d h:i:sa");
		
		/*Here is cart discount through the points*/
		if ($type == "cart_subtotal_point" || $type == "pur_by_points") {
			$cart_subtotal_point_arr = get_user_meta($user_id, 'points_details', true);
			if(isset($cart_subtotal_point_arr[$type]) && !empty($cart_subtotal_point_arr[$type])) {
				$cart_array = array(
					$type => $points,
					'date'=>$today_date);
				$cart_subtotal_point_arr[$type][] = $cart_array;
			}
			else {
				if(!is_array($cart_subtotal_point_arr)){
					$cart_subtotal_point_arr = array();
				}
				$cart_array = array(
					$type => $points,
					'date'=>$today_date);
				$cart_subtotal_point_arr[$type][] = $cart_array;
			}
			/*Update the user meta for the points details*/
			update_user_meta($user_id,'points_details', $cart_subtotal_point_arr);
		}
		if ($type == 'Receiver_point_details' || $type == 'Sender_point_details') {
			$mwb_points_sharing = get_user_meta($user_id, 'points_details', true);
			if(isset($mwb_points_sharing[$type]) && !empty($mwb_points_sharing[$type])){
				$custom_array = array(
					$type => $points,
					'date' => $today_date,
					$data['type'] => $data['user_id']
					);
				$mwb_points_sharing[$type][] = $custom_array;
			}
			else{
				if(!is_array($mwb_points_sharing)){
					$mwb_points_sharing = array();
				}
				$mwb_points_sharing[$type][] = array(
					$type => $points,
					'date' => $today_date,
					$data['type'] => $data['user_id']
					);
			}
			/*Update the user meta for the points details*/
			update_user_meta($user_id,'points_details', $mwb_points_sharing);
		}
		return "Successfully";
	}	

	/**
	* Runs a cron for notifying the users who have any memberhip level and which is going to be expired in next two weeks.
	* 
	* @name mwb_wpr_do_this_hourly.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_do_this_hourly() {	
		$today_date = date_i18n("Y-m-d");
		$args['meta_query'] = array(						
			array(
				'key'=>'membership_level'
				)
			);
		$user_data= get_users( $args );
		if(is_array($user_data) && !empty($user_data)){
			foreach ($user_data as $key => $value) {
				$user_id = $value->data->ID;
				$user_email=$value->data->user_email;
				if(isset($user_id) && !empty($user_id)){
					$mwb_wpr_mem_expr = get_user_meta($user_id,'membership_expiration',true);
					$user_level = get_user_meta($user_id,'membership_level',true);
					if(isset($mwb_wpr_mem_expr) && !empty($mwb_wpr_mem_expr)){
						$notification_date= date('Y-m-d', strtotime($mwb_wpr_mem_expr. ' -1 weeks'));
						if($today_date == $notification_date)
						{
							$subject = __('Membership Expiration Alert!','ultimate-woocommerce-points-and-rewards');
							$message = __('Your User Level ','ultimate-woocommerce-points-and-rewards').$user_level.__(' is going to expired on date of ','ultimate-woocommerce-points-and-rewards').$mwb_wpr_mem_expr.__(' You can upgrade your level or can renew that level again after expiration.','ultimate-woocommerce-points-and-rewards');
							wc_mail($user_email,$subject,$message);
						}
						$expired_date= date('Y-m-d', strtotime($mwb_wpr_mem_expr));
						if($today_date > $expired_date)
						{
							delete_user_meta($user_id,'membership_level');
							$subject = __('No Longer Membership User','ultimate-woocommerce-points-and-rewards');
							$message = __('Your User Level ','ultimate-woocommerce-points-and-rewards').$user_level.__(' has been expired. You can upgrade your level to another or can renew that level again ','ultimate-woocommerce-points-and-rewards');
							wc_mail($user_email,$subject,$message);
						}
					}
				}
			}
		}
	}

	/**
	* The function is used for append the variable point to the single product page as well as variable product support for purchased through points and for membership product
	* 
	* @name mwb_wpr_append_variable_point
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_append_variable_point() {
		$response['result'] = false;
		$response['message'] = __("Error during various variation handling. Try Again!",'ultimate-woocommerce-points-and-rewards');
		$mwb_wpr_proceed_for_purchase_throgh_point = false;
		$points_calculation = '';$price = '';
		$public_obj = $this->generate_public_obj();
		$variation_id = sanitize_post($_POST['variation_id']);
		/*Get the resctiction settings*/
		/*Check the restrction */
		$mwb_wpr_restrict_pro_by_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_restrict_pro_by_points');
		$mwb_wpr_categ_list = $this->mwb_wpr_get_product_purchase_settings('mwb_wpr_restrictions_for_purchasing_cat');
		if(empty($mwb_wpr_categ_list)) {
			$mwb_wpr_categ_list = array();
		}
		$user_id = get_current_user_ID();
		if(!empty($variation_id)){
			$user_level = get_user_meta($user_id,'membership_level',true);
			$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);
			$mwb_wpr_mem_expr = get_user_meta($user_id,'membership_expiration',true);
			$membership_settings_array = get_option('mwb_wpr_membership_settings',true);
			$mwb_wpr_membership_roles = isset($membership_settings_array['membership_roles']) && !empty($membership_settings_array['membership_roles']) ? $membership_settings_array['membership_roles'] : array();
			$today_date = date_i18n("Y-m-d");
			/*Product purchase product text*/
			$mwb_wpr_purchase_product_text = $this->mwb_wpr_get_product_purchase_settings('mwb_wpr_purchase_product_text');
			/*Check not product text should not be empty*/
			if (empty($mwb_wpr_purchase_product_text)) {
				$mwb_wpr_purchase_product_text = __('Use your Points for purchasing this Product','ultimate-woocommerce-points-and-rewards');
			}
			$mwb_wpr_parent_id = wp_get_post_parent_id($variation_id);
			/*Get the settings of the purchase points*/
			$enable_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_points');

			$mwb_wpr_purchase_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_purchase_points');
			$mwb_wpr_purchase_points = (0 == $mwb_wpr_purchase_points)?1:$mwb_wpr_purchase_points;
			$new_price = 1;
			$mwb_wpr_product_purchase_price = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_product_purchase_price');
			$mwb_wpr_product_purchase_price = (0 == $mwb_wpr_product_purchase_price)?1:$mwb_wpr_product_purchase_price;
			$mwb_wpr_restrict_pro_by_points = $this->mwb_wpr_get_product_purchase_settings_num('mwb_wpr_restrict_pro_by_points');
			if(!empty($mwb_wpr_parent_id) && $mwb_wpr_parent_id > 0){
				$check_enable = get_post_meta($mwb_wpr_parent_id, 'mwb_product_points_enable', 'no');
				$check_disbale = get_post_meta($mwb_wpr_parent_id, 'mwb_product_purchase_through_point_disable', 'no');
				if(empty($check_disbale)){
					$check_disbale = 'no';
				}
				if($check_enable == 'yes'){
					$mwb_wpr_variable_points = (int)get_post_meta( $variation_id,'mwb_wpr_variable_points',true);
					if($mwb_wpr_variable_points > 0){
						$response['result'] = true;
						$response['variable_points'] = $mwb_wpr_variable_points;
						$response['message'] = __("Successfully Assigned!",'ultimate-woocommerce-points-and-rewards');
							// echo json_encode($response);
							// wp_die();
					}
				}
				if($enable_purchase_points){
					if($mwb_wpr_restrict_pro_by_points){
						$terms = get_the_terms ( $mwb_wpr_parent_id, 'product_cat' );
						if(is_array($terms) && !empty($terms)){
							foreach ( $terms as $term ) {
								$cat_id = $term->term_id;
								$parent_cat = $term->parent;
								if(isset($mwb_wpr_categ_list) && !empty($mwb_wpr_categ_list)){
									if(in_array($cat_id, $mwb_wpr_categ_list) || in_array($parent_cat, $mwb_wpr_categ_list)){
										$mwb_wpr_proceed_for_purchase_throgh_point = true;
										break;
									}
								}
								else{
									$mwb_wpr_proceed_for_purchase_throgh_point = false;
								}
							}
						}							
					}
					else{
						$mwb_wpr_proceed_for_purchase_throgh_point = true;
					}	
				}

				$variable_product = wc_get_product( $variation_id );
				$variable_price = $variable_product->get_price();
				if(isset($user_level) && !empty($user_level)){
					if( isset( $mwb_wpr_mem_expr ) && !empty( $mwb_wpr_mem_expr ) && $today_date <= $mwb_wpr_mem_expr )
					{
						if(is_array($mwb_wpr_membership_roles) && !empty($mwb_wpr_membership_roles))
						{
							foreach($mwb_wpr_membership_roles as $roles => $values)
							{	
								if($user_level == $roles)
								{	
									if(is_array($values['Product']) && !empty($values['Product']))
									{
										if(in_array($mwb_wpr_parent_id, $values['Product']) && !$public_obj->check_exclude_sale_products($variable_product))
										{	
											$new_price = $variable_price - ($variable_price * $values['Discount'])/100;
											$price = '<span class="price"><del><span class="woocommerce-Price-amount amount">'.wc_price( $variable_price ).'</del> <ins><span class="woocommerce-Price-amount amount">'.wc_price($new_price).'</span></ins></span>';
											$points_calculation =ceil(($new_price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);
										}
										$response['result_price'] = "html";
										$response['variable_price_html'] = $price;
										$mwb_wpr_variable_pro_pur_pnt = '<label for="mwb_wpr_pro_cost_to_points"><input type="checkbox" name="mwb_wpr_pro_cost_to_points" id="mwb_wpr_pro_cost_to_points" class="input-text" value="'.$points_calculation.'">'.$mwb_wpr_purchase_product_text.'</label><input type="hidden" name="mwb_wpr_hidden_points" class="mwb_wpr_hidden_points" value="'.$points_calculation.'"><p class="mwb_wpr_purchase_pro_point">'.__('Spend ','ultimate-woocommerce-points-and-rewards').$points_calculation.__(' Points for Purchasing this Product for Single Quantity','ultimate-woocommerce-points-and-rewards').'</p><span class="mwb_wpr_notice"></span><div class="mwb_wpr_enter_some_points" style="display: none;"><input type="number" name="mwb_wpr_some_custom_points" id="mwb_wpr_some_custom_points" value="'.$points_calculation.'"></div>';
										if($enable_purchase_points && $mwb_wpr_proceed_for_purchase_throgh_point && $check_disbale == 'no'){
											if($get_points >= $points_calculation){
												$response['result_point'] = "product_purchased_using_point";
												$response['variable_points_cal_html'] = $mwb_wpr_variable_pro_pur_pnt;
											}
											elseif($points_calculation > $get_points){
												$extra_need = $points_calculation - $get_points;
												$mwb_wpr_variable_pro_pur_pnt = '<p class="mwb_wpr_purchase_pro_point">'.__('You need extra ','ultimate-woocommerce-points-and-rewards').$extra_need.__(' Points for get this product for free','ultimate-woocommerce-points-and-rewards').'</p>';
												$response['result_point'] = "product_purchased_using_point";
												$response['variable_points_cal_html'] = $mwb_wpr_variable_pro_pur_pnt;
											}
										}
									}
									else if(!$public_obj->check_exclude_sale_products($variable_product))
									{
										$terms = get_the_terms ( $mwb_wpr_parent_id, 'product_cat' );
										if(is_array($terms) && !empty($terms))
										{
											foreach ( $terms as $term ) 
											{
												$cat_id = $term->term_id;
												$parent_cat = $term->parent;
												if(in_array($cat_id, $values['Prod_Categ']) || in_array($parent_cat, $values['Prod_Categ'])){	
													$new_price = $variable_price - ($variable_price * $values['Discount'])/100;
													$price = '<span class="price"><del><span class="woocommerce-Price-amount amount">'.wc_price( $variable_price ).'</del> <ins><span class="woocommerce-Price-amount amount">'.wc_price($new_price).'</span></ins></span>';
													$points_calculation =ceil(($new_price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);


													$response['result_price'] = "html";
													$response['variable_price_html'] = $price;
													$mwb_wpr_variable_pro_pur_pnt = '<label for="mwb_wpr_pro_cost_to_points"><input type="checkbox" name="mwb_wpr_pro_cost_to_points" id="mwb_wpr_pro_cost_to_points" class="input-text" value="'.$points_calculation.'">'.$mwb_wpr_purchase_product_text.'</label><input type="hidden" name="mwb_wpr_hidden_points" class="mwb_wpr_hidden_points" value="'.$points_calculation.'"><p class="mwb_wpr_purchase_pro_point">'.__('Spend ','ultimate-woocommerce-points-and-rewards').$points_calculation.__(' Points for Purchasing this Product for Single Quantity','ultimate-woocommerce-points-and-rewards').'</p><span class="mwb_wpr_notice"></span><div class="mwb_wpr_enter_some_points" style="display: none;"><input type="number" name="mwb_wpr_some_custom_points" id="mwb_wpr_some_custom_points" value="'.$points_calculation.'"></div>';
													break;
												}
												if($enable_purchase_points && $mwb_wpr_proceed_for_purchase_throgh_point && $check_disbale == 'no'){
													if($get_points >= $points_calculation){
														$response['result_point'] = "product_purchased_using_point";
														$response['variable_points_cal_html'] = $mwb_wpr_variable_pro_pur_pnt;
													}
													elseif($enable_purchase_points && $points_calculation > $get_points){
														$extra_need = $points_calculation - $get_points;
														$mwb_wpr_variable_pro_pur_pnt = '<p class="mwb_wpr_purchase_pro_point">'.__('You need extra ','ultimate-woocommerce-points-and-rewards').$extra_need.__(' Points for get this product for free','ultimate-woocommerce-points-and-rewards').'</p>';
														$response['result_point'] = "product_purchased_using_point";
														$response['variable_points_cal_html'] = $mwb_wpr_variable_pro_pur_pnt;
													}
												}
											}
										}
									}	
								}
							}
						}
					}
				}
				else{
					$points_calculation =ceil(($variable_price*$mwb_wpr_purchase_points)/$mwb_wpr_product_purchase_price);
					$mwb_wpr_variable_pro_pur_pnt = '<label for="mwb_wpr_pro_cost_to_points"><input type="checkbox" name="mwb_wpr_pro_cost_to_points" id="mwb_wpr_pro_cost_to_points" class="input-text" value="'.$points_calculation.'">'.$mwb_wpr_purchase_product_text.'</label><input type="hidden" name="mwb_wpr_hidden_points" class="mwb_wpr_hidden_points" value="'.$points_calculation.'"><p class="mwb_wpr_purchase_pro_point">'.__('Spend ','ultimate-woocommerce-points-and-rewards').$points_calculation.__(' Points for Purchasing this Product for Single Quantity','ultimate-woocommerce-points-and-rewards').'</p><span class="mwb_wpr_notice"></span><div class="mwb_wpr_enter_some_points" style="display: none;"><input type="number" name="mwb_wpr_some_custom_points" id="mwb_wpr_some_custom_points" value="'.$points_calculation.'"></div>';
					if($enable_purchase_points && $mwb_wpr_proceed_for_purchase_throgh_point && $check_disbale == 'no'){
						if($get_points >= $points_calculation){
							$response['result_point'] = "product_purchased_using_point";
							$response['variable_points_cal_html'] = $mwb_wpr_variable_pro_pur_pnt;
						}
						elseif($points_calculation > $get_points){
							$extra_need = $points_calculation - $get_points;
							$mwb_wpr_variable_pro_pur_pnt = '<p class="mwb_wpr_purchase_pro_point">'.__('You need extra ','ultimate-woocommerce-points-and-rewards').$extra_need.__(' Points for get this product for free','ultimate-woocommerce-points-and-rewards').'</p>';
							$response['result_point'] = "product_purchased_using_point";
							$response['variable_points_cal_html'] = $mwb_wpr_variable_pro_pur_pnt;
						}
					}
				}
			}

				//MWB CUSTOM CODE

			$enable_product_purchase_points = get_post_meta($mwb_wpr_parent_id, 'mwb_product_purchase_points_only',true);
			$mwb_product_purchase_value = get_post_meta($variation_id, 'mwb_wpr_variable_points_purchase',true);

			if(isset($enable_product_purchase_points) && $enable_product_purchase_points == 'yes'){
				if(isset($mwb_product_purchase_value) && !empty($mwb_product_purchase_value))
				{	

						//$price = $mwb_product_purchase_value.' Points';
					$response['purchase_pro_pnts_only'] = "purchased_pro_points";
					$response['price_html'] = $mwb_product_purchase_value;

				}
			}			
		}
		echo json_encode($response);
		wp_die();
	}
	/**
	* The function is used assignging the custom point through purchase
	* 
	* @name mwb_pro_purchase_points_only
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_pro_purchase_points_only(){
		$response['result'] = false;
		$response['message'] = __("Error during various variation handling. Try Again!",'ultimate-woocommerce-points-and-rewards');
		$variation_id = sanitize_post($_POST['variation_id']);
		$mwb_wpr_parent_id = wp_get_post_parent_id($variation_id);
		$enable_product_purchase_points = get_post_meta($mwb_wpr_parent_id, 'mwb_product_purchase_points_only',true);
		$mwb_product_purchase_value = get_post_meta($variation_id, 'mwb_wpr_variable_points_purchase',true);

		if(isset($enable_product_purchase_points) && $enable_product_purchase_points == 'yes'){
			if(isset($mwb_product_purchase_value) && !empty($mwb_product_purchase_value)) {	
				$response['result'] = true;
				$response['purchase_pro_pnts_only'] = "purchased_pro_points";
				$response['price_html'] = $mwb_product_purchase_value;
			}
		}
		echo json_encode($response);
		wp_die();
	}

	/**
	* The function for appends the required/custom message for users to let them know how many points they are going to earn/deduct
	* 
	* @name mwb_wpr_woocommerce_thankyou
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_thankyou($thankyou_msg, $order) {
		$admin_obj = new Ultimate_Woocommerce_Points_And_Rewards_Admin('ultimate-woocommerce-points-and-rewards','1.0.0');	
		$mwb_wpr_thnku_order_msg = $admin_obj->mwb_wpr_get_points_expiration_settings("mwb_wpr_thnku_order_msg");
		$mwb_wpr_thnku_order_msg_usin_points = $admin_obj->mwb_wpr_get_points_expiration_settings("mwb_wpr_thnku_order_msg_usin_points");
		$item_points = 0;
		$purchasing_points = 0;
		$mwb_wpr_coupon_conversion_value=get_option('mwb_wpr_coupons_gallery',array());
		$mwb_wpr_coupon_conversion_price=isset($mwb_wpr_coupon_conversion_value['mwb_wpr_coupon_conversion_price']) ? $mwb_wpr_coupon_conversion_value['mwb_wpr_coupon_conversion_price'] : 1 ;
		$mwb_wpr_coupon_conversion_points=isset($mwb_wpr_coupon_conversion_value['mwb_wpr_coupon_conversion_points']) ? $mwb_wpr_coupon_conversion_value['mwb_wpr_coupon_conversion_points'] : 1 ;
		$order_id = $order->get_order_number();
		$user_id = $order->get_user_id();
		$get_points = (int)get_user_meta($user_id, 'mwb_wpr_points', true);

		foreach( $order->get_items() as $item_id => $item ) {
			$item_quantity = wc_get_order_item_meta($item_id, '_qty', true);
			$mwb_wpr_items=$item->get_meta_data();
			foreach ($mwb_wpr_items as $key => $mwb_wpr_value)  {
				if(isset($mwb_wpr_value->key) && !empty($mwb_wpr_value->key) && ($mwb_wpr_value->key=='Points') ) {	
					$item_points += (int)$mwb_wpr_value->value;
					$thankyou_msg .= $mwb_wpr_thnku_order_msg;
					$thankyou_msg = str_replace('[POINTS]', $item_points, $thankyou_msg);
					$thankyou_msg = str_replace('[TOTALPOINT]', $get_points, $thankyou_msg);
				}
				if(isset($mwb_wpr_value->key) && !empty($mwb_wpr_value->key) && ($mwb_wpr_value->key=='Purchasing Option') ) {	
					$purchasing_points += (int)$mwb_wpr_value->value * $item_quantity ;
					$thankyou_msg .= $mwb_wpr_thnku_order_msg_usin_points;
					$thankyou_msg = str_replace('[POINTS]', $purchasing_points, $thankyou_msg);
					$thankyou_msg = str_replace('[TOTALPOINT]', $get_points, $thankyou_msg);
				}
			}
		}
		$item_conversion_id_set = get_post_meta($order_id, "$order_id#item_conversion_id", false);
		$order_total=$order->get_total();
		$order_total = str_replace( wc_get_price_decimal_separator(), '.', strval( $order_total ) );
		$points_calculation =ceil(($order_total*$mwb_wpr_coupon_conversion_points)/$mwb_wpr_coupon_conversion_price);
		if( isset( $item_conversion_id_set[0] ) && !empty( $item_conversion_id_set[0] ) && $item_conversion_id_set[0]=='set' ) {
			$item_points += $points_calculation;
			$thankyou_msg .= $mwb_wpr_thnku_order_msg;
			$thankyou_msg = str_replace('[POINTS]', $item_points, $thankyou_msg);
			$thankyou_msg = str_replace('[TOTALPOINT]', $get_points, $thankyou_msg);
		}

		return $thankyou_msg;
	}

}
