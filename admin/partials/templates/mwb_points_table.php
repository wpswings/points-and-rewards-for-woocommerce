<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * This is construct of class where all users point listed.
 *
 * @name Points_Log_List_Table
 * @category Class
 * @author makewebbetter<webmaster@makewebbetter.com>
 * @link https://www.makewebbetter.com/
 */
class Points_Log_List_Table extends WP_List_Table {

	public $example_data;

	/**
	 * This construct colomns in point table.
	 *
	 * @name get_columns.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function get_columns() {

		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'user_name'     => __( 'User Name', MWB_RWPR_Domain ),
			'user_email'    => __( 'User Email', MWB_RWPR_Domain ),
			'user_points'   => __( 'Total Points', MWB_RWPR_Domain ),
			'sign'          => __( 'Choose +/-', MWB_RWPR_Domain ),
			'add_sub_points' => __( 'Enter Points', MWB_RWPR_Domain ),
			'reason'        => __( 'Enter Remark', MWB_RWPR_Domain ),
			'details'       => __( 'Action', MWB_RWPR_Domain ),

		);
		return $columns;
	}
	/**
	 * This show points table list.
	 *
	 * @name column_default.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */

	function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'user_name':
				$actions = array(
					'view_point_log' => '<a href="' . MWB_RWPR_HOME_URL . 'admin.php?page=mwb-rwpr-setting&tab=points-table&user_id=' . $item['id'] . '&action=view_point_log">' . __( 'View Point Log', MWB_RWPR_Domain ) . '</a>',
					'view_coupon_detail' => '<a href="' . MWB_RWPR_HOME_URL . 'admin.php?page=mwb-rwpr-setting&tab=points-table&user_id=' . $item['id'] . '&action=view">' . __( 'View Coupon Detail', MWB_RWPR_Domain ) . '</a>',
				);
				return $item[ $column_name ] . $this->row_actions( $actions );
			case 'user_email':
				return '<b>' . $item[ $column_name ] . '</b>';
			case 'user_points':
				return '<b>' . $item[ $column_name ] . '</b>';
			case 'sign':
				$html = '<select id="mwb_sign' . $item['id'] . '" style="width:35%;"><option value="+">+</option><option value="-">-</option></select>';
				return $html;
			case 'add_sub_points':/*
				$max_pnt = get_user_meta( $item['id'],'mwb_wpr_points',true);*/
				$html = '<input style="width:75%;" type="number" min="0" id="add_sub_points' . $item['id'] . '" value="">';
				return $html;
			case 'reason':
				$html = '<input style="width: 100%;" type="text" id="mwb_remark' . $item['id'] . '" min="0" value="">';
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
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function view_html( $user_id ) {

		echo '<a  href="javascript:void(0)" class="mwb_points_update button button-primary mwb_wpr_save_changes" data-id="' . $user_id . '">' . __( 'Update', MWB_RWPR_Domain ) . '</a>';

	}

	/**
	 * Perform admin bulk action setting for points table.
	 *
	 * @name process_bulk_action.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function process_bulk_action() {

		if ( 'bulk-delete' === $this->current_action() ) {

			if ( isset( $_POST['mpr_points_ids'] ) && ! empty( $_POST['mpr_points_ids'] ) ) {
				$all_id = $_POST['mpr_points_ids'];
				foreach ( $all_id as $key => $value ) {

					delete_user_meta( $value, 'mwb_wpr_points' );
				}
			}
		}
	}
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @name process_bulk_action.
	 * @return array
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', MWB_RWPR_Domain ),
		);
		return $actions;
	}

	/**
	 * Returns an associative array containing the bulk action for sorting.
	 *
	 * @name get_sortable_columns.
	 * @return array
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'user_name'    => array( 'user_name', false ),
			'user_email'  => array( 'user_email', false ),
			'user_points'  => array( 'user_points', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Prepare items for sorting.
	 *
	 * @name prepare_items.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function prepare_items() {
		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();

		$this->example_data = $this->get_users_points();
		$data = $this->example_data;

		usort( $data, array( $this, 'mwb_wpr_usort_reorder' ) );

		$current_page = $this->get_pagenum();
		$total_items = count( $data );
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->items = $data;
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
	 * @name mwb_wpr_usort_reorder.
	 * @return array
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function mwb_wpr_usort_reorder( $cloumna, $cloumnb ) {
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'id';
		$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc';
		if ( is_numeric( $cloumna[ $orderby ] ) && is_numeric( $cloumnb[ $orderby ] ) ) {
			if ( $cloumna[ $orderby ] == $cloumnb[ $orderby ] ) {
				return 0;
			} elseif ( $cloumna[ $orderby ] < $cloumnb[ $orderby ] ) {
				$result = -1;
				return ( $order === 'asc' ) ? $result : -$result;
			} elseif ( $cloumna[ $orderby ] > $cloumnb[ $orderby ] ) {
				$result = 1;
				return ( $order === 'asc' ) ? $result : -$result;
			}
		} else {
			$result = strcmp( $cloumna[ $orderby ], $cloumnb[ $orderby ] );
			return ( $order === 'asc' ) ? $result : -$result;
		}
	}
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="mpr_points_ids[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 * This function gives points to user if he doesnot get points.
	 *
	 * @name get_users_points.
	 * @return array
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */

