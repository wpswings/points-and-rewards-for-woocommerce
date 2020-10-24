<?php
/**
 * Exit if accessed directly
 *
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
 * @name Membership_Log_List_Table
 * @since      1.0.0
 * @category Class
 * @author makewebbetter<webmaster@makewebbetter.com>
 * @link http://www.makewebbetter.com/
 */
class Membership_Log_List_Table extends WP_List_Table {
	/**
	 * This variable used for the totoal data.
	 *
	 * @var $example_data
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public $example_data;

	/**
	 * This construct colomns in point table.
	 *
	 * @name get_columns.
	 * @since      1.0.0
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function get_columns() {

		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'user_name'   => __( 'User Name', 'points-and-rewards-for-woocommerce' ),
			'user_email'  => __( 'User Email', 'points-and-rewards-for-woocommerce' ),
			'user_points' => __( 'User Points', 'points-and-rewards-for-woocommerce' ),
			'user_level'  => __( 'Level', 'points-and-rewards-for-woocommerce' ),

		);
		return $columns;
	}
	/**
	 * This show points table list.
	 *
	 * @name column_default.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @since      1.0.0
	 * @link http://www.makewebbetter.com/
	 * @param array  $item array of the row.
	 * @param string $column_name column name of the Table.
	 */
	public function column_default( $item, $column_name ) {

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
	 * @since      1.0.0
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function process_bulk_action() {

		if ( 'bulk-delete' === $this->current_action() ) {
			if ( isset( $_POST['membership-log'] ) ) {
				$mwb_membership_nonce = sanitize_text_field( wp_unslash( $_POST['membership-log'] ) );
				if ( wp_verify_nonce( $mwb_membership_nonce, 'membership-log' ) ) {
					if ( isset( $_POST['mpr_points_ids'] ) && ! empty( $_POST['mpr_points_ids'] ) ) {
						$all_id = map_deep( wp_unslash( $_POST['mpr_points_ids'] ), 'sanitize_text_field' );
						if ( ! empty( $all_id ) && is_array( $all_id ) ) {
							foreach ( $all_id as $key => $value ) {

								delete_user_meta( $value, 'membership_level' );
							}
						}
					}
				}
			}
		}
	}
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @name process_bulk_action.
	 * @since      1.0.0
	 * @return array
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'points-and-rewards-for-woocommerce' ),
		);
		return $actions;
	}

	/**
	 * Returns an associative array containing the bulk action for sorting.
	 *
	 * @name get_sortable_columns.
	 * @since      1.0.0
	 * @return array
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
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
	 * Prepare items for sorting.
	 *
	 * @name prepare_items.
	 * @since      1.0.0
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function prepare_items() {
		$per_page              = 10;
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();

		$this->example_data = $this->get_users_points();
		$data               = $this->example_data;
		$tst                = usort( $data, array( $this, 'mwb_wpr_usort_reorder' ) );
		$current_page       = $this->get_pagenum();
		$total_items        = count( $data );
		$data               = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->items        = $data;
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
	 * @since      1.0.0
	 * @return array
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 * @param array $cloumna column id .
	 * @param array $cloumnb .
	 */
	public function mwb_wpr_usort_reorder( $cloumna, $cloumnb ) {
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
	 * Print the checkobox in the Table.
	 *
	 * @name column_cb.
	 * @since      1.0.0
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
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
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function get_users_points() {
		$args['meta_query'] = array(
			'relation' => 'AND',
			array(
				'key'     => 'membership_level',
				'compare' => 'EXISTS',
			),
			array(
				'key' => 'mwb_wpr_points',

			),
		);
		if ( isset( $_REQUEST['s'] ) ) {
			$data           = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
			$args['search'] = '*' . $data . '*';
		}
		$user_data        = new WP_User_Query( $args );
		$user_data        = $user_data->get_results();
		$points_data      = array();
		foreach ( $user_data as $key => $value ) {
			$points_data[] = array(
				'id'          => $value->data->ID,
				'user_name'   => $value->data->user_nicename,
				'user_email'  => $value->data->user_email,
				'user_points' => get_user_meta( $value->data->ID, 'mwb_wpr_points', true ),
				'user_level'  => get_user_meta( $value->data->ID, 'membership_level', true ),
			);
		}
		return $points_data;
	}
}
?>
<h3 class="wp-heading-inline" id="mwb_wpr_points_table_heading">
	<?php esc_html_e( 'Membership Log', 'points-and-rewards-for-woocommerce' ); ?></h3>
<form method="post">
	<input type="hidden" name="page"
		value="<?php esc_html_e( 'points_log_list_table', 'points-and-rewards-for-woocommerce' ); ?>">
	<?php wp_nonce_field( 'membership-log', 'membership-log' ); ?>
	<?php
	$mylisttable = new Membership_Log_List_Table();
	$mylisttable->prepare_items();
	$mylisttable->search_box( __( 'Search Users', 'points-and-rewards-for-woocommerce' ), 'mwb-wpr-user' );
	$mylisttable->display();
	?>
</form>
