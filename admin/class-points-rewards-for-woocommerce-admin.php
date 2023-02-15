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
		if ( 'woocommerce_page_wps-rwpr-setting' == $hook || ( isset( $pagescreen ) && 'plugins' === $pagescreen ) ) {
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
			$pagescreen = $screen->id;

			if ( isset( $_GET['page'] ) && 'wps-rwpr-setting' == $_GET['page'] || 'product' == $pagescreen ) {

				wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
				wp_enqueue_style( 'woocommerce_admin_menu_styles' );
				wp_enqueue_style( 'woocommerce_admin_styles' );
				wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
				// Sticky-JS.
				wp_enqueue_script( 'sticky_js', WPS_RWPR_DIR_URL . '/admin/js/jquery.sticky-sidebar.min.js', array( 'jquery' ), WC_VERSION, true );
				wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.js', array( 'jquery' ), WC_VERSION, true );
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
					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
					'validpoint'         => __( 'Please enter a valid points', 'points-and-rewards-for-woocommerce' ),
					'Labelname'          => __( 'Enter the Name of the Level', 'points-and-rewards-for-woocommerce' ),
					'Labeltext'          => __( 'Enter Level', 'points-and-rewards-for-woocommerce' ),
					'Points'             => __( 'Enter Points', 'points-and-rewards-for-woocommerce' ),
					'Categ_text'         => __( 'Select Product Category', 'points-and-rewards-for-woocommerce' ),
					'Remove_text'        => __( 'Remove', 'points-and-rewards-for-woocommerce' ),
					'Categ_option'       => $option_categ,
					'Prod_text'          => __( 'Select Product', 'points-and-rewards-for-woocommerce' ),
					'Discounttext'       => __( 'Enter Discount (%)', 'points-and-rewards-for-woocommerce' ),
					'error_notice'       => __( 'Fields cannot be empty', 'points-and-rewards-for-woocommerce' ),
					'LevelName_notice'   => __( 'Please Enter the Name of the Level', 'points-and-rewards-for-woocommerce' ),
					'LevelValue_notice'  => __( 'Please Enter valid Points', 'points-and-rewards-for-woocommerce' ),
					'CategValue_notice'  => __( 'Please select a category', 'points-and-rewards-for-woocommerce' ),
					'ProdValue_notice'   => __( 'Please select a product', 'points-and-rewards-for-woocommerce' ),
					'Discount_notice'    => __( 'Please enter a valid discount', 'points-and-rewards-for-woocommerce' ),
					'success_assign'     => __( 'Points are assigned successfully!', 'points-and-rewards-for-woocommerce' ),
					'error_assign'       => __( 'Enter Some Valid Points!', 'points-and-rewards-for-woocommerce' ),
					'success_remove'     => __( 'Points are removed successfully!', 'points-and-rewards-for-woocommerce' ),
					'Days'               => __( 'Days', 'points-and-rewards-for-woocommerce' ),
					'Weeks'              => __( 'Weeks', 'points-and-rewards-for-woocommerce' ),
					'Months'             => __( 'Months', 'points-and-rewards-for-woocommerce' ),
					'Years'              => __( 'Years', 'points-and-rewards-for-woocommerce' ),
					'Exp_period'         => __( 'Expiration Period', 'points-and-rewards-for-woocommerce' ),
					'wps_wpr_url'        => $url,
					'reason'             => __( 'Please enter Remark', 'points-and-rewards-for-woocommerce' ),
					'wps_wpr_nonce'      => wp_create_nonce( 'wps-wpr-verify-nonce' ),
					'check_pro_activate' => ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ),
					'pro_text'           => __( 'Please purchase the pro plugin to add multiple memberships.', 'points-and-rewards-for-woocommerce' ),
					'pro_link_text'      => __( 'Click here', 'points-and-rewards-for-woocommerce' ),
					'pro_link'           => 'https://wpswings.com/product/points-and-rewards-for-woocommerce-pro/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro',
					'success_update'     => __( 'Points are updated successfully', 'points-and-rewards-for-woocommerce' ),
					'support_confirm'     => __( 'Email sent successfully', 'points-and-rewards-for-woocommerce' ),
					'negative'          => __( 'Negative Values Not Allowed', 'points-and-rewards-for-woocommerce' ),
				);

				wp_enqueue_script( $this->plugin_name . 'admin-js', WPS_RWPR_DIR_URL . 'admin/js/points-rewards-for-woocommerce-admin.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'select2', 'sticky_js' ), $this->version, false );
				wp_localize_script( $this->plugin_name . 'admin-js', 'wps_wpr_object', $wps_wpr );
			}
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param  string $hook       The name of page.
	 * @since    1.0.0
	 */
	public function swal_enqueue_scripts( $hook ) {

		$screen = get_current_screen();
		if ( isset( $screen->id ) ) {

			if ( isset( $_GET['page'] ) && 'wps-rwpr-setting' == $_GET['page'] ) {

				wp_enqueue_script( 'wps_my_par_custom_js', plugin_dir_url( __FILE__ ) . 'js/points-rewards-for-woocommerce-admin-swal.js', array( 'jquery' ), $this->version, false );
				wp_enqueue_script( $this->plugin_name . '-swal', plugin_dir_url( __FILE__ ) . 'js/swal.js', array( 'jquery' ), $this->version, false );
				wp_enqueue_script( $this->plugin_name . '-swall', plugin_dir_url( __FILE__ ) . 'js/swall.js', array( 'jquery' ), $this->version, false );
				wp_localize_script(
					'wps_my_par_custom_js',
					'localised',
					array(
						'ajaxurl'               => admin_url( 'admin-ajax.php' ),
						'nonce'                 => wp_create_nonce( 'wps-wpr-verify-nonce' ),
						'callback'              => 'ajax_callbacks',
						'pending_count'         => ! empty( $this->wps_par_get_count( 'pending' ) ) && is_array( $this->wps_par_get_count( 'pending' ) ) ? $this->wps_par_get_count( 'pending' ) : 0,
						'pending_orders'        => $this->wps_par_get_count( 'pending', 'orders' ),
						'completed_orders'      => $this->wps_par_get_count( 'done', 'orders' ),
						'completed_users'       => ! empty( $this->wps_par_get_count_users( 'users' ) ) && is_array( $this->wps_par_get_count_users( 'users' ) ) ? $this->wps_par_get_count_users( 'users' ) : 0,
						'completed_users_count' => ! empty( $this->wps_par_get_count_users( 'users' ) ) && is_array( $this->wps_par_get_count_users( 'users' ) ) ? count( $this->wps_par_get_count_users( 'users' ) ) : 0,
					)
				);
			}
		}
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
		check_ajax_referer( 'wps-wpr-verify-nonce', 'wps_nonce' );
		if ( isset( $_POST['points'] ) && is_numeric( $_POST['points'] ) && isset( $_POST['user_id'] ) && isset( $_POST['sign'] ) && isset( $_POST['reason'] ) ) {

			$user_id = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
			/* Get the user points*/
			$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			/* Get the Input Values*/
			$points = sanitize_text_field( wp_unslash( $_POST['points'] ) );
			$sign   = sanitize_text_field( wp_unslash( $_POST['sign'] ) );
			$reason = sanitize_text_field( wp_unslash( $_POST['reason'] ) );
			/* calculate users points*/
			if ( '+' === $sign ) {
				$total_points = $get_points + $points;
			} elseif ( '-' === $sign ) {
				if ( $points <= $get_points ) {
					$total_points = $get_points - $points;
				} else {
					$points = $get_points;
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
						<option value="days"
						<?php
						if ( isset( $value['Exp_Days'] ) ) {
							if ( 'days' == $value['Exp_Days'] ) {
								?>
							selected="selected"
								<?php
							}
						}
						?>
						>
						<?php esc_html_e( 'Days', 'points-and-rewards-for-woocommerce' ); ?>
						</option>
						<option value="weeks"
						<?php
						if ( isset( $value['Exp_Days'] ) ) {
							if ( 'weeks' == $value['Exp_Days'] ) {
								?>
							selected="selected"
								<?php
							}
						}
						?>
						><?php esc_html_e( 'Weeks', 'points-and-rewards-for-woocommerce' ); ?>
						</option>
						<option value="months"
						<?php
						if ( isset( $value['Exp_Days'] ) ) {
							if ( 'months' == $value['Exp_Days'] ) {
								?>
							selected="selected"
								<?php
							}
						}
						?>
						><?php esc_html_e( 'Months', 'points-and-rewards-for-woocommerce' ); ?>
						</option>
						<option value="years"
						<?php
						if ( isset( $value['Exp_Days'] ) ) {
							if ( 'years' == $value['Exp_Days'] ) {
								?>
							selected="selected"
								<?php
							}
						}
						?>
						><?php esc_html_e( 'Years', 'points-and-rewards-for-woocommerce' ); ?>
						</option>	
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
						<input type="number" min="1" value="<?php echo esc_html( $this->check_is_not_empty( isset( $value['Discount'] ) ? $value['Discount'] : '' ) ); ?>" name="wps_wpr_membership_discount_<?php echo esc_html( $count ); ?>" id="wps_wpr_membership_discount_<?php echo esc_html( $count ); ?>" class="input-text" required>
						</label>			
					</td>
					<input type = "hidden" value="<?php echo esc_html( $count ); ?>" name="hidden_count">
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
					<input type="text" name="wps_wpr_thankyouorder_minimum[]" class="wps_wpr_thankyouorder_minimum input-text wc_input_price"  placeholder = "No minimum"  value="<?php echo ( ! empty( $thankyouorder_min[ $key ] ) ) ? esc_html( $thankyouorder_min[ $key ] ) : ''; ?>">
				</label>
			</td>
			<td class="forminp forminp-text">
				<label for="wps_wpr_thankyouorder_maximum">
					<input type="text" name="wps_wpr_thankyouorder_maximum[]" class="wps_wpr_thankyouorder_maximum"  placeholder = "No maximum"  value="<?php echo ( ! empty( $thankyouorder_max[ $key ] ) ) ? esc_html( $thankyouorder_max[ $key ] ) : ''; ?>" required>
				</label>
			</td>
			<td class="forminp forminp-text">
				<label for="wps_wpr_thankyouorder_current_type">
					<input type="text" name="wps_wpr_thankyouorder_current_type[]" class="wps_wpr_thankyouorder_current_type input-text wc_input_price"  value="<?php echo ( ! empty( $thankyouorder_value[ $key ] ) ) ? esc_html( $thankyouorder_value[ $key ] ) : ''; ?>">
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
	 * @since 1.0.7
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_display_notification_bar() {
		$screen = get_current_screen();
		if ( isset( $screen->id ) ) {

			$pagescreen = $screen->id;
			if ( isset( $_GET['page'] ) && 'wps-rwpr-setting' == $_GET['page'] || 'product' == $pagescreen ) {

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
					'title'    => __( 'Enable Wallet points setting', 'points-and-rewards-for-woocommerce' ),
					'type'     => 'checkbox',
					'desc'     => __( 'Enable Wallet points setting', 'points-and-rewards-for-woocommerce' ),
					'id'       => 'wps_wpr_general_setting_wallet_enablee',
					'desc_tip' => __( 'Check this box to enable points conversion to the amount on the wallet.', 'points-and-rewards-for-woocommerce' ),
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
	 * Undocumented function
	 *
	 * @return void
	 */
	public function wpswing_migrate_code() {
		wps_wpr_convert_db_keys();
	}

	/**
	 * Order count.
	 *
	 * @param string $type type.
	 * @param string $action action.
	 * @since    1.0.0
	 */
	public function wps_par_get_count( $type = 'all', $action = 'count' ) {
		switch ( $type ) {
			case 'pending':
				$product_meta_array = array(
					'mwb_product_points_enable',
					'mwb_points_product_value',
					'mwb_product_purchase_points_only',
					'mwb_points_product_purchase_value',
					'mwb_product_purchase_through_point_disable',
					'mwb_wpr_variable_points',
					'mwb_wpr_variable_points_purchase',
					'mwb_wpr_points_coupon',
				);

				$order_meta_array = array(
					'mwb_cart_discount#$fee_id',
					'mwb_cart_discount#points',
				);

				$result = get_posts(
					array(
						'post_type'   => 'shop_order',
						'meta_key'    => $order_meta_array, // phpcs:ignore
						'post_status' => array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ),
						'numberposts' => -1,
						'fields'      => 'ids',
					)
				);

				$simple_product = get_posts(
					array(
						'post_type'   => 'product',
						'meta_key'    => $product_meta_array, // phpcs:ignore
						'post_status' => array( 'publish', 'draft', 'trash', 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ),
						'numberposts' => -1,
						'fields'      => 'ids',
					)
				);

				$variable_product = get_posts(
					array(
						'post_type'   => 'product_variation',
						'post_status' => array( 'publish', 'draft', 'trash', 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ),
						'numberposts' => -1,
						'meta_key'    => $product_meta_array, // phpcs:ignore
						'fields'      => 'ids',
					)
				);

				if ( ! empty( $result ) && is_array( $result ) && ! empty( $simple_product ) && is_array( $simple_product ) ) {
					$result = array_merge( $result, $simple_product );
				}
				if ( ! empty( $result ) && is_array( $result ) && ! empty( $variable_product ) && is_array( $variable_product ) ) {
					$result = array_merge( $result, $variable_product );
				}
				break;
			default:
				$result = false;
				break;
		}

		if ( empty( $result ) && ! is_array( $result ) ) {
			return array();
		}

		if ( 'count' === $action ) {
			$result = ! empty( $result ) ? count( $result ) : 0;
		}
		return $result;
	}

	/**
	 * Ajax Call back.
	 */
	public function ajax_callbacks() {

		check_ajax_referer( 'wps-wpr-verify-nonce', 'nonce' );
		$event = ! empty( $_POST['event'] ) ? sanitize_text_field( wp_unslash( $_POST['event'] ) ) : '';
		if ( method_exists( $this, $event ) ) {
			$data = $this->$event( $_POST );
		} else {
			$data = esc_html__( 'method not found', 'one-click-upsell-addon' );
		}
		echo wp_json_encode( $data );
		wp_die();
	}

	/**
	 * Import order callback.
	 *
	 * @param array $posted_data The $_POST data.
	 */
	public function import_single_order( $posted_data = array() ) {

		$orders = ! empty( $posted_data['orders'] ) ? $posted_data['orders'] : array();
		if ( empty( $orders ) ) {
			return array();
		}

		// Remove this order from request.
		foreach ( $orders as $key => $order ) {

			$order_id = ! empty( $order ) ? $order : false;
			unset( $orders[ $key ] );
			break;
		}
		// Attempt for one order.
		if ( ! empty( $order_id ) ) {
			$user_post_meta_keys = array(
				'mwb_cart_discount#$fee_id',
				'mwb_cart_discount#points',
				'mwb_product_points_enable',
				'mwb_points_product_value',
				'mwb_wpr_points_coupon',
				'mwb_product_purchase_points_only',
				'mwb_points_product_purchase_value',
				'mwb_product_purchase_through_point_disable',
				'mwb_wpr_variable_points',
				'mwb_wpr_variable_points_purchase',
			);
			$user_post_meta_keys = apply_filters( 'wps_userpost_meta_keys_pro', $user_post_meta_keys );
			foreach ( $user_post_meta_keys as $index => $meta_key ) {

				$new_key    = str_replace( 'mwb_', 'wps_', $meta_key );
				$meta_value = get_post_meta( $order_id, $meta_key, true );
				if ( ! empty( $meta_value ) || '0' === $meta_value ) {

					update_post_meta( $order_id, $new_key, $meta_value );
					update_user_meta( $order_id, 'copy_' . $meta_key, $meta_value );
					delete_post_meta( $order_id, $meta_key );
				} else {
					delete_post_meta( $order_id, $meta_key );
				}
			}
		}
		return compact( 'orders' );
	}

	/**
	 * Import_users_wps function
	 *
	 * @param array $posted_data for posted array.
	 * @return users
	 */
	public function import_users_wps( $posted_data = array() ) {
		$users = ! empty( $posted_data['users'] ) ? $posted_data['users'] : array();
		if ( empty( $users ) ) {
			return array();
		}
		foreach ( $users as $key => $user ) {
			$user_id = ! empty( $user['ID'] ) ? $user['ID'] : false;
			unset( $users[ $key ] );
			break;
		}
		if ( ! empty( $user_id ) ) {

			$user_meta_keys = array(
				'mwb_points_referral',
				'mwb_points_referral_invite',
				'mwb_wpr_points',
				'mwb_wpr_no_of_orders',
				'mwb_wpr_user_log',
				'mwb_wpr_points_expiration_date',
				'mwb_wpr_birthday_points_year',
			);
			foreach ( $user_meta_keys as $index => $meta_key ) {
						$new_key    = str_replace( 'mwb_', 'wps_', $meta_key );
						$meta_value = get_user_meta( $user_id, $meta_key, true );
				if ( ! empty( $meta_value ) || '0' === $meta_value ) {
					update_user_meta( $user_id, $new_key, $meta_value );
					update_user_meta( $user_id, 'copy_' . $meta_key, $meta_value );
					delete_user_meta( $user_id, $meta_key );
				} else {
					delete_user_meta( $user_id, $meta_key );
				}
			}
		}
		return compact( 'users' );
	}

	/**
	 * Migration to new domain notice.
	 */
	public function wps_wpr_updgrade_notice() {
		$screen = get_current_screen();

		if ( isset( $screen->id ) ) {
			$pagescreen = $screen->id;

			if ( 'woocommerce_page_wps-rwpr-setting' !== $pagescreen ) {
				return;
			}
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$tab                = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$wps__order_count   = $this->wps_par_get_count( 'pending' );
		$wps__order_count   = ! empty( $wps__order_count ) && is_array( $wps__order_count ) ? count( $wps__order_count ) : 0;
		$wps_meta_key_count = $this->wps_par_get_count_users( 'users' );
		$wps_meta_key_count = ! empty( $wps_meta_key_count ) && is_array( $wps_meta_key_count ) ? count( $wps_meta_key_count ) : 0;
		if ( 'wps-rwpr-setting' === $tab && ( 0 !== $wps__order_count ) || ( 0 !== $wps_meta_key_count ) ) {
			?>

		<tr class="plugin-update-tr active notice-warning notice-alt">
			<td colspan="4" class="plugin-update colspanchange">
				<div class="notice notice-error inline update-message notice-alt">
					<div class='wps-notice-title wps-notice-section'>
						<p><strong>IMPORTANT NOTICE:</strong></p>
					</div>
					<div class='wps-notice-content wps-notice-section'>
						<p>The latest  Update includes some substantial changes across different areas of plugin<strong> Please Migrate your data by clicking on Start Migration<strong></p>
					</div>
					<div class="treat-wrapper">
						<button class="treat-button">Start Migration!</button>
					</div>
				</div>
			</td>
		</tr>
		<style>
			.treat-button {
			font-family: "Fascinate Inline", cursive;
			-webkit-appearance: none;
				-moz-appearance: none;
					appearance: none;
			background: linear-gradient(to bottom, #F46001, #E14802);
			border: none;
			color: #FFF;
			border-radius: 2em;
			padding: 0.6em 1.5em;
			width: 200px;
			font-size: 20px;
			overflow: hidden;
			-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none;
					user-select: none;
			cursor: pointer;
			z-index: 1;
			box-shadow: 0 0 1em rgba(255, 255, 255, 0.2);
			transition: transform 0.1s cubic-bezier(0.5, 0, 0.5, 1), box-shadow 0.2s;
			outline: none;
			}
			.treat-button:hover {
			box-shadow: 0 0 2em rgba(255, 255, 255, 0.3);
			}
			.wps-notice-section > p:before {
				content: none;
			}
		</style>
			<?php
		}
	}

	/**
	 * Wps_par_get_count_users function
	 *
	 * @param string $type for type.
	 * @param string $action for action .
	 * @return $result
	 */
	public function wps_par_get_count_users( $type = 'all', $action = 'count' ) {
		switch ( $type ) {
			case 'users':
				$user_array = array(
					'mwb_points_referral',
					'mwb_points_referral_invite',
					'mwb_wpr_points',
					'mwb_wpr_no_of_orders',
					'mwb_wpr_user_log',
					'mwb_wpr_points_expiration_date',
					'mwb_wpr_birthday_points_year',
				);
				$result = get_users(
					array(
						'limit'    => - 1,
						'meta_key' => $user_array, // phpcs:ignore
						'fields'   => array( 'ID' ),
					)
				);
				break;
			default:
				$result = false;
				break;
		}
		if ( empty( $result ) ) {
			return array();
		}
		return $result;
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
					'title'    => __( 'Enable Renewal Subscription Point Settings', 'points-and-rewards-for-woocommerce' ),
					'type'     => 'checkbox',
					'desc'     => __( 'Enable this setting to give points when subscription is renewal', 'points-and-rewards-for-woocommerce' ),
					'id'       => 'wps_wpr_enable_subscription_renewal_settings',
					'desc_tip' => __( 'Check this box to give points only when subscription is renewal', 'points-and-rewards-for-woocommerce' ),
					'default'  => 0,
				),
				array(
					'title'             => __( 'Enter Subscription Renewal Points', 'points-and-rewards-for-woocommerce' ),
					'type'              => 'number',
					'default'           => 1,
					'id'                => 'wps_wpr_subscription__renewal_points',
					'custom_attributes' => array( 'min' => '"1"' ),
					'class'             => 'input-text wps_wpr_new_woo_ver_style_text',
					'desc_tip'          => __( 'Entered Points will be awarded to user when subscription is renewal', 'points-and-rewards-for-woocommerce' ),
				),
				array(
					'title'    => __( 'Enable to show message on Account Page', 'points-and-rewards-for-woocommerce' ),
					'type'     => 'checkbox',
					'desc'     => __( 'Enable this setting to show message on account page for user acknowledge', 'points-and-rewards-for-woocommerce' ),
					'id'       => 'wps_wpr_enable__renewal_message_settings',
					'desc_tip' => __( 'Check this box to show message on account page for user to know how much he/she will earn', 'points-and-rewards-for-woocommerce' ),
					'default'  => 0,
				),
				array(
					'title'    => __( 'Enter Renewal Message', 'points-and-rewards-for-woocommerce' ),
					'type'     => 'text',
					'id'       => 'wps_wpr_subscription__renewal_message',
					'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
					'desc'     => __( 'Entered message will shown on the user Account Page. Please enter message including [Points] this shortcode.', 'points-and-rewards-for-woocommerce' ),
					'desc_tip' => __( 'Please enter some message for user to know about renewal points', 'points-and-rewards-for-woocommerce' ),
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
	 * This function is used to awarded user with points when order is renewal.You will earn [Points] points when your subscription should be renewal.
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
				$wps_wpr_renewal_points_awarded = get_post_meta( $order_id, 'wps_wpr_renewal_points_awarded', true );

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
						update_post_meta( $order_id, 'wps_wpr_renewal_points_awarded', 'done' );
						update_post_meta( $order_id, 'wps_wpr_subscription_renewal_awarded_points', $wps_wpr_subscription__renewal_points );

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
}
