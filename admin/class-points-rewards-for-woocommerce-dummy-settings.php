<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin
 * @author     makewebbetter <ticket@makewebbetter.com>
 */
class Points_Rewards_For_WooCommerce_Dummy_Settings {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_action( 'admin_enqueue_scripts', array( $this, 'wps_wpr_enqueue_dummy_file' ) );
		add_filter( 'wps_wpr_general_settings', array( $this, 'add_wps_dummy_settings' ), 10, 1 );
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_dummy_customer_rank_listing' ) );
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_dummy_daily_sign_up_points' ) );
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_dummy_first_order_points' ) );
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_dummy_round_points_settings' ) );
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_dummy_birthday_order_points' ) );
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_dummy_user_roles' ) );
		add_action( 'wps_wpr_additional_general_settings', array( $this, 'wps_wpr_additional_dummy_cart_points_settings' ), 10, 2 );
		add_filter( 'wps_wpr_coupon_settings', array( $this, 'wps_wpr_add_dummy_per_currrency_on_subtotal_option' ), 20 );
		add_filter( 'wps_wpr_coupon_settings', array( $this, 'wps_wpr_dummy_add_coupon_settings' ) );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_custom_notification_callback' ), 20 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_signup_notification_callback' ), 20 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_product_purchase_notification_callback' ), 20 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_order_amount_notification_callback' ), 20 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_emails_notification_settings' ) );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_referral_notification_callback' ) );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_upgrade_membership_notification_callback' ) );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_deduct_assign_notification_callback' ) );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_coupon_notification' ), 35 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_bithday_notification_callback' ), 30 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_points_on_cart_notification_callback' ), 27 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dumm_email_notification_settings_first_order_notification_callback' ), 29 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_notification_settings_order_total_range_notification_callback' ), 28 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_number_dummy_of_order_rewards_points_notifications' ), 34 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_gamification_dummy_notification_enable_settings' ), 33 );
		add_filter( 'wps_wpr_email_notification_settings', array( $this, 'wps_wpr_add_dummy_email_user_badge_enable_notification' ), 36 );
		add_filter( 'wps_wpr_assign_product_points_settings', array( $this, 'wps_wpr_display_dummy_assign_points_on_shop_page' ) );
		add_action( 'wps_wpr_product_assign_points', array( $this, 'wps_wpr_add_dummy_new_catories_wise_settings' ) );
		add_filter( 'wps_rwpr_add_setting_tab', array( $this, 'wps_add_dummy_purchase_through_points_settings_tab' ), 20, 1 );
		add_filter( 'wps_wpr_show_shortcoe_text', array( $this, 'wps_wpr_show_dummy_referral_link_shortcoe' ) );
		add_action( 'wps_wpr_others_settings', array( $this, 'wps_wpr_other_dummy_settings' ), 10, 1 );
		add_filter( 'wps_rwpr_add_setting_tab', array( $this, 'wps_add_points_dummy_notification_addon_settings_tab' ), 22, 1 );
		add_filter( 'wps_rwpr_add_setting_tab', array( $this, 'wps_add_api_dummy_settings_tab' ), 23, 1 );
		add_filter( 'wps_wpr_others_settings', array( $this, 'wps_wpr_total_earning_dummy_points_settings' ), 12, 1 );
	}

	/**
	 * This function is used to show dummy html for referral settings.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function add_wps_dummy_settings( $settings ) {

		$new_inserted_array = array(
			array(
				'title'             => __( 'Minimum Referrals Required', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'id'                => 'wps_wpr_general_refer_minimum',
				'custom_attributes' => array( 'min' => '0' ),
				'class'             => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'Minimum number of referrals required to get referral points when the new customer sign-ups.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Select page where you want to Redirect', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'select',
				'id'       => 'wps_wpr_referral_page',
				'class'    => 'wc-enhanced-select wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Choose page where you want to redirect user through referral link', 'points-and-rewards-for-woocommerce' ),
				'options'  => $this->wps_wpr_dummy_all_pages(),
			),
			array(
				'title'    => __( 'Referral Purchase Points', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'default'  => 1,
				'id'       => 'wps_wpr_general_referal_purchase_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __(
					'Check this box to enable the referral purchase points.',
					'points-and-rewards-for-woocommerce'
				),
				'desc'     => __( 'Enable Referral Purchase Points', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'            => __( 'Referral Purchase Points Type', 'points-and-rewards-for-woocommerce' ),
				'type'             => 'singleSelectDropDownWithKeyvalue',
				'id'               => 'wps_wpr_general_referal_purchase_point_type',
				'class'            => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'custom_attribute' => array(
					array(
						'id'   => 'wps_wpr_fixed_points',
						'name' => __( 'Fixed', 'points-and-rewards-for-woocommerce' ),
					),
					array(
						'id'   => 'wps_wpr_percentage_points',
						'name' => __( 'Percentage', 'points-and-rewards-for-woocommerce' ),
					),
				),
				'desc_tip'         => __(
					'Select the points type on referral purchase depending upon the order total.',
					'points-and-rewards-for-woocommerce'
				),
			),
			array(
				'title'             => __( 'Enter Referral Purchase Points', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'default'           => 1,
				'id'                => 'wps_wpr_general_referal_purchase_value',
				'class'             => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'custom_attributes' => array( 'min' => '0' ),
				'desc_tip'          => __(
					'Entered Points Will be Assigned to That User by Which Another User Referred From.',
					'points-and-rewards-for-woocommerce'
				),
			),
			array(
				'title'             => __( 'Minimum order total for referral purchase reward', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'default'           => 1,
				'id'                => 'wps_wpr_minimum_order_total_for_referral_points',
				'class'             => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'custom_attributes' => array( 'min' => '1' ),
				'desc_tip'          => __( 'Referral purchase points will be rewarded when the order total exceeds the minimum required amount.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Assign Only Referral Purchase Points', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_general_refer_value_disable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __(
					'Check this if you want to assign only purchase points to referred user not referral points.',
					'points-and-rewards-for-woocommerce'
				),
				'desc'     => __( 'Make sure Referral Points & Referral Purchase Points should be enabled.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Referral Purchase Limit', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_general_referal_purchase_limit',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc'     => __( 'Toggle This To Enable The Referral Purchase Points', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __(
					'Toggle this to Set the Limit on the Orders for the Referee',
					'points-and-rewards-for-woocommerce'
				),
			),
			array(
				'title'             => __( 'Number of Orders for the Referral Purchase Limit', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '0' ),
				'id'                => 'wps_wpr_general_referal_order_limit',
				'class'             => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip'          => __(
					'The Number of orders, the Referee would get assigned only until the limit of orders is reached',
					'points-and-rewards-for-woocommerce'
				),
			),
			array(
				'title'    => __( 'Static Referral Link', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_referral_link_permanent',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to make the referral key permanent.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Make Referral Link Permanent', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'             => __( 'Referral Link Expiry', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '0' ),
				'id'                => 'wps_wpr_ref_link_expiry',
				'class'             => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'Number of Days After the Referral Link Will be Expired', 'points-and-rewards-for-woocommerce' ),
				'desc'              => __( 'Days', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Refer Via Referral Coupon Code', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_general_referral_code_enable',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc'     => __( 'Enable to Refer via referral Coupon code', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Toggle This to Assign Only Purchase Points to Referred User.', 'points-and-rewards-for-woocommerce' ),

			),
			array(
				'title'             => __( 'Amount for the Referral Coupon Discount', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '0' ),
				'id'                => 'wps_wpr_general_referral_code__limit',
				'class'             => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip'          => __(
					'Enter the amount for the referral coupon discount',
					'points-and-rewards-for-woocommerce'
				),
			),
			array(
				'title'            => __( 'Referral Purchase Coupon Type', 'points-and-rewards-for-woocommerce' ),
				'type'             => 'singleSelectDropDownWithKeyvalue',
				'id'               => 'wps_wpr_general_referal_coupon_purchase_type',
				'class'            => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'custom_attribute' => array(
					array(
						'id'   => 'wps_wpr_fixed_coupon_points',
						'name' => __( 'Fixed', 'points-and-rewards-for-woocommerce' ),
					),
					array(
						'id'   => 'wps_wpr_percentage_coupon_points',
						'name' => __( 'Percentage', 'points-and-rewards-for-woocommerce' ),
					),
				),
				'desc_tip'         => __(
					'Select Coupon type on referral purchase depending upon the order total.',
					'points-and-rewards-for-woocommerce'
				),
			),
			array(
				'type' => 'sectionend',
			),
			array(
				'title' => __( 'Comments Points', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Comments Points', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_general_comment_enable',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc'     => __( 'Enable Comments Points for Rewards', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Check this box to enable the Comment Points when a comment is approved.', 'points-and-rewards-for-woocommerce' ),

			),
			array(
				'title'             => __( 'Enter Comments Points', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '0' ),
				'id'                => 'wps_wpr_general_comment_value',
				'class'             => 'wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'The points which new customers will get after their comments are approved.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'             => __( 'User per post comment', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '0' ),
				'class'             => 'wps_wpr_pro_plugin_settings',
				'id'                => 'wps_wpr_general_comment_per_post_comment',
				'desc_tip'          => __( 'Number of Comments a User Can Have Per Post.', 'points-and-rewards-for-woocommerce' ),
			),
		);
		$settings = $this->wps_dummy_insert_keys_value_pair( $settings, $new_inserted_array, 10 );
		$settings = $this->wps_wpr_dummy_cart_add_max_apply_points_settings( $settings );
		return $settings;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $arr            arr.
	 * @param array $inserted_array inserted_array.
	 * @param int   $index          index.
	 * @return array
	 */
	public function wps_dummy_insert_keys_value_pair( $arr, $inserted_array, $index ) {
		$arrayend   = array_splice( $arr, $index );
		$arraystart = array_splice( $arr, 0, $index );
		return ( array_merge( $arraystart, $inserted_array, $arrayend ) );
	}

	/**
	 * This function is used to add dummy redemption settings.
	 *
	 * @param  array $settings settings.
	 * @return array
	 */
	public function wps_wpr_dummy_cart_add_max_apply_points_settings( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Point Usage Limitation', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_max_points_on_cart',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc'     => esc_html__( 'Allow customers to pay a particular part of the order using points.', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Check this box to enable the Maximum Points to apply to the cart', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'            => __( 'Select Points Limitation Type', 'points-and-rewards-for-woocommerce' ),
				'id'               => 'wps_wpr_cart_point_type',
				'class'            => 'wps_wgm_new_woo_ver_style_select wps_wpr_pro_plugin_settings',
				'type'             => 'singleSelectDropDownWithKeyvalue',
				'desc_tip'         => __( 'Select the discount Type to apply points', 'points-and-rewards-for-woocommerce' ),
				'custom_attribute' => array(
					array(
						'id'   => 'wps_wpr_fixed_cart',
						'name' => __( 'Fixed', 'points-and-rewards-for-woocommerce' ),
					),
					array(
						'id'   => 'wps_wpr_percentage_cart',
						'name' => __( 'Percentage', 'points-and-rewards-for-woocommerce' ),
					),
				),
			),
			array(
				'title'             => __( 'Enter Amount', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '"0"' ),
				'id'                => 'wps_wpr_amount_value',
				'class'             => 'wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'Enter the amount that customers can pay using their points', 'points-and-rewards-for-woocommerce' ),
				'desc'              => __( ' Enter the amount that customer can pay using their points', 'points-and-rewards-for-woocommerce' ),
			),

			array(
				'title'    => __( 'Point Restriction on sale Product', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_points_restrict_sale',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc'     => esc_html__( 'Toggle This To Restrict The Points Discount on Sale Items.', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Check this box to restrict the points discount on sales item', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'             => __( 'Minimum points you want to start the redemption', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '"0"' ),
				'id'                => 'wps_wpr_apply_points_value',
				'class'             => 'wps_wpr_pro_plugin_settings',
				'desc_tip'          => esc_html__( 'Enter the min points you want the user to redeem points.', 'points-and-rewards-for-woocommerce' ),
				'desc'              => __( 'Enter the minimum points you want the user to redeem.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Restrict points redemption by category', 'points-and-rewards-for-woocommerce' ),
				'id'       => 'wps_wpr_restrict_redeem_points_category_wise',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'type'     => 'search&select',
				'multiple' => 'multiple',
				'desc_tip' => __( 'Select the categories in which you want to allow customers to redeem points.', 'points-and-rewards-for-woocommerce' ),
				'options'  => $this->wps_wpr_dummy_all_pages(),
			),
		);

		$key = (int) $this->wps_wpr_dummy_get_key( $settings );
		if ( $key > 1 ) {
			$arr1 = array_slice( $settings, $key + 1 );
			$arr2 = array_slice( $settings, 0, $key + 1 );
			array_splice( $arr1, 0, 0, $add );
		} else {
			$arr1 = array_slice( $settings, $key + 42 );
			$arr2 = array_slice( $settings, 0, $key + 42 );
			array_splice( $arr1, 0, 0, $add );
		}
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * Function to get the dummy corresponding key of matching value.
	 *
	 * @param  array $settings settings.
	 * @return array
	 */
	public function wps_wpr_dummy_get_key( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {

				if ( array_key_exists( 'title', $val ) ) {
					if ( 'Apply Points on Checkout' == $val['title'] ) {

						return $key;
					}
				}
			}
		}
	}

	/**
	 * This functions is used to get all pages for dummy redirect user using referral link.
	 *
	 * @return pages
	 */
	public function wps_wpr_dummy_all_pages() {
		$wps_pages_ids = array();
		$wps_pages     = get_pages();
		if ( isset( $wps_pages ) && ! empty( $wps_pages ) && is_array( $wps_pages ) ) {
			foreach ( $wps_pages as $pagedata ) {
				if ( 'Checkout' !== $pagedata->post_title ) {

					$wps_pages_ids[] = array(
						'id' => $pagedata->ID,
						'name' => $pagedata->post_title,
					);
				}
			}
		}
		return $wps_pages_ids;
	}

	/**
	 * Customer ranking dummy settings.
	 *
	 * @param array $wps_wpr_general_settings wps_wpr_general_settings.
	 * @return array
	 */
	public function wps_wpr_dummy_customer_rank_listing( $wps_wpr_general_settings ) {

		$my_new_inserted_array = array(
			array(
				'title' => __( 'Enable Customer Rank Settings', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable To Rank the Customer on Basis of Points', 'points-and-rewards-for-woocommerce' ),
				'id'       => 'wps_wpr_general_setting_customer_rank_list',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable customer rank points setting.', 'points-and-rewards-for-woocommerce' ),
				'default'  => 0,
			),
			array(
				'title'             => __( 'Number of Customers to be Listed With ShortCode [CUSTOMERRANK]', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'default'           => 1,
				'id'                => 'wps_wpr_general_no_of_customer_list',
				'custom_attributes' => array( 'min' => '"0"' ),
				'class'             => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'The Number of Customers To Be Listed During Ranking', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
		);
		$wps_wpr_general_settings = $this->wps_dummy_insert_keys_value_pair( $wps_wpr_general_settings, $my_new_inserted_array, 51 );
		return $wps_wpr_general_settings;
	}

	/**
	 * Add General settings in the lite.
	 *
	 * @param array $wps_wpr_general_settings general settings.
	 * @return array
	 */
	public function wps_wpr_dummy_daily_sign_up_points( $wps_wpr_general_settings ) {

		$my_new_inserted_array = array(
			array(
				'title' => __( 'Enable First Daily login Points settings', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable to give points on first daily login', 'points-and-rewards-for-woocommerce' ),
				'id'       => 'wps_wpr_general_setting_daily_enablee',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the setting of the daily points.', 'points-and-rewards-for-woocommerce' ),
				'default'  => 0,
			),
			array(
				'title'             => __( 'Enter First Daily login Points', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'default'           => 1,
				'id'                => 'wps_wpr_general_daily_login_value',
				'custom_attributes' => array( 'min' => '"0"' ),
				'class'             => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'The Points New Customers Will Get When They Login Daily', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
		);
		$wps_wpr_general_settings = $this->wps_dummy_insert_keys_value_pair( $wps_wpr_general_settings, $my_new_inserted_array, 51 );
		return $wps_wpr_general_settings;
	}

	/**
	 * Add General settings in the lite.
	 *
	 * @param array $wps_wpr_general_settings general settings.
	 * @return array
	 */
	public function wps_wpr_dummy_first_order_points( $wps_wpr_general_settings ) {
		$my_new_inserted_array = array(
			array(
				'title' => __( 'Enable First order Points settings', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable to give points on first order purchase', 'points-and-rewards-for-woocommerce' ),
				'id'       => 'wps_wpr_general_setting_enablee',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the first order points setting.', 'points-and-rewards-for-woocommerce' ),
				'default'  => 0,
			),
			array(
				'title'             => __( 'Enter First order purchase Points', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'default'           => 1,
				'id'                => 'wps_wpr_general_first_value',
				'custom_attributes' => array( 'min' => '"0"' ),
				'class'             => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'The Points New Customers Will Get After Their First Order', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
		);
		$wps_wpr_general_settings = $this->wps_dummy_insert_keys_value_pair( $wps_wpr_general_settings, $my_new_inserted_array, 51 );
		return $wps_wpr_general_settings;
	}

	/**
	 * This Function add settings of round off on admin side.
	 *
	 * @param  array $settings settings.
	 * @return array
	 */
	public function wps_wpr_dummy_round_points_settings( $settings ) {

		$new_inserted_array = array(
			array(
				'title' => __( 'Points Round Off', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'            => __( 'Select Points Roundoff', 'points-and-rewards-for-woocommerce' ),
				'id'               => 'wps_wpr_point_round_off',
				'class'            => 'wps_wgm_new_woo_ver_style_select wps_wpr_pro_plugin_settings',
				'type'             => 'singleSelectDropDownWithKeyvalue',
				'desc_tip'         => __( 'Select the discount Type to apply points', 'points-and-rewards-for-woocommerce' ),
				'custom_attribute' => array(
					array(
						'id'   => 'wps_wpr_round_up',
						'name' => 'Round Up',
					),
					array(
						'id'   => 'wps_wpr_round_down',
						'name' => 'Round Down',
					),
				),
			),
			array(
				'type' => 'sectionend',
			),
		);
		$settings = $this->wps_dummy_insert_keys_value_pair( $settings, $new_inserted_array, 200 );
		return $settings;
	}

	/**
	 * Add General settings in the lite.
	 *
	 * @param array $wps_wpr_general_settings general settings.
	 * @return array
	 */
	public function wps_wpr_dummy_birthday_order_points( $wps_wpr_general_settings ) {

		$my_new_inserted_array = array(
			array(
				'title' => __( 'Enable Birthday Points settings', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable to give points on birthday', 'points-and-rewards-for-woocommerce' ),
				'id'       => 'wps_wpr_general_setting_birthday_enablee',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable points on the birthday setting.', 'points-and-rewards-for-woocommerce' ),
				'default'  => 0,
			),
			array(
				'title'             => __( 'Enter Birthday Points to give on bday', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'default'           => 1,
				'id'                => 'wps_wpr_general_birthday_value',
				'custom_attributes' => array( 'min' => '"0"' ),
				'class'             => 'input-text wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'The Points That The Customers Will Get, Only on Their Birthday', 'points-and-rewards-for-woocommerce' ),
			),

			array(
				'type' => 'sectionend',
			),
		);
		$wps_wpr_general_settings = $this->wps_dummy_insert_keys_value_pair( $wps_wpr_general_settings, $my_new_inserted_array, 150 );
		return $wps_wpr_general_settings;
	}

	/**
	 * Undocumented function.
	 *
	 * @param  array $wps_wpr_general_settings wps_wpr_general_settings.
	 * @return array
	 */
	public function wps_wpr_dummy_user_roles( $wps_wpr_general_settings ) {
		$my_new_inserted_array = array(
			array(
				'type' => 'sectionend',
			),
			array(
				'title' => __( 'Allow selected user role to use points feature', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Allow user roles', 'points-and-rewards-for-woocommerce' ),
				'id'       => 'wps_wpr_allowed_selected_user_role',
				'type'     => 'search&select',
				'multiple' => 'multiple',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Allow Selected User Roles to Use Points Features. Leave Empty for All Users', 'points-and-rewards-for-woocommerce' ),
				'options'  => $this->wps_wpr_dummy_allowed_user(),
			),
		);

		$wps_wpr_general_settings = $this->wps_dummy_insert_keys_value_pair( $wps_wpr_general_settings, $my_new_inserted_array, 130 );
		return $wps_wpr_general_settings;
	}

	/**
	 * Mwb_wpr_allowed_user function.
	 *
	 * @return roles
	 */
	public function wps_wpr_dummy_allowed_user() {
		global $wp_roles;
		$all_roles   = $wp_roles->roles;
		$roles_array = array();
		foreach ( $all_roles as $role => $value ) {

			$roles_array[] = array(
				'id'   => $role,
				'name' => $value['name'],
			);

		}
		return $roles_array;
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value            value.
	 * @param array $general_settings general_settings.
	 * @return void
	 */
	public function wps_wpr_additional_dummy_cart_points_settings( $value, $general_settings ) {
		if ( 'singleSelectDropDownWithKeyvalue' == $value['type'] ) {
			$this->wps_wpr_generate_dummy_single_select_drop_down_with_key_value_pair( $value, $general_settings );
		}
		if ( 'search&select' == $value['type'] ) {
			$this->wps_wpr_generate_dummy_search_select_html( $value, $general_settings );
		}
		if ( 'select' == $value['type'] ) {
			$this->wps_wpr__select_dummy_html( $value, $general_settings );
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value          value.
	 * @param array $saved_settings saved_settings.
	 * @return void
	 */
	public function wps_wpr_generate_dummy_single_select_drop_down_with_key_value_pair( $value, $saved_settings ) {
		$selectedvalue = isset( $saved_settings[ $value['id'] ] ) ? ( $saved_settings[ $value['id'] ] ) : array();
		if ( '' == $selectedvalue ) {
			$selectedvalue = '';
		}
		?>
		<select name="<?php echo esc_attr( array_key_exists( 'id', $value ) ? $value['id'] : '' ); ?>" class="<?php echo esc_attr( array_key_exists( 'class', $value ) ? $value['class'] : '' ); ?>">
			<?php
			if ( is_array( $value['custom_attribute'] ) && ! empty( $value['custom_attribute'] ) ) {
				foreach ( $value['custom_attribute'] as $option ) {
					$select = 0;
					if ( $option['id'] == $selectedvalue && ! empty( $selectedvalue ) ) {
						$select = 1;
					}
					?>
					<option value="<?php echo esc_attr( $option['id'] ); ?>" <?php echo selected( 1, $select ); ?> ><?php echo esc_attr( $option['name'] ); ?></option>
					<?php
				}
			}
			?>
		</select>
		<?php
		if ( isset( $value['desc'] ) && ! empty( $value['desc'] ) ) {
			?>
			<p><?php echo esc_html( $value['desc'] ); ?></p>
			<?php
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value            value.
	 * @param array $general_settings general_settings.
	 * @return void
	 */
	public function wps_wpr__select_dummy_html( $value, $general_settings ) {
		$selectedvalue = isset( $general_settings[ $value['id'] ] ) ? ( $general_settings[ $value['id'] ] ) : array();
		if ( '' == $selectedvalue ) {
			$selectedvalue = '';
		}
		?>
		<label for="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>">
			<select name="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>[]" id="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" 
			<?php if ( array_key_exists( 'select', $value ) ) : ?>
			<?php endif; ?>
				class="<?php echo ( array_key_exists( 'class', $value ) ) ? esc_html( $value['class'] ) : ''; ?>"
				<?php
				if ( array_key_exists( 'custom_attribute', $value ) ) {
					foreach ( $value['custom_attribute'] as $attribute_name => $attribute_val ) {
						echo wp_kses_post( $attribute_name . '=' . $attribute_val );
					}
				}
				if ( is_array( $value['options'] ) && ! empty( $value['options'] ) ) {
					foreach ( $value['options'] as $option ) {
						$select = 0;
						if ( is_array( $selectedvalue ) && in_array( $option['id'], $selectedvalue ) && ! empty( $selectedvalue ) ) {
							$select = 1;
						}
						?>
						><option value="<?php echo esc_html( $option['id'] ); ?>" <?php echo selected( 1, $select ); ?> ><?php echo esc_html( $option['name'] ); ?></option>
						<?php
					}
				}
				?>
			</select>
		</label>
		<?php
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_per_currrency_on_subtotal_option( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Per currency points in subtotal', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_per_cerrency_points_on_order_subtotal',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc'     => __( 'Allow per currency points conversion on subtotal.', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Check this box if you want to enable per currency points conversion on subtotal.', 'points-and-rewards-for-woocommerce' ),
			),

		);

		$key  = (int) $this->wps_wpr_get_dummy_key_per_currency_for_subtotal( $settings );
		$arr1 = array_slice( $settings, $key + 1 );
		$arr2 = array_slice( $settings, 0, $key + 1 );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_per_currency_for_subtotal( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {

				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_coupon_conversion_enable' == $val['id'] ) {

						return $key;
					}
				}
			}
		}
	}

	/**
	 * Add Coupon settings in the lite
	 *
	 * @name add_wps_settings
	 * @since    1.0.0
	 * @param array $coupon_settings settings of the array.
	 */
	public function wps_wpr_dummy_add_coupon_settings( $coupon_settings ) {
		$new_inserted_array = array(
			array(
				'title' => __( 'Coupon Settings', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Points Conversion', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_enable_coupon_generation',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc'     => __( 'Enable Points Conversion Fields', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Toggle This to Enable The Coupon Generation Functionality for Customers.', 'points-and-rewards-for-woocommerce' ),
			),

			array(
				'title'       => __( 'Redeem Points Conversion', 'points-and-rewards-for-woocommerce' ),
				'desc_tip'    => __( 'Enter the redeem points for the coupon. (i.e. how many points will be equivalent to the amount)', 'points-and-rewards-for-woocommerce' ),
				'type'        => 'number_text',
				'number_text' => array(
					array(
						'type'              => 'text',
						'id'                => 'wps_wpr_coupon_redeem_price',
						'class'             => 'input-text wps_wpr_new_woo_ver_style_text wc_input_price wps_wpr_pro_plugin_settings',
						'default'           => '1',
						'custom_attributes' => array( 'min' => '"0"' ),
						'desc'              => __( '=', 'points-and-rewards-for-woocommerce' ),
						'curr'              => get_woocommerce_currency_symbol(),
					),
					array(
						'type'              => 'number',
						'id'                => 'wps_wpr_coupon_redeem_points',
						'class'             => 'input-text wc_input_price wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
						'custom_attributes' => array( 'min' => '"0"' ),
						'desc'              => __( 'Points', 'points-and-rewards-for-woocommerce' ),
					),
				),
			),
			array(
				'title'             => __( 'Enter Minimum Points Required For Generating Coupon', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '"0"' ),
				'id'                => 'wps_wpr_general_minimum_value',
				'class'             => 'wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'The minimum points customer requires for converting their points to coupon', 'points-and-rewards-for-woocommerce' ),
				'default'           => 50,
			),
			array(
				'title'    => __( 'Custom Convert Points', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_general_custom_convert_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc'     => __( 'Enable to allow customers to convert some of the points to coupon out of their given total points.', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Toggle This to Allow Customers to Convert Their Custom Points to Coupons Out of Their Total Points.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Individual Use', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_coupon_individual_use',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc'     => __( 'Allow Coupons to use Individually.', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Toggle This if the Coupon Can Not Be Used in Conjunction WIth Another coupon.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Free Shipping', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_points_freeshipping',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc'     => __( 'Allow Coupons on Free Shipping.', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Check this box if the coupon grants free shipping. A free shipping method must be enabled in your shipping zone and be set to require " a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'             => __( 'Coupon Length', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '"0"' ),
				'id'                => 'wps_wpr_points_coupon_length',
				'class'             => 'wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'Enter Coupon length excluding the prefix.(Minimum length is set to 5', 'points-and-rewards-for-woocommerce' ),
				'default'           => 5,
			),
			array(
				'title'             => __( 'Coupon Expiry After Days', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '"0"' ),
				'id'                => 'wps_wpr_coupon_expiry',
				'class'             => 'wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'Enter The Number of Days After the Coupon Will Expire When the Order is Completed. Keep Value Zero for No Expiration.', 'points-and-rewards-for-woocommerce' ),
				'default'           => 0,
			),
			array(
				'title'             => __( 'Minimum Spend', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '"0"' ),
				'id'                => 'wps_wpr_coupon_minspend',
				'class'             => 'wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'This field allows you to set the minimum spend (subtotal, including taxes) allowed to use the coupon. Keep value "0" for no limit.', 'points-and-rewards-for-woocommerce' ),
				'default'           => 0,
			),
			array(
				'title'             => __( 'Maximum Spend', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '"0"' ),
				'id'                => 'wps_wpr_coupon_maxspend',
				'class'             => 'wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'This field allows you to set the maximum spend (subtotal, including taxes) allowed when using the Coupon. Keep value "0" for no limit.', 'points-and-rewards-for-woocommerce' ),
				'default'           => 0,
			),
			array(
				'title'             => __( 'Coupon No of time uses', 'points-and-rewards-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '"0"' ),
				'id'                => 'wps_wpr_coupon_use',
				'class'             => 'wps_wpr_pro_plugin_settings',
				'desc_tip'          => __( 'How many times this coupon can be used before the Coupon is void. Keep value "0" for no limit.', 'points-and-rewards-for-woocommerce' ),
				'default'           => 0,
			),
			array(
				'type' => 'sectionend',
			),
		);

		$coupon_settings = $this->wps_dummy_insert_keys_value_pair( $coupon_settings, $new_inserted_array, 4 );
		return $coupon_settings;
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_custom_notification_callback( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_email_subject_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the custom points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Custom Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_custom_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $settings settings.
	 * @return string
	 */
	public function wps_wpr_get_dummy_key_custom_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {

				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_email_subject' == $val['id'] ) {

						return $key;
					}
				}
			}
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param  array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_signup_notification_callback( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_signup_email_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the signup points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Signup Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_signup_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return string
	 */
	public function wps_wpr_get_dummy_key_signup_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {

				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_signup_email_subject' == $val['id'] ) {

						return $key;
					}
				}
			}
		}
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_product_purchase_notification_callback( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_product_email_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the product purchase points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Product Purchase Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_product_purchase_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_product_purchase_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_product_email_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param  array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_order_amount_notification_callback( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_amount_email_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the order amount points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Order Amount Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_order_amount_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_order_amount_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_amount_email_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param  array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_emails_notification_settings( $settings ) {
		$new_inserted_array = array(
			array(
				'title' => __( 'Comment Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_comment_email_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the comment points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Enable Comment Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wps_wpr_comment_email_subject',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Comment Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'textarea_email',
				'id'       => 'wps_wpr_comment_email_discription_custom_id',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'You have received', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' points and your total points are', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( '.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode in place of comment points ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' shortcode in place of Referral points. Use ', 'points-and-rewards-for-woocommerce' ) . '[Per Currency Spent Points]' . __( ' shortcode in place of per currency spent points and ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
			array(
				'title' => __( 'Referral Purchase Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_referral_purchase_email_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the referral purchase points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Enable Referral Purchase Notification', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wps_wpr_referral_purchase_email_subject',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Referral Purchase Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'textarea_email',
				'id'       => 'wps_wpr_referral_purchase_email_discription_custom_id',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'You have received ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' points and your total points are ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]',
				'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode in place of Referral Purchase Points ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' in place of Referral points', 'points-and-rewards-for-woocommerce' ) . ' [Per Currency Spent Points]' . __( ' in place of Per Currency spent points and ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),

			),
			array(
				'type' => 'sectionend',
			),
			array(
				'title' => __( "Deduct 'Per Currency Spent' Point Notification", 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_deduct_per_currency_point_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the Deduct Per Currency Spent points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Enable Deduct Per Currency Spent Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wps_wpr_deduct_per_currency_point_subject',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Your Points have been Deducted', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'textarea_email',
				'id'       => 'wps_wpr_deduct_per_currency_point_description',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Your [DEDUCTEDPOINT] has been deducted from your total points as you have request for your refund, and your Total Point are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[DEDUCTEDPOINT]' . __( ' shortcode in place of points which have been deducted. Use  ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username and use ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Remaining Points.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
			array(
				'title' => __( 'Point Sharing Notification', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_point_sharing_point_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the points sharing notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Enable Points Sharing Notification', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wps_wpr_point_sharing_subject',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Received Points Successfully!!', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'textarea_email',
				'id'       => 'wps_wpr_point_sharing_description',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'You have received', 'points-and-rewards-for-woocommerce' ) . '[RECEIVEDPOINT]' . __( 'by your one of the friends having an Email Id is' ) . '[SENDEREMAIL]' . __( 'and your total points are', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( '.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[RECEIVEDPOINT]' . __( ' shortcode in place of points which have been received. Use ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of a username, ', 'points-and-rewards-for-woocommerce' ) . '[SENDEREMAIL]' . __( ' shortcode in place of the email id of the Sender, and ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
			array(
				'title' => __( 'Purchase Products through Points Notification', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_pro_pur_by_points_email_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the purchase of products through points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Purchase Products through Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wps_wpr_pro_pur_by_points_email_subject',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Product Purchased Through Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'textarea_email',
				'id'       => 'wps_wpr_pro_pur_by_points_discription_custom_id',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Product Purchased Point', 'points-and-rewards-for-woocommerce' ) . '[PROPURPOINTS]' . __( ' has been deducted from your points on purchasing, and your Total Points are ' ) . '[Total Points]' . __( '.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[PROPURPOINTS]' . __( ' shortcode in place of purchasing points, ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of a username, and ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
			array(
				'title' => __( "Return 'Product Purchase through Point' Notification", 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_return_pro_pur_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Toggle This to Enable the Notification for Return Product Purchase Through Points.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Return Product Purchase through Point', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wps_wpr_return_pro_pur_subject',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Your Points have been Deducted', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'textarea_email',
				'id'       => 'wps_wpr_return_pro_pur_description',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Your [RETURNPOINT] has been returned to your point account as you have request for your refund and your Total Point is [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[RETURNPOINT]' . __( ' shortcode in place of points which have been returned, ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username, and ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Remaining Points.', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$settings = $this->wps_dummy_insert_keys_value_pair( $settings, $new_inserted_array, 19 );
		return $settings;
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_referral_notification_callback( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_referral_email_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the referral points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Referral Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_order_referral_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_order_referral_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_referral_email_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_upgrade_membership_notification_callback( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_membership_email_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the referral points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Upgrade Membership Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_upgrade_membership_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function will add specific enable/disable for upgrade membership notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_upgrade_membership_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_membership_email_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * This function will add specific enable/disable for deduct assign points notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_deduct_assign_notification_callback( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_deduct_assigned_point_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Check this box to enable the deduct assign points notification.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Deduct Assign Points Notification', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_deduct_assign_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function will add specific enable/disable for deduct assign points notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_deduct_assign_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_deduct_assigned_point_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param  array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_coupon_notification( $settings ) {
		$new_inserted_array = array(
			array(
				'title' => __( 'Mail Notification for Coupon referral', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_point_on_coupon_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Toggle This to Enable Notification for First Order Coupon Referral Completion.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Points when referee gets points', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wps_wpr_point_on_coupon_referal_subject',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Points added', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'textarea_email',
				'id'       => 'wps_wpr_point_on_copon_referal_order_desc',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Your Coupon Code [COUPONCODE] is applied and you will get [POINTS] points', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Use', 'points-and-rewards-for-woocommerce' ) . '[COUPONCODE]' . __( ' shortcode in place of coupon code and ', 'points-and-rewards-for-woocommerce' ) . '[POINTS]' . __( ' shortcode in place of points ', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
		);

		$settings = $this->wps_dummy_insert_keys_value_pair( $settings, $new_inserted_array, 76 );
		return $settings;
	}

	/**
	 * This Function is used for bdy notification
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_bithday_notification_callback( $settings ) {
		$my_new_inserted_array = array(

			array(
				'title' => __( 'Points Only on Bday Notification', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_point_on_birthday_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Toggle This to Enable Notification on Birthday Points.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Points Only on Birthday', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wps_wpr_point_on_bday_subject',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Points Added', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'textarea_email',
				'id'       => 'wps_wpr_point_on_bday_order_desc',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Your [BIRTHDAYPOINT] Points have been added. Now your Total Points are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Use', 'points-and-rewards-for-woocommerce' ) . '[BIRTHDAYPOINT]' . __( ' shortcode in place of points which have been added, ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of a username, and ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
		);

		$settings = $this->wps_dummy_insert_keys_value_pair( $settings, $my_new_inserted_array, 66 );
		return $settings;
	}

	/**
	 * This function will add specific enable/disable for deduct apply points on cart notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_points_on_cart_notification_callback( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_point_on_cart_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Toggle This to Enable the Notification on Cart Subtotal Points.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Points On Cart Sub-Total Notification', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_points_on_cart_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function will add specific enable/disable for deduct apply points on cart notificatio
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_points_on_cart_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_point_on_cart_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * This function will add specific enable/disable for order total range notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dumm_email_notification_settings_first_order_notification_callback( $settings ) {
		$my_new_inserted_array = array(

			array(
				'title' => __( 'Points Only on First order', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_point_on_first_order_point_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Toggle This to Enable Notification On First Order Points.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Points Only on First order', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wps_wpr_point_on_first_order_subject',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Points Added', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'textarea_email',
				'id'       => 'wps_wpr_point_on_first_order_desc',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
				'default'  => __( 'Your [FIRSTORDERPOINT] Points have been added. Now your Total Points are [Total Points].', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[FIRSTORDERPOINT]' . __( ' shortcode in place of points which have been added, ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of a username, and ', 'points-and-rewards-for-woocommerce' ) . '[FIRSTORDERPOINT]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
		);

		$settings = $this->wps_dummy_insert_keys_value_pair( $settings, $my_new_inserted_array, 72 );
		return $settings;
	}

	/**
	 * This function will add specific enable/disable for order total range notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_notification_settings_order_total_range_notification_callback( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_point_on_order_total_range_setting_enable',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Toggle This to Enable the Notification on Order Total Range Points.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Points On Order Total Range Notification', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_order_total_range_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function will add specific enable/disable for order total range notification.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_order_total_range_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_point_on_order_total_range_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * This function is used to create order rewards points enable html settings.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_number_dummy_of_order_rewards_points_notifications( $settings ) {
		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_enable_order_rewards_points_notifications',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Toggle This to Enable The Notification on Order Rewards Points.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Order Rewards Points', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_order_rewards_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function is used to show checkbox html on order rewards settings.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_order_rewards_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_order_rewards_points_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * This function is used to create enable setting for game features.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_gamification_dummy_notification_enable_settings( $settings ) {

		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_enable_game_points_notift_settings',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Toggle This to Enable The Notification on Game Points.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Gamification Points', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_game_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function is used to show checkbox html on order rewards settings.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_game_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_game_points_mail_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * This function is used to create enable setting for user badge enable features.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_add_dummy_email_user_badge_enable_notification( $settings ) {

		$add = array(
			array(
				'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_enable_user_badge_notift_settings',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Toggle This to Enable The Notification on Game Points.', 'points-and-rewards-for-woocommerce' ),
				'desc'     => __( 'Badge Points', 'points-and-rewards-for-woocommerce' ),
			),
		);

		$key  = (int) $this->wps_wpr_get_dummy_key_badge_points_notification( $settings );
		$arr1 = array_slice( $settings, $key );
		$arr2 = array_slice( $settings, 0, $key );
		array_splice( $arr1, 0, 0, $add );
		return array_merge( $arr2, $arr1 );
	}

	/**
	 * This function is used to show checkbox html on user badges settings.
	 *
	 * @param array $settings settings.
	 * @return array
	 */
	public function wps_wpr_get_dummy_key_badge_points_notification( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $val ) {
				if ( array_key_exists( 'id', $val ) ) {
					if ( 'wps_wpr_badges_points_mail_subject' == $val['id'] ) {
						return $key;
					}
				}
			}
		}
	}

	/**
	 * This function is use to create show shop page assign points html.
	 *
	 * @param array $wps_wpr_assign_product_table_settings wps_wpr_assign_product_table_settings.
	 * @return array
	 */
	public function wps_wpr_display_dummy_assign_points_on_shop_page( $wps_wpr_assign_product_table_settings ) {

		$wps_wpr_shop_points_html = array(
			array(
				'title'    => __( 'Display Assign Points On Shop Page', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'desc'     => esc_html__( 'Enable this setting to show assign points on Shop Page', 'points-and-rewards-for-woocommerce' ),
				'id'       => 'wps_wpr_show_assign_points_on_shop_page',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Enable this setting to show assigned points on the Shop Page', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
		);
		return array_merge( $wps_wpr_assign_product_table_settings, $wps_wpr_shop_points_html );
	}

	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public function wps_wpr_add_dummy_new_catories_wise_settings() {
		?>
		<p class="wps_wpr_notice"><?php esc_html_e( 'This is a category-wise setting to assign points to a product of categories. Enter some valid points for assigning, and leave blank fields to remove assigned points.', 'points-and-rewards-for-woocommerce' ); ?></p>
		<table class="form-table wps_wpr_pro_points_setting mwp_wpr_settings wps_wpr_pro_plugin_settings">
			<tbody>
				<tr>
					<th class="titledesc"><?php esc_html_e( 'Categories', 'points-and-rewards-for-woocommerce' ); ?></th>
					<th class="titledesc"><?php esc_html_e( 'Enter Points', 'points-and-rewards-for-woocommerce' ); ?></th>
					<th class="titledesc"><?php esc_html_e( 'Assign/Remove', 'points-and-rewards-for-woocommerce' ); ?></th>
				</tr>
				<?php
				$args       = array( 'taxonomy' => 'product_cat' );
				$categories = get_terms( $args );
				if ( isset( $categories ) && ! empty( $categories ) ) {
					foreach ( $categories as $category ) {

						$catid               = $category->term_id;
						$catname             = $category->name;
						$wps_wpr_categ_point = get_option( 'wps_wpr_points_to_per_categ_' . $catid, '' );
						?>
						<tr>
							<td><?php echo esc_html( $catname ); ?></td>
							<td><input type="number" min="1" name="wps_wpr_points_to_per_categ" id="wps_wpr_points_to_per_categ_<?php echo esc_html( $catid ); ?>" value="<?php echo esc_html( $wps_wpr_categ_point ); ?>" class="input-text wps_wpr_new_woo_ver_style_text"></td>
							<td><input type="button" value='<?php esc_html_e( 'Submit', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_submit_per_category wps_wpr_disabled_pro_plugin" name="wps_wpr_submit_per_category" id="<?php echo esc_html( $catid ); ?>"></td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Undocumented function.
	 *
	 * @param  array $tabs tabs.
	 * @return array
	 */
	public function wps_add_dummy_purchase_through_points_settings_tab( $tabs ) {
		$new_tab = array(
			'product-purchase-points' => array(
				'title'     => __( 'Product Purchase Points', 'points-and-rewards-for-woocommerce' ),
				'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/dummyfile/wps-pro-dummy-purchase-points.php',
			),
			'points-expiration'       => array(
				'title'     => __( 'Points Expiration', 'points-and-rewards-for-woocommerce' ),
				'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/dummyfile/wps-dummy-point-expiration.php',
			),
		);

		$tabs = $this->wps_dummy_insert_keys_value_pair( $tabs, $new_tab, 7 );
		return $tabs;
	}

	/**
	 * Undocumented function.
	 *
	 * @return array
	 */
	public function wps_wpr_get_dummy_category() {
		$args       = array( 'taxonomy' => 'product_cat' );
		$categories = get_terms( $args );
		if ( isset( $categories ) && ! empty( $categories ) ) {
			$category = array();
			foreach ( $categories as $cat ) {
				$category[] = array(
					'id'   => $cat->term_id,
					'name' => $cat->name,
				);
			}
			return $category;
		}
	}

	/**
	 * This function is for generating for the heading for the Settings
	 *
	 * @name wps_rwpr_generate_heading
	 * @param array $value value array for the heading.
	 * @since 1.0.0
	 */
	public function wps_rwpr_generate_dummy_heading( $value ) {
		if ( array_key_exists( 'title', $value ) ) {
			?>
			<div class="wps_wpr_general_sign_title">
				<?php echo esc_html( $value['title'] ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value value.
	 * @return void
	 */
	public function wps_rwpr_generate_dummy_label( $value ) {
		?>
		<div class="wps_wpr_general_label">
			<label for="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" class='m1'><?php echo ( array_key_exists( 'title', $value ) ) ? esc_html( $value['title'] ) : ''; ?></label>
			<?php if ( array_key_exists( 'pro', $value ) ) { ?>
			<span class="wps_wpr_general_pro">Pro</span>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value value.
	 * @return void
	 */
	public function wps_rwpr_generate_dummy_tool_tip( $value ) {
		if ( array_key_exists( 'desc_tip', $value ) ) {
			$allowed_tags = $this->wps_wpr_allowed_dummy_html();
			echo wp_kses( wc_help_tip( $value['desc_tip'] ), $allowed_tags );
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value            value.
	 * @param array $general_settings general_settings.
	 * @return void
	 */
	public function wps_rwpr_generate_dummy_checkbox_html( $value, $general_settings ) {
		$enable_wps_wpr = isset( $general_settings[ $value['id'] ] ) ? intval( $general_settings[ $value['id'] ] ) : 0;
		?>
		<label for="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>">
			<input type="checkbox" name="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" <?php checked( $enable_wps_wpr, 1 ); ?> id="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" class="<?php echo ( array_key_exists( 'class', $value ) ) ? esc_html( $value['class'] ) : ''; ?>"> <?php echo ( array_key_exists( 'desc', $value ) ) ? esc_html( $value['desc'] ) : ''; ?>
		</label>
		<?php
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value            value.
	 * @param array $general_settings general_settings.
	 * @return void
	 */
	public function wps_rwpr_generate_dummy_number_html( $value, $general_settings ) {

		$default_val      = array_key_exists( 'default', $value ) ? $value['default'] : 1;
		$wps_signup_value = isset( $general_settings[ $value['id'] ] ) ? intval( $general_settings[ $value['id'] ] ) : $default_val;
		?>
		<label for="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>">
			<input type="number" 
			<?php
			if ( array_key_exists( 'custom_attributes', $value ) ) {
				foreach ( $value['custom_attributes'] as $attribute_name => $attribute_val ) {

					echo esc_html( $attribute_name );
					$allowed_tags = $this->wps_wpr_allowed_dummy_html();
					echo wp_kses( "=$attribute_val", $allowed_tags );

				}
			}
			?>
			value="<?php echo esc_html( $wps_signup_value ); ?>" name="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" id="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>"
			class="<?php echo ( array_key_exists( 'class', $value ) ) ? esc_html( $value['class'] ) : ''; ?>"><?php echo ( array_key_exists( 'desc', $value ) ) ? esc_html( $value['desc'] ) : ''; ?>
		</label>
		<?php
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value            value.
	 * @param array $general_settings general_settings.
	 * @return void
	 */
	public function wps_rwpr_generate_dummy_text_html( $value, $general_settings ) {
		$wps_signup_value = isset( $general_settings[ $value['id'] ] ) ? ( $general_settings[ $value['id'] ] ) : '';
		if ( empty( $wps_signup_value ) ) {
			$wps_signup_value = array_key_exists( 'default', $value ) ? $value['default'] : '';
		}
		?>
		<label for="
			<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>">
			<input type="text" 
			<?php
			if ( array_key_exists( 'custom_attributes', $value ) ) {
				foreach ( $value['custom_attributes'] as $attribute_name => $attribute_val ) {
					echo esc_html( $attribute_name );
					$allowed_tags = $this->wps_wpr_allowed_dummy_html();
					echo wp_kses( "=$attribute_val", $allowed_tags );
				}
			}
			?>
				style ="<?php echo ( array_key_exists( 'style', $value ) ) ? esc_html( $value['style'] ) : ''; ?>"
				value="<?php echo esc_html( $wps_signup_value ); ?>" name="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" id="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>"
				class="<?php echo ( array_key_exists( 'class', $value ) ) ? esc_html( $value['class'] ) : ''; ?>"><?php echo ( array_key_exists( 'desc', $value ) ) ? esc_html( $value['desc'] ) : ''; ?>
		</label>
		<?php
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value            value.
	 * @param array $general_settings general_settings.
	 * @return void
	 */
	public function wps_rwpr_generate_dummy_textarea_html( $value, $general_settings ) {
		$wps_get_textarea_id = isset( $value['id'] ) ? $value['id'] : '';
		$wps_show_text_area  = false;
		if ( isset( $wps_get_textarea_id ) && '' !== $wps_get_textarea_id ) {
			$wps_show_text_area = apply_filters( 'wps_wpr_remove_text_area_in_pro', $wps_show_text_area, $value, $general_settings );
		}
		if ( false == $wps_show_text_area ) {
			$wps_signup_value = isset( $general_settings[ $value['id'] ] ) ? ( $general_settings[ $value['id'] ] ) : '';
			if ( empty( $wps_signup_value ) ) {
				$wps_signup_value = array_key_exists( 'default', $value ) ? esc_html( $value['default'] ) : '';
			}
			?>
			<span class="description"><?php echo array_key_exists( 'desc', $value ) ? esc_html( $value['desc'] ) : ''; ?></span>	
			<label for="wps_wpr_general_text_points" class="wps_wpr_label">
				<textarea 
					<?php
					if ( array_key_exists( 'custom_attributes', $value ) ) {
						foreach ( $value['custom_attributes'] as $attribute_name => $attribute_val ) {
							echo esc_html( $attribute_name );
							$allowed_tags = $this->wps_wpr_allowed_dummy_html();
							echo wp_kses( "=$attribute_val", $allowed_tags );
						}
					}
					?>
					name="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" id="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>"
					class="<?php echo ( array_key_exists( 'class', $value ) ) ? esc_html( $value['class'] ) : ''; ?>"><?php echo wp_kses( ( $wps_signup_value ), $this->wps_wpr_allowed_dummy_html() ); ?></textarea>
			</label>
			<p class="description"><?php echo esc_html( $value['desc2'] ); ?></p>
			<?php
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $value            value.
	 * @param array $general_settings general_settings.
	 * @return void
	 */
	public function wps_wpr_generate_dummy_search_select_html( $value, $general_settings ) {
		$selectedvalue = isset( $general_settings[ $value['id'] ] ) ? ( $general_settings[ $value['id'] ] ) : array();
		if ( '' == $selectedvalue ) {
			$selectedvalue = '';
		}
		?>
		<label for="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" >
			<select name="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>[]" id="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" 
			<?php if ( array_key_exists( 'multiple', $value ) ) : ?>
			multiple = "<?php echo ( array_key_exists( 'multiple', $value ) ) ? esc_html( $value['multiple'] ) : false; ?>"
			<?php endif; ?>
				class="<?php echo ( array_key_exists( 'class', $value ) ) ? esc_html( $value['class'] ) : ''; ?>"
				<?php
				if ( array_key_exists( 'custom_attribute', $value ) ) {
					foreach ( $value['custom_attribute'] as $attribute_name => $attribute_val ) {
						echo wp_kses_post( $attribute_name . '=' . $attribute_val );
					}
				}
				if ( is_array( $value['options'] ) && ! empty( $value['options'] ) ) {
					foreach ( $value['options'] as $option ) {
						$select = 0;
						if ( is_array( $selectedvalue ) && in_array( $option['id'], $selectedvalue ) && ! empty( $selectedvalue ) ) {
							$select = 1;
						}
						?>
						><option value="<?php echo esc_html( $option['id'] ); ?>" <?php echo selected( 1, $select ); ?> ><?php echo esc_html( $option['name'] ); ?></option>
						<?php
					}
				}
				?>
			</select>
		</label>
		<?php
	}

	/**
	 * Undocumented function.
	 *
	 * @return array
	 */
	public function wps_wpr_allowed_dummy_html() {
		$allowed_tags = array(
			'span' => array(
				'class'    => array(),
				'title'    => array(),
				'style'    => array(),
				'data-tip' => array(),
			),
			'min'   => array(),
			'max'   => array(),
			'class' => array(),
			'style' => array(),
			'<br>'  => array(),
		);
		return $allowed_tags;
	}

	/**
	 * Undocumented function.
	 *
	 * @return array
	 */
	public function wps_wpr_get_dummy_option_of_points() {
		$wps_wpr_points_expiration = array(
			'days'   => __( 'Days', 'points-and-rewards-for-woocommerce' ),
			'weeks'  => __( 'Weeks', 'points-and-rewards-for-woocommerce' ),
			'months' => __( 'Months', 'points-and-rewards-for-woocommerce' ),
			'years'  => __( 'Years', 'points-and-rewards-for-woocommerce' ),
		);
		$wps_wpr_option            = array();
		foreach ( $wps_wpr_points_expiration as $key => $value ) {
			$wps_wpr_option[] = array(
				'id' => $key,
				'name' => $value,
			);
		}
		return $wps_wpr_option;
	}

	/**
	 * This function is used for showing shortcode description.
	 *
	 * @param array $shortcode_array shortcode array.
	 * @return array
	 */
	public function wps_wpr_show_dummy_referral_link_shortcoe( $shortcode_array ) {
		$shortcode_array['desc5'] = __( 'In addition, our pro plugin will offer a customizable referral link shortcode for enhanced user engagement.', 'points-and-rewards-for-woocommerce' );
		return $shortcode_array;
	}

	/**
	 * Undocumented function.
	 *
	 * @param  array $settings settings.
	 * @return array
	 */
	public function wps_wpr_other_dummy_settings( $settings ) {

		$wps_pro_settings = array(
			array(
				'title' => __( 'Thankyou Page Settings', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'id'                => 'wps_wpr_thnku_order_msg',
				'type'              => 'textarea',
				'title'             => __( 'Thank You Order Message When Your Customers Gain Some Points', 'points-and-rewards-for-woocommerce' ),
				'desc_tip'          => __( 'Entered Message will appears at thankyou page when any ordered item is having some of the points', 'points-and-rewards-for-woocommerce' ),
				'class'             => 'input-text wps_wpr_pro_plugin_settings',
				'desc2'             => __( 'Use these shortcodes to provide an appropriate message for your customers. Use ', 'points-and-rewards-for-woocommerce' ) . '[POINTS]' . __( ' shortcode for product points and ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINT]' . __( ' for their Total Points ', 'points-and-rewards-for-woocommerce' ),
				'custom_attributes' => array(
					'cols' => '"35"',
					'rows' => '"5"',
				),
			),
			array(
				'id'                => 'wps_wpr_thnku_order_msg_usin_points',
				'type'              => 'textarea',
				'title'             => __( 'Thank You Order Message When Your Customers Spent Some of Their Points', 'points-and-rewards-for-woocommerce' ),
				'desc_tip'          => __( 'Entered Message will appears at thankyou page when any item has been purchased through points', 'points-and-rewards-for-woocommerce' ),
				'class'             => 'input-text wps_wpr_pro_plugin_settings',
				'desc2'             => __( 'Use these shortcodes to provide an appropriate message for your customers. Use ', 'points-and-rewards-for-woocommerce' ) . '[POINTS]' . __( ' shortcode for product points and ', 'points-and-rewards-for-woocommerce' ) . ' [TOTALPOINT]' . __( ' for their Total Points ', 'points-and-rewards-for-woocommerce' ),
				'custom_attributes' => array(
					'cols' => '"35"',
					'rows' => '"5"',
				),
				'default'           => '',
			),
			array(
				'type' => 'sectionend',
			),
			array(
				'title' => __( 'Points Sharing', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'id'       => 'wps_wpr_user_can_send_point',
				'type'     => 'checkbox',
				'title'    => __( 'Point Sharing', 'points-and-rewards-for-woocommerce' ),
				'desc_tip' => __( 'Toggle This to Enable Your Customers to Share Some of Their Points to Other Users', 'points-and-rewards-for-woocommerce' ),
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc'     => __( 'Enable Point Sharing', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
		);

		$settings = $this->wps_dummy_insert_keys_value_pair( $settings, $wps_pro_settings, 5 );
		return $settings;
	}

	/**
	 * This function will add the api panel.
	 *
	 * @param array $tabs tabs.
	 * @return array
	 */
	public function wps_add_points_dummy_notification_addon_settings_tab( $tabs ) {
		$new_tab = array(
			'notification_addon' => array(
				'title'     => __( 'Notification Addon', 'points-and-rewards-for-woocommerce' ),
				'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/dummyfile/wps-wpr-points-and-rewards-dummy-addon.php',
			),
		);

		$tabs = $this->wps_dummy_insert_keys_value_pair( $tabs, $new_tab, 11 );
		return $tabs;
	}

	/**
	 * Mwb_wpr_get_pages function
	 *
	 * @return pages
	 */
	public function wps_wpr_get_dummy_pages() {

		$wps_page_title = array();
		$wps_pages      = get_pages();
		if ( isset( $wps_pages ) && ! empty( $wps_pages ) && is_array( $wps_pages ) ) {
			foreach ( $wps_pages as $pagedata ) {
				$wps_page_title[] = array(

					'id'   => $pagedata->ID,
					'name' => $pagedata->post_title,
				);
			}
		}
		$wps_page_title[] = array(
			'id'   => 'details',
			'name' => 'Product Detail',
		);
		return $wps_page_title;
	}

	/**
	 * This function is for generating for the color
	 *
	 * @name wps_rwpr_generate_color_box
	 * @param array $value value array for the color box.
	 * @param array $general_settings  whole settings array.
	 * @since 1.0.0
	 */
	public function wps_rwpr_generate_dummy_color_box( $value, $general_settings ) {
		$wps_color_value = isset( $general_settings[ $value['id'] ] ) ? ( $general_settings[ $value['id'] ] ) : '';
		if ( empty( $wps_color_value ) ) {
			$wps_color_value = array_key_exists( 'default', $value ) ? esc_html( $value['default'] ) : '';
		}
		?>
			<label for="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?> ">
				<input 
				<?php
				if ( array_key_exists( 'custom_attributes', $value ) ) {
					foreach ( $value['custom_attributes'] as $attribute_name => $attribute_val ) {
						echo esc_html( $attribute_name );
						$allowed_tags = $this->wps_wpr_allowed_dummy_html();
						echo wp_kses( "=$attribute_val", $allowed_tags );
					}
				}
				?>
				style ="<?php echo ( array_key_exists( 'style', $value ) ) ? esc_html( $value['style'] ) : ''; ?>"
				name="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>" 
				id="<?php echo ( array_key_exists( 'id', $value ) ) ? esc_html( $value['id'] ) : ''; ?>"
				type="color" 
				value="<?php echo esc_html( $wps_color_value ); ?>">
			</label>
		<?php
	}

	/**
	 * This function will add the api panel.
	 *
	 * @param array $tabs tabs.
	 * @return array
	 */
	public function wps_add_api_dummy_settings_tab( $tabs ) {
		$new_tab = array(
			'api_settings' => array(
				'title'     => __( 'API Settings', 'points-and-rewards-for-woocommerce' ),
				'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/dummyfile/wps-wpr-api-dummy-features.php',
			),
		);

		$tabs = $this->wps_dummy_insert_keys_value_pair( $tabs, $new_tab, 12 );
		return $tabs;
	}

	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public function wps_wpr_enqueue_dummy_file() {

		$screen = get_current_screen();
		if ( ! empty( $screen ) && ! empty( $screen->id ) ) {
			if ( wp_verify_nonce( ! empty( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '', 'par_main_setting' ) ) {
				if ( ! empty( $_GET['page'] ) && 'wps-rwpr-setting' == $_GET['page'] ) {

					wp_register_style( 'wps_wpr_dummy_css_file', WPS_RWPR_DIR_URL . 'admin/partials/dummyfile/dummycss/wps-points-and-rewards-dummy.css', array(), '2.5.1' );
					wp_enqueue_style( 'wps_wpr_dummy_css_file' );
					wp_register_script( 'wps_wpr_dummy_js_file', WPS_RWPR_DIR_URL . 'admin/partials/dummyfile/dummyjs/wps-points-and-rewards-dummy.js', array(), '2.5.1', true );
					wp_enqueue_script( 'wps_wpr_dummy_js_file' );
					wp_localize_script(
						'wps_wpr_dummy_js_file',
						'wps_dummy_obj',
						array(
							'api_tabs'       => esc_html__( 'API Settings', 'points-and-rewards-for-woocommerce' ),
							'pur_points_tab' => esc_html__( 'Product Purchase Points', 'points-and-rewards-for-woocommerce' ),
							'expire_tab'     => esc_html__( 'Points Expiration', 'points-and-rewards-for-woocommerce' ),
							'addon_tabs'     => esc_html__( 'Notification Addon', 'points-and-rewards-for-woocommerce' ),
						),
					);

					$this->wps_wpr_dummy_pro_popup();
				}
			}
		}
	}

	/**
	 * This function is used to show pop-up for go pro message.
	 *
	 * @return void
	 */
	public function wps_wpr_dummy_pro_popup() {
		if ( wp_verify_nonce( ! empty( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '', 'par_main_setting' ) ) {
			if ( ! empty( $_GET['page'] ) && 'wps-rwpr-setting' == $_GET['page'] ) {

				?>
				<div class="wps-wpr__popup-dummy-for-pro" style="display: none;">
					<div class="dummy_popup-shadow"></div>
					<div class="dummy_popup-content">
						<span class="dummy_popup-close dashicons dashicons-no-alt"></span>
						<img src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/images/go-pro.png' ); ?>" alt="Go Pro Image" width="100" height="auto">
						<h3><?php esc_html_e( 'To access more functionalities, try out our PRO plugin.', 'points-and-rewards-for-woocommerce' ); ?></h3>
						<p><?php esc_html_e( 'Enjoy Referral Purchase Points, easy coupon generation, Multi-level Membership, and special birthday rewards. Elevate your experience!.', 'points-and-rewards-for-woocommerce' ); ?></p>
						<a href="https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro/" target="_blank"><?php esc_html_e( 'Go PRO Now!', 'points-and-rewards-for-woocommerce' ); ?></a>
					</div>
				</div>
				<?php
			}
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param  array $wps_wpr_other_settings wps_wpr_other_settings.
	 * @return array
	 */
	public function wps_wpr_total_earning_dummy_points_settings( $wps_wpr_other_settings ) {

		$other_settings = array(
			array(
				'title' => __( 'Display Total Earning Points', 'points-and-rewards-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Toggle to show the total earning points on the Cart page', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_cart_page_total_earning_points',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Inform the user about the number of points they will earn by placing this order.', 'points-and-rewards-for-woocommerce' ),
				'default'  => 0,
				'desc'     => __( 'Toggle this setting if you want to display the total earning points of an order on the cart page', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'title'    => __( 'Toggle to show the total earning points on the Checkout page', 'points-and-rewards-for-woocommerce' ),
				'type'     => 'checkbox',
				'id'       => 'wps_wpr_checkout_page_total_earning_points',
				'class'    => 'input-text wps_wpr_pro_plugin_settings',
				'desc_tip' => __( 'Inform the user about the number of points they will earn by placing this order.', 'points-and-rewards-for-woocommerce' ),
				'default'  => 0,
				'desc'     => __( 'Toggle this setting if you want to display the total earning points of an order on the checkout page', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type' => 'sectionend',
			),
		);
		$wps_wpr_other_settings = $this->wps_dummy_insert_keys_value_pair( $wps_wpr_other_settings, $other_settings, 24 );
		return $wps_wpr_other_settings;
	}

}
