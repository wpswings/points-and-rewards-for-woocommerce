<?php
/**
 * Exit if accessed directly
 *
 * @since      1.0.0
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * This is construct of class where all users point listed.
 *
 * @name Points_Log_List_Table
 * @since      1.0.0
 * @category Class
 * @author WP Swings <webmaster@wpswings.com>
 * @link https://www.wpswings.com/
 */
class Points_Log_List_Table extends WP_List_Table {
	/**
	 * This is variable which is used for the store all the data.
	 *
	 * @var array $example_data variable for store data.
	 */
	public $example_data;

	/**
	 * This is variable which is used for the store all the data.
	 *
	 * @var array $wps_total_counta variable for store data.
	 */
	public $wps_total_count;


	/**
	 * This construct colomns in point table.
	 *
	 * @name get_columns.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function get_columns() {

		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'user_name'      => __( 'User Name', 'points-and-rewards-for-woocommerce' ),
			'user_email'     => __( 'User Email', 'points-and-rewards-for-woocommerce' ),
			'user_points'    => __( 'Total Points', 'points-and-rewards-for-woocommerce' ),
			'sign'           => __( 'Choose +/-', 'points-and-rewards-for-woocommerce' ),
			'add_sub_points' => __( 'Enter Points', 'points-and-rewards-for-woocommerce' ),
			'reason'         => __( 'Enter Remark', 'points-and-rewards-for-woocommerce' ),
			'details'        => __( 'Action', 'points-and-rewards-for-woocommerce' ),

		);
		return $columns;
	}

	/**
	 * This show points table list.
	 *
	 * @name column_default.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array  $item  array of the items.
	 * @param string $column_name name of the colmn.
	 */
	public function column_default( $item, $column_name ) {

		$wps_user = get_user_by( 'id', $item['id'] );
		$points   = ! empty( get_user_meta( $item['id'], 'wps_wpr_points', true ) ) ? get_user_meta( $item['id'], 'wps_wpr_points', true ) : 0;

		switch ( $column_name ) {

			case 'user_name':
				$actions = array(
					'view_point_log' => '<a href="' . WPS_RWPR_HOME_URL . 'admin.php?page=wps-rwpr-setting&tab=points-table&user_id=' . $item['id'] . '&action=view_point_log">' . __( 'View Point Log', 'points-and-rewards-for-woocommerce' ) . '</a>',

				);
				$actions = apply_filters( 'wps_add_coupon_details', $actions, $item['id'] );
				return $wps_user->user_nicename . $this->row_actions( $actions );
			case 'user_email':
				return '<b>' . $wps_user->user_email . '</b>';
			case 'user_points':
				return '<b>' . $points . '</b>';
			case 'sign':
				$html = '<select id="wps_sign' . $item['id'] . '" ><option value="+">+</option><option value="-">-</option></select>';
				return $html;
			case 'add_sub_points':
				$html = '<input class="wps_rwpr_width_seventyfive" type="number" min="0" id="add_sub_points' . $item['id'] . '" value="">';
				return $html;
			case 'reason':
				$html = '<input class="wps_rwpr_width_hundred" type="text" id="wps_remark' . $item['id'] . '" min="0" value="">';
				return $html;
			case 'details':
				return $this->view_html( $item['id'] );

			default:
				return false;
		}
	}

	/**
	 * This construct update button on points table.
	 *
	 * @name view_html.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int $user_id  user id of the user.
	 */
	public function view_html( $user_id ) {

		echo '<a  href="javascript:void(0)" class="wps_points_update button button-primary wps_wpr_save_changes" data-id="' . esc_html( $user_id ) . '">' . esc_html__( 'Update', 'points-and-rewards-for-woocommerce' ) . '</a>';

	}

