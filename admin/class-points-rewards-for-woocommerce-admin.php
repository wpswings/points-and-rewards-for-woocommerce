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
 * @author     makewebbetter <webmaster@makewebbetter.com>
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
		if ( 'woocommerce_page_mwb-rwpr-setting' == $hook ) {
			wp_enqueue_style( $this->plugin_name, MWB_RWPR_DIR_URL . 'admin/css/points-rewards-for-woocommerce-admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'select2' );
		}
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

			if ( isset( $_GET['page'] ) && 'mwb-rwpr-setting' == $_GET['page'] || 'product' == $pagescreen ) {
				wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
				wp_enqueue_style( 'woocommerce_admin_menu_styles' );
				wp_enqueue_style( 'woocommerce_admin_styles' );
				wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
				// Sticky-JS.
				wp_enqueue_script( 'sticky_js', MWB_RWPR_DIR_URL . '/admin/js/jquery.sticky-sidebar.min.js', array( 'jquery' ), WC_VERSION, true );
				wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.js', array( 'jquery' ), WC_VERSION, true );
				$locale  = localeconv();
				$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
				$params  = array(
					/* translators: %s: decimal */
					'i18n_decimal_error'               => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'points-rewards-for-woocommerce' ), $decimal ),
					/* translators: %s: price decimal separator */
					'i18n_mon_decimal_error'           => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'points-rewards-for-woocommerce' ), wc_get_price_decimal_separator() ),
					'i18n_country_iso_error'           => __( 'Please enter in country code with two capital letters.', 'points-rewards-for-woocommerce' ),
					'i18_sale_less_than_regular_error' => __( 'Please enter in a value less than the regular price.', 'points-rewards-for-woocommerce' ),
					'decimal_point'                    => $decimal,
					'mon_decimal_point'                => wc_get_price_decimal_separator(),
					'strings'                          => array(
						'import_products' => __( 'Import', 'points-rewards-for-woocommerce' ),
						'export_products' => __( 'Export', 'points-rewards-for-woocommerce' ),
					),
					'urls'                             => array(
						'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
						'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
					),
				);
				wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );

				wp_enqueue_script( 'woocommerce_admin' );
				$args_cat   = array( 'taxonomy' => 'product_cat' );
				$categories = get_terms( $args_cat );
				$option_arr = array();
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
				$url     = admin_url( 'admin.php?page=mwb-wpr-setting' );
				$mwb_wpr = array(
					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
					'validpoint'         => __( 'Please enter valid points', 'points-rewards-for-woocommerce' ),
					'Labelname'          => __( 'Enter the Name of the Level', 'points-rewards-for-woocommerce' ),
					'Labeltext'          => __( 'Enter Level', 'points-rewards-for-woocommerce' ),
					'Points'             => __( 'Enter Points', 'points-rewards-for-woocommerce' ),
					'Categ_text'         => __( 'Select Product Category', 'points-rewards-for-woocommerce' ),
					'Remove_text'        => __( 'Remove', 'points-rewards-for-woocommerce' ),
					'Categ_option'       => $option_categ,
					'Prod_text'          => __( 'Select Product', 'points-rewards-for-woocommerce' ),
					'Discounttext'       => __( 'Enter Discount (%)', 'points-rewards-for-woocommerce' ),
					'error_notice'       => __( 'Fields cannot be empty', 'points-rewards-for-woocommerce' ),
					'LevelName_notice'   => __( 'Please Enter the Name of the Level', 'points-rewards-for-woocommerce' ),
					'LevelValue_notice'  => __( 'Please Enter valid Points', 'points-rewards-for-woocommerce' ),
					'CategValue_notice'  => __( 'Please select a category', 'points-rewards-for-woocommerce' ),
					'ProdValue_notice'   => __( 'Please select a product', 'points-rewards-for-woocommerce' ),
					'Discount_notice'    => __( 'Please enter valid discount', 'points-rewards-for-woocommerce' ),
					'success_assign'     => __( 'Points are assigned successfully!', 'points-rewards-for-woocommerce' ),
					'error_assign'       => __( 'Enter Some Valid Points!', 'points-rewards-for-woocommerce' ),
					'success_remove'     => __( 'Points are removed successfully!', 'points-rewards-for-woocommerce' ),
					'Days'               => __( 'Days', 'points-rewards-for-woocommerce' ),
					'Weeks'              => __( 'Weeks', 'points-rewards-for-woocommerce' ),
					'Months'             => __( 'Months', 'points-rewards-for-woocommerce' ),
					'Years'              => __( 'Years', 'points-rewards-for-woocommerce' ),
					'Exp_period'         => __( 'Expiration Period', 'points-rewards-for-woocommerce' ),
					'mwb_wpr_url'        => $url,
					'reason'             => __( 'Please enter Remark', 'points-rewards-for-woocommerce' ),
					'mwb_wpr_nonce'      => wp_create_nonce( 'mwb-wpr-verify-nonce' ),
					'check_pro_activate' => ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ),
					'pro_text'           => __( 'Please Purchase the Pro Plugin.', 'points-rewards-for-woocommerce' ),
					'pro_link_text'      => __( 'Click here', 'points-rewards-for-woocommerce' ),
					'pro_link'       => 'https://makewebbetter.com/product/woocommerce-points-and-rewards?utm_source=MWB-PAR-org&utm_medium=MWB-org-plugin&utm_campaign=MWB-PAR-org',
					'success_update'     => __( 'Points are updated successfully', 'points-rewards-for-woocommerce' ),
					'support_confirm'     => __( 'Email sent successfully', 'points-rewards-for-woocommerce' ),
				);

				wp_enqueue_script( $this->plugin_name . 'admin-js', MWB_RWPR_DIR_URL . 'admin/js/points-rewards-for-woocommerce-admin.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'select2' ), $this->version, false );

				wp_localize_script( $this->plugin_name . 'admin-js', 'mwb_wpr_object', $mwb_wpr );

			}
		}

	}

	/**
	 * Add a submenu inside the Woocommerce Menu Page
	 *
	 * @since 1.0.0
	 * @name mwb_rwpr_admin_menu()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_rwpr_admin_menu() {
		add_submenu_page( 'woocommerce', __( 'Points and Rewards', 'points-rewards-for-woocommerce' ), __( 'Points and Rewards', 'points-rewards-for-woocommerce' ), 'manage_options', 'mwb-rwpr-setting', array( $this, 'mwb_rwpr_admin_setting' ) );
	}

	/**
	 * This is function is used for the validating the data.
	 *
	 * @name mwb_wpr_allowed_html
	 * @since 1.0.0
	 */
	public function mwb_wpr_allowed_html() {
		$allowed_tags = array(
			'span' => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
				'data-tip' => array(),
			),
			'min' => array(),
			'max' => array(),
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
	 * @name mwb_rwpr_admin_setting()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_rwpr_admin_setting() {
		include_once MWB_RWPR_DIR_PATH . '/admin/partials/points-rewards-for-woocommerce-admin-display.php';
	}

	/**
	 * This function update points
	 *
	 * @name mwb_wpr_points_update
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wpr_points_update() {
		check_ajax_referer( 'mwb-wpr-verify-nonce', 'mwb_nonce' );
		if ( isset( $_POST['points'] ) && is_numeric( $_POST['points'] ) && isset( $_POST['user_id'] ) && isset( $_POST['sign'] ) && isset( $_POST['reason'] ) ) {

			$user_id = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
			/* Get the user points*/
			$get_points = (int) get_user_meta( $user_id, 'mwb_wpr_points', true );
			/* Get the Input Values*/
			$points     = sanitize_text_field( wp_unslash( $_POST['points'] ) );
			$sign       = sanitize_text_field( wp_unslash( $_POST['sign'] ) );
			$reason     = sanitize_text_field( wp_unslash( $_POST['reason'] ) );
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
				update_user_meta( $user_id, 'mwb_wpr_points', $total_points );
			}
			/* Update user points*/
			self::mwb_wpr_update_points_details( $user_id, 'admin_points', $points, $data );
			/* Send Mail to the user*/
			$this->mwb_wpr_send_mail_details( $user_id, 'admin_notification', $points );
			wp_die();
		}
	}

	/**
	 * This function is use to update points details
	 *
	 * @name mwb_wpr_update_points_details
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @param int    $user_id user id of the user.
	 * @param string $type type of the points details.
	 * @param int    $points points.
	 * @param array  $data  array of the data.
	 * @link https://www.makewebbetter.com/
	 */
	public static function mwb_wpr_update_points_details( $user_id, $type, $points, $data ) {
		/* Get the points of the points details*/
		$today_date = date_i18n( 'Y-m-d h:i:sa' );
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
	 * @name mwb_wpr_update_points_details
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 * @param int    $user_id user id of the user.
	 * @param string $type type of the points details.
	 */
	public function mwb_wpr_send_mail_details( $user_id, $type, $point ) {
		$user                      = get_user_by( 'ID', $user_id );
		$user_email                = $user->user_email;
		$user_name                 = $user->user_login;
		$mwb_wpr_notificatin_array = get_option( 'mwb_wpr_notificatin_array', true );
		if ( 'admin_notification' == $type ) {
			/* Check is settings array is not empty*/
			if ( is_array( $mwb_wpr_notificatin_array ) && ! empty( $mwb_wpr_notificatin_array ) ) {
				/*Get the mail subject*/
				$mwb_wpr_email_subject = $this->mwb_wpr_get_subject( 'mwb_wpr_email_subject' );
				/*Get the mail custom description*/
				$mwb_wpr_email_discription = $this->mwb_wpr_get_email_description( 'mwb_wpr_email_discription_custom_id' );
				/*Get the total points*/
				$total_points              = $this->mwb_wpr_get_user_points( $user_id );
				$mwb_wpr_email_discription = str_replace( '[Total Points]', $total_points, $mwb_wpr_email_discription );
				$mwb_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $mwb_wpr_email_discription );
				$mwb_wpr_email_discription = str_replace( '[Points]', $point, $mwb_wpr_email_discription );
				if ( self::mwb_wpr_check_mail_notfication_is_enable() ) {
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					wc_mail( $user_email, $mwb_wpr_email_subject, $mwb_wpr_email_discription, $headers );
				}
			}
		}
	}

	/**
	 * This function is use to check is notification setting is enable or not
	 *
	 * @name mwb_wpr_check_mail_notfication_is_enable
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public static function mwb_wpr_check_mail_notfication_is_enable() {
		$mwb_points_notification_enable = false;
		$mwb_wpr_notificatin_array      = get_option( 'mwb_wpr_notificatin_array', true );
		$mwb_wpr_notificatin_enable     = isset( $mwb_wpr_notificatin_array['mwb_wpr_notification_setting_enable'] ) ? intval( $mwb_wpr_notificatin_array['mwb_wpr_notification_setting_enable'] ) : 0;
		if ( 1 == $mwb_wpr_notificatin_enable ) {
			$mwb_points_notification_enable = true;
		}
		return $mwb_points_notification_enable;
	}

	/**
	 * This function is used to get the subject
	 *
	 * @name mwb_wpr_check_mail_notfication_is_enable
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 * @param int $id of the database array.
	 */
	public function mwb_wpr_get_subject( $id ) {
		$mwb_wpr_notificatin_array = get_option( 'mwb_wpr_notificatin_array', true );
		$mwb_wpr_email_subject     = isset( $mwb_wpr_notificatin_array[ $id ] ) ? $mwb_wpr_notificatin_array[ $id ] : '';
		return $mwb_wpr_email_subject;
	}

	/**
	 * This function is used to get the Email descriptiion
	 *
	 * @name mwb_wpr_check_mail_notfication_is_enable
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 * @param int $id of the database array.
	 */
	public function mwb_wpr_get_email_description( $id ) {
		$mwb_wpr_notificatin_array = get_option( 'mwb_wpr_notificatin_array', true );
		$mwb_wpr_email_discription = isset( $mwb_wpr_notificatin_array[ $id ] ) ? $mwb_wpr_notificatin_array[ $id ] : '';
		return $mwb_wpr_email_discription;
	}

	/**
	 * This function is used to get user points
	 *
	 * @name mwb_wpr_get_user_points
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 * @param int $user_id user id of the user.
	 */
	public function mwb_wpr_get_user_points( $user_id ) {
		$mwb_wpr_total_points = 0;
		$mwb_wpr_points       = get_user_meta( $user_id, 'mwb_wpr_points', true );
		if ( ! empty( $mwb_wpr_points ) ) {
			$mwb_wpr_total_points = $mwb_wpr_points;
		}
		return $mwb_wpr_total_points;
	}

	/**
	 * This function append the option field after selecting Product category through ajax
	 *
	 * @name mwb_wpr_select_category.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wpr_select_category() {
		check_ajax_referer( 'mwb-wpr-verify-nonce', 'mwb_nonce' );
		$mwb_wpr_categ_list = array();
		if ( isset( $_POST['mwb_wpr_categ_list'] ) && ! empty( $_POST['mwb_wpr_categ_list'] ) ) {
			$mwb_wpr_categ_list = map_deep( wp_unslash( $_POST['mwb_wpr_categ_list'] ), 'sanitize_text_field' );
		}
		$response['result'] = __( 'Fail due to an error', 'points-rewards-for-woocommerce' );
		if ( isset( $mwb_wpr_categ_list ) ) {
			$products              = array();
			$selected_cat          = $mwb_wpr_categ_list;
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
				global $product;

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
	 * @name mwb_wpr_add_rule_for_membership.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 * @param array $mwb_wpr_membership_roles array of the user roles.
	 */
	public function mwb_wpr_add_rule_for_membership( $mwb_wpr_membership_roles ) {
		?>
		<div class="parent_of_div">
			<?php
			$count = 0;
			if ( is_array( $mwb_wpr_membership_roles ) && ! empty( $mwb_wpr_membership_roles ) ) {
				$key = array_key_first( $mwb_wpr_membership_roles );
				$this->mwb_wpr_membership_role( $count, $key, $mwb_wpr_membership_roles[ $key ] );
			} else {
				$this->mwb_wpr_membership_role( $count, '', '' );
			}
			?>
		</div>
		<?php
	}

	/**
	 * This function is used for checking is not empty the value
	 *
	 * @name check_is_not_empty.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 * @param array $value value of the database.
	 */
	public function check_is_not_empty( $value ) {
		return ! empty( $value ) ? $value : '';
	}

	/**
	 * This function is used for adding the membership
	 *
	 * @name mwb_wpr_membership_role.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 * @param int   $count count of the membership.
	 * @param int   $key key of the array.
	 * @param array $value value of one array.
	 */
	public function mwb_wpr_membership_role( $count, $key, $value ) {
		?>
		<div id ="mwb_wpr_parent_repeatable_<?php echo esc_html( $count ); ?>" data-id="<?php echo esc_html( $count ); ?>" class="mwb_wpr_repeat">
			<table class="mwb_wpr_repeatable_section">
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="mwb_wpr_membership_level_name"><?php esc_html_e( 'Enter Level', 'points-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags = $this->mwb_wpr_allowed_html();
						$attribute_description = __( 'Entered text will be name of the level for membership', 'points-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<label for="mwb_wpr_membership_level_name">
							<input type="text" name="mwb_wpr_membership_level_name_<?php echo esc_html( $count ); ?>" value="<?php echo esc_html( $this->check_is_not_empty( $key ) ); ?>" id="mwb_wpr_membership_level_name_<?php echo esc_html( $count ); ?>" class="text_points" required><?php esc_html_e( 'Enter the Name of the Level', 'points-rewards-for-woocommerce' ); ?>
						</label>
						<?php if ( ! empty( $value ) ) : ?>
						<input type="button" value='<?php esc_html_e( 'Remove', 'points-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_remove_button" id="<?php echo esc_html( $count ); ?>">	
						<?php endif; ?> 			
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="mwb_wpr_membership_level_value"><?php esc_html_e( 'Enter Points', 'points-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags = $this->mwb_wpr_allowed_html();
						$attribute_description = __( 'Entered points need to be reached for this level', 'points-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );

						?>
						<label for="mwb_wpr_membership_level_value">
						<input type="number" min="1" value="<?php echo esc_html( $this->check_is_not_empty( isset( $value['Points'] ) ? $value['Points'] : '' ) ); ?>" name="mwb_wpr_membership_level_value_<?php echo esc_html( $count ); ?>" id="mwb_wpr_membership_level_value_<?php echo esc_html( $count ); ?>" class="input-text" required>
						</label>			
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="mwb_wpr_membership_expiration"><?php esc_html_e( 'Expiration Period', 'points-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags = $this->mwb_wpr_allowed_html();
						$attribute_description = __( 'Select the days,week,month or year for expiartion of current level', 'points-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						$exp_num = isset( $value['Exp_Number'] ) ? $value['Exp_Number'] : '';
						?>
						<input type="number" min="1" value="<?php echo esc_html( $exp_num ); ?>" name="mwb_wpr_membership_expiration_<?php echo esc_html( $count ); ?>" id="mwb_wpr_membership_expiration_<?php echo esc_html( $count ); ?>" class="input-text" required>
						<select id="mwb_wpr_membership_expiration_days_<?php echo esc_html( $count ); ?>" name="mwb_wpr_membership_expiration_days_<?php echo esc_html( $count ); ?>">
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
						<?php esc_html_e( 'Days', 'points-rewards-for-woocommerce' ); ?>
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
						><?php esc_html_e( 'Weeks', 'points-rewards-for-woocommerce' ); ?>
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
						><?php esc_html_e( 'Months', 'points-rewards-for-woocommerce' ); ?>
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
						><?php esc_html_e( 'Years', 'points-rewards-for-woocommerce' ); ?>
						</option>	
						</select>		
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="mwb_wpr_membership_category_list"><?php esc_html_e( 'Select Product Category', 'points-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags = $this->mwb_wpr_allowed_html();
						$attribute_description = __( 'Select', 'points-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<select id="mwb_wpr_membership_category_list_<?php echo esc_html( $count ); ?>" required="true" multiple="multiple" class="mwb_wpr_common_class_categ" data-id="<?php echo esc_html( $count ); ?>" name="mwb_wpr_membership_category_list_<?php echo esc_html( $count ); ?>[]">
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
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="mwb_wpr_membership_product_list"><?php esc_html_e( 'Select Product', 'points-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags = $this->mwb_wpr_allowed_html();
						$attribute_description = __( 'Product of selected category will get displayed in Select Product Section', 'points-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<select id="mwb_wpr_membership_product_list_<?php echo esc_html( $count ); ?>" multiple="multiple" name="mwb_wpr_membership_product_list_<?php echo esc_html( $count ); ?>[]">
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
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="mwb_wpr_membership_discount"><?php esc_html_e( 'Enter Discount (%)', 'points-rewards-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$allowed_tags = $this->mwb_wpr_allowed_html();
						$attribute_description = __( 'Entered Discount will be applied on above selected products', 'points-rewards-for-woocommerce' );
						echo wp_kses( wc_help_tip( $attribute_description ), $allowed_tags );
						?>
						<label for="mwb_wpr_membership_discount">
						<input type="number" min="1" value="<?php echo esc_html( $this->check_is_not_empty( isset( $value['Discount'] ) ? $value['Discount'] : '' ) ); ?>" name="mwb_wpr_membership_discount_<?php echo esc_html( $count ); ?>" id="mwb_wpr_membership_discount_<?php echo esc_html( $count ); ?>" class="input-text" required>
						</label>			
					</td>
					<input type = "hidden" value="<?php echo esc_html( $count ); ?>" name="hidden_count">
				</tr>
				<?php do_action( 'mwb_wpr_add_membership', $count ); ?>
			</table>
		</div>
		<?php
	}

	/**
	 * This function is ised for adding actions
	 *
	 * @name mwb_wpr_add_membership_rule.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wpr_add_membership_rule() {
		global $public_obj;
		add_action( 'mwb_wpr_add_membership_rule', array( $this, 'mwb_wpr_add_rule_for_membership' ), 10 );
		add_action( 'mwb_wpr_order_total_points', array( $this, 'mwb_wpr_add_order_total_points' ), 10, 3 );
		$public_obj = $this;
	}

	/**
	 * This function is used to add order total points.
	 *
	 * @name mwb_wpr_add_order_total_points.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 * @param array $thankyouorder_min  array of the min satements.
	 * @param array $thankyouorder_max array of the max rules.
	 * @param array $thankyouorder_value array of the points value.
	 */
	public function mwb_wpr_add_order_total_points( $thankyouorder_min, $thankyouorder_max, $thankyouorder_value ) {
		if ( isset( $thankyouorder_min ) && null != $thankyouorder_min && isset( $thankyouorder_max ) && null != $thankyouorder_max && isset( $thankyouorder_value ) && null != $thankyouorder_value ) {
			$mwb_wpr_no = 1;
			if ( count( $thankyouorder_min ) == count( $thankyouorder_max ) && count( $thankyouorder_max ) == count( $thankyouorder_value ) ) {
				$this->mwb_wpr_add_rule_for_order_total_points( $thankyouorder_min, $thankyouorder_max, $thankyouorder_value, '0' );
			}
		} else {
			$this->mwb_wpr_add_rule_for_order_total_points( array(), array(), array(), '' );
		}
	}

	/**
	 * This function is used to add rule for order total.
	 *
	 * @name mwb_wpr_add_rule_for_membership.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 * @param array $thankyouorder_min  array of the min satements.
	 * @param array $thankyouorder_max array of the max rules.
	 * @param array $thankyouorder_value array of the points value.
	 * @param int   $key  value of the array key.
	 */
	public function mwb_wpr_add_rule_for_order_total_points( $thankyouorder_min, $thankyouorder_max, $thankyouorder_value, $key ) {
		?>
		<table class="form-table wp-list-table widefat fixed striped">
			<tbody class="mwb_wpr_thankyouorder_tbody"> 
				<tr valign="top">
					<th><?php esc_html_e( 'Minimum', 'points-rewards-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Maximum', 'points-rewards-for-woocommerce' ); ?></th>

					<th><?php esc_html_e( 'Points', 'points-rewards-for-woocommerce' ); ?></th>
					<?php if ( ! empty( $key ) ) : ?> 
					<th class="mwb_wpr_remove_thankyouorder_content"><?php esc_html_e( 'Action', 'points-rewards-for-woocommerce' ); ?></th>
					<?php endif; ?>
				</tr>
				<tr valign="top">
					<td class="forminp forminp-text">
						<label for="mwb_wpr_thankyouorder_minimum">
							<input type="text" name="mwb_wpr_thankyouorder_minimum[]" class="mwb_wpr_thankyouorder_minimum input-text wc_input_price" required="" placeholder = "No minimum" value="<?php echo ( ! empty( $thankyouorder_min[ $key ] ) ) ? esc_html( $thankyouorder_min[ $key ] ) : ''; ?>">
						</label>
					</td>
					<td class="forminp forminp-text">
						<label for="mwb_wpr_thankyouorder_maximum">
							<input type="text" name="mwb_wpr_thankyouorder_maximum[]" class="mwb_wpr_thankyouorder_maximum"  placeholder = "No maximum" value="<?php echo ( ! empty( $thankyouorder_max[ $key ] ) ) ? esc_html( $thankyouorder_max[ $key ] ) : ''; ?>">
						</label>
					</td>
					<td class="forminp forminp-text">
						<label for="mwb_wpr_thankyouorder_current_type">
							<input type="text" name="mwb_wpr_thankyouorder_current_type[]" class="mwb_wpr_thankyouorder_current_type input-text wc_input_price" required=""  value="<?php echo ( ! empty( $thankyouorder_value[ $key ] ) ) ? esc_html( $thankyouorder_value[ $key ] ) : ''; ?>">
						</label>
					</td>    
					<?php if ( ! empty( $key ) ) : ?>                       
						<td class="mwb_wpr_remove_thankyouorder_content forminp forminp-text">
							<input type="button" value="<?php esc_html_e( 'Remove', 'points-rewards-for-woocommerce' ); ?>" class="mwb_wpr_remove_thankyouorder button" >
						</td>
					<?php endif; ?>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * This function is used to show support popup.
	 *
	 * @name mwb_wpr_support_popup.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wpr_support_popup() {
		check_ajax_referer( 'mwb-wpr-verify-nonce', 'mwb_nonce' );
		if ( current_user_can( 'administrator' ) ) {
			$status = get_option( 'mwb_wpr_suggestions_sent', false );
			if ( ! $status ) {
				$current_user = wp_get_current_user();
				if ( ! empty( $current_user ) ) {
					$message  = 'Plugin : points-and-rewards-for-woocommerce<br/>';
					$message .= 'Email Id : ' . $current_user->user_email . '<br/>';
					$message .= 'First Name : ' . $current_user->user_firstname . '<br/>';
					$message .= 'Last Name : ' . $current_user->user_lastname . '<br/>';
					$message .= 'Site URL : ' . site_url() . '<br/>';
					$message .= 'Wordpress Version : ' . get_bloginfo( 'version' ) . '<br/>';
					$message .= 'Plugin Version : ' . REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION . '<br/>';
					$message .= 'Woocommerce Version : ' . WC()->version . '<br/>';

					$to      = 'plugins@makewebbetter.com';
					$subject = 'Points And Rewards Customers Details';
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					$status  = wp_mail( $to, $subject, $message, $headers );
				}
				if ( $status ) {
					update_option( 'mwb_wpr_suggestions_sent', true );
				}
			}
		}
		wp_die();
	}

	/**
	 * This function is used to save data if user is not interested in support .
	 *
	 * @name mwb_wpr_support_popup_later.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wpr_support_popup_later() {
		check_ajax_referer( 'mwb-wpr-verify-nonce', 'mwb_nonce' );
		if ( current_user_can( 'administrator' ) ) {
			$status = get_option( 'mwb_wpr_suggestions_later', false );
			if ( ! $status ) {
				update_option( 'mwb_wpr_suggestions_later', true );
			}
		}
		wp_die();
	}
}
