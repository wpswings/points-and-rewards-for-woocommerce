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
class Points_Rewards_For_WooCommerce_Admin {

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
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @param  string $hook       The name of page.
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		$screen = get_current_screen();
		if ( isset( $screen->id ) ) {
			$pagescreen = $screen->id;
		}

		$style_url        = WPS_RWPR_DIR_URL . 'build/style-index.css';
		wp_enqueue_style(
			'wps-admin-react-styles',
			$style_url,
			array(),
			time(),
			false
		);

		if ( 'woocommerce_page_wps-rwpr-setting' == $hook || 'woocommerce_page_wps-rwpr-setting' === $pagescreen ) {
			wp_enqueue_style( $this->plugin_name, WPS_RWPR_DIR_URL . 'admin/css/points-rewards-for-woocommerce-admin.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'select2' );
		}
		wp_enqueue_style( 'wps_admin_overview', WPS_RWPR_DIR_URL . 'admin/css/points-rewards-for-woocommerce-admin-overview.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param  string $hook       The name of page.
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		$screen = get_current_screen();
		if ( isset( $screen->id ) ) {

			if ( wp_verify_nonce( ! empty( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '', 'par_main_setting' ) ) {
				if ( isset( $_GET['page'] ) && 'wps-rwpr-setting' == $_GET['page'] || 'product' == $screen->id ) {

					wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
					wp_enqueue_style( 'woocommerce_admin_menu_styles' );
					wp_enqueue_style( 'woocommerce_admin_styles' );
					wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION, true );
					wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.js', array( 'jquery' ), WC_VERSION, true );
					wp_enqueue_script( 'sticky_js', WPS_RWPR_DIR_URL . '/admin/js/jquery.sticky-sidebar.min.js', array( 'jquery' ), WC_VERSION, true );
					wp_enqueue_media();

					$locale  = localeconv();
					$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
					$params  = array(
						/* translators: %s: decimal */
						'i18n_decimal_error'               => sprintf( __( 'Please enter the decimal (%s) format without thousand separators.', 'points-and-rewards-for-woocommerce' ), $decimal ),
						/* translators: %s: price decimal separator */
						'i18n_mon_decimal_error'           => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'points-and-rewards-for-woocommerce' ), wc_get_price_decimal_separator() ),
						'i18n_country_iso_error'           => __( 'Please enter the country code with two capital letters.', 'points-and-rewards-for-woocommerce' ),
						'i18_sale_less_than_regular_error' => __( 'Please enter the value less than the regular price.', 'points-and-rewards-for-woocommerce' ),
						'decimal_point'                    => $decimal,
						'mon_decimal_point'                => wc_get_price_decimal_separator(),
						'strings'                          => array(
							'import_products' => __( 'Import', 'points-and-rewards-for-woocommerce' ),
							'export_products' => __( 'Export', 'points-and-rewards-for-woocommerce' ),
						),
						'urls'                             => array(
							'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
							'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
						),
					);

					wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
					wp_enqueue_script( 'woocommerce_admin' );
					$args_cat     = array( 'taxonomy' => 'product_cat' );
					$categories   = get_terms( $args_cat );
					$option_categ = array();
					if ( isset( $categories ) && ! empty( $categories ) ) {
						foreach ( $categories as $category ) {

							$catid   = $category->term_id;
							$catname = $category->name;

							$option_categ[] = array(
								'id'       => $catid,
								'cat_name' => $catname,
							);
						}
					}

					$url     = admin_url( 'admin.php?page=wps-wpr-setting' );
					$wps_wpr = array(
						'ajaxurl'                => admin_url( 'admin-ajax.php' ),
						'validpoint'             => __( 'Please enter a valid points', 'points-and-rewards-for-woocommerce' ),
						'Labelname'              => __( 'Enter the Name of the Level', 'points-and-rewards-for-woocommerce' ),
						'Labeltext'              => __( 'Enter Level', 'points-and-rewards-for-woocommerce' ),
						'Points'                 => __( 'Enter Points', 'points-and-rewards-for-woocommerce' ),
						'Categ_text'             => __( 'Select Product Category', 'points-and-rewards-for-woocommerce' ),
						'Remove_text'            => __( 'Remove', 'points-and-rewards-for-woocommerce' ),
						'Categ_option'           => $option_categ,
						'Prod_text'              => __( 'Select Product', 'points-and-rewards-for-woocommerce' ),
						'Discounttext'           => __( 'Enter Discount (%)', 'points-and-rewards-for-woocommerce' ),
						'error_notice'           => __( 'Fields cannot be empty', 'points-and-rewards-for-woocommerce' ),
						'LevelName_notice'       => __( 'Please Enter the Name of the Level', 'points-and-rewards-for-woocommerce' ),
						'LevelValue_notice'      => __( 'Please Enter valid Points', 'points-and-rewards-for-woocommerce' ),
						'CategValue_notice'      => __( 'Please select a category', 'points-and-rewards-for-woocommerce' ),
						'ProdValue_notice'       => __( 'Please select a product', 'points-and-rewards-for-woocommerce' ),
						'Discount_notice'        => __( 'Please enter a valid discount', 'points-and-rewards-for-woocommerce' ),
						'success_assign'         => __( 'Points are assigned successfully!', 'points-and-rewards-for-woocommerce' ),
						'error_assign'           => __( 'Enter Some Valid Points!', 'points-and-rewards-for-woocommerce' ),
						'success_remove'         => __( 'Points are removed successfully!', 'points-and-rewards-for-woocommerce' ),
						'Days'                   => __( 'Days', 'points-and-rewards-for-woocommerce' ),
						'Weeks'                  => __( 'Weeks', 'points-and-rewards-for-woocommerce' ),
						'Months'                 => __( 'Months', 'points-and-rewards-for-woocommerce' ),
						'Years'                  => __( 'Years', 'points-and-rewards-for-woocommerce' ),
						'Exp_period'             => __( 'Expiration Period', 'points-and-rewards-for-woocommerce' ),
						'wps_wpr_url'            => $url,
						'reason'                 => __( 'Please enter Remark', 'points-and-rewards-for-woocommerce' ),
						'wps_wpr_nonce'          => wp_create_nonce( 'wps-wpr-verify-nonce' ),
						'check_pro_activate'     => ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ),
						'pro_text'               => __( 'Please purchase the pro plugin to add multiple memberships.', 'points-and-rewards-for-woocommerce' ),
						'pro_link_text'          => __( 'Click here', 'points-and-rewards-for-woocommerce' ),
						'pro_link'               => 'https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro',
						'success_update'         => __( 'Points are updated successfully', 'points-and-rewards-for-woocommerce' ),
						'support_confirm'        => __( 'Email sent successfully', 'points-and-rewards-for-woocommerce' ),
						'negative'               => __( 'Negative Values Not Allowed', 'points-and-rewards-for-woocommerce' ),
						'segment_reached_msg'    => esc_html__( 'You Can Add Only 12 Segments in Win Wheel', 'points-and-rewards-for-woocommerce' ),
						'segment_limit_msg'      => esc_html__( 'Win Wheel cannot have less then 6 Segments', 'points-and-rewards-for-woocommerce' ),
						'wps_badge_image'        => esc_html( WPS_RWPR_DIR_URL . 'admin/images/vip.png', ),
						'badge_pro__text'        => esc_html__( 'Please purchase the pro plugin to add multiple Badges.', 'points-and-rewards-for-woocommerce' ),
						'threshold_warning_msg'  => esc_html__( 'Threshold points should be greater than previous threshold points !', 'points-and-rewards-for-woocommerce' ),
						'invalid_files'          => esc_html__( 'Please choose valid files', 'points-and-rewards-for-woocommerce' ),
						'radio_validate_msg'     => esc_html__( 'Please choose any option !!', 'points-and-rewards-for-woocommerce' ),
						'csv_import_success_msg' => esc_html__( 'CSV file imported successfully.', 'points-and-rewards-for-woocommerce' ),
					);

					wp_enqueue_script( $this->plugin_name . 'admin-js', WPS_RWPR_DIR_URL . 'admin/js/points-rewards-for-woocommerce-admin.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'select2', 'sticky_js' ), $this->version, false );
					wp_localize_script( $this->plugin_name . 'admin-js', 'wps_wpr_object', $wps_wpr );

					// user report work.
					if ( isset( $_GET['wps_reports_userid'] ) ) {

						$user_id   = ! empty( $_GET['wps_reports_userid'] ) ? sanitize_text_field( wp_unslash( $_GET['wps_reports_userid'] ) ) : '';
						$user_data = $this->wps_wpr_get_user_reports_data( $user_id );
						// js for the multistep from.
						$script_path      = WPS_RWPR_DIR_URL . 'build/index.js';
						$path = preg_replace( '/\?v=[\d]+$/', '', $script_path );
						// $fileTime = filemtime($path);
						$script_asset_path = WPS_RWPR_DIR_URL . 'build/index.asset.php';
						$script_asset      = file_exists( $script_asset_path )
							? require $script_asset_path
							: array(
								'dependencies' => array(
									'wp-hooks',
									'wp-element',
									'wp-i18n',
									'wc-components',
								),
								'version'      => $path,
							);
						$script_url        = WPS_RWPR_DIR_URL . 'build/index.js';
						wp_register_script(
							'react-app-block',
							$script_url,
							$script_asset['dependencies'],
							$script_asset['version'],
							true
						);

						wp_enqueue_script( 'react-app-block' );
						wp_localize_script(
							'react-app-block',
							'frontend_ajax_object',
							array(
								'ajaxurl'            => admin_url( 'admin-ajax.php' ),
								'wps_standard_nonce' => wp_create_nonce( 'ajax-nonce' ),
								'name'            => $user_data['name'],
								'email'           => $user_data['email'],
								'membership_name' => $user_data['membership_name'],
								'referral_count'  => $user_data['referral_count'],
								'redeem_points'   => $user_data['redeem_points'],
								'current_points'  => $user_data['current_points'],
								'overall_points'  => $user_data['overall_points'],
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param  int $user_id user_id.
	 * @return array
	 */
	public function wps_wpr_get_user_reports_data( $user_id ) {

		$data = array();
		if ( ! empty( $user_id ) ) {

			$user = get_user_by( 'ID', $user_id );
			$data = array(
				'name'            => $user->display_name,
				'email'           => $user->user_email,
				'membership_name' => get_user_meta( $user_id, 'membership_level', true ),
				'referral_count'  => ! empty( get_user_meta( $user_id, 'wps_referral_counting', true ) ) ? get_user_meta( $user_id, 'wps_referral_counting', true ) : 0,
				'redeem_points'   => ! empty( get_user_meta( $user_id, 'wps_wpr_redeemed_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_redeemed_points', true ) : 0,
				'current_points'  => ! empty( get_user_meta( $user_id, 'wps_wpr_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_points', true ) : 0,
				'overall_points'  => ! empty( get_user_meta( $user_id, 'wps_wpr_overall__accumulated_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_overall__accumulated_points', true ) : 0,
			);
		}
		return $data;
	}

	/**
	 * Add a submenu inside the Woocommerce Menu Page
	 *
	 * @since 1.0.0
	 * @name wps_rwpr_admin_menu()
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_rwpr_admin_menu() {
		add_submenu_page( 'woocommerce', __( 'Points and Rewards', 'points-and-rewards-for-woocommerce' ), __( 'Points and Rewards', 'points-and-rewards-for-woocommerce' ), 'manage_options', 'wps-rwpr-setting', array( $this, 'wps_rwpr_admin_setting' ) );
	}

	/**
	 * This is function is used for the validating the data.
	 *
	 * @name wps_wpr_allowed_html
	 * @since 1.0.0
	 */
	public function wps_wpr_allowed_html() {
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
	 * Including a File for displaying the required setting page for setup the plugin
	 *
	 * @since 1.0.0
	 * @name wps_rwpr_admin_setting()
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_rwpr_admin_setting() {
		include_once WPS_RWPR_DIR_PATH . '/admin/partials/points-rewards-for-woocommerce-admin-display.php';
	}

	/**
	 * This function update points
	 *
	 * @name wps_wpr_points_update
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_points_update() {

		// check if user have capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wps-wpr-verify-nonce', 'wps_nonce' );
		if ( isset( $_POST['points'] ) && is_numeric( $_POST['points'] ) && isset( $_POST['user_id'] ) && isset( $_POST['sign'] ) && isset( $_POST['reason'] ) ) {

			$user_id    = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
			$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			$points     = sanitize_text_field( wp_unslash( $_POST['points'] ) );
			$sign       = sanitize_text_field( wp_unslash( $_POST['sign'] ) );
			$reason     = sanitize_text_field( wp_unslash( $_POST['reason'] ) );

			if ( '+' === $sign ) {

				$total_points = $get_points + $points;
			} elseif ( '-' === $sign ) {
				if ( $points <= $get_points ) {

					$total_points = $get_points - $points;
				} else {

					$points       = $get_points;
					$total_points = $get_points - $points;
				}
			}
			$data = array(
				'sign'   => $sign,
				'reason' => $reason,
			);
			/* Update user points*/
			if ( isset( $total_points ) && $total_points >= 0 ) {
				update_user_meta( $user_id, 'wps_wpr_points', $total_points );
			}
			/* Update user points*/
			self::wps_wpr_update_points_details( $user_id, 'admin_points', $points, $data );
			/* Send Mail to the user*/
			$this->wps_wpr_send_mail_details( $user_id, 'admin_notification', $points );
			wp_die();
		}
	}

	/**
	 * This function is use to update points details
	 *
	 * @name wps_wpr_update_points_details
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @param int    $user_id user id of the user.
	 * @param string $type type of the points details.
	 * @param int    $points points.
	 * @param array  $data  array of the data.
	 * @link https://www.wpswings.com/
	 */
	public static function wps_wpr_update_points_details( $user_id, $type, $points, $data ) {

		/* Get the points of the points details*/
		$today_date   = date_i18n( 'Y-m-d h:i:sa' );
		$admin_points = get_user_meta( $user_id, 'points_details', true );
		$admin_points = ! empty( $admin_points ) && is_array( $admin_points ) ? $admin_points : array();
		if ( isset( $points ) && ! empty( $points ) ) {
			/* Check the type of the array*/
			if ( 'admin_points' == $type && ! empty( $data ) ) {
				if ( isset( $admin_points['admin_points'] ) && ! empty( $admin_points['admin_points'] ) ) {

					$admin_array                    = array();
					$admin_array                    = array(
						'admin_points' => $points,
						'date'         => $today_date,
						'sign'         => $data['sign'],
						'reason'       => $data['reason'],
					);
					$admin_points['admin_points'][] = $admin_array;
				} else {

					if ( ! is_array( $admin_points ) ) {
						$admin_points = array();
					}
					$admin_array                    = array(
						'admin_points' => $points,
						'date'         => $today_date,
						'sign'         => $data['sign'],
						'reason'       => $data['reason'],
					);
					$admin_points['admin_points'][] = $admin_array;
				}
			}
			/* Update the points details*/
			if ( ! empty( $admin_points ) && is_array( $admin_points ) ) {
				update_user_meta( $user_id, 'points_details', $admin_points );
			}
		}
	}

	/**
	 * This function is use to send mail to the user of points details
	 *
	 * @name wps_wpr_update_points_details
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int    $user_id user id of the user.
	 * @param string $type type of the points details.
	 * @param int    $point points.
	 */
	public function wps_wpr_send_mail_details( $user_id, $type, $point ) {
		$user                      = get_user_by( 'ID', $user_id );
		$user_email                = $user->user_email;
		$user_name                 = $user->user_login;
		$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
		if ( 'admin_notification' == $type ) {
			/* Check is settings array is not empty*/
			if ( is_array( $wps_wpr_notificatin_array ) && ! empty( $wps_wpr_notificatin_array ) ) {

				/*Get the mail subject*/
				$wps_wpr_email_subject = $this->wps_wpr_get_subject( 'wps_wpr_email_subject' );
				/*Get the mail custom description*/
				$wps_wpr_email_discription = $this->wps_wpr_get_email_description( 'wps_wpr_email_discription_custom_id' );
				/*Get the total points*/
				$total_points              = $this->wps_wpr_get_user_points( $user_id );
				$wps_wpr_email_discription = str_replace( '[Total Points]', $total_points, $wps_wpr_email_discription );
				$wps_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $wps_wpr_email_discription );
				$wps_wpr_email_discription = str_replace( '[Points]', $point, $wps_wpr_email_discription );

				$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'admin_notification' );

				if ( self::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {
					$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
					$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
				}
			}
		}
	}

	/**
	 * This function is use to check is notification setting is enable or not
	 *
	 * @name wps_wpr_check_mail_notfication_is_enable
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public static function wps_wpr_check_mail_notfication_is_enable() {
		$wps_points_notification_enable = false;
		$wps_wpr_notificatin_array      = get_option( 'wps_wpr_notificatin_array', true );
		$wps_wpr_notificatin_enable     = isset( $wps_wpr_notificatin_array['wps_wpr_notification_setting_enable'] ) ? intval( $wps_wpr_notificatin_array['wps_wpr_notification_setting_enable'] ) : 0;

		if ( 1 == $wps_wpr_notificatin_enable ) {
			$wps_points_notification_enable = true;
		}
		return $wps_points_notification_enable;
	}

	/**
	 * This function is used to get the subject
	 *
	 * @name wps_wpr_check_mail_notfication_is_enable
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int $id of the database array.
	 */
	public function wps_wpr_get_subject( $id ) {
		$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
		$wps_wpr_email_subject     = isset( $wps_wpr_notificatin_array[ $id ] ) ? $wps_wpr_notificatin_array[ $id ] : '';
		return $wps_wpr_email_subject;
	}

	/**
	 * This function is used to get the Email descriptiion
	 *
	 * @name wps_wpr_check_mail_notfication_is_enable
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int $id of the database array.
	 */
	public function wps_wpr_get_email_description( $id ) {
		$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
		$wps_wpr_email_discription = isset( $wps_wpr_notificatin_array[ $id ] ) ? $wps_wpr_notificatin_array[ $id ] : '';
		return $wps_wpr_email_discription;
	}

	/**
	 * This function is used to get user points
	 *
	 * @name wps_wpr_get_user_points
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int $user_id user id of the user.
	 */
	public function wps_wpr_get_user_points( $user_id ) {
		$wps_wpr_total_points = 0;
		$wps_wpr_points       = get_user_meta( $user_id, 'wps_wpr_points', true );

		if ( ! empty( $wps_wpr_points ) ) {
			$wps_wpr_total_points = $wps_wpr_points;
		}
		return $wps_wpr_total_points;
	}

	/**
	 * This function append the option field after selecting Product category through ajax
	 *
	 * @name wps_wpr_select_category.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_select_category() {
		check_ajax_referer( 'wps-wpr-verify-nonce', 'wps_nonce' );
		$wps_wpr_categ_list = array();
		if ( isset( $_POST['wps_wpr_categ_list'] ) && ! empty( $_POST['wps_wpr_categ_list'] ) ) {
			$wps_wpr_categ_list = map_deep( wp_unslash( $_POST['wps_wpr_categ_list'] ), 'sanitize_text_field' );
		}
		$response['result'] = __( 'Fail due to an error', 'points-and-rewards-for-woocommerce' );
		if ( isset( $wps_wpr_categ_list ) ) {
			$products              = array();
			$selected_cat          = $wps_wpr_categ_list;
			$tax_query['taxonomy'] = 'product_cat';
			$tax_query['field']    = 'id';
			$tax_query['terms']    = $selected_cat;
			$tax_queries[]         = $tax_query;
			$args                  = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'tax_query'      => $tax_queries,
				'orderby'        => 'rand',
			);
			$loop                  = new WP_Query( $args );
			while ( $loop->have_posts() ) :
				$loop->the_post();

				$product_id              = $loop->post->ID;
				$product_title           = $loop->post->post_title;
				$products[ $product_id ] = $product_title;
			endwhile;
			$response['data']   = $products;
			$response['result'] = 'success';
			wp_send_json( $response );

		}
	}

	/**
	 * This function append the rule in the membership
	 *
	 * @name wps_wpr_add_rule_for_membership.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $wps_wpr_membership_roles array of the user roles.
	 */
	public function wps_wpr_add_rule_for_membership( $wps_wpr_membership_roles ) {
		?>
		<div class="parent_of_div">
			<?php
			$count = 0;
			if ( is_array( $wps_wpr_membership_roles ) && ! empty( $wps_wpr_membership_roles ) ) {
				$key = array_key_first( $wps_wpr_membership_roles );
				$this->wps_wpr_membership_role( $count, $key, $wps_wpr_membership_roles[ $key ] );
			} else {
				$this->wps_wpr_membership_role( $count, '', '' );
			}
			?>
		</div>
		<?php
	}

	/**
	 * This function is used for checking is not empty the value
	 *
	 * @name check_is_not_empty.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $value value of the database.
	 */
	public function check_is_not_empty( $value ) {
		return ! empty( $value ) ? $value : '';
	}

	/**
	 * This function is used for adding the membership
	 *
	 * @name wps_wpr_membership_role.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int   $count count of the membership.
	 * @param int   $key key of the array.
	 * @param array $value value of one array.
	 */
	public function wps_wpr_membership_role( $count, $key, $value ) {
		?>
		<div id ="wps_wpr_parent_repeatable_<?php echo esc_html( $count ); ?>" data-id="<?php echo esc_html( $count ); ?>" class="wps_wpr_repeat">
			<table class="wps_wpr_repeatable_section">
				<tr valign="top">
					<th scope="row" class="wps-wpr-titledesc">
						<label for="wps_wpr_membership_level_name"><?php esc_html_e( 'Enter Level', 'points-and-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags          = $this->wps_wpr_allowed_html();
						$attribute_description = __( 'The entered text will be the name of the level for membership', 'points-and-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<label for="wps_wpr_membership_level_name">
							<input type="text" name="wps_wpr_membership_level_name_<?php echo esc_html( $count ); ?>" value="<?php echo esc_html( $this->check_is_not_empty( $key ) ); ?>" id="wps_wpr_membership_level_name_<?php echo esc_html( $count ); ?>" class="text_points" required><?php esc_html_e( 'Enter the Name of the Level', 'points-and-rewards-for-woocommerce' ); ?>
						</label>
						<?php if ( ! empty( $value ) ) : ?>
						<input type="button" value='<?php esc_html_e( 'Remove', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_remove_button" id="<?php echo esc_html( $count ); ?>">	
						<?php endif; ?> 			
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="wps-wpr-titledesc">
						<label for="wps_wpr_membership_level_value"><?php esc_html_e( 'Enter Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags          = $this->wps_wpr_allowed_html();
						$attribute_description = __( 'Entered points need to be reached for this level', 'points-and-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );

						?>
						<label for="wps_wpr_membership_level_value">
						<input type="number" min="1" value="<?php echo esc_html( $this->check_is_not_empty( isset( $value['Points'] ) ? $value['Points'] : '' ) ); ?>" name="wps_wpr_membership_level_value_<?php echo esc_html( $count ); ?>" id="wps_wpr_membership_level_value_<?php echo esc_html( $count ); ?>" class="input-text" required>
						</label>			
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="wps-wpr-titledesc">
						<label for="wps_wpr_membership_expiration"><?php esc_html_e( 'Expiration Period', 'points-and-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags          = $this->wps_wpr_allowed_html();
						$attribute_description = __( 'Select the days, week, month, or year for the expiration of the current level', 'points-and-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						$exp_num = isset( $value['Exp_Number'] ) ? $value['Exp_Number'] : '';
						?>
						<input type="number" min="1" value="<?php echo esc_html( $exp_num ); ?>" name="wps_wpr_membership_expiration_<?php echo esc_html( $count ); ?>" id="wps_wpr_membership_expiration_<?php echo esc_html( $count ); ?>" class="input-text" required>
						<select id="wps_wpr_membership_expiration_days_<?php echo esc_html( $count ); ?>" name="wps_wpr_membership_expiration_days_<?php echo esc_html( $count ); ?>">
						<option value="<?php esc_html_e( 'days', 'points-and-rewards-for-woocommerce' ); ?>" <?php selected( esc_html__( 'days', 'points-and-rewards-for-woocommerce' ), isset( $value['Exp_Days'] ) ? $value['Exp_Days'] : '' ); ?>><?php esc_html_e( 'Days', 'points-and-rewards-for-woocommerce' ); ?></option>
						<option value="<?php esc_html_e( 'weeks', 'points-and-rewards-for-woocommerce' ); ?>" <?php selected( esc_html__( 'weeks', 'points-and-rewards-for-woocommerce' ), isset( $value['Exp_Days'] ) ? $value['Exp_Days'] : '' ); ?>><?php esc_html_e( 'Weeks', 'points-and-rewards-for-woocommerce' ); ?></option>
						<option value="<?php esc_html_e( 'months', 'points-and-rewards-for-woocommerce' ); ?>" <?php selected( esc_html__( 'months', 'points-and-rewards-for-woocommerce' ), isset( $value['Exp_Days'] ) ? $value['Exp_Days'] : '' ); ?>><?php esc_html_e( 'Months', 'points-and-rewards-for-woocommerce' ); ?></option>
						<option value="<?php esc_html_e( 'years', 'points-and-rewards-for-woocommerce' ); ?>" <?php selected( esc_html__( 'years', 'points-and-rewards-for-woocommerce' ), isset( $value['Exp_Days'] ) ? $value['Exp_Days'] : '' ); ?>><?php esc_html_e( 'Years', 'points-and-rewards-for-woocommerce' ); ?></option>	
						</select>		
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="wps-wpr-titledesc">
						<label for="wps_wpr_membership_category_list"><?php esc_html_e( 'Select Product Category', 'points-and-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags          = $this->wps_wpr_allowed_html();
						$attribute_description = __( 'Select Product Category', 'points-and-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<select id="wps_wpr_membership_category_list_<?php echo esc_html( $count ); ?>" required="true" multiple="multiple" class="wps_wpr_common_class_categ" data-id="<?php echo esc_html( $count ); ?>" name="wps_wpr_membership_category_list_<?php echo esc_html( $count ); ?>[]">
							<?php
							$args       = array( 'taxonomy' => 'product_cat' );
							$categories = get_terms( $args );
							if ( isset( $categories ) && ! empty( $categories ) ) {

								foreach ( $categories as $category ) {
									$catid     = $category->term_id;
									$catname   = $category->name;
									$catselect = '';
									if ( isset( $value['Prod_Categ'] ) && ! empty( $value['Prod_Categ'] ) ) {
										if ( is_array( $value['Prod_Categ'] ) && in_array( $catid, $value['Prod_Categ'] ) ) {
											$catselect = "selected='selected'";
										}
									}

									?>
									<option value="<?php echo esc_html( $catid ); ?>" <?php echo esc_html( $catselect ); ?>><?php echo esc_html( $catname ); ?></option>
									<?php
								}
							}
							?>
						</select>
						<br />
						<a href="#" id="wps_wpr_membership_select_all_category_<?php echo esc_html( $count ); ?>" class="wps_wpr_membership_select_all_category_common button" data-id="<?php echo esc_html( $count ); ?>"><?php esc_html_e( 'Select all', 'points-and-rewards-for-woocommerce' ); ?></a>

						<a href="#" id="wps_wpr_membership_select_none_category_<?php echo esc_html( $count ); ?>" class="wps_wpr_membership_select_none_category_common button" data-id="<?php echo esc_html( $count ); ?>"><?php esc_html_e( 'Select none', 'points-and-rewards-for-woocommerce' ); ?></a>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="wps-wpr-titledesc">
						<label for="wps_wpr_membership_product_list"><?php esc_html_e( 'Select Products', 'points-and-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags          = $this->wps_wpr_allowed_html();
						$attribute_description = __( 'Product of selected category will get displayed in the Select Product Section', 'points-and-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<select id="wps_wpr_membership_product_list_<?php echo esc_html( $count ); ?>" multiple="multiple" name="wps_wpr_membership_product_list_<?php echo esc_html( $count ); ?>[]">
							<?php
							$tax_queries           = array();
							$tax_query['taxonomy'] = 'product_cat';
							$tax_query['field']    = 'id';
							$tax_query['terms']    = isset( $value['Prod_Categ'] ) ? $value['Prod_Categ'] : '';
							$tax_queries[]         = $tax_query;
							$args                  = array(
								'post_type'      => 'product',
								'posts_per_page' => -1,
								'tax_query'      => $tax_queries,
								'orderby'        => 'rand',
							);
							$loop                  = new WP_Query( $args );
							while ( $loop->have_posts() ) :
								$loop->the_post();
								$productselect = '';
								$productid     = $loop->post->ID;
								$productitle   = $loop->post->post_title;
								if ( ! empty( $this->check_is_not_empty( $value['Product'] ) ) ) {
									if ( is_array( $value['Product'] ) && in_array( $productid, $value['Product'] ) ) {

										$productselect = "selected='selected'";
									}
								}
								?>
							<option value="<?php echo esc_html( $productid ); ?>" <?php echo esc_html( $productselect ); ?>><?php echo esc_html( $productitle ); ?>
							</option>
								<?php
							endwhile;

							?>
						</select>
						<br/>
						<span class="wps_wpr_select_product"><?php esc_html_e( 'Select the products if you want to provide a discount on the specific products of the selected category. Leave empty to select all the products of the selected category.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="wps-wpr-titledesc">
						<label for="wps_wpr_membership_discount"><?php esc_html_e( 'Enter Discount (%)', 'points-and-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags          = $this->wps_wpr_allowed_html();
						$attribute_description = __( 'Entered Discount will be applied to the above-selected products', 'points-and-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<label for="wps_wpr_membership_discount">
						<input type="number" min="1" max="100" value="<?php echo esc_html( $this->check_is_not_empty( isset( $value['Discount'] ) ? $value['Discount'] : '' ) ); ?>" name="wps_wpr_membership_discount_<?php echo esc_html( $count ); ?>" id="wps_wpr_membership_discount_<?php echo esc_html( $count ); ?>" class="input-text" required>
						</label>	
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label for="wps_wpr_enable_to_rewards_with_points"><?php esc_html_e( 'Rewards Members with points', 'points-and-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags          = $this->wps_wpr_allowed_html();
						$attribute_description = __( 'Check this box to rewards user with points on the basis of his membership level.', 'points-and-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<label for="wps_wpr_enable_to_rewards_with_points">
							<input type="checkbox" name="wps_wpr_enable_to_rewards_with_points_<?php echo esc_html( $count ); ?>" id="wps_wpr_enable_to_rewards_with_points_<?php echo esc_html( $count ); ?>" value="1" <?php checked( ! empty( $value['enable_mem_reward_points'] ) ? $value['enable_mem_reward_points'] : '0', 1 ); ?>>
						</label>
					</td>
				</tr>
				<tr>
					<th>
						<label for="wps_wpr_mem_reward_type"><?php esc_html_e( 'Rewards Points type', 'points-and-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags          = $this->wps_wpr_allowed_html();
						$attribute_description = __( 'Assign points type, percentage will calculate on the basis of user cart sub total.', 'points-and-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<label for="wps_wpr_choose_mem_points_type">
							<select name="wps_wpr_choose_mem_points_type_<?php echo esc_html( $count ); ?>" id="wps_wpr_choose_mem_points_type_<?php echo esc_html( $count ); ?>" class="wps_wpr_assign_mem_rewards_points">
								<option value="fixed" <?php selected( ! empty( $value['assign_mem_points_type'] ) ? $value['assign_mem_points_type'] : '', 'fixed' ); ?>><?php esc_html_e( 'Fixed', 'points-and-rewards-for-woocommerce' ); ?></option>
								<option value="percent" <?php selected( ! empty( $value['assign_mem_points_type'] ) ? $value['assign_mem_points_type'] : '', 'percent' ); ?>><?php esc_html_e( 'Percent', 'points-and-rewards-for-woocommerce' ); ?></option>
							</select>
						</label>
					</td>
				</tr>
				<tr>
					<th>
						<label for="wps_wpr_mem_rewards_points"><?php esc_html_e( 'Enter Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags          = $this->wps_wpr_allowed_html();
						$attribute_description = __( 'Please enter the value that will be assigned to the user when the order is completed.', 'points-and-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<label for="wps_wpr_assign_mem_points_val">
							<input type="number" min="0" name="wps_wpr_assign_mem_points_val_<?php echo esc_html( $count ); ?>" id="wps_wpr_assign_mem_points_val_<?php echo esc_html( $count ); ?>" value="<?php echo esc_html( ! empty( $value['mem_rewards_points_val'] ) ? $value['mem_rewards_points_val'] : 0 ); ?>">
						</label>
					</td>
					<input type="hidden" value="<?php echo esc_html( $count ); ?>" name="hidden_count">
				</tr>
				<?php do_action( 'wps_wpr_add_membership', $count ); ?>
			</table>
		</div>
		<?php
	}

	/**
	 * This function is ised for adding actions
	 *
	 * @name wps_wpr_add_membership_rule.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_add_membership_rule() {
		global $public_obj;
		if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {

			add_action( 'wps_wpr_add_membership_rule', array( $this, 'wps_wpr_add_rule_for_membership' ), 10 );
			add_action( 'wps_wpr_order_total_points', array( $this, 'wps_wpr_add_order_total_points' ), 10, 3 );
		}
		$public_obj = $this;
	}

	/**
	 * This function is used to add order total points.
	 *
	 * @name wps_wpr_add_order_total_points.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $thankyouorder_min  array of the min satements.
	 * @param array $thankyouorder_max array of the max rules.
	 * @param array $thankyouorder_value array of the points value.
	 */
	public function wps_wpr_add_order_total_points( $thankyouorder_min, $thankyouorder_max, $thankyouorder_value ) {

		if ( isset( $thankyouorder_min ) && null != $thankyouorder_min && isset( $thankyouorder_max ) && null != $thankyouorder_max && isset( $thankyouorder_value ) && null != $thankyouorder_value ) {
			if ( count( $thankyouorder_min ) == count( $thankyouorder_max ) && count( $thankyouorder_max ) == count( $thankyouorder_value ) ) {

				?>
				<table class="form-table wp-list-table widefat fixed striped">
					<thead> 
						<tr valign="top">
							<th><?php esc_html_e( 'Minimum', 'points-and-rewards-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Maximum', 'points-and-rewards-for-woocommerce' ); ?></th>

							<th><?php esc_html_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?></th>

							<?php if ( count( $thankyouorder_min ) > 1 ) { ?>
							<th class="wps_wpr_remove_thankyouorder_content"><?php esc_html_e( 'Action', 'points-and-rewards-for-woocommerce' ); ?></th>
							<?php } ?>

						</tr>
					</thead>
					<tbody  class="wps_wpr_thankyouorder_tbody">
				<?php
				$this->wps_wpr_add_rule_for_order_total_points( $thankyouorder_min, $thankyouorder_max, $thankyouorder_value, '0' );
				?>
					</tbody>
				</table>
				<?php
			}
		} else {
			?>
			<table class="form-table wp-list-table widefat fixed striped">
				<thead> 
					<tr valign="top">
						<th><?php esc_html_e( 'Minimum', 'points-and-rewards-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Maximum', 'points-and-rewards-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?></th>
					</tr>
				</thead>
			<tbody  class="wps_wpr_thankyouorder_tbody">
			<?php
			$this->wps_wpr_add_rule_for_order_total_points( array(), array(), array(), '' );
			?>
			</tbody>
			</table>
			<?php
		}
	}

	/**
	 * This function is used to add rule for order total.
	 *
	 * @name wps_wpr_add_rule_for_membership.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $thankyouorder_min  array of the min satements.
	 * @param array $thankyouorder_max array of the max rules.
	 * @param array $thankyouorder_value array of the points value.
	 * @param int   $key  value of the array key.
	 */
	public function wps_wpr_add_rule_for_order_total_points( $thankyouorder_min, $thankyouorder_max, $thankyouorder_value, $key ) {
		?>
		<tr valign="top">
			<td class="forminp forminp-text">
				<label for="wps_wpr_thankyouorder_minimum">
					<input type="number" min="1" name="wps_wpr_thankyouorder_minimum[]" class="wps_wpr_thankyouorder_minimum input-text wc_input_price"  placeholder = "No minimum"  value="<?php echo ( ! empty( $thankyouorder_min[ $key ] ) ) ? esc_html( $thankyouorder_min[ $key ] ) : ''; ?>">
				</label>
			</td>
			<td class="forminp forminp-text">
				<label for="wps_wpr_thankyouorder_maximum">
					<input type="number" min="1" name="wps_wpr_thankyouorder_maximum[]" class="wps_wpr_thankyouorder_maximum"  placeholder = "No maximum"  value="<?php echo ( ! empty( $thankyouorder_max[ $key ] ) ) ? esc_html( $thankyouorder_max[ $key ] ) : ''; ?>" required>
				</label>
			</td>
			<td class="forminp forminp-text">
				<label for="wps_wpr_thankyouorder_current_type">
					<input type="number" min="1" name="wps_wpr_thankyouorder_current_type[]" class="wps_wpr_thankyouorder_current_type input-text wc_input_price"  value="<?php echo ( ! empty( $thankyouorder_value[ $key ] ) ) ? esc_html( $thankyouorder_value[ $key ] ) : ''; ?>">
				</label>
			</td>    
			<?php if ( ! empty( $key ) ) : ?>                       
				<td class="wps_wpr_remove_thankyouorder_content forminp forminp-text">
					<input type="button" value="<?php esc_html_e( 'Remove', 'points-and-rewards-for-woocommerce' ); ?>" class="wps_wpr_remove_thankyouorder button" >
				</td>
			<?php endif; ?>
		</tr>
		<?php
	}

	/**
	 * This function is used to set cron if user want to get support.
	 *
	 * @since 1.0.7
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_check_for_notification_daily() {
		$is_already_sent = get_option( 'onboarding-data-sent', false );
		// Already submitted the data.
		if ( ! empty( $is_already_sent ) && 'sent' == $is_already_sent ) {

			$offset = get_option( 'gmt_offset' );
			$time   = time() + $offset * 60 * 60;
			if ( ! wp_next_scheduled( 'wps_wpr_check_for_notification_update' ) ) {

				wp_schedule_event( $time, 'daily', 'wps_wpr_check_for_notification_update' );
			}
		}

		// calling to create crone for banner image.
		$this->wps_wpr_set_cron_for_plugin_banner_notification();
	}

	/**
	 * This function is used to save notification message with notification id.
	 *
	 * @since 1.0.7
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_save_notice_message() {
		$wps_notification_data = $this->wps_wpr_get_update_notification_data();
		if ( is_array( $wps_notification_data ) && ! empty( $wps_notification_data ) ) {

			$notification_id      = array_key_exists( 'notification_id', $wps_notification_data[0] ) ? $wps_notification_data[0]['notification_id'] : '';
			$notification_message = array_key_exists( 'notification_message', $wps_notification_data[0] ) ? $wps_notification_data[0]['notification_message'] : '';
			update_option( 'wps_wpr_notify_new_msg_id', $notification_id );
			update_option( 'wps_wpr_notify_new_message', $notification_message );
		}
	}

	/**
	 * This function is used to get notification data from server.
	 *
	 * @since 1.0.7
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_get_update_notification_data() {
		$wps_notification_data = array();
		$url                   = 'https://demo.wpswings.com/client-notification/points-and-rewards-for-woocommerce/wps-client-notify.php';
		$attr                  = array(
			'action' => 'wps_notification_fetch',
			'plugin_version' => REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION,
		);
		$query    = esc_url_raw( add_query_arg( $attr, $url ) );
		$response = wp_remote_get(
			$query,
			array(
				'timeout' => 20,
				'sslverify' => false,
			)
		);
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			/* translators: %s: for error message */
			echo '<p><strong>' . sprintf( esc_html__( ' Something went wrong: %s ', 'points-and-rewards-for-woocommerce' ), esc_html( stripslashes( $error_message ) ) ) . '</strong></p>';
		} else {
			$wps_notification_data = json_decode( wp_remote_retrieve_body( $response ), true );
		}
		return $wps_notification_data;
	}

	/**
	 * This function is used to display notoification bar at admin.
	 *
	 * @return void
	 */
	public function wps_wpr_display_notification_bar() {
		$screen = get_current_screen();
		if ( isset( $screen->id ) ) {

			if ( wp_verify_nonce( ! empty( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '', 'par_main_setting' ) ) {
				if ( isset( $_GET['page'] ) && 'wps-rwpr-setting' == $_GET['page'] || 'product' == $screen->id ) {

					$notification_id = get_option( 'wps_wpr_notify_new_msg_id', false );
					if ( isset( $notification_id ) && '' !== $notification_id ) {

						$hidden_id            = get_option( 'wps_wpr_notify_hide_notification', false );
						$notification_message = get_option( 'wps_wpr_notify_new_message', '' );
						if ( isset( $hidden_id ) && $hidden_id < $notification_id ) {
							if ( '' !== $notification_message ) {

								?>
								<div class="notice is-dismissible notice-info" id="dismiss_notice">
									<div class="notice-container">
										<div class="notice-image">
											<img src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/images/wpswings_logo.png' ); ?>" alt="WP Swings">
										</div> 
										<div class="notice-content">
											<?php echo wp_kses_post( $notification_message ); ?>
										</div>				
									</div>
									<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
								</div>
								<?php
							}
						}
					}
				}
			}
		}
	}

	/**
	 * This function is used to dismiss admin notices.
	 *
	 * @name wps_wpr_dismiss_notice
	 * @since 1.0.7
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_dismiss_notice() {
		if ( isset( $_REQUEST['wps_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wps_nonce'] ) ), 'wps-wpr-verify-nonce' ) ) { // WPCS: input var ok, sanitization ok.

			$notification_id = get_option( 'wps_wpr_notify_new_msg_id', false );
			if ( isset( $notification_id ) && '' !== $notification_id ) {

				update_option( 'wps_wpr_notify_hide_notification', $notification_id );
			}
			wp_send_json_success();
		}
	}

	/**
	 * Get all valid screens to add scripts and templates.
	 *
	 * @param  array $valid_screens valid screen.
	 * @since    1.0.7
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function add_wps_frontend_screens( $valid_screens = array() ) {

		if ( is_array( $valid_screens ) ) {
			// Push your screen here.
			array_push( $valid_screens, 'woocommerce_page_wps-rwpr-setting' );
		}
		return $valid_screens;
	}

	/**
	 * Get all valid slugs to add deactivate popup.
	 *
	 * @param  array $valid_screens valid screen.
	 * @since    1.0.7
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function add_wps_deactivation_screens( $valid_screens = array() ) {

		if ( is_array( $valid_screens ) ) {
			// Push your screen here.
			array_push( $valid_screens, 'points-and-rewards-for-woocommerce' );
		}
		return $valid_screens;
	}

	/**
	 * Mwb_wpr_wallet_order_point function
	 *
	 * @param [array] $wps_wpr_general_settings for setting.
	 * @return array
	 */
	public function wps_wpr_wallet_order_point( $wps_wpr_general_settings ) {
		if ( is_plugin_active( 'wallet-system-for-woocommerce/wallet-system-for-woocommerce.php' ) && ! empty( get_option( 'wps_wsfw_enable', '' ) ) ) {

			$my_new_inserted_array = array(
				array(
					'title' => __( 'Enable Wallet points setting', 'points-and-rewards-for-woocommerce' ),
					'type'  => 'title',
				),
				array(
					'title'    => __( 'Wallet points setting', 'points-and-rewards-for-woocommerce' ),
					'type'     => 'checkbox',
					'desc'     => __( 'Enable Wallet points setting', 'points-and-rewards-for-woocommerce' ),
					'id'       => 'wps_wpr_general_setting_wallet_enablee',
					'desc_tip' => __( 'Toggle this box to enable points conversion to the amount on the wallet.', 'points-and-rewards-for-woocommerce' ),
					'default'  => 0,
				),
				array(
					'title'       => __( 'Conversion rate', 'points-and-rewards-for-woocommerce' ),
					'type'        => 'number_text',
					'number_text' => array(
						array(
							'type'              => 'number',
							'id'                => 'wps_wpr_wallet_points_rate',
							'class'             => 'input-text wc_input_price wps_wpr_new_woo_ver_style_text',
							'custom_attributes' => array( 'min' => '"1"' ),
							'desc_tip'          => __(
								'The entered point will be converted in the front end.',
								'points-and-rewards-for-woocommerce'
							),
							'desc'              => __( ' Points = ', 'points-and-rewards-for-woocommerce' ),
						),
						array(
							'type'              => 'text',
							'id'                => 'wps_wpr_wallet_price_rate',
							'class'             => 'input-text wps_wpr_new_woo_ver_style_text wc_input_price',
							'custom_attributes' => array( 'min' => '"1"' ),
							'desc_tip'          => __(
								'Entered amount to convert',
								'points-and-rewards-for-woocommerce'
							),
							'default'           => '1',
							'curr'              => get_woocommerce_currency_symbol(),
						),
					),
				),
				array(
					'type' => 'sectionend',
				),

			);
			$wps_wpr_general_settings = $this->insert_key_value_pair( $wps_wpr_general_settings, $my_new_inserted_array, 150 );
		}
		return $wps_wpr_general_settings;
	}

	/**
	 * Insert array
	 *
	 * @name insert_key_value_pair
	 * @since    1.0.0
	 * @param array $arr array of the settings.
	 * @param array $inserted_array new array of the settings.
	 * @param int   $index index of the array.
	 */
	public function insert_key_value_pair( $arr, $inserted_array, $index ) {
		$arrayend   = array_splice( $arr, $index );
		$arraystart = array_splice( $arr, 0, $index );
		return ( array_merge( $arraystart, $inserted_array, $arrayend ) );
	}

	/**
	 * This function is used to make PAR compatible with PAR.
	 *
	 * @param array $wps_wpr_general_settings wps_wpr_general_settings.
	 * @return array
	 */
	public function wps_wpr_subscription_settings( $wps_wpr_general_settings ) {

		if ( function_exists( 'wps_sfw_check_plugin_enable' ) && wps_sfw_check_plugin_enable() ) {

			$my_new_inserted_array    = array(
				array(
					'title' => __( 'Subscription Points Settings', 'points-and-rewards-for-woocommerce' ),
					'type'  => 'title',
				),
				array(
					'title'    => __( 'Renewal Subscription Point Settings', 'points-and-rewards-for-woocommerce' ),
					'type'     => 'checkbox',
					'desc'     => __( 'Toggle This to Give Points, Only When the Subscription is Renewed.', 'points-and-rewards-for-woocommerce' ),
					'id'       => 'wps_wpr_enable_subscription_renewal_settings',
					'desc_tip' => __( 'Toggle this box to give points only when subscription is renewal', 'points-and-rewards-for-woocommerce' ),
					'default'  => 0,
				),
				array(
					'title'             => __( 'Enter Subscription Renewal Points', 'points-and-rewards-for-woocommerce' ),
					'type'              => 'number',
					'default'           => 1,
					'id'                => 'wps_wpr_subscription__renewal_points',
					'custom_attributes' => array( 'min' => '"1"' ),
					'class'             => 'input-text wps_wpr_new_woo_ver_style_text',
					'desc_tip'          => __( 'Entered Points Will be Awarded to The User When Their Subscription is Renewed', 'points-and-rewards-for-woocommerce' ),
				),
				array(
					'title'    => __( 'Show message on Account Page', 'points-and-rewards-for-woocommerce' ),
					'type'     => 'checkbox',
					'desc'     => __( 'Enable this setting to show a message on the account page for user acknowledgment', 'points-and-rewards-for-woocommerce' ),
					'id'       => 'wps_wpr_enable__renewal_message_settings',
					'desc_tip' => __( 'Toggle This to Show a Message on the Accounts Page for Users to Know How Much They Will Earn', 'points-and-rewards-for-woocommerce' ),
					'default'  => 0,
				),
				array(
					'title'    => __( 'Enter Renewal Message', 'points-and-rewards-for-woocommerce' ),
					'type'     => 'text',
					'id'       => 'wps_wpr_subscription__renewal_message',
					'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
					'desc'     => __( 'The entered message will be shown on the user’s Account Page. Please enter a message including the [Points] shortcode', 'points-and-rewards-for-woocommerce' ),
					'desc_tip' => __( 'Message for Users To Know About Referral Points', 'points-and-rewards-for-woocommerce' ),
				),
				array(
					'type' => 'sectionend',
				),
			);
			$wps_wpr_general_settings = $this->insert_key_value_pair( $wps_wpr_general_settings, $my_new_inserted_array, 150 );
		}
		return $wps_wpr_general_settings;
	}

	/**
	 * This function is used to awarded user with points when order is renewal.
	 *
	 * @param int $order_id $order id.
	 * @return void
	 */
	public function wps_wpr_subscription_renewal_point( $order_id ) {

		if ( ! empty( $order_id ) && ! is_null( $order_id ) ) {

			$order              = wc_get_order( $order_id );
			$order_status       = $order->get_status();
			$user_id            = absint( $order->get_user_id() );
			$user               = get_user_by( 'ID', $user_id );
			$user_email         = $user->user_email;
			$user_name          = $user->user_login;
			$today_date         = date_i18n( 'Y-m-d h:i:sa' );
			$user_points        = get_user_meta( $user_id, 'wps_wpr_points', true );
			$user_points        = ! empty( $user_points ) && ! is_null( $user_points ) ? $user_points : 0;
			$wps_points_details = get_user_meta( $user_id, 'points_details', true );
			$wps_points_details = ! empty( $wps_points_details ) && is_array( $wps_points_details ) ? $wps_points_details : array();

			// Renewal setting values.
			$wps_wpr_general_settings                     = get_option( 'wps_wpr_settings_gallery', array() );
			$wps_wpr_enable_subscription_renewal_settings = ! empty( $wps_wpr_general_settings['wps_wpr_enable_subscription_renewal_settings'] ) ? $wps_wpr_general_settings['wps_wpr_enable_subscription_renewal_settings'] : 0;
			$wps_wpr_subscription__renewal_points         = ! empty( $wps_wpr_general_settings['wps_wpr_subscription__renewal_points'] ) ? $wps_wpr_general_settings['wps_wpr_subscription__renewal_points'] : 0;

			if ( '1' == $wps_wpr_enable_subscription_renewal_settings ) {
				// hpos.
				$wps_wpr_renewal_points_awarded = wps_wpr_hpos_get_meta_data( $order_id, 'wps_wpr_renewal_points_awarded', true );

				if ( empty( $wps_wpr_renewal_points_awarded ) ) {
					if ( 'processing' === $order_status ) {

						$wps_wpr_total_points = $user_points + $wps_wpr_subscription__renewal_points;
						if ( isset( $wps_points_details['subscription_renewal_points'] ) && ! empty( $wps_points_details['subscription_renewal_points'] ) ) {

							$currency_arr = array();
							$currency_arr = array(
								'subscription_renewal_points' => $wps_wpr_subscription__renewal_points,
								'date'                        => $today_date,
							);
							$wps_points_details['subscription_renewal_points'][] = $currency_arr;
						} else {
							$currency_arr = array();
							$currency_arr = array(
								'subscription_renewal_points' => $wps_wpr_subscription__renewal_points,
								'date'                        => $today_date,
							);
							$wps_points_details['subscription_renewal_points'][] = $currency_arr;
						}

						update_user_meta( $user_id, 'wps_wpr_points', $wps_wpr_total_points );
						update_user_meta( $user_id, 'points_details', $wps_points_details );
						// hpos.
						wps_wpr_hpos_update_meta_data( $order_id, 'wps_wpr_renewal_points_awarded', 'done' );
						wps_wpr_hpos_update_meta_data( $order_id, 'wps_wpr_subscription_renewal_awarded_points', $wps_wpr_subscription__renewal_points );

						if ( self::wps_wpr_check_mail_notfication_is_enable() ) {

							$wps_wpr_email_subject     = esc_html__( 'Subscription Renewal Points Notification', 'points-and-rewards-for-woocommerce' );
							$wps_wpr_email_discription = esc_html__( 'You have received [Points] points and your total points are [Total Points]', 'points-and-rewards-for-woocommerce' );
							$wps_wpr_email_discription = str_replace( '[Total Points]', $wps_wpr_total_points, $wps_wpr_email_discription );
							$wps_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $wps_wpr_email_discription );
							$wps_wpr_email_discription = str_replace( '[Points]', $wps_wpr_subscription__renewal_points, $wps_wpr_email_discription );

							$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
							$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
						}
					}
				}
			}
		}
	}

	/**
	 * This function is used to assign points on previuos orders.
	 *
	 * @return bool
	 */
	public function wps_wpr_assign_points_on_previous_order_call() {

		check_ajax_referer( 'wps-wpr-verify-nonce', 'nonce' );
		// check admin capability.
		if ( ! current_user_can( 'manage_options' ) ) {

			return false;
		}

		if ( wps_wpr_restrict_user_fun() ) {

			return;
		}

		$offset             = 0;
		$order_per_page     = 200;
		$response           = array();
		$response['result'] = false;
		$response['msg']    = esc_html__( 'Points not awarded', 'points-and-rewards-for-woocommerce' );
		if ( isset( $_POST ) ) {

			$rewards_points = ! empty( $_POST['rewards_points'] ) ? sanitize_text_field( wp_unslash( $_POST['rewards_points'] ) ) : '0';
			do {

				$args = array(
					'type'   => 'shop_order',
					'status' => array( 'wc-completed' ),
					'return' => 'ids',
					'offset' => $offset,
					'limit'  => $order_per_page,
				);

				$orders = wc_get_orders( $args );
				if ( is_wp_error( $orders ) ) {

					$response['result'] = false;
					$response['msg']    = esc_html__( 'Error Occurred', 'points-and-rewards-for-woocommerce' );
					return false;
				}

				if ( ! empty( $orders ) && is_array( $orders ) ) {

					$flag     = false;
					$negative = 0;
					$positive = 0;
					foreach ( $orders as $order_id ) {
						// hpos.
						$per_curreny_points_check = wps_wpr_hpos_get_meta_data( $order_id, "$order_id#item_conversion_id", true );
						$referral_purchase_check  = wps_wpr_hpos_get_meta_data( $order_id, 'wps_wpr_awarded_referral_purchase_points', true );
						$order_total_points_check = wps_wpr_hpos_get_meta_data( $order_id, "$order_id#points_assignedon_order_total", true );

						if ( ! empty( $per_curreny_points_check ) || ! empty( $referral_purchase_check ) || ! empty( $order_total_points_check ) ) {

							continue;
						}

						$order = wc_get_order( $order_id );
						if ( ! empty( $order ) ) {
							if ( 'completed' === $order->get_status() ) {

								// calling funciton to update users points.
								$flag = $this->wps_wpr_add_points_on_old_orders( $order, $rewards_points );
								if ( $flag ) {

									++$positive;
									++$negative;
								}
							}
						}
					}

					// show success msg when points awarded.
					if ( $positive > 0 ) {

						$response['result'] = true;
						$response['msg']    = esc_html__( 'Points awarded successfully', 'points-and-rewards-for-woocommerce' );
					}
					// show msg if points is already awarded.
					if ( 0 === $negative ) {

						$response['result'] = false;
						$response['msg']    = esc_html__( 'Points already awarded on this orders', 'points-and-rewards-for-woocommerce' );
					}
				}

				$offset     += $order_per_page;
				$order_total = count( $orders );
			} while ( $order_total === $order_per_page );
		}
		wp_send_json( $response );
		wp_die();
	}

	/**
	 * This function is used to update user points via order previous methods.
	 *
	 * @param object $order order.
	 * @param string $rewards_points rewards_points.
	 * @return bool
	 */
	public function wps_wpr_add_points_on_old_orders( $order, $rewards_points ) {

		if ( ! empty( $order ) ) {
			// hpos.
			$wps_wpr_assign_points_to_old_orders = wps_wpr_hpos_get_meta_data( $order->get_id(), 'wps_wpr_assign_points_to_old_orders', true );
			if ( empty( $wps_wpr_assign_points_to_old_orders ) ) {

				$get_points     = get_user_meta( $order->get_user_id(), 'wps_wpr_points', true );
				$get_points     = ! empty( $get_points ) ? $get_points : 0;
				$updated_points = (int) $get_points + $rewards_points;

				update_user_meta( $order->get_user_id(), 'wps_wpr_points', $updated_points );
				wps_wpr_hpos_update_meta_data( $order->get_id(), 'wps_wpr_assign_points_to_old_orders', 'done' );

				// calling function to create logs.
				$this->wps_wpr_create_log_for_previous_order( $order->get_user_id(), $rewards_points, $order->get_id() );
				return true;
			}
		}
	}

	/**
	 * This function is used to create logs for previuos orders.
	 *
	 * @param string $user_id user_id.
	 * @param string $rewards_points rewards points.
	 * @param string $order_id order id.
	 * @return void
	 */
	public function wps_wpr_create_log_for_previous_order( $user_id, $rewards_points, $order_id ) {

		if ( $rewards_points > 0 ) {

			$previous_order_logs = get_user_meta( $user_id, 'points_details', true );
			$previous_order_logs = ! empty( $previous_order_logs ) && is_array( $previous_order_logs ) ? $previous_order_logs : array();

			if ( isset( $previous_order_logs['award_points_on_previous_order'] ) && ! empty( $previous_order_logs['award_points_on_previous_order'] ) ) {

				$orders_points = array(
					'award_points_on_previous_order' => + $rewards_points,
					'date'                           => date_i18n( 'Y-m-d h:i:sa' ),
					'order_no'                       => $order_id,
				);
				$previous_order_logs['award_points_on_previous_order'][] = $orders_points;
			} else {

				$orders_points = array(
					'award_points_on_previous_order' => + $rewards_points,
					'date'                           => date_i18n( 'Y-m-d h:i:sa' ),
					'order_no'                       => $order_id,
				);
				$previous_order_logs['award_points_on_previous_order'][] = $orders_points;
			}
			update_user_meta( $user_id, 'points_details', $previous_order_logs );
		}
	}

	/** +++++++++++ Plugin Banner Notification ++++++++++++++ */

	/**
	 * This function is used to create crone for banner image.
	 *
	 * @return void
	 */
	public function wps_wpr_set_cron_for_plugin_banner_notification() {

		$wps_wpr_offset = get_option( 'gmt_offset' );
		$wps_wpr_time   = time() + $wps_wpr_offset * 60 * 60;
		if ( ! wp_next_scheduled( 'wps_wgm_check_for_notification_update' ) ) {

			wp_schedule_event( $wps_wpr_time, 'daily', 'wps_wgm_check_for_notification_update' );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function wps_wpr_save_banner_notice_message() {

		$wps_notification_data = $this->wps_wpr_get_update_banner_notification_data();
		if ( is_array( $wps_notification_data ) && ! empty( $wps_notification_data ) ) {

			$banner_id    = array_key_exists( 'notification_id', $wps_notification_data[0] ) ? $wps_notification_data[0]['wps_banner_id'] : '';
			$banner_image = array_key_exists( 'notification_message', $wps_notification_data[0] ) ? $wps_notification_data[0]['wps_banner_image'] : '';
			$banner_url   = array_key_exists( 'notification_message', $wps_notification_data[0] ) ? $wps_notification_data[0]['wps_banner_url'] : '';
			$banner_type  = array_key_exists( 'notification_message', $wps_notification_data[0] ) ? $wps_notification_data[0]['wps_banner_type'] : '';

			update_option( 'wps_wgm_notify_new_banner_id', $banner_id );
			update_option( 'wps_wgm_notify_new_banner_image', $banner_image );
			update_option( 'wps_wgm_notify_new_banner_url', $banner_url );

			if ( 'regular' == $banner_type ) {
				update_option( 'wps_wgm_notify_hide_baneer_notification', '' );
			}
		}
	}

	/**
	 * This function is used to get banner data from api.
	 *
	 * @return array
	 */
	public function wps_wpr_get_update_banner_notification_data() {
		$wps_notification_data = array();
		$url                   = 'https://demo.wpswings.com/client-notification/woo-gift-cards-lite/wps-client-notify.php';
		$attr                  = array(
			'action'         => 'wps_notification_fetch',
			'plugin_version' => REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION,
		);
		$query                 = esc_url_raw( add_query_arg( $attr, $url ) );
		$response              = wp_remote_get(
			$query,
			array(
				'timeout'   => 20,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo '<p><strong>' . esc_html__( 'Something went wrong: ', 'points-and-rewards-for-woocommerce' ) . esc_html( stripslashes( $error_message ) ) . '</strong></p>';
		} else {
			$wps_notification_data = json_decode( wp_remote_retrieve_body( $response ), true );
		}
		return $wps_notification_data;
	}

	/**
	 * Calling ajax to save banner id.
	 *
	 * @return void
	 */
	public function wps_wpr_dismiss_notice__banner_callback() {
		if ( isset( $_REQUEST['wps_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wps_nonce'] ) ), 'wps-wpr-verify-nonce' ) ) {

			$banner_id = get_option( 'wps_wgm_notify_new_banner_id', false );
			if ( isset( $banner_id ) && '' != $banner_id ) {

				update_option( 'wps_wgm_notify_hide_baneer_notification', $banner_id );
			}
			wp_send_json_success();
		}
		wp_die();
	}

	/*** +++++++ Membership Plugin Compatibility. ++++++++ */

	/**
	 * This function is used to create meta fields for PAR compatibility.
	 *
	 * @param array  $settings_fields settings_fields.
	 * @param object $instance instance.
	 * @param object $post post.
	 * @return void
	 */
	public function wps_wpr_membership_meta_fields( $settings_fields, $instance, $post ) {

		$wps_wpr_enable_points_settings          = wps_wpr_hpos_get_meta_data( $post->ID, 'wps_wpr_enable_points_settings', true );
		$wps_wpr_membership_assign_points_values = wps_wpr_hpos_get_meta_data( $post->ID, 'wps_wpr_membership_assign_points_values', true );
		?>
		<input type="hidden" name="wps_wpr_membership_compatible_nonce" value="<?php echo esc_html( wp_create_nonce( 'membership-compatible-nonce' ) ); ?>">
		<h2 class="wps_membership_offer_title">
			<?php esc_html_e( 'Points and Rewards Section', 'points-and-rewards-for-woocommerce' ); ?>
		</h2>
		<h3>
			<?php esc_html_e( 'You can assign points to user when this membership will be purchased by user.', 'points-and-rewards-for-woocommerce' ); ?>
		</h3>
		<h5>
			<?php esc_html_e( 'Note : Points should be assign only when status of membership is completed.', 'points-and-rewards-for-woocommerce' ); ?>
		</h5>
		<table>
			<tr>
				<th scope="row" class="titledesc">
					<label for="wps_wpr_enable_points_settings"><?php esc_html_e( 'Enable Points Settings', 'points-and-rewards-for-woocommerce' ); ?></label>
					<td>
						<?php
							$description = esc_html__( 'Please enable this settings to assign points to users.', 'points-and-rewards-for-woocommerce' );
							$instance->tool_tip( $description );
						?>
					</td>
				</th>
				<td id="mfw_free_shipping" class="forminp forminp-text">
					<input type="checkbox"  class="wps_wpr_enable_points_settings" name="wps_wpr_enable_points_settings" value="yes" <?php checked( 'yes', $wps_wpr_enable_points_settings ); ?>>
				</td>
			</tr>
			<tr>
				<th scope="row" class="titledesc">
					<label for="wps_wpr_membership_assign_points_values"><?php esc_html_e( 'Enter Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<td>
						<?php
							$description = esc_html__( 'Points should be assigned to the user when the order status is marked as completed.', 'points-and-rewards-for-woocommerce' );
							$instance->tool_tip( $description );
						?>
					</td>
				</th>
				<td id="mfw_free_shipping" class="forminp forminp-text">
					<input type="number" min="1" class="wps_wpr_membership_assign_points_values" name="wps_wpr_membership_assign_points_values" value="<?php echo esc_html( $wps_wpr_membership_assign_points_values ); ?>">
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * This function is used to save membership settings data.
	 *
	 * @param  string $post_id post_id.
	 * @return void
	 */
	public function wps_wpr_save_membership_fields( $post_id ) {

		// Return if doing autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Return if doing ajax :: Quick edits.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Return on post trash, quick-edit, new post.
		if ( empty( $_POST['action'] ) || 'editpost' != $_POST['action'] ) {
			return;
		}

		$wps_wpr_membership_compatible_nonce = ! empty( $_POST['wps_wpr_membership_compatible_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_membership_compatible_nonce'] ) ) : '';
		if ( wp_verify_nonce( $wps_wpr_membership_compatible_nonce, 'membership-compatible-nonce' ) ) {

			$wps_wpr_enable_points_settings          = ! empty( $_POST['wps_wpr_enable_points_settings'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_points_settings'] ) ) : 'no';
			$wps_wpr_membership_assign_points_values = ! empty( $_POST['wps_wpr_membership_assign_points_values'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_membership_assign_points_values'] ) ) : 0;
			wps_wpr_hpos_update_meta_data( $post_id, 'wps_wpr_enable_points_settings', $wps_wpr_enable_points_settings );
			wps_wpr_hpos_update_meta_data( $post_id, 'wps_wpr_membership_assign_points_values', $wps_wpr_membership_assign_points_values );
		}
	}

	/**
	 * This function is used to assign points when membership is purchased and completed.
	 *
	 * @param string $data data.
	 * @return void
	 */
	public function wps_wpr_assign_points_to_user_call( $data ) {

		$user_id = ! empty( $data['user_id'] ) ? sanitize_text_field( wp_unslash( $data['user_id'] ) ) : 0;
		if ( $user_id > 0 ) {

			$post_id       = ! empty( $data['post_id'] ) ? sanitize_text_field( wp_unslash( $data['post_id'] ) ) : 0;
			$member_status = ! empty( $data['member_status'] ) ? sanitize_text_field( wp_unslash( $data['member_status'] ) ) : '';
			$plan_id       = ! empty( $data['members_plan_assign'] ) ? sanitize_text_field( wp_unslash( $data['members_plan_assign'] ) ) : 0;
			if ( 'complete' === $member_status ) {
				if ( $plan_id > 0 ) {

					$get_points                              = ! empty( get_user_meta( $user_id, 'wps_wpr_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_points', true ) : 0;
					$mem_assign_points_log                   = get_user_meta( $user_id, 'points_details', true );
					$mem_assign_points_log                   = ! empty( $mem_assign_points_log ) && is_array( $mem_assign_points_log ) ? $mem_assign_points_log : array();
					$wps_wpr_enable_points_settings          = wps_wpr_hpos_get_meta_data( $plan_id, 'wps_wpr_enable_points_settings', true );
					$wps_wpr_membership_assign_points_values = ! empty( wps_wpr_hpos_get_meta_data( $plan_id, 'wps_wpr_membership_assign_points_values', true ) ) ? wps_wpr_hpos_get_meta_data( $plan_id, 'wps_wpr_membership_assign_points_values', true ) : 0;
					if ( 'yes' === $wps_wpr_enable_points_settings ) {

						$updated_points = (int) $get_points + $wps_wpr_membership_assign_points_values;
						if ( ! empty( $mem_assign_points_log['member_assign_rewards_points'] ) ) {

							$arr = array();
							$arr = array(
								'member_assign_rewards_points' => $wps_wpr_membership_assign_points_values,
								'date'                         => date_i18n( 'Y-m-d h:i:sa' ),
								'membership_name'              => get_the_title( $plan_id ),
							);
							$mem_assign_points_log['member_assign_rewards_points'][] = $arr;
						} else {

							$arr = array();
							$arr = array(
								'member_assign_rewards_points' => $wps_wpr_membership_assign_points_values,
								'date'                         => date_i18n( 'Y-m-d h:i:sa' ),
								'membership_name'              => get_the_title( $plan_id ),
							);
							$mem_assign_points_log['member_assign_rewards_points'][] = $arr;
						}
						update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
						update_user_meta( $user_id, 'points_details', $mem_assign_points_log );
						wps_wpr_hpos_update_meta_data( $post_id, 'wps_wpr_membership_plugin_assign_points_rewarded_done', $wps_wpr_membership_assign_points_values );
						wps_wpr_hpos_update_meta_data( $post_id, 'wps_wpr_assign_user_id', $user_id );
					}
				}
			}
		}
	}

	/**
	 * This function is used to assign points when membership is purchased and completed.
	 *
	 * @param string $post_id post id.
	 * @return void
	 */
	public function wps_wps_assign_points_member_edit_page( $post_id ) {

		// Return if doing autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Return if doing ajax.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Return on post trash, quick-edit, new post.
		if ( empty( $_POST['save'] ) ) {
			return;
		}

		// Nonce verification.
		check_admin_referer( 'wps_members_creation_nonce', 'wps_members_nonce_field' );

		$wps_wpr_assign_user_id = wps_wpr_hpos_get_meta_data( $post_id, 'wps_wpr_assign_user_id', true );
		$billing_email          = ! empty( $_POST['billing_email'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_email'] ) ) : '';
		if ( ! empty( $billing_email ) ) {
			$user_ob = get_user_by( 'email', $billing_email );
			$user_id = ! empty( $user_ob->ID ) ? $user_ob->ID : 0;
		} else {

			$user_id = $wps_wpr_assign_user_id;
		}

		if ( $user_id > 0 ) {

			$member_status = ! empty( $_POST['member_status'] ) ? sanitize_text_field( wp_unslash( $_POST['member_status'] ) ) : '';
			$plan_id       = ! empty( $_POST['members_plan_assign'] ) ? sanitize_text_field( wp_unslash( $_POST['members_plan_assign'] ) ) : 0;
			if ( 'complete' === $member_status ) {
				if ( $plan_id > 0 ) {

					$get_points                              = ! empty( get_user_meta( $user_id, 'wps_wpr_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_points', true ) : 0;
					$mem_assign_points_log                   = get_user_meta( $user_id, 'points_details', true );
					$mem_assign_points_log                   = ! empty( $mem_assign_points_log ) && is_array( $mem_assign_points_log ) ? $mem_assign_points_log : array();
					$wps_wpr_enable_points_settings          = wps_wpr_hpos_get_meta_data( $plan_id, 'wps_wpr_enable_points_settings', true );
					$wps_wpr_membership_assign_points_values = ! empty( wps_wpr_hpos_get_meta_data( $plan_id, 'wps_wpr_membership_assign_points_values', true ) ) ? wps_wpr_hpos_get_meta_data( $plan_id, 'wps_wpr_membership_assign_points_values', true ) : 0;
					if ( 'yes' === $wps_wpr_enable_points_settings ) {

						$updated_points = (int) $get_points + $wps_wpr_membership_assign_points_values;
						if ( ! empty( $mem_assign_points_log['member_assign_rewards_points'] ) ) {

							$arr = array();
							$arr = array(
								'member_assign_rewards_points' => $wps_wpr_membership_assign_points_values,
								'date'                         => date_i18n( 'Y-m-d h:i:sa' ),
								'membership_name'              => get_the_title( $plan_id ),
							);
							$mem_assign_points_log['member_assign_rewards_points'][] = $arr;
						} else {

							$arr = array();
							$arr = array(
								'member_assign_rewards_points' => $wps_wpr_membership_assign_points_values,
								'date'                         => date_i18n( 'Y-m-d h:i:sa' ),
								'membership_name'              => get_the_title( $plan_id ),
							);
							$mem_assign_points_log['member_assign_rewards_points'][] = $arr;
						}
						update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
						update_user_meta( $user_id, 'points_details', $mem_assign_points_log );
						wps_wpr_hpos_update_meta_data( $post_id, 'wps_wpr_membership_plugin_assign_points_rewarded_done', $wps_wpr_membership_assign_points_values );
					}
				}
			} elseif ( 'cancelled' === $member_status ) {
				if ( $plan_id > 0 ) {

					$wps_wpr_membership_plugin_assign_points_rewarded_done = wps_wpr_hpos_get_meta_data( $post_id, 'wps_wpr_membership_plugin_assign_points_rewarded_done', true );
					if ( $wps_wpr_membership_plugin_assign_points_rewarded_done > 0 ) {

						$get_points                              = ! empty( get_user_meta( $user_id, 'wps_wpr_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_points', true ) : 0;
						$mem_assign_points_refund_log            = get_user_meta( $user_id, 'points_details', true );
						$mem_assign_points_refund_log            = ! empty( $mem_assign_points_refund_log ) && is_array( $mem_assign_points_refund_log ) ? $mem_assign_points_refund_log : array();
						$wps_wpr_enable_points_settings          = wps_wpr_hpos_get_meta_data( $plan_id, 'wps_wpr_enable_points_settings', true );
						$wps_wpr_membership_assign_points_values = ! empty( wps_wpr_hpos_get_meta_data( $plan_id, 'wps_wpr_membership_assign_points_values', true ) ) ? wps_wpr_hpos_get_meta_data( $plan_id, 'wps_wpr_membership_assign_points_values', true ) : 0;
						if ( 'yes' === $wps_wpr_enable_points_settings ) {

							$updated_points = (int) $get_points - $wps_wpr_membership_assign_points_values;
							if ( ! empty( $mem_assign_points_refund_log['refund_member_assign_rewards_points'] ) ) {

								$arr = array();
								$arr = array(
									'refund_member_assign_rewards_points' => $wps_wpr_membership_assign_points_values,
									'date'                                => date_i18n( 'Y-m-d h:i:sa' ),
									'membership_name'                     => get_the_title( $plan_id ),
								);
								$mem_assign_points_refund_log['refund_member_assign_rewards_points'][] = $arr;
							} else {

								$arr = array();
								$arr = array(
									'refund_member_assign_rewards_points' => $wps_wpr_membership_assign_points_values,
									'date'                                => date_i18n( 'Y-m-d h:i:sa' ),
									'membership_name'                     => get_the_title( $plan_id ),
								);
								$mem_assign_points_refund_log['refund_member_assign_rewards_points'][] = $arr;
							}
							update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
							update_user_meta( $user_id, 'points_details', $mem_assign_points_refund_log );
						}
					}
				}
			}
		}
	}

	/** Migration functions */

	/**
	 * Undocumented function.
	 *
	 * @param  string $count count.
	 * @return bool
	 */
	public function wps_par_get_count( $count = '' ) {

		return 0;
	}

	/**
	 * Undocumented function.
	 *
	 * @param string $count count.
	 * @return bool
	 */
	public function wps_par_get_count_users( $count = '' ) {

		return 0;
	}

	/**
	 * Multivendor X plugin Compatibility.
	 */

	/**
	 * Add wallet for vendor module function.
	 *
	 * @param [type] $payment_mode is the payment method.
	 * @return mixed
	 */
	public function wsfw_admin_mvx_list_mxfdxfodules( $payment_mode ) {

		$payment_mode['par_payment'] = __( 'Points', 'multivendorx' );
		return $payment_mode;
	}

	/**
	 * Add status to order function
	 *
	 * @param [type] $payment_mode is the payment status.
	 * @return mixed
	 */
	public function wsfw_mvx_parent_order_to_vendor_order_statuses_to_sync( $payment_mode ) {

		$payment_mode = array( 'on-hold', 'pending', 'processing', 'cancelled', 'failed', 'completed' );
		return $payment_mode;
	}

	/**
	 * Add status to order function through multivendor
	 *
	 * @param [type] $order_id is the order id.
	 * @param [type] $old_status is the previous status.
	 * @param [type] $new_status is the new status.
	 * @return void
	 */
	public function wps_wpr_assign_vendor_commission_points( $order_id, $old_status, $new_status ) {

		if ( $old_status != $new_status ) {
			if ( wps_wpr_restrict_user_fun() ) {

				return;
			}

			// assigning payment method points.
			$this->wps_wpr_rewards_payment_method_points( $order_id, $old_status, $new_status );

			if ( 'completed' === $new_status || 'processing' === $new_status ) {

				if ( function_exists( 'mvx_get_order' ) ) {

					$is_vendor_order = ( $order_id ) ? mvx_get_order( $order_id ) : false;
					if ( $is_vendor_order ) {
						if ( class_exists( 'MVX_Commission' ) ) {

							$order                       = wc_get_order( $order_id );
							$wps_wpr_paid_status_through = wps_wpr_hpos_get_meta_data( $order_id, 'wps_wpr_paid_status_through', true );
							if ( empty( $wps_wpr_paid_status_through ) ) {

								$obj           = new MVX_Commission();
								$commission_id = wps_wpr_hpos_get_meta_data( $order_id, '_commission_id', true );
								if ( ! empty( $commission_id ) ) {

									$commission        = $obj->get_commission( $commission_id );
									$vendor            = $commission->vendor;
									$commission_status = get_post_meta( $commission_id, '_paid_status', true );
									$commission_amount = get_post_meta( $commission_id, '_commission_amount', true );
									$payment_method    = get_user_meta( $vendor->id, '_vendor_payment_mode', true );
									update_post_meta( $commission_id, '_paid_status', 'paid' );

									if ( empty( $commission_amount ) ) {
										return;
									}

									if ( 'par_payment' == $payment_method || 'points' == $payment_method ) {

										wps_wpr_hpos_update_meta_data( $order_id, 'wps_wpr_paid_status_through', 'wps_wpr_paid' );
										$wps_wpr_vendor_commission_amount_assigned = wps_wpr_hpos_get_meta_data( $order_id, 'wps_wpr_vendor_commission_amount_assigned', true );
										if ( empty( $wps_wpr_vendor_commission_amount_assigned ) ) {

											$get_points       = get_user_meta( $vendor->id, 'wps_wpr_points', true );
											$get_points       = empty( $get_points ) ? 0 : $get_points;
											$get_points       = $get_points + $commission_amount;
											$mem__refund_logs = get_user_meta( $vendor->id, 'points_details', true );
											$mem__refund_logs = ! empty( $mem__refund_logs ) && is_array( $mem__refund_logs ) ? $mem__refund_logs : array();
											if ( ! empty( $mem__refund_logs['wps_vendor_commissions_amount'] ) ) {

												$user_badges_arr                                       = array(
													'wps_vendor_commissions_amount' => $commission_amount,
													'date'                          => date_i18n( 'Y-m-d h:i:sa' ),
													'order_id'                      => $order_id,
												);
												$mem__refund_logs['wps_vendor_commissions_amount'][] = $user_badges_arr;
											} else {

												$user_badges_arr                                        = array(
													'wps_vendor_commissions_amount' => $commission_amount,
													'date'                          => date_i18n( 'Y-m-d h:i:sa' ),
													'order_id'                      => $order_id,
												);
												$mem__refund_logs['wps_vendor_commissions_amount'][] = $user_badges_arr;
											}

											update_user_meta( $vendor->id, 'wps_wpr_points', $get_points );
											update_user_meta( $vendor->id, 'points_details', $mem__refund_logs );
											wps_wpr_hpos_update_meta_data( $order_id, 'wps_wpr_vendor_commission_amount_assigned', 'done' );
											$obj->add_commission_note( $commission_id, __( 'Commission paid to vendor through points', 'multivendorx' ), $vendor->id );
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

	/**
	 * This function is used to restrict user from points table.
	 *
	 * @return void
	 */
	public function wps_wpr_restrict_user_from_points_table() {

		check_ajax_referer( 'wps-wpr-verify-nonce', 'wps_nonce' );
		$checked = ! empty( $_POST['checked'] ) ? sanitize_text_field( wp_unslash( $_POST['checked'] ) ) : 'no';
		$user_id = ! empty( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : 0;
		if ( 'yes' === $checked ) {

			update_user_meta( $user_id, 'wps_wpr_restrict_user', $checked );
		} else {

			update_user_meta( $user_id, 'wps_wpr_restrict_user', 'no' );
		}
		wp_die();
	}

	/**
	 * This function is used to give points according to the user selected payment method while placing the order.
	 *
	 * @param  int    $order_id    order id.
	 * @param  string $old_status old status.
	 * @param  string $new_status new status.
	 * @return bool
	 */
	public function wps_wpr_rewards_payment_method_points( $order_id, $old_status, $new_status ) {

		$wps_wpr_other_settings                  = get_option( 'wps_wpr_other_settings', array() );
		$wps_wpr_other_settings                  = ! empty( $wps_wpr_other_settings ) && is_array( $wps_wpr_other_settings ) ? $wps_wpr_other_settings : array();
		$wps_wpr_enable_payment_rewards_settings = ! empty( $wps_wpr_other_settings['wps_wpr_enable_payment_rewards_settings'] ) ? $wps_wpr_other_settings['wps_wpr_enable_payment_rewards_settings'] : 0;
		$wps_wpr_choose_payment_method           = ! empty( $wps_wpr_other_settings['wps_wpr_choose_payment_method'] ) ? $wps_wpr_other_settings['wps_wpr_choose_payment_method'] : '';
		$wps_wpr_payment_method_rewards_points   = ! empty( $wps_wpr_other_settings['wps_wpr_payment_method_rewards_points'] ) ? $wps_wpr_other_settings['wps_wpr_payment_method_rewards_points'] : 0;
		$wps_wpr_restrict_rewards_points         = ! empty( $wps_wpr_other_settings['wps_wpr_restrict_rewards_points'] ) ? $wps_wpr_other_settings['wps_wpr_restrict_rewards_points'] : '';

		// if restriction not to earn settings is enable than return from here.
		if ( 1 == $wps_wpr_restrict_rewards_points ) {

			return false;
		}

		if ( 1 === $wps_wpr_enable_payment_rewards_settings ) {

			$order   = wc_get_order( $order_id );
			$user_id = $order->get_user_id();
			// if guest user than return from here.
			if ( empty( $user_id ) ) {

				return;
			}

			if ( $order->get_payment_method() === $wps_wpr_choose_payment_method ) {
				// assign points when order is completed.
				if ( 'completed' === $new_status ) {

					$wps_wpr_payment_rewards_done = get_post_meta( $order_id, 'wps_wpr_payment_rewards_done', true );
					if ( empty( $wps_wpr_payment_rewards_done ) ) {

						$get_points              = get_user_meta( $user_id, 'wps_wpr_points', true );
						$get_points              = ! empty( $get_points ) ? $get_points : 0;
						$payment_rewards_details = get_user_meta( $user_id, 'points_details', true );
						$payment_rewards_details = ! empty( $payment_rewards_details ) && is_array( $payment_rewards_details ) ? $payment_rewards_details : array();
						$updated_points          = (int) $get_points + $wps_wpr_payment_method_rewards_points;

						if ( isset( $payment_rewards_details['payment_methods_points'] ) && ! empty( $payment_rewards_details['payment_methods_points'] ) ) {

							$arr = array(
								'date'                   => date_i18n( 'Y-m-d h:i:sa' ),
								'payment_methods_points' => $wps_wpr_payment_method_rewards_points,
							);
							$payment_rewards_details['payment_methods_points'][] = $arr;
						} else {

							$arr = array(
								'date'                   => date_i18n( 'Y-m-d h:i:sa' ),
								'payment_methods_points' => $wps_wpr_payment_method_rewards_points,
							);
							$payment_rewards_details['payment_methods_points'][] = $arr;
						}

						update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
						update_user_meta( $user_id, 'points_details', $payment_rewards_details );
						update_post_meta( $order_id, 'wps_wpr_payment_rewards_done', 'done' );
						update_post_meta( $order_id, 'wps_wpr_payment_method_rewards_points', $wps_wpr_payment_method_rewards_points );
					}
				}

				// refund points when order is cancelled or refunded.
				if ( 'completed' === $old_status && ( 'refunded' === $new_status || 'cancelled' === $new_status ) ) {

					$wps_wpr_payment_points_refunded = get_post_meta( $order_id, 'wps_wpr_payment_points_refunded', true );
					if ( empty( $wps_wpr_payment_points_refunded ) ) {

						$wps_wpr_payment_method_rewards_points = get_post_meta( $order_id, 'wps_wpr_payment_method_rewards_points', true );
						if ( $wps_wpr_payment_method_rewards_points > 0 ) {

							$user_points            = get_user_meta( $user_id, 'wps_wpr_points', true );
							$user_points            = ! empty( $user_points ) ? $user_points : 0;
							$payment_refund_details = get_user_meta( $user_id, 'points_details', true );
							$payment_refund_details = ! empty( $payment_refund_details ) && is_array( $payment_refund_details ) ? $payment_refund_details : array();
							$updated_points         = (int) $user_points - $wps_wpr_payment_method_rewards_points;

							if ( isset( $payment_refund_details['refund_payment_points_details'] ) && ! empty( $payment_refund_details['refund_payment_points_details'] ) ) {

								$arr = array(
									'date'                          => date_i18n( 'Y-m-d h:i:sa' ),
									'refund_payment_points_details' => $wps_wpr_payment_method_rewards_points,
								);
								$payment_refund_details['refund_payment_points_details'][] = $arr;
							} else {

								$arr = array(
									'date'                          => date_i18n( 'Y-m-d h:i:sa' ),
									'refund_payment_points_details' => $wps_wpr_payment_method_rewards_points,
								);
								$payment_refund_details['refund_payment_points_details'][] = $arr;
							}

							update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
							update_user_meta( $user_id, 'points_details', $payment_refund_details );
							update_post_meta( $order_id, 'wps_wpr_payment_points_refunded', 'done' );
						}
					}
				}
			}
		}
	}

	/**
	 * This function show import points html.
	 *
	 * @return void
	 */
	public function wps_wpr_add_additional_import_points() {
		?>
		<div class="wps_wpr_import_userspoints">
			<div class="wps_wpr_points_table_second_wrappers">
				<h3 class="wps_wpr_heading"><?php esc_html_e( 'Import', 'points-and-rewards-for-woocommerce' ); ?></h3>
				<table class="form-table wps_wpr_general_setting">
					<tbody>
						<tr valign="top">
							<td colspan="3" class="wps_wpr_instructions_tabledata">
								<h3><?php esc_html_e( 'Instructions', 'points-and-rewards-for-woocommerce' ); ?></h3>
								<p> 1 - <?php esc_html_e( 'To import user points. You need to choose a CSV file and click Import.', 'points-and-rewards-for-woocommerce' ); ?></p>
								<p> 2 - <?php esc_html_e( 'CSV for users points must have 3 columns in this order(User Email, Points, Reason Also the first row must have respective headings).', 'points-and-rewards-for-woocommerce' ); ?> </p>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Choose a CSV file:', 'points-and-rewards-for-woocommerce' ); ?>
							</th>
							<td>
								<input class="wps_wpr_csv_custom_userpoints_import" name="userpoints_csv_import" id="userpoints_csv_import" type="file" size="25" value="" aria-required="true" />

								<input type="hidden" value="134217728" name="max_file_size"><br>
								<small><?php esc_html_e( 'Maximum size:128 MB', 'points-and-rewards-for-woocommerce' ); ?></small>
							</td>
							<td>
								<a href="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>/uploads/wps_wpr_userpoints_sample.csv"><?php esc_html_e( 'Export Demo CSV', 'points-and-rewards-for-woocommerce' ); ?>
								<span class="wps_sample_export"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>/images/download.png"></span>
								</a>
							</td>
						</tr>
						<tr>
							<td>
								<p id="wps_import_content"><input type="submit" name="wps_wpr_csv_custom_userpoints_import" id="wps_wpr_csv_custom_userpoints_import" class="button-primary woocommerce-save-button wps_import" value="<?php esc_html_e( 'Import', 'points-and-rewards-for-woocommerce' ); ?>" /></p>
							</td>
							<?php
							$wps_active_plugin = get_plugins();
							$wps_active_plugin = ! empty( $wps_active_plugin ) && is_array( $wps_active_plugin ) ? $wps_active_plugin : array();
							if ( ! array_key_exists( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php', $wps_active_plugin ) ) {
								?>
								<td class="wps_wpr_pro_plugin_settings">
									<p class="wps_wpr_export_paragraph"><input type="button" id="wps_wpr_export_points_table_data" class="button-primary woocommerce-save-button wps_wpr_disabled_pro_plugin" value="<?php esc_html_e( 'Export', 'points-and-rewards-for-woocommerce' ); ?>" />
									<img class="wps_wpr_export_user_loader" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/loading.gif' ); ?>"></p>
								</td>
								<?php
							}
							?>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			$wps_active_plugin = get_plugins();
			$wps_active_plugin = ! empty( $wps_active_plugin ) && is_array( $wps_active_plugin ) ? $wps_active_plugin : array();
			if ( ! array_key_exists( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php', $wps_active_plugin ) ) {
				?>
				<div class="wps_wpr_points_table_second_wrappers wps_wpr_pro_plugin_settings">
					<h3 class="wps_wpr_heading"><?php esc_html_e( 'Reset Users Points', 'points-and-rewards-for-woocommerce' ); ?></h3>
					<table class="form-table wps_wpr_general_setting">
						<tbody>
							<tr valign="top">
								<td class="wps_wpr_instructions_tabledata">
									<p><?php esc_html_e( 'To Reset Points of all users in a single go, click on Reset Points Button.', 'points-and-rewards-for-woocommerce' ); ?></p>
									<p><?php esc_html_e( 'Please note that resetting the points will remove all existing points of user and assigned zero(0)', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
								<td class="wps_wpr_instructions_tabledata_btn">
									<p class="wps_wpr_reset_user_paragraph"><input type="button" id="wps_wpr_reset_user_points" class="button-primary woocommerce-save-button wps_wpr_disabled_pro_plugin" value="<?php esc_html_e( 'Reset Points', 'points-and-rewards-for-woocommerce' ); ?>" />
									<img class="wps_wpr_reset_user_loader" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/loading.gif' ); ?>"></p>
									<span class="wps_wpr_reset_user_notice"></span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php
			}
			?>
			<?php wp_nonce_field( 'wps_upload_csv', 'wps_wpr_nonce' ); ?>
		</div>
		<div class="wps_wpr_export_points_table_main_wrap">
			<div class="wps_wpr_export_shadow"></div>
			<div class="wps_wpr_export_points_table_in">
				<div class="wps_wpr_export_close">&times;</div>
				<h4><?php esc_html_e( 'Please choose an option : ', 'points-and-rewards-for-woocommerce' ); ?></h4>
				<div class="wps_wpr_export_points_table_options">
					<label for="wps_wpr_add_points"><input type="radio" id="wps_wpr_add_points" name="wps_wpr_export_table_option" class="wps_wpr_export_table_option" value="add"><?php esc_html_e( 'Add Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<label for="wps_wpr_remove_points"><input type="radio" id="wps_wpr_remove_points" name="wps_wpr_export_table_option" class="wps_wpr_export_table_option" value="subtract"><?php esc_html_e( 'Substract Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<label for="wps_wpr_overrirde_points"><input type="radio" id="wps_wpr_overrirde_points" name="wps_wpr_export_table_option" class="wps_wpr_export_table_option" value="override"><?php esc_html_e( 'Override Points', 'points-and-rewards-for-woocommerce' ); ?></label>
				</div>
				<div class="wps_wpr_confirm_import_option_wrap">
					<input type="button" id="wps_wpr_confirm_import_option" class="button-primary woocommerce-save-button" value="<?php esc_html_e( 'Proceed', 'points-and-rewards-for-woocommerce' ); ?>">
				</div>
				<p class="wps_wpr_radion_button_notice"></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Import user points via csv file using recursive ajax method.
	 *
	 * @return void
	 */
	public function wps_large_scv_import() {
		check_ajax_referer( 'wps-wpr-verify-nonce', 'wps_nonce' );

		$start          = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( intval( $_POST['start'] ) ) ) : 0;
		$chunk_size     = 1000; // Adjust chunk size as needed.
		$temp_file_path = ! empty( $_FILES['userpoints_csv_import']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['userpoints_csv_import']['tmp_name'] ) ) : '';
		$file_path      = ! empty( $_FILES['userpoints_csv_import']['name'] ) ? sanitize_text_field( wp_unslash( $_FILES['userpoints_csv_import']['name'] ) ) : '';

		if ( empty( $temp_file_path ) || empty( $file_path ) ) {
			wp_send_json(
				array(
					'result' => false,
					'msg'    => esc_html__( 'File path missing.', 'points-and-rewards-for-woocommerce' ),
				)
			);
			wp_die();
		}

		if ( strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) ) !== 'csv' ) {
			wp_send_json(
				array(
					'result' => false,
					'msg'    => esc_html__( 'Please choose a CSV file.', 'points-and-rewards-for-woocommerce' ),
				)
			);
			wp_die();
		}

		$imp_counter = 0;
		$success     = $this->wps_file_get_contents_chunked(
			$temp_file_path,
			$start,
			$chunk_size,
			function( $chunk, &$handle, $iteration ) use ( &$imp_counter ) {
				if ( 0 != $imp_counter ) {
					$this->wps_update_points_of_users( $chunk[0], $chunk[1], $chunk[2] );
				}
				$imp_counter = 1;
			}
		);

		if ( true === $success ) {

			$total_lines = count( file( $temp_file_path ) );
			$progress    = min( 100, round( ( $start + $chunk_size ) / $total_lines * 100 ) );

			wp_send_json(
				array(
					'progress' => $progress,
					'start'    => $start + $chunk_size,
					'finished' => ( $start + $chunk_size ) >= $total_lines,
					'msg'      => 'Processing chunk: ' . $start,
				)
			);
		} else {
			wp_send_json(
				array(
					'result' => true,
					'msg'    => 'Error processing file.',
				)
			);
		}
		wp_die();
	}

	/**
	 * This function is used to get write on csv file.
	 *
	 * @param  string $file file.
	 * @param  int    $start start.
	 * @param  array  $chunk_size chunk size.
	 * @param  string $callback callback.
	 * @return bool
	 */
	public function wps_file_get_contents_chunked( $file, $start, $chunk_size, $callback ) {
		try {
			$handle = fopen( $file, 'r' );
			$i      = 0;
			// Move to the start position.
			while ( $i < $start && ! feof( $handle ) ) {
				fgetcsv( $handle );
				$i++;
			}

			$chunk = 0;
			while ( $chunk < $chunk_size && ! feof( $handle ) ) {
				call_user_func_array( $callback, array( fgetcsv( $handle ), &$handle, $i ) );
				$i++;
				$chunk++;
			}

			fclose( $handle );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
		return true;
	}

	/**
	 * This function is used to update user points while importing csv file.
	 *
	 * @param  string $wps_user_email wps_user_email.
	 * @param  int    $wps_user_points wps_user_points.
	 * @param  string $import_points_reason import_points_reason.
	 * @return bool
	 */
	public function wps_update_points_of_users( $wps_user_email, $wps_user_points, $import_points_reason ) {
		check_ajax_referer( 'wps-wpr-verify-nonce', 'wps_nonce' );
		$user                        = get_user_by( 'email', $wps_user_email );
		$wps_wpr_export_table_option = ! empty( $_POST['wps_wpr_export_table_option'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_export_table_option'] ) ) : 'add';
		if ( isset( $user ) ) {

			$user_id         = $user->ID;
			$get_user_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			$get_user_points = ! empty( $get_user_points ) ? (int) $get_user_points : 0;
			$wps_user_points = ! empty( $wps_user_points ) ? $wps_user_points : 0;
			$admin_points    = get_user_meta( $user_id, 'points_details', true );
			$admin_points    = ! empty( $admin_points ) && is_array( $admin_points ) ? $admin_points : array();
			$today_date      = date_i18n( 'Y-m-d h:i:sa' );

			// calculate points according to import option.
			$wps_update_csv_points = 0;
			$sign                  = '+';
			if ( 'add' === $wps_wpr_export_table_option ) {

				$wps_wpr_reason        = $import_points_reason;
				$sign                  = '+';
				$wps_update_csv_points = $get_user_points + (int) $wps_user_points;
			} elseif ( 'subtract' === $wps_wpr_export_table_option ) {

				$wps_wpr_reason        = $import_points_reason;
				$sign                  = '-';
				$wps_update_csv_points = $get_user_points - (int) $wps_user_points;
			} elseif ( 'override' === $wps_wpr_export_table_option ) {

				// translators: %s: get_user_points.
				$wps_wpr_reason        = $import_points_reason;
				$wps_update_csv_points = (int) $wps_user_points;
			}

			if ( isset( $wps_user_points ) && ! empty( $wps_user_points ) ) {
				if ( isset( $admin_points['admin_points'] ) && ! empty( $admin_points['admin_points'] ) ) {

					$admin_array = array(
						'admin_points' => $wps_user_points,
						'date'         => $today_date,
						'sign'         => $sign,
						'reason'       => $wps_wpr_reason,
					);
					$admin_points['admin_points'][] = $admin_array;
				} else {

					$admin_array = array(
						'admin_points' => $wps_user_points,
						'date'         => $today_date,
						'sign'         => $sign,
						'reason'       => $wps_wpr_reason,
					);
					$admin_points['admin_points'][] = $admin_array;
				}
				update_user_meta( $user_id, 'points_details', $admin_points );
				update_user_meta( $user_id, 'wps_wpr_points', $wps_update_csv_points );
			}
		}
		return true;
	}

}