	/**
	 * Perform admin bulk action setting for points table.
	 *
	 * @name process_bulk_action.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function process_bulk_action() {

		if ( 'bulk-delete' === $this->current_action() ) {
			if ( isset( $_POST['points-log'] ) ) {
				$wps_membership_nonce = sanitize_text_field( wp_unslash( $_POST['points-log'] ) );
				if ( wp_verify_nonce( $wps_membership_nonce, 'points-log' ) ) {
					if ( isset( $_POST['mpr_points_ids'] ) && ! empty( $_POST['mpr_points_ids'] ) ) {
						$all_id = map_deep( wp_unslash( $_POST['mpr_points_ids'] ), 'sanitize_text_field' );
						foreach ( $all_id as $key => $value ) {
							delete_user_meta( $value, 'wps_wpr_points' );
						}
					}
				}
			}
		}
		do_action( 'wps_wpr_process_bulk_reset_option', $this->current_action(), $_POST );

	}
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @name process_bulk_action.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Reset Points', 'points-and-rewards-for-woocommerce' ),
		);
		return apply_filters( 'wps_wpr_points_log_bulk_option', $actions );
	}

	/**
	 * Returns an associative array containing the bulk action for sorting.
	 *
	 * @name get_sortable_columns.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'user_name'   => array( 'user_name', false ),
			'user_email'  => array( 'user_email', false ),
			'user_points' => array( 'user_points', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Undocumented function
	 *
	 * @param object $data data.
	 * @return array
	 */
	public function wps_wpr_sort_user_table( $data ) {
		$points_data = array();
		if ( ! empty( $data ) ) {
			foreach ( $data as $sort_id ) {
				$user          = get_userdata( $sort_id['id'] );
				$points        = get_user_meta( $sort_id['id'], 'wps_wpr_points', true );
				$points_data[] = array(
					'id'          => $sort_id['id'],
					'user_name'   => $user->user_name,
					'user_email'  => $user->user_email,
					'user_points' => $points,
				);
			}
		}
		return $points_data;
	}

	/**
	 * Prepare items for sorting.
	 *
	 * @name prepare_items.
	 * @since      1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function prepare_items() {
		$per_page              = 10;
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
		$current_page = $this->get_pagenum();

		$this->example_data = $this->get_users_points( $current_page, $per_page );
		$data               = $this->example_data;

		$sort_data = $this->wps_wpr_sort_user_table( $data );
		usort( $sort_data, array( $this, 'wps_wpr_usort_reorder' ) );

		$total_items = $this->wps_total_count;
		$this->items = $sort_data;
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);

	}

	/**
	 * Return sorted associative array.
	 *
	 * @name wps_wpr_usort_reorder.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $cloumna column of the points.
	 * @param array $cloumnb column of the points.
	 */
	public function wps_wpr_usort_reorder( $cloumna, $cloumnb ) {
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'desc';
		if ( is_numeric( $cloumna[ $orderby ] ) && is_numeric( $cloumnb[ $orderby ] ) ) {
			if ( $cloumna[ $orderby ] == $cloumnb[ $orderby ] ) {
				return 0;
			} elseif ( $cloumna[ $orderby ] < $cloumnb[ $orderby ] ) {
				$result = -1;
				return ( 'asc' === $order ) ? $result : -$result;
			} elseif ( $cloumna[ $orderby ] > $cloumnb[ $orderby ] ) {
				$result = 1;
				return ( 'asc' === $order ) ? $result : -$result;
			}
		} else {
			$result = strcmp( $cloumna[ $orderby ], $cloumnb[ $orderby ] );
			return ( 'asc' === $order ) ? $result : -$result;
		}
	}

	/**
	 * THis function is used for the add the checkbox.
	 *
	 * @name column_cb.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $item array of the items.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="mpr_points_ids[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 * This function gives points to user if he doesnot get points.
	 *
	 * @name get_users_points.
	 * @since      1.0.0
	 * @return array
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int $current_page current page.
	 * @param int $per_page no of pages.
	 */
	public function get_users_points( $current_page, $per_page ) {
		$args = array(
			'fields'       => 'ID',
			'meta_query'   => array(
				'relation' => 'OR',
				array(
					'key'     => 'wps_wpr_points',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'wps_wpr_points',
					'compare' => 'NOT EXISTS',
				),
			),
		);

		$args['number'] = $per_page;
		$args['offset'] = ( $current_page - 1 ) * $per_page;
		if ( isset( $_REQUEST['s'] ) ) {
			$wps_request_search = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
			$args['search']     = '*' . $wps_request_search . '*';
		}

		$user_data   = new WP_User_Query( $args );
		$total_count = $user_data->get_total();
		$user_data   = $user_data->get_results();
		$points_data = array();
		foreach ( $user_data as $key => $value ) {
			$points_data[] = array(
				'id' => $value,
			);
		}
		$this->wps_total_count = $total_count;
		return $points_data;
	}
}

