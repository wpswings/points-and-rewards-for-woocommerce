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
 * @link http://www.makewebbetter.com/
 */
class Points_Log_List_Table extends WP_List_Table {

	public $example_data;

	/**
	 * This construct colomns in point table.
	 *
	 * @name get_columns.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	function get_columns() {

		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'user_name' => __( 'User Name', MWB_RWPR_Domain ),
			'user_email'    => __( 'User Email', MWB_RWPR_Domain ),
			'user_points'     => __( 'User Points', MWB_RWPR_Domain ),
			'user_level' => __( 'Level', MWB_RWPR_Domain ),

		);
		return $columns;
	}
	/**
	 * This show points table list.
	 *
	 * @name column_default.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'user_name':
				return $item[ $column_name ];
			case 'user_email':
				return '<b>' . $item[ $column_name ] . '</b>';
			case 'user_points':
				return '<b>' . $item[ $column_name ] . '</b>';
			case 'user_level':
				return '<b>' . $item[ $column_name ] . '</b>';

			default:
				return false;
		}
	}



	/**
	 * Perform admin bulk action setting for points table.
	 *
	 * @name process_bulk_action.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function process_bulk_action() {

		if ( 'bulk-delete' === $this->current_action() ) {

			if ( isset( $_POST['mpr_points_ids'] ) && ! empty( $_POST['mpr_points_ids'] ) ) {
				$all_id = $_POST['mpr_points_ids'];
				foreach ( $all_id as $key => $value ) {

					delete_user_meta( $value, 'membership_level' );
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
	 * @link http://www.makewebbetter.com/
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
	 * @link http://www.makewebbetter.com/
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
	 * @link http://www.makewebbetter.com/
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
		$tst = usort( $data, array( $this, 'mwb_wpr_usort_reorder' ) );
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
	 * @link http://www.makewebbetter.com/
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
	 * @link http://www.makewebbetter.com/
	 */
	function get_users_points() {
		$args['meta_query'] = array(
			'relation' => 'AND',
			array(
				'key' => 'membership_level',
				'compare' => 'EXISTS',
			),
			array(
				'key' => 'mwb_wpr_points',

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
			$points_data[] = array(
				'id' => $value->data->ID,
				'user_name' => $value->data->user_nicename,
				'user_email' => $value->data->user_email,
				'user_points' => get_user_meta( $value->data->ID, 'mwb_wpr_points', true ),
				'user_level' => get_user_meta( $value->data->ID, 'membership_level', true ),
			);
		}
		return $points_data;
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

