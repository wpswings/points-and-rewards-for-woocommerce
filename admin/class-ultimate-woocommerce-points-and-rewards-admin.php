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
class Ultimate_Woocommerce_Points_And_Rewards_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {

		// Enqueue styles only on this plugin's menu page.

		wp_enqueue_style( $this->plugin_name, ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_URL . 'admin/css/ultimate-woocommerce-points-and-rewards-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		wp_enqueue_script( $this->plugin_name . 'admin-js', ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_URL . 'admin/js/ultimate-woocommerce-points-and-rewards-admin.js', array( 'jquery' ), $this->version, false );
		/*Category*/
		$args_cat = array('taxonomy'=>'product_cat');
		$categories = get_terms($args_cat);
		$option_arr = array();
		if(isset($categories) && !empty($categories)) {
			foreach($categories as $category) {
				$catid = $category->term_id;
				$catname = $category->name;

				$option_categ[] = array(
					'id'=>$catid,
					'cat_name'=>$catname
					);
			}
		}
		$url = admin_url ( 'admin.php?page=mwb-rwpr-setting');
		wp_localize_script( $this->plugin_name . 'admin-js', 'license_ajax_object', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'reloadurl' => admin_url( 'admin.php?page=ultimate_woocommerce_points_and_rewards_menu' ),
			'license_nonce' => wp_create_nonce( 'ultimate-woocommerce-points-and-rewards-license-nonce-action' ),
			'validpoint'=> __('Please enter valid points','ultimate-woocommerce-points-and-rewards'),
			'Labelname'=>__('Enter the Name of the Level','ultimate-woocommerce-points-and-rewards'),
			'Labeltext'=>__('Enter Level','ultimate-woocommerce-points-and-rewards'),
			'Points'=>__('Enter Points','ultimate-woocommerce-points-and-rewards'),
			'Categ_text'=>__('Select Product Category','ultimate-woocommerce-points-and-rewards'),
			'Remove_text'=>__('Remove','ultimate-woocommerce-points-and-rewards'),
			'Categ_option'=>$option_categ,
			'Prod_text'=>__('Select Product','ultimate-woocommerce-points-and-rewards'),
			'Discounttext'=>__('Enter Discount (%)','ultimate-woocommerce-points-and-rewards'),
			'error_notice'=>__('Fields cannot be empty','ultimate-woocommerce-points-and-rewards'),
			'LevelName_notice'=>__('Please Enter the Name of the Level','ultimate-woocommerce-points-and-rewards'),
			'LevelValue_notice'=>__('Please Enter valid Points','ultimate-woocommerce-points-and-rewards'),
			'CategValue_notice'=>__('Please select a category','ultimate-woocommerce-points-and-rewards'),
			'ProdValue_notice'=>__('Please select a product','ultimate-woocommerce-points-and-rewards'),
			'Discount_notice'=>__('Please enter valid discount','ultimate-woocommerce-points-and-rewards'),
			'success_assign'=>__('Points are assigned successfully!','ultimate-woocommerce-points-and-rewards'),
			'error_assign'=>__('Enter Some Valid Points!','ultimate-woocommerce-points-and-rewards'),
			'success_remove'=>__('Points are removed successfully!','ultimate-woocommerce-points-and-rewards'),
			'Days'=>__('Days','ultimate-woocommerce-points-and-rewards'),
			'Weeks'=>__('Weeks','ultimate-woocommerce-points-and-rewards'),
			'Months'=>__('Months','ultimate-woocommerce-points-and-rewards'),
			'Years'=>__('Years','ultimate-woocommerce-points-and-rewards'),
			'Exp_period'=>__('Expiration Period','ultimate-woocommerce-points-and-rewards'),
			'mwb_wpr_url' => $url,
			'reason' => __('Please enter Remark','ultimate-woocommerce-points-and-rewards'),
			'mwb_wpr_nonce' =>  wp_create_nonce( "mwb-wpr-verify-nonce" )
			) 
		);

	}

	/**
	* This function is used for getting the purchase settings
	* 
	* @name mwb_wpr_get_product_purchase_settings_num
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	* @param string $id for key of the settings.
	*/
	public function mwb_wpr_get_points_expiration_settings_num($id) {
		$mwb_wpr_value = 0;
		$general_settings = get_option('mwb_wpr_points_expiration_settings',true);
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
	public function mwb_wpr_get_points_expiration_settings($id) {
		$mwb_wpr_value ='';
		$general_settings = get_option('mwb_wpr_points_expiration_settings',true);
		if(!empty($general_settings[$id])) {
			$mwb_wpr_value = $general_settings[$id];
		}
		return $mwb_wpr_value;
	}

	/**
	 * Validate license.
	 *
	 * @since    1.0.0
	 */
	public function validate_license_handle() {

		/*First check the nonce, if it fails the function will break*/
		check_ajax_referer( 'ultimate-woocommerce-points-and-rewards-license-nonce-action', 'ultimate-woocommerce-points-and-rewards-license-nonce' );

		$mwb_license_key = !empty( $_POST['ultimate_woocommerce_points_and_rewards_purchase_code'] ) ? sanitize_text_field( $_POST['ultimate_woocommerce_points_and_rewards_purchase_code'] ) : '';

    	/*API query parameters*/ 
		$api_params = array(
		            'slm_action' => 'slm_activate',
		            'secret_key' => ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_SPECIAL_SECRET_KEY,
		            'license_key' => $mwb_license_key,
		            'registered_domain' => $_SERVER['SERVER_NAME'],
		            'item_reference' => urlencode( ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_ITEM_REFERENCE ),
		);

		/*Send query to the license manager server*/ 
		$query = esc_url_raw( add_query_arg( $api_params, ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_SERVER_URL ) );

		$response = wp_remote_get( $query, array( 'timeout' => 20, 'sslverify' => false ) );

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( isset( $license_data->result ) && 'success' === $license_data->result ) {

			update_option( 'ultimate_woocommerce_points_and_rewards_lcns_key', $mwb_license_key );
			update_option( 'ultimate_woocommerce_points_and_rewards_lcns_status', 'true' );

			echo json_encode( array( 'status' => true, 'msg' =>__( 'Successfully Verified...', 'ultimate-woocommerce-points-and-rewards' ) ) );
		}
		else{

			$error_message = !empty( $license_data->message ) ? $license_data->message : __( 'License Verification Failed.', 'ultimate-woocommerce-points-and-rewards' );

			echo json_encode( array( 'status' => false, 'msg' => $error_message ) );
		}

        wp_die();
    }

    /**
     * Validate License daily.
     *
     * @since 1.0.0
     */
    public function validate_license_daily() {

    	$mwb_license_key = get_option( 'ultimate_woocommerce_points_and_rewards_lcns_key', '' );

		/*API query parameters*/ 
		$api_params = array(
            'slm_action' => 'slm_check',
            'secret_key' => ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_SPECIAL_SECRET_KEY,
            'license_key' => $mwb_license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode( ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_ITEM_REFERENCE ),
		);
           
     	$query = esc_url_raw( add_query_arg( $api_params, ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_SERVER_URL ) );

        $mwb_response = wp_remote_get( $query, array( 'timeout' => 20, 'sslverify' => false ) );

		$license_data = json_decode( wp_remote_retrieve_body( $mwb_response ) );

        if( isset( $license_data->result ) && 'success' === $license_data->result && isset( $license_data->status ) && 'active' === $license_data->status ) {

			update_option( 'ultimate_woocommerce_points_and_rewards_lcns_key', $mwb_license_key );
			update_option( 'ultimate_woocommerce_points_and_rewards_lcns_status', 'true' );
        }

        else {

			delete_option( 'ultimate_woocommerce_points_and_rewards_lcns_key' );
			update_option( 'ultimate_woocommerce_points_and_rewards_lcns_status', 'false' );
        }
        
    }

    /**
	* Register The settings in the Referral Settings 
	* @name add_mwb_settings 
	* @since    1.0.0
	* @param $settings
	*/
	public function add_mwb_settings($settings) {
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {
			$new_inserted_array = array(
				array(
					'title' => __('Minimum Referrals Required',MWB_UWPR_DOMAIN),
					'type'	=> "number",
					'id'	=> "mwb_wpr_general_refer_minimum",
					'custom_attributes'   => array('min'=>'1'),
					'class'	  => 'input-text mwb_wpr_new_woo_ver_style_text',
					'desc_tip'=> __('Minimum number of referrals required to get referral points when the new customer sign ups.', MWB_UWPR_DOMAIN),
					),
				array(
					'title' => __('Enable Referral Purchase Points',MWB_RWPR_Domain),
					'type'	=> "checkbox",
					'default'	=> 1,
					'id'	=> "mwb_wpr_general_referal_purchase_enable",
					'class'	  => 'input-text',
					'desc_tip'=> __('Check this box to enable the referral purchase points.', 
						MWB_RWPR_Domain),
					'desc'	  => __('Enable Referral Purchase Points',MWB_RWPR_Domain)
					),
				array(
					'title' => __('Enter Referral Purchase Points',MWB_RWPR_Domain),
					'type'	=> "number",
					'default'	=> 1,
					'id'	=> "mwb_wpr_general_referal_purchase_value",
					'class'	  => 'input-text mwb_wpr_new_woo_ver_style_text',
					'custom_attributes'=> array('min'=>'1'),
					'desc_tip'=> __('Entered point will assign to that user by which another user reffered from refrral link and purchase some products.', 
						MWB_RWPR_Domain),
					),

				array(
					'title' => __('Assign Only Referral Purchase Points',MWB_UWPR_DOMAIN),
					'type'	=> "checkbox",
					'id'	=> "mwb_wpr_general_refer_value_disable",
					'class'	  => 'input-text',
					'desc_tip'=> __('Check this if you want to assign only purchase points to referred user not referral points.', 
						MWB_UWPR_DOMAIN),
					'desc'	  => __('Make sure Referral Points & Referral Purchase Points should be enable.',MWB_UWPR_DOMAIN)
					),
				array(
					'title' => __('Enable Referral Purchase Limit',MWB_UWPR_DOMAIN),
					'type'	=> "checkbox",
					'id'	=> "mwb_wpr_general_referal_purchase_limit",
					'class'	  => 'input-text',
					'desc'	=> __('Enable limit for Referral Purchase Option',MWB_UWPR_DOMAIN),
					'desc_tip'=> __('Check this box to provide some limitation for referral purchase point, where you can set the number of orders for refree', 
						MWB_UWPR_DOMAIN),
					),
				array(
					'title' => __('Set the Number of Orders for Referral Purchase Limit',MWB_UWPR_DOMAIN),
					'type'	=> "number",
					'custom_attributes'   => array('min'=>'1'),
					'id'	=> "mwb_wpr_general_referal_order_limit",
					'class'	  => 'input-text',
					'desc_tip'=> __('Enter the number of orders, Refree would get assigned only till the limit(no of orders) would be reached', 
						MWB_UWPR_DOMAIN),
					),
				array(
					'title' => __('Static Referral Link',MWB_UWPR_DOMAIN),
					'type'	=> "checkbox",
					'id'	=> "mwb_wpr_referral_link_permanent",
					'class'	  => 'input-text',
					'desc_tip'=> __('Enter the number of orders, Refree would get assigned only till the limit(no of orders) would be reached', MWB_UWPR_DOMAIN),
					'desc'	=>__("Make Referral Link Permanent",MWB_UWPR_DOMAIN),
					),
				array(
					'title' => __('Referral Link Expiry',MWB_UWPR_DOMAIN),
					'type'	=> "number",
					'id'	=> "mwb_wpr_ref_link_expiry",
					'class'	  => 'input-text mwb_wpr_new_woo_ver_style_text',
					'desc_tip'=> __('Set the number of days after that the system will not able to remember the reffered user anymore', MWB_UWPR_DOMAIN),
					'desc'	=>__("Days",MWB_UWPR_DOMAIN),
					),
				array(
					'type' 	=> 'sectionend',
					),
				array(
					'title' => __('Comments Points',MWB_UWPR_DOMAIN), 
					'type'	=> 'title',
					),
				array(
					'title' => __('Enable Comments Points',MWB_UWPR_DOMAIN),
					'type'	=> 'checkbox',
					'id'	=> 'mwb_wpr_general_comment_enable',
					'desc' 	=> __('Enable Comments Points for Rewards',MWB_UWPR_DOMAIN),
					'desc_tip'=> __('Check this box to enable the Comment Points when comment is approved.', MWB_UWPR_DOMAIN),

					),
				array(
					'title' => __('Enter Comments Points',MWB_UWPR_DOMAIN),
					'type'	=> 'number',
					'id'	=> 'mwb_wpr_general_comment_value',
					'desc_tip'=> __('The points which new customer will get after their comment are approved.', MWB_UWPR_DOMAIN),
					),
			);
			$settings = $this->InsertKeyValuePair($settings,$new_inserted_array,10);
		}
		return $settings;
	}

	/**
	* Add the Email Notification Setting in the woocommerce 
	* @name add_mwb_settings 
	* @since    1.0.0
	* @param $settings
	*/
	public function mwb_wpr_add_email_notification_settings($settings) {
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {
			$new_inserted_array = array(
				array(
					'title' =>  __("Comment Points Notification Settings",MWB_UWPR_DOMAIN), 
					'type'  => 'title',
					),
				array(
					'title'         => __( 'Email Subject', MWB_UWPR_DOMAIN ),
					'type'          => 'text',					
					'id' 			=> 'mwb_wpr_comment_email_subject',
					'class' 			=> 'input-text',
					'desc_tip' 		=> __('Input subject for email.', MWB_UWPR_DOMAIN),
					'default' 	=>__('Comment Points Notification',MWB_UWPR_DOMAIN),
					),
				array(
					'title'         => __( 'Email Description', MWB_UWPR_DOMAIN ),
					'type'          => 'textarea_email',					
					'id' 			=> 'mwb_wpr_comment_email_discription_custom_id',
					'class' 			=> 'input-text',
					'desc_tip' 		=> __('Enter Email Description for user.', MWB_UWPR_DOMAIN),
					'default'	=> __('You have received',MWB_UWPR_DOMAIN).'[Points]'.__(' points and your total points is',MWB_UWPR_DOMAIN).'[Total Points]'.__('.',MWB_UWPR_DOMAIN),
					'desc'			=>	__('Use ',MWB_UWPR_DOMAIN ).'[Points]'.__(' shortcode in place of comment points ',MWB_UWPR_DOMAIN).'[USERNAME]'.__(' shortcode in place of username ',MWB_UWPR_DOMAIN).'[Refer Points]'.__(' shortcode in place of Referral points.', MWB_UWPR_DOMAIN).'[Per Currency Spent Points]'.__('in place of per currency spent points and',MWB_UWPR_DOMAIN).'[Total Points]'.__('shortcode in place of Total Points.',MWB_UWPR_DOMAIN),
					),
				array(
					'type'  => 'sectionend',
					),
				array(
					'title' =>  __("Point Sharing Notification",MWB_UWPR_DOMAIN), 
					'type'  => 'title',
					),
				array(
					'title'         => __( 'Email Subject', MWB_UWPR_DOMAIN ),
					'type'          => 'text',					
					'id' 			=> 'mwb_wpr_point_sharing_subject',
					'class' 			=> 'input-text',
					'desc_tip' 		=> __('Input subject for email.', MWB_UWPR_DOMAIN),
					'default' 	=>__('Received Points Successfully!!',MWB_UWPR_DOMAIN),
					),
				array(
					'title'         => __( 'Email Description', MWB_UWPR_DOMAIN ),
					'type'          => 'textarea_email',					
					'id' 			=> 'mwb_wpr_point_sharing_description',
					'class' 			=> 'input-text',
					'desc_tip' 		=> __('Enter Email Description for user.', MWB_UWPR_DOMAIN),
					'default'	=> __('You have received',MWB_UWPR_DOMAIN).'[RECEIVEDPOINT]'.__('by your one of the friend having Email Id is').'[SENDEREMAIL]'.__('and your total points is',MWB_UWPR_DOMAIN).'[Total Points]'.__('.',MWB_UWPR_DOMAIN),
					'desc'			=>	__('Use ',MWB_UWPR_DOMAIN ).'[RECEIVEDPOINT]'.__(' shortcode in place of points which has been received ',MWB_UWPR_DOMAIN).'[USERNAME]'.__(' shortcode in place of username ',MWB_UWPR_DOMAIN).'[SENDEREMAIL]'.__(' shortcode in place of email id of Sender.', MWB_UWPR_DOMAIN).'[Total Points]'.__('shortcode in place of Total Points.',MWB_UWPR_DOMAIN),
					),
				array(
					'type'  => 'sectionend',
					),
				array(
					'title' =>  __("Purchase Products through Points Notification",MWB_UWPR_DOMAIN), 
					'type'  => 'title',
					),
				array(
					'title'         => __( 'Email Subject', MWB_UWPR_DOMAIN ),
					'type'          => 'text',					
					'id' 			=> 'mwb_wpr_pro_pur_by_points_email_subject',
					'class' 			=> 'input-text',
					'desc_tip' 		=> __('Input subject for email.', MWB_UWPR_DOMAIN),
					'default' 	=>__('Product Purchased Through Points Notification',MWB_UWPR_DOMAIN),
					),
				array(
					'title'         => __( 'Email Description', MWB_UWPR_DOMAIN ),
					'type'          => 'textarea_email',					
					'id' 			=> 'mwb_wpr_pro_pur_by_points_discription_custom_id',
					'class' 			=> 'input-text',
					'desc_tip' 		=> __('Enter Email Description for user.', MWB_UWPR_DOMAIN),
					'default'	=> __('Product Purchased Point',MWB_UWPR_DOMAIN).'[PROPURPOINTS]'.__('has been deducted from your points on purchasing, and your Total Point is').'[Total Points]'.__('.',MWB_UWPR_DOMAIN),
					'desc'			=>	__('Use ',MWB_UWPR_DOMAIN ).'[PROPURPOINTS]'.__(' shortcode in place of purchasing points',MWB_UWPR_DOMAIN).'[USERNAME]'.__(' shortcode in place of username ',MWB_UWPR_DOMAIN).'[Total Points]'.__('shortcode in place of Total Points.',MWB_UWPR_DOMAIN),
					),
				array(
					'type'  => 'sectionend',
					),
				);
				$settings = $this->InsertKeyValuePair($settings,$new_inserted_array,19);
		}
		return $settings;
	}

	/**
	* Add Coupon settings in the lite
	* @name add_mwb_settings 
	* @since    1.0.0
	* @param $settings
	*/
	public function mwb_wpr_add_coupon_settings($coupon_settings) {
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {
			$new_inserted_array = array(
				array(
					'title' =>  __("Coupon Settings",MWB_UWPR_DOMAIN), 
					'type'  => 'title',
					),
				array(
					'title' => __('Enable Points Conversion',MWB_RWPR_Domain), 
					'type'  =>  'checkbox',
					'id'  =>  'mwb_wpr_enable_coupon_generation',
					'class' =>  'input-text',
					'desc'  =>  __('Enable Points Conversion Fields',MWB_RWPR_Domain),
					'desc_tip' => __('Check this box if you want to enable the coupon generation functionality for customers.', MWB_RWPR_Domain)
					),
				array(
					'title' => __('Redeem Points Conversion', MWB_RWPR_Domain),
					'desc_tip'  => __('Enter the redeem points for the coupon.(i.e., how much points will be equivalent to the amount)', MWB_RWPR_Domain),
					'type'    => 'number_text',
					'number_text' => array(
						array(
							'type'  => "number",
							'id'    => "mwb_wpr_coupon_redeem_points",
							'class'   => 'input-text wc_input_price mwb_wpr_new_woo_ver_style_text',
							'custom_attributes'=> array('min'=>'"1"'),
							'desc' => __("Points =", MWB_RWPR_Domain),
							),
						array(
							'type'  => "text",
							'id'    => "mwb_wpr_coupon_redeem_price",
							'class'   => 'input-text mwb_wpr_new_woo_ver_style_text wc_input_price',
							'default'  => '1',
							'custom_attributes'=> array('min'=>'"1"'),
							),
						),
					),
				array(
					'title' => __('Enter Minimum Points Required For Generating Coupon',MWB_UWPR_DOMAIN),
					'type'	=> 'number',
					'id'	=> 'mwb_wpr_general_minimum_value',
					'desc_tip'=> __('The minimum points customer requires for converting their points to coupon', MWB_UWPR_DOMAIN),
					'default' => 50,
					),
				array(
					'title' => __('Enable Custom Convert Points',MWB_RWPR_Domain), 
					'type'  =>  'checkbox',
					'id'  =>  'mwb_wpr_general_custom_convert_enable',
					'class' =>  'input-text',
					'desc'  =>  __('Enable to allow customers to convert some of the points to coupon out of their given total points.',MWB_RWPR_Domain),
					'desc_tip' => __('Check this box to allow your customers to convert their custom points to coupon out of their total points.', MWB_RWPR_Domain)
					),
				array(
					'title' => __('Individual Use',MWB_RWPR_Domain), 
					'type'  =>  'checkbox',
					'id'  =>  'mwb_wpr_coupon_individual_use',
					'class' =>  'input-text',
					'desc'  =>  __('Allow Coupons to use Individually.',MWB_RWPR_Domain),
					'desc_tip' => __('Check this box to if this Coupon can not be used in conjunction with other Coupons.', MWB_RWPR_Domain)
					),
				array(
					'title' => __('Free Shipping',MWB_RWPR_Domain), 
					'type'  =>  'checkbox',
					'id'  =>  'mwb_wpr_points_freeshipping',
					'class' =>  'input-text',
					'desc'  =>  __('Allow Coupons on Free Shipping.',MWB_RWPR_Domain),
					'desc_tip' => __('Check this box if the coupon grants free shipping. A free shipping method must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', MWB_RWPR_Domain)
					),
				array(
					'title' => __('Coupon Length',MWB_UWPR_DOMAIN),
					'type'	=> 'number',
					'id'	=> 'mwb_wpr_points_coupon_length',
					'desc_tip'=> __('Enter Coupon length excluding the prefix.(Minimum length is set to 5', MWB_UWPR_DOMAIN),
					'default' => 5,
					),
				array(
					'title' => __('Coupon Expiry After Days',MWB_UWPR_DOMAIN),
					'type'	=> 'number',
					'id'	=> 'mwb_wpr_coupon_expiry',
					'desc_tip'=> __('Enter number of days after which Coupon will get expired. Keep value "1" for one day expiry when order is completed. Keep value "0" for no expiry.', MWB_UWPR_DOMAIN),
					'default' => 0,
					),
				array(
					'title' => __('Minimum Spend',MWB_UWPR_DOMAIN),
					'type'	=> 'number',
					'id'	=> 'mwb_wpr_coupon_minspend',
					'desc_tip'=> __('This field allows you to set the minimum spend (subtotal, including taxes) allowed to use the coupon.', MWB_UWPR_DOMAIN),
					'default' => 0,
					),
				array(
					'title' => __('Maximum Spend',MWB_UWPR_DOMAIN),
					'type'	=> 'number',
					'id'	=> 'mwb_wpr_coupon_maxspend',
					'desc_tip'=> __('This field allows you to set the maximum spend (subtotal, including taxes) allowed when using the Coupon.', MWB_UWPR_DOMAIN),
					'default' => 0,
					),
				array(
					'title' => __('Coupon No of time uses',MWB_UWPR_DOMAIN),
					'type'	=> 'number',
					'id'	=> 'mwb_wpr_coupon_use',
					'desc_tip'=> __('How many times this coupon can be used before Coupon is void.', MWB_UWPR_DOMAIN),
					),
				array(
					'type'  => 'sectionend',
					),
				);
			$coupon_settings = $this->InsertKeyValuePair($coupon_settings,$new_inserted_array,4);
		}
		return $coupon_settings;
	}

	/**
	* Add Pro settings of the other setting
	* @name add_mwb_settings 
	* @since    1.0.0
	* @param $settings
	*/
	public function mwb_wpr_other_settings($settings) {

		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {
			$mwb_pro_settings = array(
				array(
					'title' => __('Thankyou Page Settings',MWB_RWPR_Domain), 
					'type'	=> 'title',
					),
				array(
					'id'	=>	'mwb_wpr_thnku_order_msg',
					'type'	=>	'textarea',		
					'title'	=>	__('Enter Thankyou Order Message When your customer gain some points', MWB_RWPR_Domain),
					'desc_tip'	=>	__('Entered Message will appears at thankyou page when any order item is having some of the points', MWB_RWPR_Domain),
					'class' =>	'input-text',
					'desc2'	=>	__('Use these shortcodes for providing an appropriate message for your customers ', MWB_RWPR_Domain).'[POINTS]'.__(' for product points ',MWB_RWPR_Domain).'[TOTALPOINT]'.__(' for their Total Points ',MWB_RWPR_Domain),

					'custom_attributes' => array('cols' => '"35"' , 'rows' => '"5"'),
					'default'	=>	__('Your Current Level',MWB_RWPR_Domain),
					),
				array(
					'id'	=>	'mwb_wpr_thnku_order_msg_usin_points',
					'type'	=>	'textarea',		
					'title'	=>	__('Enter Thankyou Order Message When your customer spent some of the points', MWB_RWPR_Domain),
					'desc_tip'	=>	__('Entered Message will appears at thankyou page when any item has been purchased through points', MWB_RWPR_Domain),
					'class' =>	'input-text',
					'desc2'	=>	__('Use these shortcodes for providing an appropriate message for your customers ', MWB_RWPR_Domain).'[POINTS]'.__(' for product points ',MWB_RWPR_Domain).' [TOTALPOINT]'.__(' for their Total Points ',MWB_RWPR_Domain),
					'custom_attributes' => array('cols' => '"35"','rows' => '"5"'),
					'default'	=>	'',
					),
				array(
					'type' 	=> 'sectionend',
					),
				array(
					'title' => __('Points Sharing',MWB_RWPR_Domain), 
					'type'	=> 'title',
					),
				array(
					'id'	=>	'mwb_wpr_user_can_send_point',
					'type'	=>	'checkbox',		
					'title'	=>	__('Point Sharing', MWB_RWPR_Domain),
					'desc_tip'	=>	__('Check this box if you want to let your customers to send some of the points from his/her account to any other user', MWB_RWPR_Domain),
					'class' =>	'input-text',
					'desc'	=>	__('Enable Point Sharing',MWB_RWPR_Domain),
					),
				array(
					'type' 	=> 'sectionend',
					),
				);
			$settings = $this->InsertKeyValuePair($settings,$mwb_pro_settings,5);
		}
		return $settings;
	}
	/**
	* Insert array 
	* @name InsertKeyValuePair 
	* @since    1.0.0
	* @param $arr, $inserted_array, $index
	*/
	public function InsertKeyValuePair($arr, $inserted_array, $index) {
		$arrayEnd = array_splice($arr, $index);
		$arrayStart = array_splice($arr, 0, $index);   
		return (array_merge($arrayStart, $inserted_array, $arrayEnd ));
	}

	/**
	* This function update points on comment.
	* 
	* @name mwb_wpr_give_points_on_comment
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_give_points_on_comment($new_status, $old_status, $comment ) {
		global $current_user;
		$user_email=$comment->comment_author_email;
		if($new_status == 'approved'){
			/*Generate the public class object*/
			$public_obj = $this->generate_public_obj();
			$enable_mwb_comment =$public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_comment_enable');
			if($enable_mwb_comment ) {
				$today_date = date_i18n("Y-m-d h:i:sa");
				$mwb_comment_value = $public_obj->mwb_wpr_get_general_settings_num('mwb_wpr_general_comment_value');
				$mwb_comment_value = ($mwb_comment_value == 0)?1:$mwb_comment_value;
				/*Get the total points of the users*/
				$get_points = get_user_meta($comment->user_id, 'mwb_wpr_points', true);
				/*Get Details of the points*/
				$get_detail_point = get_user_meta($comment->user_id, 'points_details', true);
				/*Update the points details in the woocommerce*/
				if(isset($get_detail_point['comment']) && !empty($get_detail_point['comment']) ) {
					$comment_arr = array();
					$comment_arr= array(
						'comment'=>$mwb_comment_value,
						'date'=>$today_date);
					$get_detail_point['comment'][] = $comment_arr;

				}
				else {
					if(!is_array($get_detail_point)) {
						$get_detail_point = array();
					}
					$comment_arr= array(
						'comment'=>$mwb_comment_value,
						'date'=>$today_date);
					
					$get_detail_point['comment'][]= $comment_arr;
				}
				/*Update user points*/
				update_user_meta( $comment->user_id , 'mwb_wpr_points' , $mwb_comment_value+$get_points );
				/*Update user points Details*/
				update_user_meta( $comment->user_id , 'points_details' , $get_detail_point);
				/*Send mail to customer that he has earned points*/
				$this->mwb_wpr_send_mail_comment($comment->user_id,$mwb_comment_value);
			}
		}
	}

	/**
	* This function use to send mail to Regarding the customer points
	* 
	* @name mwb_wpr_give_points_on_comment
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_send_mail_comment($user_id,$mwb_comment_value) {
		$user=get_user_by('ID',$user_id);
		$user_email=$user->user_email;
		$user_name = $user->user_firstname;	
		$mwb_wpr_notificatin_array=get_option('mwb_wpr_notificatin_array',true);
		/*Generate of the object of the public object*/
		$public_obj = $this->generate_public_obj();
		/*Get the points of the user*/
		$get_points = get_user_meta($comment->user_id, 'mwb_wpr_points', true);
		/*check the condition*/
		if(is_array($mwb_wpr_notificatin_array) && !empty($mwb_wpr_notificatin_array)) {
			$total_points=$get_points;
			/* Get the subject of the comment email*/
			$mwb_wpr_email_subject = Rewardeem_woocommerce_Points_Rewards_Public::mwb_wpr_get_email_notification_description('mwb_wpr_comment_email_subject');
			/* Get the Description of the comment email*/
			$mwb_wpr_email_discription=Rewardeem_woocommerce_Points_Rewards_Public::mwb_wpr_get_email_notification_description('mwb_wpr_comment_email_discription_custom_id');
			/*Replace the shortcode in the description*/
			$mwb_wpr_email_discription=str_replace("[Points]",$mwb_comment_value,$mwb_wpr_email_discription);
			$mwb_wpr_email_discription=str_replace("[Total Points]",$total_points,$mwb_wpr_email_discription);
			$mwb_wpr_email_discription = str_replace("[USERNAME]",$user_name,$mwb_wpr_email_discription);
			/*Check is points Email notification is enable*/
			if(Rewardeem_woocommerce_Points_Rewards_Admin::mwb_wpr_check_mail_notfication_is_enable()) {
				$headers = array('Content-Type: text/html; charset=UTF-8');
				wc_mail($user_email,$mwb_wpr_email_subject,$mwb_wpr_email_discription,$headers);
			}
		}

	}
	
	/**
	* Generate the public obj.
	* @name generate_public_obj 
	* @since    1.0.0
	*/
	public function generate_public_obj() {
		if (class_exists('Rewardeem_woocommerce_Points_Rewards_Public')) {
		$public_obj = new Rewardeem_woocommerce_Points_Rewards_Public('rewardeem-woocommerce-points-rewards','1.0.0');
		return $public_obj;
		}
	}

	/**
	* This is the function adding category wise settings
	* 
	* @name mwb_wpr_add_new_catories_wise_settings
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link http://www.makewebbetter.com/
	*/
	public function mwb_wpr_add_new_catories_wise_settings() {
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {
			?>
			<p class="mwb_wpr_notice"><?php _e("This is the category wise setting for assigning points to product of categories, enter some valid points for assigning, leave blank fields for removing assigned points",'rewardeem-woocommerce-points-rewards');?></p>
			<table class="form-table mwb_wpr_pro_points_setting mwp_wpr_settings">
				<tbody>
					<tr>
						<th class="titledesc"><?php _e('Categories','ultimate-woocommerce-points-and-rewards');?></th>
						<th class="titledesc"><?php _e('Enter Points','ultimate-woocommerce-points-and-rewards');?></th>
						<th class="titledesc"><?php _e('Assign/Remove','ultimate-woocommerce-points-and-rewards');?></th>
					</tr>
					<?php
					$args = array('taxonomy'=>'product_cat');
					$categories = get_terms($args);
					if(isset($categories) && !empty($categories))
					{	
						foreach($categories as $category)
						{
							$catid = $category->term_id;
							$catname = $category->name;
							$mwb_wpr_categ_point = get_option("mwb_wpr_points_to_per_categ_".$catid,'');
							?>
							<tr>
								<td><?php echo $catname;?></td>
								<td><input type="number" min="1" name="mwb_wpr_points_to_per_categ" id="mwb_wpr_points_to_per_categ_<?php echo $catid ;?>" value="<?php echo $mwb_wpr_categ_point; ?>" class="input-text mwb_wpr_new_woo_ver_style_text"></td>
								<td><input type="button" value='<?php _e('Submit','ultimate-woocommerce-points-and-rewards') ?>' class="button-primary woocommerce-save-button mwb_wpr_submit_per_category" name="mwb_wpr_submit_per_category" id="<?php echo $catid ;?>"></td>
							</tr>
							<?php
						}
					}	
					?>
				</tbody>
			</table>
			<?php
		}
	}

	/**
	* This function append the option field after selecting Product category through ajax in Assign Product Points Tab
	* 
	* @name mwb_wpr_select_category.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_per_pro_category() {
		check_ajax_referer( 'mwb-wpr-verify-nonce', 'mwb_nonce' );
		$mwb_wpr_categ_id = sanitize_post($_POST['mwb_wpr_categ_id']);
		$mwb_wpr_categ_point = sanitize_post($_POST['mwb_wpr_categ_point']);
		$response['result'] = __('Fail due to an error','ultimate-woocommerce-points-and-rewards');
		if(isset($mwb_wpr_categ_id) && !empty($mwb_wpr_categ_id)) {
			$products = array();
			$selected_cat = $mwb_wpr_categ_id;
			$tax_query['taxonomy'] = 'product_cat';
			$tax_query['field'] = 'id';
			$tax_query['terms'] = $selected_cat;
			$tax_queries[] = $tax_query;
			$args = array( 'post_type' => 'product', 'posts_per_page' => -1, 'tax_query' => $tax_queries, 'orderby' => 'rand' );
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post(); global $product;

			$product_id = $loop->post->ID;
			$product_title = $loop->post->post_title;
			if(isset($mwb_wpr_categ_point) && !empty($mwb_wpr_categ_point))
			{	
				$product = wc_get_product($product_id);
				if( $product->is_type( 'variable' ) && $product->has_child() ) {
					$parent_id = $product->get_id();
					$parent_product = wc_get_product($parent_id);	
					foreach ($parent_product->get_children() as $child_id) {
						update_post_meta($parent_id, 'mwb_product_points_enable', 'yes');
						update_post_meta($child_id, 'mwb_wpr_variable_points', $mwb_wpr_categ_point);
					}

				}
				else {
					update_post_meta($product_id, 'mwb_product_points_enable', 'yes');
					update_post_meta($product_id, 'mwb_points_product_value', $mwb_wpr_categ_point);
					update_option("mwb_wpr_points_to_per_categ_".$mwb_wpr_categ_id, $mwb_wpr_categ_point);
				}
			}
			else {
				update_post_meta($product_id, 'mwb_product_points_enable', 'no');
				update_post_meta($product_id, 'mwb_points_product_value', '');
				update_option("mwb_wpr_points_to_per_categ_".$mwb_wpr_categ_id, $mwb_wpr_categ_point);
			}
			endwhile;
			$response['category_id'] = $mwb_wpr_categ_id;
			$response['categ_point'] = $mwb_wpr_categ_point;
			$response['result'] = 'success';
			echo json_encode($response);
			wp_die();
		}
		
	}

	/**
	* This function append the puchase through settings tab
	* 
	* @name mwb_wpr_select_category.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_add_purchase_through_points_settings_tab($tabs) {
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {
			$new_tab = array(
				'product-purchase-points' => array(
					'title' => __( 'Product Purchase Points','ultimate-woocommerce-points-and-rewards' ),
					'file_path' => ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_PATH.'admin/partials/template/mwb_pro_purchase_points.php',
					),
				'points-expiration' => array(
					'title' => __('Points Expiration','ultimate-woocommerce-points-and-rewards'),
					'file_path' => ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_PATH.'admin/partials/template/mwb_point_expiration.php',
					)
				);
			$tabs = $this->InsertKeyValuePair($tabs,$new_tab,7);
		}
		return $tabs;
	}

	/**
	* This function will add the license panel.
	* 
	* @name mwb_add_license_panel.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_add_license_panel($tabs) {
		$new_tab = array(
			'license' => array(
				'title' => __( 'License','ultimate-woocommerce-points-and-rewards' ),
				'file_path' => ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_PATH.'admin/partials/ultimate-woocommerce-points-and-rewards-admin-license.php',
				),
			);
		$tabs = $this->InsertKeyValuePair($tabs,$new_tab,10);
		return $tabs;
	}

	/**
	* This function append the option field after selecting Product category through ajax in Product Purchase Points Tab
	* 
	* @name mwb_wpr_per_pro_pnt_category.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_per_pro_pnt_category() {
		check_ajax_referer( 'mwb-wpr-verify-nonce', 'mwb_nonce' );
		$mwb_wpr_categ_id = sanitize_post($_POST['mwb_wpr_categ_id']);
		$mwb_wpr_categ_point = sanitize_post($_POST['mwb_wpr_categ_point']);
		$response['result'] = __('Fail due to an error','ultimate-woocommerce-points-and-rewards');
		if(isset($mwb_wpr_categ_id) && !empty($mwb_wpr_categ_id)) {
			$products = array();
			$selected_cat = $mwb_wpr_categ_id;
			$tax_query['taxonomy'] = 'product_cat';
			$tax_query['field'] = 'id';
			$tax_query['terms'] = $selected_cat;
			$tax_queries[] = $tax_query;
			$args = array( 'post_type' => 'product', 'posts_per_page' => -1, 'tax_query' => $tax_queries, 'orderby' => 'rand' );
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post(); global $product;

			$product_id = $loop->post->ID;
			$product_title = $loop->post->post_title;
			if(isset($mwb_wpr_categ_point) && !empty($mwb_wpr_categ_point))
			{	
				$product = wc_get_product($product_id);
				if( $product->is_type( 'variable' ) && $product->has_child() ){
					$parent_id = $product->get_id();
					$parent_product = wc_get_product($parent_id);	
					foreach ($parent_product->get_children() as $child_id) {
						update_post_meta($parent_id, 'mwb_product_purchase_points_only','yes');
						update_post_meta($child_id, 'mwb_wpr_variable_points_purchase', $mwb_wpr_categ_point);							
					}

				}
				else {
					update_post_meta($product_id, 'mwb_product_purchase_points_only','yes');
					update_post_meta($product_id, 'mwb_points_product_purchase_value', $mwb_wpr_categ_point);
					update_option("mwb_wpr_purchase_points_cat".$mwb_wpr_categ_id, $mwb_wpr_categ_point);							
				}
			}
			else {
				update_post_meta($product_id, 'mwb_product_purchase_points_only', 'no');
				update_post_meta($product_id, 'mwb_points_product_purchase_value', '');
				update_option("mwb_wpr_purchase_points_cat".$mwb_wpr_categ_id, $mwb_wpr_categ_point);
			}
			endwhile;
			$response['category_id'] = $mwb_wpr_categ_id;
			$response['categ_point'] = $mwb_wpr_categ_point;
			$response['result'] = 'success';
			echo json_encode($response);
			wp_die();
		}
	} 

	/**
	* This construct add tab in products menu.
	* 
	* @name mwb_wpr_add_points_tab
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	* @param type of array
	*/ 
	public function mwb_wpr_add_points_tab($all_tabs) {
		$all_tabs['points'] =  array(
			'label'  => __( 'Points and Rewards', 'ultimate-woocommerce-points-and-rewards' ),
			'target' => 'points_data',
			);
		return $all_tabs;
	}

	/**
	* This construct set products point.
	* 
	* @name mwb_wpr_points_input
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_points_input() {
		global $post;
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {
			$product_is_variable = false;	
			$product = wc_get_product($post->ID);
			if( $product->is_type( 'variable' ) && $product->has_child() ){
				$product_is_variable = true;	
			}
			?>
			<div id="points_data" class="panel woocommerce_options_panel">
				<div class="options_group">
					<?php 
					woocommerce_wp_checkbox( 
						array( 
							'id' => 'mwb_product_points_enable', 
							'wrapper_class' => 'show_if_points', 
							'label' => __( 'Enable', 'ultimate-woocommerce-points-and-rewards' ), 
							'description' => __( 'Enable Points Per Product', 'ultimate-woocommerce-points-and-rewards' ) 
							) 
						);
					if(!$product_is_variable){
						woocommerce_wp_text_input( array(
							'id'                => 'mwb_points_product_value',
							'label'             => __( 'Enter the Points', 'ultimate-woocommerce-points-and-rewards' ),
							'desc_tip'          => true,
							'custom_attributes'   => array('min'=>'0'),
							'description'       => __( 'Please enter the number of points for this product ', 'ultimate-woocommerce-points-and-rewards' ),
							'type'              => 'number',
							) );
					}
					woocommerce_wp_checkbox( 
						array( 
							'id' => 'mwb_product_purchase_through_point_disable',
							'wrapper_class' => 'show_if_points', 
							'label' => __( 'Do not allow to purchase through points', 'ultimate-woocommerce-points-and-rewards' ), 
							'description' => __( 'Do not allow to purchase purchase this product thorugh points', 'ultimate-woocommerce-points-and-rewards') 
							) 
						);

					woocommerce_wp_checkbox( 
						array( 
							'id' => 'mwb_product_purchase_points_only',
							'wrapper_class' => 'show_if_points_only', 
							'label' => __( 'Enable', 'ultimate-woocommerce-points-and-rewards' ), 
							'description' => __( 'Enable Purchase through points only', 'ultimate-woocommerce-points-and-rewards' ) 
							) 
						);
					if(!$product_is_variable){
						woocommerce_wp_text_input( array(
							'id'                => 'mwb_points_product_purchase_value',
							'label'             => __( 'Enter the Points For Purchase', 'ultimate-woocommerce-points-and-rewards' ),
							'desc_tip'          => true,
							'custom_attributes'   => array('min'=>'0'),
							'description'       => __( 'Please enter the number of points for purchase this product ', 'ultimate-woocommerce-points-and-rewards' ),
							'type'              => 'number',
							) );
					}
					?>
					<input type="hidden" name="mwb_product_hidden_field"></input>
				</div>
			</div>
			<?php
		} 
		
	}

	/**
	 * This function is used to add the textbox for variable products
	 * 
	 * @name mwb_wpr_woocommerce_variation_options_pricing
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wpr_woocommerce_variation_options_pricing( $loop, $variation_data, $variation ) {
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {

			if(isset($variation_data['mwb_wpr_variable_points'][0])){
				$mwb_wpr_variable_points = $variation_data['mwb_wpr_variable_points'][0];
			}else {
				$mwb_wpr_variable_points = '';
			}
			?>
			<?php
			if(is_admin()) {
				woocommerce_wp_text_input( 
					array(
						'id'            => "mwb_wpr_variable_points_{$loop}",
						'name'          => "mwb_wpr_variable_points_{$loop}",
						'value'         => $mwb_wpr_variable_points,
						'label'         => __( 'Enter Point', 'ultimate-woocommerce-points-and-rewards' ),
						'data_type'     => 'price',
						'wrapper_class' => 'form-row form-row-first',
						'placeholder'   => __( 'Product Point', 'ultimate-woocommerce-points-and-rewards' ),
						) 
					);
			}

			if(isset($variation_data['mwb_wpr_variable_points_purchase'][0])){
				$mwb_wpr_variable_points_purchase = $variation_data['mwb_wpr_variable_points_purchase'][0];
			}else{
				$mwb_wpr_variable_points_purchase = '';
			}

			if(is_admin()) {
				woocommerce_wp_text_input( 
					array(
						'id'            => "mwb_wpr_variable_points_purchase_{$loop}",
						'name'          => "mwb_wpr_variable_points_purchase_{$loop}",
						'value'         => $mwb_wpr_variable_points_purchase,
						'label'         => __( 'Enter Point for purchase', 'ultimate-woocommerce-points-and-rewards' ),
						'data_type'     => 'price',
						'wrapper_class' => 'form-row form-row-first',
						'placeholder'   => __( 'Product Point for purchase', 'ultimate-woocommerce-points-and-rewards' ),
						) 
					);
			}
		}
	}

	/**
	 * This function is used to save the product variation points
	 * 
	 * @name mwb_wpr_woocommerce_save_product_variation
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wpr_woocommerce_save_product_variation($variation_id, $i){
		if(isset($_POST['mwb_wpr_variable_points_'.$i])) {
			$mwb_wpr_points = $_POST['mwb_wpr_variable_points_'.$i];
			update_post_meta($variation_id, 'mwb_wpr_variable_points', $mwb_wpr_points);
		}
		if(isset($_POST['mwb_wpr_variable_points_purchase_'.$i])) {
			$mwb_wpr_points_purchase = $_POST['mwb_wpr_variable_points_purchase_'.$i];	

			update_post_meta($variation_id, 'mwb_wpr_variable_points_purchase', $mwb_wpr_points_purchase);
		}
	}

	/**
	* This function update product custom points
	* 
	* @name woo_add_custom_points_fields_save
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function woo_add_custom_points_fields_save($post_id) {

		if(isset($_POST['mwb_product_hidden_field'])) {
			$enable_product_points = isset($_POST['mwb_product_points_enable'])?sanitize_post($_POST['mwb_product_points_enable']): 'no';
			$enable_product_purchase_points = isset($_POST['mwb_product_purchase_points_only'])?sanitize_post($_POST['mwb_product_purchase_points_only']): 'no';				
			$mwb_pro_pur_by_point_disable = isset($_POST['mwb_product_purchase_through_point_disable'])?sanitize_post($_POST['mwb_product_purchase_through_point_disable']): 'no';
			$mwb_product_value = (isset($_POST['mwb_points_product_value']) && $_POST['mwb_points_product_value'] !=null )? sanitize_post($_POST['mwb_points_product_value']): 1;
			$mwb_product_purchase_value = (isset($_POST['mwb_points_product_purchase_value']) && $_POST['mwb_points_product_purchase_value'] !=null )? sanitize_post($_POST['mwb_points_product_purchase_value']): 1;
			update_post_meta($post_id, 'mwb_product_points_enable', $enable_product_points);
			update_post_meta($post_id, 'mwb_product_purchase_points_only', $enable_product_purchase_points);
			update_post_meta($post_id, 'mwb_points_product_value', $mwb_product_value);	
			update_post_meta($post_id, 'mwb_points_product_purchase_value', $mwb_product_purchase_value);	
			update_post_meta($post_id, 'mwb_product_purchase_through_point_disable', $mwb_pro_pur_by_point_disable);	
		}
	}

	/**
	* This function is used for run the cron for points expiration and handles accordingly
	* 
	* @name mwb_wpr_check_daily_about_points_expiration
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_check_daily_about_points_expiration() {
		$message = '';
		/*Get all settings*/
		$mwb_wpr_points_expiration_enable = $this->mwb_wpr_get_points_expiration_settings_num('mwb_wpr_points_expiration_enable');
		$mwb_wpr_email_tpl = file_get_contents( ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_PATH.'/admin/mwb_wpr_points_expiration_email_template.php');
		/*Get the expiration email*/
		$mwb_wpr_points_expiration_email = $this->mwb_wpr_get_points_expiration_settings('mwb_wpr_points_expiration_email');
		/*Check the condition*/
		if($mwb_wpr_points_expiration_enable){
			/*Get the thresold value*/
			$mwb_wpr_points_expiration_threshold = $this->mwb_wpr_get_points_expiration_settings_num('mwb_wpr_points_expiration_threshold');
			/*Get the expiration time*/
			$mwb_wpr_points_expiration_time_num = $this->mwb_wpr_get_points_expiration_settings_num('mwb_wpr_points_expiration_time_num');
			/*Get the settings of expiration time*/
			$mwb_wpr_points_expiration_time_drop = $this->mwb_wpr_get_points_expiration_settings('mwb_wpr_points_expiration_time_drop');
			/*Check the condition is not empty*/
			$mwb_wpr_points_expiration_time_drop = (!empty($mwb_wpr_points_expiration_time_drop))?$mwb_wpr_points_expiration_time_drop['0']:'days';

			$today_date = date_i18n("Y-m-d");
			$args['meta_query'] = array(                        
				array(
					'key'=>'mwb_wpr_points'
					)
				);
			$user_data= get_users( $args );
			if(is_array($user_data) && !empty($user_data)){
				foreach ($user_data as $key => $value) {
					$user_id = $value->data->ID;
					$user_email=$value->data->user_email;
					if(isset($user_id) && !empty($user_id)){
						$get_points = get_user_meta($user_id,'mwb_wpr_points',true);
						if($get_points == $mwb_wpr_points_expiration_threshold || $get_points > $mwb_wpr_points_expiration_threshold){
							$get_expiration_date = get_user_meta($user_id,'mwb_wpr_points_expiration_date',true);
							if(!isset($get_expiration_date) || empty($get_expiration_date)){
								$expiration_date= date_i18n('Y-m-d', strtotime($today_date. ' +'.$mwb_wpr_points_expiration_time_num.' '.$mwb_wpr_points_expiration_time_drop));
								update_user_meta($user_id,'mwb_wpr_points_expiration_date',$expiration_date);
								$headers = array('Content-Type: text/html; charset=UTF-8');
	                                //Expiration Date has been set to User
								$subject = __('Redeem your Points before it will get expired!','ultimate-woocommerce-points-and-rewards');
								$mwb_wpr_threshold_notif = get_option('mwb_wpr_threshold_notif','You have reached your Threshold and your Total Point is: [TOTALPOINT], which will get expired on [EXPIRYDATE]');
								$message = $mwb_wpr_email_tpl;
								$message = str_replace('[CUSTOMMESSAGE]', $mwb_wpr_threshold_notif, $message);
								$sitename = get_bloginfo();
								$message = str_replace('[SITENAME]', $sitename, $message);
								$message = str_replace('[TOTALPOINT]', $get_points, $message);
								$message = str_replace('[EXPIRYDATE]', $expiration_date, $message);
								wc_mail($user_email,$subject,$message,$headers);
							}
						}
						$get_expiration_date = get_user_meta($user_id,'mwb_wpr_points_expiration_date',true);
						if(isset($get_expiration_date) && !empty($get_expiration_date)){
							$send_notification_date= date_i18n('Y-m-d', strtotime($get_expiration_date. ' -'.$mwb_wpr_points_expiration_email.' days'));
							if(isset($send_notification_date) && !empty($send_notification_date)){
								if($today_date == $send_notification_date){
									$mwb_user_point_expiry = get_user_meta($user_id,'mwb_wpr_points_expiration_date',true);
									$headers = array('Content-Type: text/html; charset=UTF-8');
									$subject = __('Hurry!! Points Expiration has just a few days','ultimate-woocommerce-points-and-rewards');
									$mwb_wpr_re_notification = get_option('mwb_wpr_re_notification','Do not forget to redeem your points([TOTALPOINT]) before it will get expired on [EXPIRYDATE]');
									$message = $mwb_wpr_email_tpl;
									$message = str_replace('[CUSTOMMESSAGE]', $mwb_wpr_re_notification, $message);
									$sitename = get_bloginfo();
									$message = str_replace('[SITENAME]', $sitename, $message);
									$message = str_replace('[TOTALPOINT]', $get_points, $message);
									$message = str_replace('[EXPIRYDATE]', $mwb_user_point_expiry, $message);
                                        //expiration email before one week
									wc_mail($user_email,$subject,$message,$headers);
								}
							}
							if($today_date == $get_expiration_date && $get_points > 0){
								$expired_detail_points = get_user_meta($user_id, 'points_details',true);
								if(isset($expired_detail_points['expired_details']) && !empty($expired_detail_points['expired_details'])){

									$exp_array = array(
										'expired_details'=>$get_points,
										'date'=>$today_date
										);
									$expired_detail_points['expired_details'][] = $exp_array;
								}
								else{
									if(!is_array($expired_detail_points)){
										$expired_detail_points = array();
									}
									$exp_array = array(
										'expired_details'=>$get_points,
										'date'=>$today_date
										);
									$expired_detail_points['expired_details'][] = $exp_array;
								}
								update_user_meta($user_id,'mwb_wpr_points',0);
								update_user_meta($user_id,'points_details',$expired_detail_points);
								delete_user_meta($user_id,'mwb_wpr_points_expiration_date');
								$headers = array('Content-Type: text/html; charset=UTF-8');
								$subject = __('Points has been Expired!','ultimate-woocommerce-points-and-rewards');
								$mwb_wpr_expired_notification = $this->mwb_wpr_get_points_expiration_settings('mwb_wpr_expired_notification');
								$mwb_wpr_expired_notification = (!empty($mwb_wpr_expired_notification))?$mwb_wpr_expired_notification:__('Your Points has been expired, you may earn more Points and use the benefit more','ultimate-woocommerce-points-and-rewards');
								$message = $mwb_wpr_email_tpl;
								$sitename = get_bloginfo();
								$message = str_replace('[SITENAME]', $sitename, $message);
								$message = str_replace('[CUSTOMMESSAGE]', $mwb_wpr_expired_notification, $message);
                                    //points has been expired
								wc_mail($user_email,$subject,$message,$headers);
							}
						}
					}
				}
			}
		}
	}

	/**
	* This function is used to add the Custom Widget for Points and Reward
	* 
	* @name mwb_wpr_custom_widgets.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_custom_widgets() {
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {
			include_once ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_PATH.'/admin/class-mwb-wpr-custom-widget.php';
		}
	}

	/**
	* This function is used to add the Points inside the Orders(if any)
	* 
	* @name mwb_wpr_woocommerce_admin_order_item_headers.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_admin_order_item_headers($order){

		foreach( $order->get_items() as $item_id => $item ){
			$mwb_wpr_items=$item->get_meta_data();
			foreach ($mwb_wpr_items as $key => $mwb_wpr_value){
				if(isset($mwb_wpr_value->key) && !empty($mwb_wpr_value->key) && ($mwb_wpr_value->key=='Points') ){
					?>
					<th class="quantity sortable"><?php _e( 'Points', 'ultimate-woocommerce-points-and-rewards' ); ?></th>
					<?php
				}
			}
		}
	}

	/**
	* This function is used to add the Points inside the Orders(if any)
	* 
	* @name mwb_wpr_woocommerce_admin_order_item_values.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_woocommerce_admin_order_item_values($product,$item,$item_id) {
		$mwb_wpr_items=$item->get_meta_data();
		foreach ($mwb_wpr_items as $key => $mwb_wpr_value) {
			if(isset($mwb_wpr_value->key) && !empty($mwb_wpr_value->key) && ($mwb_wpr_value->key=='Points') ) {
				$item_points = (int)$mwb_wpr_value->value;
				?>
				<td class="item_cost" width="1%" data-sort-value="<?php echo $item_points;?>">
					<div class="view">
						<?php
						echo $item_points;
						?>
					</div>
				</td>
				<?php
			}
		}
	}

	/**
	* This function is used to remove action
	* 
	* @name mwb_wpr_woocommerce_admin_order_item_values.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_remove_action() {
		global $public_obj;
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {
			remove_action('mwb_wpr_add_membership_rule',array($public_obj,'mwb_wpr_add_rule_for_membership'),10,1);
			add_action('mwb_wpr_add_membership_rule',array($this,'mwb_wpr_add_rule_pro'));
			remove_action('mwb_wpr_order_total_points',array($public_obj,'mwb_wpr_add_order_total_points'),10,3);
			add_action('mwb_wpr_order_total_points',array($this,'mwb_wpr_add_order_total_points_pro'),10,3);
		}
	}

	/**
	* This function is used to add rule
	* 
	* @name mwb_wpr_woocommerce_admin_order_item_values.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_add_rule_pro($mwb_wpr_membership_roles) {
		global $public_obj;
		?>
		<div class="parent_of_div">
			<?php 
			$count = 0;
			if(is_array($mwb_wpr_membership_roles) && !empty($mwb_wpr_membership_roles)) {

				foreach($mwb_wpr_membership_roles as $role => $values) {
					$public_obj->mwb_wpr_membership_role($count,$role,$values);
					$count++;
				}
			}
			else {
				$public_obj->mwb_wpr_membership_role($count,'','');
			}
			?>
		</div>
		<?php
	}

	/**
	* This function is used to add rule for order total points.
	* 
	* @name mwb_wpr_woocommerce_admin_order_item_values.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_add_order_total_points_pro($thankyouorder_min,$thankyouorder_max,$thankyouorder_value) {
		global $public_obj;
		if( isset($thankyouorder_min) && $thankyouorder_min !=null && isset($thankyouorder_max) && $thankyouorder_max !=null && isset($thankyouorder_value) && $thankyouorder_value !=null) {
			$mwb_wpr_no = 1;
			if(count($thankyouorder_min) == count($thankyouorder_max) && count($thankyouorder_max) == count($thankyouorder_value) ) {
				foreach ($thankyouorder_min as $key => $value) {
					$public_obj->mwb_wpr_add_rule_for_order_total_points($thankyouorder_min,$thankyouorder_max,$thankyouorder_value,$key);
				}
			}
		}
		else {
			$public_obj->mwb_wpr_add_rule_for_order_total_points(array(),array(),array(),'');
		}                           
	} 

	/**
	* This function is used to add rule for order total points.
	* 
	* @name mwb_wpr_add_notice.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_add_notice() {
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();
		if ( ! Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() && 0 <= $day_count ) {

			$day_count_warning = floor( $day_count );

			$day_string = sprintf( _n( '%s day', '%s days', $day_count_warning, 'ultimate-woocommerce-points-and-rewards' ), number_format_i18n( $day_count_warning ) );

			$day_string = '<span id="ultimate-woocommerce-points-and-rewards-day-count" >'.$day_string.'</span>';

			?>

			<div id="ultimate-woocommerce-points-and-rewards-thirty-days-notify" class="notice notice-warning">
				<p>
					<strong><a href="?page=mwb-rwpr-setting&tab=license"><?php _e( 'Activate', 'ultimate-woocommerce-points-and-rewards' ); ?></a><?php printf( __( ' the license key before %s or you may risk losing data and the plugin will also become dysfunctional.', 'ultimate-woocommerce-points-and-rewards' ), $day_string ); ?></strong>
				</p>
			</div>

			<?php

		}
	}

	/**
	* This function is used to add rule for order total points.
	* 
	* @name mwb_wpr_add_notice.
	* @author makewebbetter<webmaster@makewebbetter.com>
	* @link https://www.makewebbetter.com/
	*/
	public function mwb_wpr_save_membership_settings_pro($mwb_wpr_no_of_section) {
		if ( isset( $mwb_wpr_no_of_section ) ) {
			$count = $mwb_wpr_no_of_section;
			$mwb_wpr_mem_enable = isset( $_POST['mwb_wpr_membership_setting_enable'] ) ? 1 : 0;
			$exclude_sale_product = isset( $_POST['exclude_sale_product'] ) ? 1 : 0;
			for ( $count = 0; $count <= $mwb_wpr_no_of_section; $count++ ) {
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
		}
		$membership_settings_array['mwb_wpr_membership_setting_enable'] = $mwb_wpr_mem_enable;
		$membership_settings_array['membership_roles'] = $membership_roles_list;
		$membership_settings_array['exclude_sale_product'] = $exclude_sale_product;
		if ( is_array( $membership_settings_array ) ) {
			update_option( 'mwb_wpr_membership_settings', $membership_settings_array );
		}
	}
	
}