if ( isset( $_POST['wps_wpr_import_user'] ) && isset( $_POST['points-log'] ) ) {
	$wps_membership_nonce = sanitize_text_field( wp_unslash( $_POST['points-log'] ) );
	$import_user          = get_option( 'wps_wpr_user_imported', false );
	if ( wp_verify_nonce( $wps_membership_nonce, 'points-log' ) ) {
		if ( false == $import_user ) {
			$user_data = get_users();

			$points_data           = array();
			$flag                  = false;
			$general_settings      = get_option( 'wps_wpr_settings_gallery', true );
			$coupon_settings_array = get_option( 'wps_wpr_coupons_gallery', array() );
			foreach ( $user_data as $key => $value ) {
				$check_user = get_user_meta( $value->data->ID, 'wps_wpr_points', false );
				if ( false == $check_user ) {
						$today_date                       = date_i18n( 'Y-m-d h:i:sa' );
						$wps_signup_value                 = isset( $general_settings['wps_wpr_general_signup_value'] ) ? intval( $general_settings['wps_wpr_general_signup_value'] ) : 1;
						$import_points['import_points'][] = array(
							'import_points' => $wps_signup_value,
							'date'          => $today_date,
						);

						update_user_meta( $value->data->ID, 'wps_wpr_points', $wps_signup_value );
						update_user_meta( $value->data->ID, 'points_details', $import_points );
						$flag                      = true;
						$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
						if ( is_array( $wps_wpr_notificatin_array ) && ! empty( $wps_wpr_notificatin_array ) ) {
							$wps_per_currency_spent_value = isset( $coupon_settings_array['wps_wpr_coupon_conversion_points'] ) ? intval( $coupon_settings_array['wps_wpr_coupon_conversion_points'] ) : 1;
							$wps_comment_value            = isset( $general_settings['wps_comment_value'] ) ? intval( $general_settings['wps_comment_value'] ) : 1;
							$wps_refer_value              = isset( $general_settings['wps_refer_value'] ) ? intval( $general_settings['wps_refer_value'] ) : 1;
							$wps_wpr_notificatin_enable   = isset( $wps_wpr_notificatin_array['wps_wpr_notificatin_enable'] ) ? intval( $wps_wpr_notificatin_array['wps_wpr_notificatin_enable'] ) : 0;
							$wps_wpr_email_subject        = isset( $wps_wpr_notificatin_array['wps_wpr_signup_email_subject'] ) ? $wps_wpr_notificatin_array['wps_wpr_signup_email_subject'] : '';
							$wps_wpr_email_discription    = isset( $wps_wpr_notificatin_array['wps_wpr_signup_email_discription_custom_id'] ) ? $wps_wpr_notificatin_array['wps_wpr_signup_email_discription_custom_id'] : '';
							$wps_wpr_email_discription    = str_replace( '[Points]', $wps_signup_value, $wps_wpr_email_discription );
							$wps_wpr_email_discription    = str_replace( '[Total Points]', $wps_signup_value, $wps_wpr_email_discription );
							$wps_wpr_email_discription    = str_replace( '[Comment Points]', $wps_comment_value, $wps_wpr_email_discription );
							$wps_wpr_email_discription    = str_replace( '[Refer Points]', $wps_refer_value, $wps_wpr_email_discription );
							$wps_wpr_email_discription    = str_replace( '[Per Currency Spent Points]', $wps_per_currency_spent_value, $wps_wpr_email_discription );
							if ( $wps_wpr_notificatin_enable ) {
								$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
								$email_status = $customer_email->trigger( $value->data->ID, $wps_wpr_email_discription, $wps_wpr_email_subject );
							}
						}
				}
			}
			if ( $flag ) {

				update_option( 'wps_wpr_user_imported', true );
			}
		}
	}
}
if ( isset( $_GET['action'] ) && isset( $_GET['user_id'] ) ) {
	if ( 'view' == $_GET['action'] ) {
		$user_log_id = sanitize_text_field( wp_unslash( $_GET['user_id'] ) );
		$user_log    = get_user_meta( $user_log_id, 'wps_wpr_user_log', true );
		?>
		<?php do_action( 'wps_wpr_add_notice' ); ?>
		<h3 class="wp-heading-inline" id="wps_wpr_points_table_heading"><?php esc_html_e( 'User Coupon Details', 'points-and-rewards-for-woocommerce' ); ?></h3>
		<?php
		if ( isset( $user_log ) && is_array( $user_log ) && null != $user_log ) {

			?>
			<table class="form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_admin_coupon_view">
				<thead>
					<tr valign="top">
						<th scope="row" class="wps_wpr_head_titledesc">
							<span class="wps_wpr_nobr"><?php echo esc_html__( 'Points', 'points-and-rewards-for-woocommerce' ); ?></span>
						</th>
						<th scope="row" class="wps_wpr_head_titledesc">
							<span class="wps_wpr_nobr"><?php echo esc_html__( 'Coupon Code', 'points-and-rewards-for-woocommerce' ); ?></span>
						</th>
						<th scope="row" class="wps_wpr_head_titledesc">
							<span class="wps_wpr_nobr"><?php echo esc_html__( 'Coupon Amount', 'points-and-rewards-for-woocommerce' ); ?></span>
						</th>
						<th scope="row" class="wps_wpr_head_titledesc">
							<span class="wps_wpr_nobr"><?php echo esc_html__( 'Amount Left', 'points-and-rewards-for-woocommerce' ); ?></span>
						</th>
						<th scope="row" class="wps_wpr_head_titledesc">
							<span class="wps_wpr_nobr"><?php echo esc_html__( 'Expiry', 'points-and-rewards-for-woocommerce' ); ?></span>
						</th>
						<th scope="row" class="wps_wpr_head_titledesc">
							<span class="wps_wpr_nobr"><?php echo esc_html__( 'Action', 'points-and-rewards-for-woocommerce' ); ?></span>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $user_log as $key => $wps_user_log ) :
						$wps_user_log['delete'] = 'delete';
						?>

						<tr valign="top">
							<?php foreach ( $wps_user_log as $column_id => $column_name ) : ?>
								<td class="forminp forminp-text">
									<?php
									if ( 'left' == $column_id ) {
										$wps_split   = explode( '#', $key );
										$column_name = get_post_meta( $wps_split[1], 'coupon_amount', true );
										echo esc_html( get_woocommerce_currency_symbol() ) . esc_html( $column_name );
									} elseif ( 'camount' == $column_id ) {
										$wps_split   = explode( '#', $key );
										$column_name = get_post_meta( $wps_split[1], 'wps_coupon_static_amount', true );
										echo esc_html( get_woocommerce_currency_symbol() ) . esc_html( $column_name );
									} elseif ( 'expiry' == $column_id ) {
										if ( WC()->version < '3.0.6' ) {

											$column_name = get_post_meta( $wps_split[1], 'expiry_date', true );
											echo esc_html( $column_name );
										} else {

											$column_name = get_post_meta( $wps_split[1], 'date_expires', true );
											if ( ! empty( $column_name ) ) {
												$dt = new DateTime( "@$column_name" );
												echo esc_html( $dt->format( 'Y-m-d' ) );
											} else {
												esc_html_e( 'No Expiry', 'points-and-rewards-for-woocommerce' );
											}
										}
									} elseif ( 'delete' == $column_name ) {
										echo '<a href="#" class="wps_wpr_delete_user_coupon" data-id="' . esc_html( $wps_split[1] ) . '" data-user_id="' . esc_html( $user_log_id ) . '">' . esc_html__( 'Delete', 'points-and-rewards-for-woocommerce' ) . '</a>';
									} else {
										echo esc_html( $column_name );
									}

									?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php
		} else {
			echo '<h3>' . esc_html__( 'No Coupons Generated Yet.', 'points-and-rewards-for-woocommerce' ) . '<h3>';
		}
		?>
		<br> 
		<a  href="<?php echo esc_url( WPS_RWPR_HOME_URL ); ?>admin.php?page=wps-rwpr-setting&tab=points-table" class="button wps_points_log_list_table_line_height button-primary wps_wpr_save_changes"><?php esc_html_e( 'Go Back', 'points-and-rewards-for-woocommerce' ); ?></a> 
		<?php

	} elseif ( 'view_point_log' == $_GET['action'] ) {
		$user_id      = sanitize_text_field( wp_unslash( $_GET['user_id'] ) );
		$point_log    = get_user_meta( $user_id, 'points_details', true );
		$total_points = get_user_meta( $user_id, 'wps_wpr_points', true );
		?>
		<h3 class="wp-heading-inline" id="wps_wpr_points_table_heading"><?php esc_html_e( 'Points Earned on Order Total Listed on Points Table', 'points-and-rewards-for-woocommerce' ); ?></h3>
		<?php
		if ( isset( $point_log ) && is_array( $point_log ) && null != $point_log ) {
			?>
 
			<div class="wps_wpr_wrapper_div">
				<?php
				if ( array_key_exists( 'registration', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Signup Event', 'points-and-rewards-for-woocommerce' ); ?>
						<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class="form-table mwp_wpr_settings wps_wpr_common_table" >
									<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<tr valign="top">
								<td class="forminp forminp-text"><?php echo( esc_html( $point_log['registration']['0']['date'] ) ); ?></td>
								<td class="forminp forminp-text"><?php echo '+' . esc_html( $point_log['registration']['0']['registration'] ); ?></td>
								</tr>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'import_points', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Signup Event', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p> 
						<div class="wps_wpr_points_view">  
							<table class = "form-table mwp_wpr_settings wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $point_log['import_points']['0']['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '+' . esc_html( $point_log['import_points']['0']['import_points'] ); ?></td>
								</tr>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'Coupon_details', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Coupon Generation', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p> 
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings wps_wpr_common_table">  
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['Coupon_details'] as $key => $value ) {
									?>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['Coupon_details'] ); ?></td>
								</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div> 
					<?php
				}
				if ( array_key_exists( 'points_on_order', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Points earned on Order Total', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings wps_wpr_common_table"> 
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['points_on_order'] as $key => $value ) {
									?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['points_on_order'] ); ?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'refund_points_on_order', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Deducted Points earned on Order Total on Order Refund', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
							<div class="wps_wpr_points_view"> 
								<table class = "form-table mwp_wpr_settings wps_wpr_common_table"> 
									<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['refund_points_on_order'] as $key => $value ) {
									?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['refund_points_on_order'] ); ?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'subscription_renewal_points', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Subscription Renewal Points', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
							<div class="wps_wpr_points_view"> 
								<table class = "form-table mwp_wpr_settings wps_wpr_common_table"> 
									<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['subscription_renewal_points'] as $key => $value ) {
									?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['subscription_renewal_points'] ); ?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'refund_subscription__renewal_points', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Refund Subscription Renewal Points', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
							<div class="wps_wpr_points_view"> 
								<table class = "form-table mwp_wpr_settings wps_wpr_common_table"> 
									<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['refund_subscription__renewal_points'] as $key => $value ) {
									?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['refund_subscription__renewal_points'] ); ?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'cancel_points_on_order_total', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Deducted Points earned on Order Total on Order Cancellation', 'points-and-rewards-for-woocommerce' ); ?>
								<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
							</p>
							<div class="wps_wpr_points_view"> 
								<table class = "form-table mwp_wpr_settings wps_wpr_common_table"> 
									<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['cancel_points_on_order_total'] as $key => $value ) {
									?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['cancel_points_on_order_total'] ); ?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'refund_points_applied_on_cart', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Apply Points of cart refunded after the order is canceled', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
							<div class="wps_wpr_points_view"> 
								<table class = "form-table mwp_wpr_settings wps_wpr_common_table"> 
									<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['refund_points_applied_on_cart'] as $key => $value ) {
									?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['refund_points_applied_on_cart'] ); ?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'product_details', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Assigned Product Point', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
							<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings wps_wpr_common_table"> 
									<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['product_details'] as $key => $value ) {
									?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['product_details'] ); ?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'pro_conversion_points', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Order Total Points - Product Conversion Points', 'points-and-rewards-for-woocommerce' ); ?>
						<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['pro_conversion_points'] as $key => $value ) {
									?>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['pro_conversion_points'] ); ?></td>
								</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'comment', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Product Review/Comment Point', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings  wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
									<?php
									foreach ( $point_log['comment'] as $key => $value ) {
										?>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['comment'] ); ?></td>
								</tr>
										<?php
									}
									?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'membership', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Membership Points', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings  wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
									<?php
									foreach ( $point_log['membership'] as $key => $value ) {
										?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['membership'] ); ?></td>
									</tr>
										<?php
									}
									?>
							</table>
						</div>
					</div>
					<?php
				}

				if ( array_key_exists( 'ref_product_detail', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Referral Purchase Point', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings  wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
										<?php
										foreach ( $point_log['ref_product_detail'] as $key => $value ) {
											?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['ref_product_detail'] ); ?></td>
									</tr>
											<?php
										}
										?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'pur_by_points', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Product has been purchased using points', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
										<?php
										foreach ( $point_log['pur_by_points'] as $key => $value ) {
											?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['pur_by_points'] ); ?></td>
									</tr>
											<?php
										}
										?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'deduction_of_points', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Deduction of points for return request', 'points-and-rewards-for-woocommerce' ); ?>
						<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
					</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
									<?php
									foreach ( $point_log['deduction_of_points'] as $key => $value ) {
										?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['deduction_of_points'] ); ?></td>
									</tr>
										<?php
									}
									?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'return_pur_points', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Return Points', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings  wps_wpr_common_table">
										<thead>
										<tr valign="top">
											<th scope="row" class="wps_wpr_head_titledesc">
												<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
											</th>
											<th scope="row" class="wps_wpr_head_titledesc">
												<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
											</th>
										</tr>
									</thead>
										<?php
										foreach ( $point_log['return_pur_points'] as $key => $value ) {
											?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['return_pur_points'] ); ?></td>
									</tr>
											<?php
										}
										?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'deduction_currency_spent', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Deduct Order Total Points - Per Currency Spent', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings  wps_wpr_common_table"> 
										<thead>
										<tr valign="top">
											<th scope="row" class="wps_wpr_head_titledesc">
												<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
											</th>
											<th scope="row" class="wps_wpr_head_titledesc">
												<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
											</th>
										</tr>
									</thead>
										<?php
										foreach ( $point_log['deduction_currency_spent'] as $key => $value ) {
											?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['deduction_currency_spent'] ); ?></td>
									</tr>
											<?php
										}
										?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'cart_subtotal_point', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Points Applied on Cart', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['cart_subtotal_point'] as $key => $value ) {
									?>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['cart_subtotal_point'] ); ?> </td>
								</tr>
									<?php
								}
								?>
						</table>
					</div>
					<?php
				}
				if ( array_key_exists( 'order__rewards_points', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Order Rewards Points', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['order__rewards_points'] as $key => $value ) {
									?>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['order__rewards_points'] ); ?> </td>
								</tr>
									<?php
								}
								?>
						</table>
					</div>
					<?php
				}
				if ( array_key_exists( 'expired_details', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Expired Points', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
							<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['expired_details'] as $key => $value ) {
									?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['expired_details'] ); ?></td>
									</tr>
									<?php
								}
								?>
						</table>
					</div>
					<?php
				}
				if ( array_key_exists( 'deduct_currency_pnt_cancel', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Order Points Deducted due to Cancelation of Order', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['deduct_currency_pnt_cancel'] as $key => $value ) {
									?>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['deduct_currency_pnt_cancel'] ); ?></td>
								</tr>
									<?php
								}
								?>
						</table>
					</div>
					<?php
				}
				if ( array_key_exists( 'deduct_bcz_cancel', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Assigned Points Deducted due to Cancelation of Order', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
							<thead>
								<tr valign="top">
									<th scope="row" class="wps_wpr_head_titledesc">
										<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
									</th>
									<th scope="row" class="wps_wpr_head_titledesc">
										<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
									</th>
								</tr>
							</thead>
							<?php
							foreach ( $point_log['deduct_bcz_cancel'] as $key => $value ) {
								?>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['deduct_bcz_cancel'] ); ?></td>
								</tr>
								<?php
							}
							?>
						</table>
					</div>
					<?php
				}
				if ( array_key_exists( 'pur_points_cancel', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Points Returned due to Cancelation of Order', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
							<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['pur_points_cancel'] as $key => $value ) {
									?>
										<tr valign="top">
											<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
											<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['pur_points_cancel'] ); ?></td>
										</tr>
									<?php
								}
								?>
						</table>
					</div>
					<?php
				}
				if ( array_key_exists( 'pur_pro_pnt_only', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Points deducted for purchasing the product', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
							<thead>
								<tr valign="top">
									<th scope="row" class="wps_wpr_head_titledesc">
										<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
									</th>
									<th scope="row" class="wps_wpr_head_titledesc">
										<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
									</th>
								</tr>
							</thead>
							<?php
							foreach ( $point_log['pur_pro_pnt_only'] as $key => $value ) {
								?>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['pur_pro_pnt_only'] ); ?> </td>
								</tr>
								<?php
							}
							?>
						</table>
					</div>
					<?php
				}
				if ( array_key_exists( 'reference_details', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Referral SignUp', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Reference Sign Up by', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead>
								<?php
								foreach ( $point_log['reference_details'] as $key => $value ) {
									$user_name = '';
									if ( isset( $value['refered_user'] ) && ! empty( $value['refered_user'] ) ) {
										$user      = get_user_by( 'ID', $value['refered_user'] );
										if ( isset( $user ) && ! empty( $user ) ) {
											$user_name = $user->user_login;
										} else {
											$user_name = esc_html__( 'This user doesn\'t exist', 'points-and-rewards-for-woocommerce' );
										}
									}
									?>
									<tr valign="top">
											<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
											<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['reference_details'] ); ?></td>
											<td class="forminp forminp-text">
										<?php
										if ( isset( $user ) && ! empty( $user ) ) {
											echo esc_html( $user_name );
										} else {
											echo esc_html( $user_name );
										}
										?>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'admin_points', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Admin Updates', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<div class="wps_wpr_points_view"> 
							<table class = "form-table mwp_wpr_settings  wps_wpr_common_table">
									<thead>
										<tr valign="top">
											<th scope="row" class="wps_wpr_head_titledesc">
												<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
											</th>
											<th scope="row" class="wps_wpr_head_titledesc">
												<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
											</th>
											<th scope="row" class="wps_wpr_head_titledesc">
												<span class="wps_wpr_nobr"><?php echo esc_html__( 'Reason', 'points-and-rewards-for-woocommerce' ); ?></span>
											</th>
										</tr>
									</thead>
									<?php
									foreach ( $point_log['admin_points'] as $key => $value ) {
										$value['sign']   = isset( $value['sign'] ) ? $value['sign'] : '+/-';
										$value['reason'] = isset( $value['reason'] ) ? $value['reason'] : __( 'Updated By Admin', 'points-and-rewards-for-woocommerce' );
										?>
										<tr valign="top">
											<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
											<td class="forminp forminp-text"><?php echo esc_html( $value['sign'] ) . esc_html( $value['admin_points'] ); ?></td>
											<td class="forminp forminp-text"><?php echo esc_html( $value['reason'] ); ?></td>
										</tr>
										<?php
									}
									?>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'Sender_point_details', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Points Shared with', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
								<thead>
									<tr valign="top">
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
										<th scope="row" class="wps_wpr_head_titledesc">
											<span class="wps_wpr_nobr"><?php echo esc_html__( 'Shared to ', 'points-and-rewards-for-woocommerce' ); ?></span>
										</th>
									</tr>
								</thead> 
								<?php
								foreach ( $point_log['Sender_point_details'] as $key => $value ) {
									$user_name = '';
									if ( isset( $value['given_to'] ) && ! empty( $value['given_to'] ) ) {
										$user      = get_user_by( 'ID', $value['given_to'] );
										$user_name = $user->user_nicename;
									}
									?>
									<tr valign="top">
										<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
										<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['Sender_point_details'] ); ?></td>
										<td class="forminp forminp-text">
												<?php
												echo esc_html( $user_name );
												?>
										</td>
									</tr>
									<?php
								}
								?>
						</table>
					</div>
					<?php
				}
				if ( array_key_exists( 'Receiver_point_details', $point_log ) ) {
					?>
					<div class="wps_wpr_slide_toggle">
						<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Receives Points', 'points-and-rewards-for-woocommerce' ); ?>
							<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
						</p>
						<table class = "form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
							<thead>
								<tr valign="top">
									<th scope="row" class="wps_wpr_head_titledesc">
										<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span>
									</th>
									<th scope="row" class="wps_wpr_head_titledesc">
										<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
									</th>
									<th scope="row" class="wps_wpr_head_titledesc">
										<span class="wps_wpr_nobr"><?php echo esc_html__( 'Received Points by', 'points-and-rewards-for-woocommerce' ); ?></span>
									</th>
								</tr>
							</thead>
							<?php
							foreach ( $point_log['Receiver_point_details'] as $key => $value ) {
								$user_name = '';
								if ( isset( $value['received_by'] ) && ! empty( $value['received_by'] ) ) {
									$user      = get_user_by( 'ID', $value['received_by'] );
									$user_name = $user->user_nicename;
								}
								?>
								<tr valign="top">
									<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
									<td class="forminp forminp-text"><?php echo '+' . esc_html( $value['Receiver_point_details'] ); ?> </td>
									<td class="forminp forminp-text">
												<?php
												echo esc_html( $user_name );
												?>
									</td>
								</tr>
								<?php
							}
							?>
						</table>
					</div>
					<?php

				}
				do_action( 'wps_points_admin_table_log', $point_log );
				?>
				<table class = "form-table mwp_wpr_settings wps_wpr_points_view_total">
					<tr valign="top">
						<td class="forminp forminp-text"><strong><?php esc_html_e( 'Total Points', 'points-and-rewards-for-woocommerce' ); ?></strong></td>
						<td class="forminp forminp-text"><strong><?php echo esc_html( $total_points ); ?></strong>
						</td>
						<td class="forminp forminp-text"></td>
					</tr>
				</table>
				</div>
			<?php
		} else {
			echo '<h3>' . esc_html__( 'No Points Generated Yet.', 'points-and-rewards-for-woocommerce' ) . '<h3>';
		}
	}
} else {
	do_action( 'wps_wpr_add_additional_import_points' );
	?>
	<h3 class="wp-heading-inline" id="wps_wpr_points_table_heading"><?php esc_html_e( 'Points Table', 'points-and-rewards-for-woocommerce' ); ?></h3>
	<?php
	$general_settings  = get_option( 'wps_wpr_settings_gallery', true );
	$enable_wps_signup = isset( $general_settings['wps_wpr_signup'] ) ? intval( $general_settings['wps_wpr_signup'] ) : 0;
	if ( $enable_wps_signup ) {
		$import_user = get_option( 'wps_wpr_user_imported', false );

		if ( false == $import_user ) {
			?>
			<div class="wps_wpr_import_user container">
				<h3>
					<?php esc_html_e( 'Import Users', 'points-and-rewards-for-woocommerce' ); ?>
				</h3>
				<p>
					<?php esc_html_e( 'Import existing users and assign them with Sign Up Points', 'points-and-rewards-for-woocommerce' ); ?>
				</p>
				<input type="submit" value="<?php esc_html_e( 'Import', 'points-and-rewards-for-woocommerce' ); ?>" id="wps_wpr_import" class="page-title-action button button-primary button-large wps_wpr_save_changes" name="wps_wpr_import_user">
			</div>
			<?php
		} else {

			$user_data  = get_users( $args );
			$guest_flag = false;
			foreach ( $user_data as $key => $value ) {
				$user_id      = $value->data->ID;
				$guest_points = get_user_meta( $value->data->ID, 'wps_wpr_points', false );

				if ( false == $guest_points ) {
					$guest_flag = true;
				}
			}
			if ( $guest_flag ) {
				?>
				<div class="wps_wpr_import_user container">
					<h3>
						<?php esc_html_e( 'Import Users', 'points-and-rewards-for-woocommerce' ); ?>
					</h3>
					<p>
						<?php esc_html_e( 'Import existing users and assign them with Sign Up Points', 'points-and-rewards-for-woocommerce' ); ?>
					</p>
					<input type="button" value="<?php esc_html_e( 'Import', 'points-and-rewards-for-woocommerce' ); ?>" id="wps_wpr_import" class="page-title-action button button-primary button-large wps_wpr_save_changes" name="wps_wpr_import_user">
				</div>
				<?php
				update_option( 'wps_wpr_user_imported', false );
			}
		}
	}
	?>
	<form method="post">
		<input type="hidden" name="page" value="<?php esc_html_e( 'points_log_list_table', 'points-and-rewards-for-woocommerce' ); ?>">
		<?php wp_nonce_field( 'points-log', 'points-log' ); ?>
		<?php
		$mylisttable = new Points_Log_List_Table();
		$mylisttable->prepare_items();
		$mylisttable->search_box( __( 'Search Users', 'points-and-rewards-for-woocommerce' ), 'wps-wpr-user' );
		$mylisttable->display();
		?>
	</form>
	<?php
}