	function get_users_points() {
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key' => 'mwb_wpr_points',
				'compare' => 'EXISTS',
			),
			array(
				'key' => 'mwb_wpr_points',
				'compare' => 'NOT EXISTS',

			),
		);

		if ( isset( $_REQUEST['s'] ) ) {
			$args['search'] = '*' . $_REQUEST['s'] . '*';
		}
		$args['role__in'] = array( 'subscriber', 'customer' );
		$user_data = new WP_User_Query( $args );
		$user_data = $user_data->get_results();
		$points_data = array();
		foreach ( $user_data as $key => $value ) {
			$user_points = get_user_meta( $value->data->ID, 'mwb_wpr_points', true );
			$user_points = (empty($user_points))?0:$user_points;
			$points_data[] = array(
				'id' => $value->data->ID,
				'user_name' => $value->data->user_nicename,
				'user_email' => $value->data->user_email,
				'user_points' => $user_points,
			);
		}
		return $points_data;
	}
}

if ( isset( $_POST['mwb_wpr_import_user'] ) ) {

	$import_user = get_option( 'mwb_wpr_user_imported', false );

	if ( $import_user == false ) {
		$user_data = get_users();

		$points_data = array();
		$flag = false;
		$general_settings = get_option( 'mwb_wpr_settings_gallery', true );
		$coupon_settings_array = get_option( 'mwb_wpr_coupons_gallery', array() );
		foreach ( $user_data as $key => $value ) {
			$check_user = get_user_meta( $value->data->ID, 'mwb_wpr_points', false );

			if ( $check_user == false ) {

				if ( in_array( 'subscriber', $value->roles ) || in_array( 'customer', $value->roles ) ) {
					$today_date = date_i18n( 'Y-m-d h:i:sa' );
					$mwb_signup_value = isset( $general_settings['mwb_signup_value'] ) ? intval( $general_settings['mwb_signup_value'] ) : 1;
					$import_points['import_points'][] = array(
						'import_points' => $mwb_signup_value,
						'date' => $today_date,
					);

					update_user_meta( $value->data->ID, 'mwb_wpr_points', $mwb_signup_value );
					update_user_meta( $value->data->ID, 'points_details', $import_points );
					$flag = true;
					$mwb_wpr_notificatin_array = get_option( 'mwb_wpr_notificatin_array', true );
					if ( is_array( $mwb_wpr_notificatin_array ) && ! empty( $mwb_wpr_notificatin_array ) ) {
						$mwb_per_currency_spent_value = isset( $coupon_settings_array['mwb_wpr_coupon_conversion_points'] ) ? intval( $coupon_settings_array['mwb_wpr_coupon_conversion_points'] ) : 1;
						$mwb_comment_value = isset( $general_settings['mwb_comment_value'] ) ? intval( $general_settings['mwb_comment_value'] ) : 1;
						$mwb_refer_value = isset( $general_settings['mwb_refer_value'] ) ? intval( $general_settings['mwb_refer_value'] ) : 1;
						$mwb_wpr_notificatin_enable = isset( $mwb_wpr_notificatin_array['mwb_wpr_notificatin_enable'] ) ? intval( $mwb_wpr_notificatin_array['mwb_wpr_notificatin_enable'] ) : 0;
						$mwb_wpr_email_subject = isset( $mwb_wpr_notificatin_array['mwb_wpr_signup_email_subject'] ) ? $mwb_wpr_notificatin_array['mwb_wpr_signup_email_subject'] : '';
						$mwb_wpr_email_discription = isset( $mwb_wpr_notificatin_array['mwb_wpr_signup_email_discription_custom_id'] ) ? $mwb_wpr_notificatin_array['mwb_wpr_signup_email_discription_custom_id'] : '';
						$mwb_wpr_email_discription = str_replace( '[Points]', $mwb_signup_value, $mwb_wpr_email_discription );
						$mwb_wpr_email_discription = str_replace( '[Total Points]', $mwb_signup_value, $mwb_wpr_email_discription );
						$mwb_wpr_email_discription = str_replace( '[Comment Points]', $mwb_comment_value, $mwb_wpr_email_discription );
						$mwb_wpr_email_discription = str_replace( '[Refer Points]', $mwb_refer_value, $mwb_wpr_email_discription );
						$mwb_wpr_email_discription = str_replace( '[Per Currency Spent Points]', $mwb_per_currency_spent_value, $mwb_wpr_email_discription );
						if ( $mwb_wpr_notificatin_enable ) {
							$headers = array( 'Content-Type: text/html; charset=UTF-8' );
							wc_mail( $value->data->user_email, $mwb_wpr_email_subject, $mwb_wpr_email_discription, $headers );
						}
					}
				}
			}
		}
		if ( $flag ) {

			update_option( 'mwb_wpr_user_imported', true );
		}
	} else {

	}
}
if ( isset( $_GET['action'] ) && isset( $_GET['user_id'] ) ) {
	if ( $_GET['action'] == 'view' ) {
		$user_log_id = sanitize_post( $_GET['user_id'] );
		$user_log = get_user_meta( $user_log_id, 'mwb_wpr_user_log', true );
		?>
		<h1 class="wp-heading-inline" id="mwb_wpr_points_table_heading"><?php _e( 'User Coupon Details', MWB_RWPR_Domain ); ?></h1>
		<?php
		if ( isset( $user_log ) && is_array( $user_log ) && $user_log != null ) {
			?>
			<table class="form-table mwp_wpr_settings mwb_wpr_points_view" >
				<thead>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<span class="nobr"><?php echo __( 'Points', MWB_RWPR_Domain ); ?></span>
						</th>
						<th scope="row" class="titledesc">
							<span class="nobr"><?php echo __( 'Coupon Code', MWB_RWPR_Domain ); ?></span>
						</th>
						<th scope="row" class="titledesc">
							<span class="nobr"><?php echo __( 'Coupon Amount', MWB_RWPR_Domain ); ?></span>
						</th>
						<th scope="row" class="titledesc">
							<span class="nobr"><?php echo __( 'Amount Left', MWB_RWPR_Domain ); ?></span>
						</th>
						<th scope="row" class="titledesc">
							<span class="nobr"><?php echo __( 'Expiry', MWB_RWPR_Domain ); ?></span>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $user_log as $key => $mwb_user_log ) : ?>

						<tr valign="top">
							<?php foreach ( $mwb_user_log as $column_id => $column_name ) : ?>
								<td class="forminp forminp-text">
									<?php
									if ( $column_id == 'left' ) {
										$mwb_split = explode( '#', $key );
										$column_name = get_post_meta( $mwb_split[1], 'coupon_amount', true );
										echo get_woocommerce_currency_symbol() . $column_name;
									} elseif ( $column_id == 'expiry' ) {
										if ( WC()->version < '3.0.6' ) {

											$column_name = get_post_meta( $mwb_split[1], 'expiry_date', true );
											echo $column_name;
										} else {
											$column_name = get_post_meta( $mwb_split[1], 'date_expires', true );
											$dt = new DateTime( "@$column_name" );
											echo $dt->format( 'Y-m-d' );

										}
									} else {
										echo $column_name;
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
			echo '<h2>' . __( 'No Coupons Generated Yet.', MWB_RWPR_Domain ) . '<h2>';
			// http://192.168.0.162/wordpress15/wp-admin/admin.php?page=mwb-rwpr-setting&tab=points-table
		}
		?>
		<br> 
		<a  href="<?php echo MWB_RWPR_HOME_URL; ?>admin.php?page=mwb-rwpr-setting&tab=points-table" style="line-height: 2" class="button button-primary mwb_wpr_save_changes"><?php _e( 'Go Back', MWB_RWPR_Domain ); ?></a> 
							 <?php

	} else if ( $_GET['action'] == 'view_point_log' ) {
		$user_id = sanitize_post( $_GET['user_id'] );
		$point_log = get_user_meta( $user_id, 'points_details', true );
		$total_points = get_user_meta( $user_id, 'mwb_wpr_points', true )
		?>
		<h1 class="wp-heading-inline" id="mwb_wpr_points_table_heading"><?php _e( 'User Point Log Details', MWB_RWPR_Domain ); ?></h1>
		<?php
		if ( isset( $point_log ) && is_array( $point_log ) && $point_log != null ) {
			?>
			  
			<div class="mwb_wpr_wrapper_div">
				<div class="mwb_wpr_points_view">
					<table class="form-table mwp_wpr_settings" >
						<thead>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<span class="nobr"><?php echo __( 'Date & Time', MWB_RWPR_Domain ); ?></span>
								</th>
								<th scope="row" class="titledesc">
									<span class="nobr"><?php echo __( 'Point Status', MWB_RWPR_Domain ); ?></span>
								</th>
								<th scope="row" class="titledesc">
									<span class="nobr"><?php echo __( 'Activity', MWB_RWPR_Domain ); ?></span>
								</th>
							</tr>
						</thead>
					</table>
				</div>
				<?php
				if ( array_key_exists( 'registration', $point_log ) ) {
					?>
					<div class="mwb_wpr_slide_toggle">
						<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Signup Event', MWB_RWPR_Domain ); ?>
						  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
					  </p>
					  <div class="mwb_wpr_points_view"> 
						  <table class="form-table mwp_wpr_settings mwb_wpr_common_table" >
								<tr valign="top">
								<td class="forminp forminp-text"><?php echo( $point_log['registration']['0']['date'] ); ?></td>
								<td class="forminp forminp-text"><?php echo '+' . ( $point_log['registration']['0']['registration'] ); ?></td>
								<td class="forminp forminp-text"><?php _e( 'Registration Points', MWB_RWPR_Domain ); ?></td>
								</tr>
							</table>
						</div>
					</div>
					<?php
				}
				if ( array_key_exists( 'import_points', $point_log ) ) {
					?>
					<div class="mwb_wpr_slide_toggle">
						<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Signup Event', MWB_RWPR_Domain ); ?>
						  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
					  </p> 
					  <div class="mwb_wpr_points_view">  
					  <table class = "form-table mwp_wpr_settings mwb_wpr_common_table">
						<tr valign="top">
							<td class="forminp forminp-text"><?php echo( $point_log['import_points']['0']['date'] ); ?></td>
							<td class="forminp forminp-text"><?php echo '+' . ( $point_log['import_points']['0']['import_points'] ); ?></td>
							<td class="forminp forminp-text"><?php _e( 'Registration Points', MWB_RWPR_Domain ); ?></td>
						</tr>
					</table></div></div>
					<?php
				}
				if ( array_key_exists( 'Coupon_details', $point_log ) ) {
					?>
					<div class="mwb_wpr_slide_toggle">
						<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Coupon Generation', MWB_RWPR_Domain ); ?>
						  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
					  </p> 
					  <div class="mwb_wpr_points_view"> 
					  <table class = "form-table mwp_wpr_settings mwb_wpr_common_table">  
						 <?php
							foreach ( $point_log['Coupon_details'] as $key => $value ) {
								?>
							<tr valign="top">
								<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
								<td class="forminp forminp-text"><?php echo '-' . $value['Coupon_details']; ?></td>
								<td class="forminp forminp-text"><?php _e( 'Coupon Points', MWB_RWPR_Domain ); ?></td>
							</tr>
								<?php
							}
							?>
					</table></div></div> 
					<?php
				}
				if ( array_key_exists( 'points_on_order', $point_log ) ) {
					?>
				<div class="mwb_wpr_slide_toggle">
					<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Points on Order', MWB_RWPR_Domain ); ?>
					  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
				  </p>
				   <div class="mwb_wpr_points_view"> 
					  <table class = "form-table mwp_wpr_settings mwb_wpr_common_table"> 
						<?php
						foreach ( $point_log['points_on_order'] as $key => $value ) {
							?>
							<tr valign="top">
								<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
								<td class="forminp forminp-text"><?php echo '+' . $value['points_on_order']; ?></td>
								<td class="forminp forminp-text"><?php _e( 'Points earned on Order Total', MWB_RWPR_Domain ); ?></td>
							</tr>
							<?php
						}
						?>
					</table></div></div>
					<?php
				}
				if ( array_key_exists( 'product_details', $point_log ) ) {
					?>
				<div class="mwb_wpr_slide_toggle">
					<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Assigned Product Point', MWB_RWPR_Domain ); ?>
					  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
				  </p>
				    <div class="mwb_wpr_points_view"> 
				  <table class = "form-table mwp_wpr_settings mwb_wpr_common_table"> 
					<?php
					foreach ( $point_log['product_details'] as $key => $value ) {
						?>
						<tr valign="top">
							<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
							<td class="forminp forminp-text"><?php echo '+' . $value['product_details']; ?></td>
							<td class="forminp forminp-text"><?php _e( 'Product purchase Points', MWB_RWPR_Domain ); ?></td>
						</tr>
						<?php
					}
					?>
				</table></div></div>
					<?php
				}
				if ( array_key_exists( 'pro_conversion_points', $point_log ) ) {
					?>
			<div class="mwb_wpr_slide_toggle">
				<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Order Total Points', MWB_RWPR_Domain ); ?>
				  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
			  </p>
			  <div class="mwb_wpr_points_view"> 
			  <table class = "form-table mwp_wpr_settings mwb_wpr_common_table">
					<?php
					foreach ( $point_log['pro_conversion_points'] as $key => $value ) {
						?>
					<tr valign="top">
						<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
						<td class="forminp forminp-text"><?php echo '+' . $value['pro_conversion_points']; ?></td>
						<td class="forminp forminp-text"><?php _e( 'Product Conversion Points', 'MWB_RWPR_Domain' ); ?></td>
					</tr>
						<?php
					}
					?>
			</table></div></div> 
					<?php
				}
				if ( array_key_exists( 'comment', $point_log ) ) {
					?>
		<div class="mwb_wpr_slide_toggle">
			<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Product Review Point', MWB_RWPR_Domain ); ?>
			  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
		  </p>
		  <div class="mwb_wpr_points_view"> 
		  <table class = "form-table mwp_wpr_settings  mwb_wpr_common_table">
					<?php
					foreach ( $point_log['comment'] as $key => $value ) {
						?>
				<tr valign="top">
					<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
					<td class="forminp forminp-text"><?php echo '+' . $value['comment']; ?></td>
					<td class="forminp forminp-text"><?php _e( 'Comment Points', MWB_RWPR_Domain ); ?></td>
				</tr>
						<?php
					}
					?>
		</table></div></div>
					<?php
				}
				if ( array_key_exists( 'membership', $point_log ) ) {
					?>
	<div class="mwb_wpr_slide_toggle">
		<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Membership Points', MWB_RWPR_Domain ); ?>
			<a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
		</p>
		<div class="mwb_wpr_points_view"> 
		<table class = "form-table mwp_wpr_settings  mwb_wpr_common_table">
						<?php
						foreach ( $point_log['membership'] as $key => $value ) {
							?>
				<tr valign="top">
					<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
					<td class="forminp forminp-text"><?php echo '-' . $value['membership']; ?></td>
					<td class="forminp forminp-text"><?php _e( 'Membership Points', MWB_RWPR_Domain ); ?></td>
				</tr>
							<?php
						}
						?>
		</table></div></div>
					<?php
				}
				if ( array_key_exists( 'reference_details', $point_log ) ) {
					?>
		<div class="mwb_wpr_slide_toggle">
			<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Referral Earns', MWB_RWPR_Domain ); ?>
			  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
		  </p>
		  <div class="mwb_wpr_points_view"> 
		  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table">
					<?php
					foreach ( $point_log['reference_details'] as $key => $value ) {
						$user_name = '';
						if ( isset( $value['refered_user'] ) && ! empty( $value['refered_user'] ) ) {
							$user = get_user_by( 'ID', $value['refered_user'] );
							$user_name = $user->user_nicename;
						}
						?>
				<tr valign="top">
					<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
					<td class="forminp forminp-text"><?php echo '+' . $value['reference_details']; ?></td>
					<td class="forminp forminp-text">
						<?php
						_e( 'Reference Points by ', MWB_RWPR_Domain );
						echo $user_name;
						?>
					</td>
				</tr>
						<?php
					}
					?>
		</table></div></div>
					<?php
				}
				if ( array_key_exists( 'ref_product_detail', $point_log ) ) {
					?>
		<div class="mwb_wpr_slide_toggle">
			<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Referral Purchase Point', MWB_RWPR_Domain ); ?>
			  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
		  </p>
		  <div class="mwb_wpr_points_view"> 
		  <table class = "form-table mwp_wpr_settings  mwb_wpr_common_table">
					<?php
					foreach ( $point_log['ref_product_detail'] as $key => $value ) {
						?>
				<tr valign="top">
					<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
					<td class="forminp forminp-text"><?php echo '+' . $value['ref_product_detail']; ?></td>
					<td class="forminp forminp-text"><?php _e( 'Product purchased by Reffered User Points', MWB_RWPR_Domain ); ?></td>
				</tr>
						<?php
					}
					?>
		</table></div></div>
					<?php
				}
				if ( array_key_exists( 'admin_points', $point_log ) ) {
					?>
		<div class="mwb_wpr_slide_toggle">
			<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Admin Updates', MWB_RWPR_Domain ); ?>
			  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
		  </p>
		  <div class="mwb_wpr_points_view"> 
		  <table class = "form-table mwp_wpr_settings  mwb_wpr_common_table">
					<?php
					foreach ( $point_log['admin_points'] as $key => $value ) {
						$value['sign'] = isset( $value['sign'] ) ? $value['sign'] : '+/-';
						$value['reason'] = isset( $value['reason'] ) ? $value['reason'] : __( 'Updated By Admin', MWB_RWPR_Domain );
						?>
				<tr valign="top">
					<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
					<td class="forminp forminp-text"><?php echo $value['sign'] . $value['admin_points']; ?></td>
					<td class="forminp forminp-text"><?php echo $value['reason']; ?></td>
				</tr>
						<?php
					}
					?>
		</table></div></div>
					<?php
				}
				if ( array_key_exists( 'pur_by_points', $point_log ) ) {
					?>
		<div class="mwb_wpr_slide_toggle">
			<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Product has been purchased using points', MWB_RWPR_Domain ); ?>
			  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
		  </p>
		  <div class="mwb_wpr_points_view"> 
		  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table">
					<?php
					foreach ( $point_log['pur_by_points'] as $key => $value ) {
						?>
				<tr valign="top">
					<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
					<td class="forminp forminp-text"><?php echo '-' . $value['pur_by_points']; ?></td>
					<td class="forminp forminp-text"><?php _e( 'Purchased through Points', MWB_RWPR_Domain ); ?></td>
				</tr>
						<?php
					}
					?>
			</table></div></div> 
					<?php
				}
				if ( array_key_exists( 'deduction_of_points', $point_log ) ) {
					?>
			<div class="mwb_wpr_slide_toggle">
				<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Deduction of points for return request', MWB_RWPR_Domain ); ?>
				  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
			  </p>
			  <div class="mwb_wpr_points_view"> 
			  <table class = "form-table mwp_wpr_settings mwb_wpr_common_table">
					<?php
					foreach ( $point_log['deduction_of_points'] as $key => $value ) {
						?>
					<tr valign="top">
						<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
						<td class="forminp forminp-text"><?php echo '-' . $value['deduction_of_points']; ?></td>
						<td class="forminp forminp-text"><?php _e( 'Deduct Points', MWB_RWPR_Domain ); ?></td>
					</tr>
						<?php
					}
					?>
			</table></div></div> 
					<?php
				}
				if ( array_key_exists( 'return_pur_points', $point_log ) ) {
					?>
		<div class="mwb_wpr_slide_toggle">
			<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Return Points', MWB_RWPR_Domain ); ?>
			  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
		  </p>
		  <div class="mwb_wpr_points_view"> 
		  <table class = "form-table mwp_wpr_settings  mwb_wpr_common_table">
					<?php
					foreach ( $point_log['return_pur_points'] as $key => $value ) {
						?>
				<tr valign="top">
					<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
					<td class="forminp forminp-text"><?php echo '+' . $value['return_pur_points']; ?></td>
					<td class="forminp forminp-text"><?php _e( 'Returned Points', MWB_RWPR_Domain ); ?></td>
				</tr>
						<?php
					}
					?>
		</table></div></div>  
					<?php
				}
				if ( array_key_exists( 'deduction_currency_spent', $point_log ) ) {
					?>
		<div class="mwb_wpr_slide_toggle">
			<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Deduct Order Total Points', MWB_RWPR_Domain ); ?>
			  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
		  </p>
		  <div class="mwb_wpr_points_view"> 
		  <table class = "form-table mwp_wpr_settings  mwb_wpr_common_table"> 
					<?php
					foreach ( $point_log['deduction_currency_spent'] as $key => $value ) {
						?>
				<tr valign="top">
					<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
					<td class="forminp forminp-text"><?php echo '-' . $value['deduction_currency_spent']; ?></td>
					<td class="forminp forminp-text"><?php _e( 'Deduct Per Currency Spent Point', MWB_RWPR_Domain ); ?></td>
				</tr>
						<?php
					}
					?>
		</table></div></div>
					<?php
				}
				if ( array_key_exists( 'Sender_point_details', $point_log ) ) {
					?>
	<div class="mwb_wpr_slide_toggle">
		<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Shared with', MWB_RWPR_Domain ); ?>
		  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
	  </p>
	  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table"> 
					<?php
					foreach ( $point_log['Sender_point_details'] as $key => $value ) {
						$user_name = '';
						if ( isset( $value['give_to'] ) && ! empty( $value['give_to'] ) ) {
							$user = get_user_by( 'ID', $value['give_to'] );
							$user_name = $user->user_nicename;
						}
						?>
			<tr valign="top">
				<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
				<td class="forminp forminp-text"><?php echo '-' . $value['Sender_point_details']; ?></td>
				<td class="forminp forminp-text">
						<?php
						_e( 'Shared to ', MWB_RWPR_Domain );
						echo $user_name;
						?>
				</td>
			</tr>
						<?php
					}
					?>
	</table></div>
					<?php
				}
				if ( array_key_exists( 'Receiver_point_details', $point_log ) ) {
					?>
<div class="mwb_wpr_slide_toggle">
	<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Receives Points', MWB_RWPR_Domain ); ?>
	  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
  </p>
  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table">
					<?php
					foreach ( $point_log['Receiver_point_details'] as $key => $value ) {
						$user_name = '';
						if ( isset( $value['received_by'] ) && ! empty( $value['received_by'] ) ) {
							$user = get_user_by( 'ID', $value['received_by'] );
							$user_name = $user->user_nicename;
						}
						?>
		<tr valign="top">
			<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
			<td class="forminp forminp-text"><?php echo '+' . $value['Receiver_point_details']; ?> </td>
			<td class="forminp forminp-text">
						<?php
						_e( 'Received Points by ', MWB_RWPR_Domain );
						echo $user_name;
						?>
			</td>
		</tr>
						<?php
					}
					?>
</table></div>
					<?php
				}
				if ( array_key_exists( 'cart_subtotal_point', $point_log ) ) {
					?>
<div class="mwb_wpr_slide_toggle">
	<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Points Applied on Cart', MWB_RWPR_Domain ); ?>
	  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
  </p>
  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table">
					<?php
					foreach ( $point_log['cart_subtotal_point'] as $key => $value ) {
						?>
		<tr valign="top">
			<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
			<td class="forminp forminp-text"><?php echo '-' . $value['cart_subtotal_point']; ?> </td>
			<td class="forminp forminp-text"><?php _e( 'Points Used on Cart Subtotal', MWB_RWPR_Domain ); ?></td>
		</tr>
						<?php
					}
					?>
</table></div>
					<?php
				}
				if ( array_key_exists( 'expired_details', $point_log ) ) {
					?>
<div class="mwb_wpr_slide_toggle">
	<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Expired Points', MWB_RWPR_Domain ); ?>
	  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
  </p>
  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table">
					<?php
					foreach ( $point_log['expired_details'] as $key => $value ) {
						?>
		<tr valign="top">
			<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
			<td class="forminp forminp-text"><?php echo '-' . $value['expired_details']; ?></td>
			<td class="forminp forminp-text"><?php _e( 'Get Expired', MWB_RWPR_Domain ); ?></td>
		</tr>
						<?php
					}
					?>
</table></div>
					<?php
				}
				if ( array_key_exists( 'deduct_currency_pnt_cancel', $point_log ) ) {
					?>
<div class="mwb_wpr_slide_toggle">
	<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Order Points Deducted due to Cancelation of Order', MWB_RWPR_Domain ); ?>
	  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
  </p>
  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table">
					<?php
					foreach ( $point_log['deduct_currency_pnt_cancel'] as $key => $value ) {
						?>
		<tr valign="top">
			<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
			<td class="forminp forminp-text"><?php echo '-' . $value['deduct_currency_pnt_cancel']; ?></td>
			<td class="forminp forminp-text"><?php _e( 'Deducted Points', MWB_RWPR_Domain ); ?></td>
		</tr>
						<?php
					}
					?>
</table></div>
					<?php
				}
				if ( array_key_exists( 'deduct_bcz_cancel', $point_log ) ) {
					?>
<div class="mwb_wpr_slide_toggle">
	<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Assigned Points Deducted due to Cancelation of Order', MWB_RWPR_Domain ); ?>
	  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
  </p>
  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table">
					<?php
					foreach ( $point_log['deduct_bcz_cancel'] as $key => $value ) {
						?>
		<tr valign="top">
			<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
			<td class="forminp forminp-text"><?php echo '-' . $value['deduct_bcz_cancel']; ?></td>
			<td class="forminp forminp-text"><?php _e( 'Deducted Points', MWB_RWPR_Domain ); ?></td>
		</tr>
						<?php
					}
					?>
</table></div>
					<?php
				}
				if ( array_key_exists( 'pur_points_cancel', $point_log ) ) {
					?>
<div class="mwb_wpr_slide_toggle">
	<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Points Returned due to Cancelation of Order', MWB_RWPR_Domain ); ?>
	  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
  </p>
  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table">
					<?php
					foreach ( $point_log['pur_points_cancel'] as $key => $value ) {
						?>
		<tr valign="top">
			<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
			<td class="forminp forminp-text"><?php echo '+' . $value['pur_points_cancel']; ?></td>
			<td class="forminp forminp-text"><?php _e( 'Returned Points', MWB_RWPR_Domain ); ?></td>
		</tr>
						<?php
					}
					?>
</table></div>
					<?php
				}
				// MWB CUSTOM CODE
				if ( array_key_exists( 'pur_pro_pnt_only', $point_log ) ) {
					?>
<div class="mwb_wpr_slide_toggle">
	<p class="mwb_wpr_view_log_notice mwb_wpr_common_slider" ><?php _e( 'Points Applied on Cart', MWB_RWPR_Domain ); ?>
	  <a class ="mwb_wpr_open_toggle"  href="javascript:;"></a>
  </p>
  <table class = "form-table mwp_wpr_settings mwb_wpr_points_view mwb_wpr_common_table">
					<?php
					foreach ( $point_log['pur_pro_pnt_only'] as $key => $value ) {
						?>
		<tr valign="top">
			<td class="forminp forminp-text"><?php echo $value['date']; ?></td>
			<td class="forminp forminp-text"><?php echo '-' . $value['pur_pro_pnt_only']; ?> </td>
			<td class="forminp forminp-text"><?php _e( 'Product Purchased by Points', MWB_RWPR_Domain ); ?></td>
		</tr>
						<?php
					}
					?>
</table></div>
					<?php
				}
				// END OF MWB CUSTOM CODE
				?>

<table class = "form-table mwp_wpr_settings mwb_wpr_points_view">
	<tr valign="top">
		<td class="forminp forminp-text"><strong><?php _e( 'Total Points', MWB_RWPR_Domain ); ?></strong></td>
		<td class="forminp forminp-text"><strong><?php echo $total_points; ?></strong>
		</td>
		<td class="forminp forminp-text"></td>
	</tr>
</table>
</div>
			<?php
		} else {
			echo '<h2>' . __( 'No Points Generated Yet.', MWB_RWPR_Domain ) . '<h2>';
		}
	}
} else {
	?>
	<h1 class="wp-heading-inline" id="mwb_wpr_points_table_heading"><?php _e( 'Points Table', MWB_RWPR_Domain ); ?></h1>
	<?php
	$general_settings = get_option( 'mwb_wpr_settings_gallery', true );
	$enable_mwb_signup = isset( $general_settings['enable_mwb_signup'] ) ? intval( $general_settings['enable_mwb_signup'] ) : 0;
	if ( $enable_mwb_signup ) {
		$import_user = get_option( 'mwb_wpr_user_imported', false );

		if ( $import_user == false ) {
			?>
			<div class="mwb_wpr_import_user container">
				<h3>
					<?php _e( 'Import Users', MWB_RWPR_Domain ); ?>
				</h3>
				<p>
					<?php _e( 'Import existing users and assign them with Sign Up Points', MWB_RWPR_Domain ); ?>
				</p>
				<input type="submit" value="<?php _e( 'Import', MWB_RWPR_Domain ); ?>" id="mwb_wpr_import" class="page-title-action button button-primary button-large mwb_wpr_save_changes" name="mwb_wpr_import_user">
			</div>
			
			<?php
		} else {
			$args['role__in'] = array( 'subscriber', 'customer' );

			$user_data = get_users( $args );
			$guest_flag = false;
			foreach ( $user_data as $key => $value ) {
				$user_id = $value->data->ID;
				$guest_points = get_user_meta( $value->data->ID, 'mwb_wpr_points', false );

				if ( $guest_points == false ) {
					$guest_flag = true;
				}
			}
			if ( $guest_flag ) {
				?>
				<div class="mwb_wpr_import_user container">
					<h3>
						<?php _e( 'Import Users', MWB_RWPR_Domain ); ?>
					</h3>
					<p>
						<?php _e( 'Import existing users and assign them with Sign Up Points', MWB_RWPR_Domain ); ?>
					</p>
					<input type="button" value="<?php _e( 'Import', MWB_RWPR_Domain ); ?>" id="mwb_wpr_import" class="page-title-action button button-primary button-large mwb_wpr_save_changes" name="mwb_wpr_import_user">
				</div>
				
				<?php
				update_option( 'mwb_wpr_user_imported', false );
			}
		}
	}
	?>
	<form method="post">
		<input type="hidden" name="page" value="<?php _e( 'points_log_list_table', MWB_RWPR_Domain ); ?>">
		<?php
		$myListTable = new Points_Log_List_Table();
		$myListTable->prepare_items();
		$myListTable->search_box( __( 'Search Users', MWB_RWPR_Domain ), 'mwb-wpr-user' );
		$myListTable->display();
		?>
	</form>
	<?php
}

