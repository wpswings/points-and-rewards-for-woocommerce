<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    wps-wpr-points-log-template
 * @subpackage points-and-rewards-for-wooCommerce/public/partials
 */

$user_id = $user_ID;
if ( isset( $user_id ) && null != $user_id && is_numeric( $user_id ) ) {
	$point_log    = get_user_meta( $user_id, 'points_details', true );
	$total_points = get_user_meta( $user_id, 'wps_wpr_points', true );
	if ( isset( $point_log ) && is_array( $point_log ) && null != $point_log ) {
		?>
		<h2><?php esc_html_e( ' Point Log Table', 'points-and-rewards-for-woocommerce' ); ?></h2>
		<?php if ( array_key_exists( 'registration', $point_log ) || array_key_exists( 'import_points', $point_log ) ) { ?>
			<div class="wps_wpr_slide_toggle">
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<tr>
						<?php
						if ( array_key_exists( 'registration', $point_log ) ) {
							?>

							<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Signup Event', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
							<td>
								<?php
								echo esc_html( wps_wpr_set_the_wordpress_date_format( $point_log['registration']['0']['date'] ) );
								?>
							</td>
							<td>
								<?php
								echo '+' . esc_html( $point_log['registration']['0']['registration'] );
								?>
							</td>
							<?php
						}
						?>
					</tr>
					<tr>
						<?php
						if ( array_key_exists( 'import_points', $point_log ) ) {
							?>

							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $point_log['import_points']['0']['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $point_log['import_points']['0']['import_points'] ); ?></td>
							<?php
						}
						?>
					</tr>
				</table>
			</div> 
			<?php
		}
		if ( array_key_exists( 'refund_points_applied_on_cart', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Apply Points of cart refunded after the order is canceled', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
				<table class="wps_wpr_common_table">
					<thead>
							<tr>
								<th class="wps-wpr-view-log-Date">
									<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
								</th>
								<th class="wps-wpr-view-log-Status">
									<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
								</th>
							</tr>
						</thead> 
					<?php
					foreach ( $point_log['refund_points_applied_on_cart'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['refund_points_applied_on_cart'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'Coupon_details', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Coupon Creation', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['Coupon_details'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['Coupon_details'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'product_details', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points Earned via Particular Product', 'points-and-rewards-for-woocommerce' ); ?> <a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['product_details'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['product_details'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'pro_conversion_points', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Per Currency Spent Points', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php foreach ( $point_log['pro_conversion_points'] as $key => $value ) { ?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['pro_conversion_points'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'points_on_order', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points Log Table With Points Earned Each time on Order Total', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php foreach ( $point_log['points_on_order'] as $key => $value ) { ?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['points_on_order'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'refund_points_on_order', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Deducted Points earned on Order Total on Order Refund', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php foreach ( $point_log['refund_points_on_order'] as $key => $value ) { ?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['refund_points_on_order'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'subscription_renewal_points', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Subscription Renewal Points', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php foreach ( $point_log['subscription_renewal_points'] as $key => $value ) { ?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['subscription_renewal_points'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'refund_subscription__renewal_points', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Refund Subscription Renewal Points', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php foreach ( $point_log['refund_subscription__renewal_points'] as $key => $value ) { ?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['refund_subscription__renewal_points'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'cancel_points_on_order_total', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Deducted Points earned on Order Total on Order Cancellation', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php foreach ( $point_log['cancel_points_on_order_total'] as $key => $value ) { ?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['cancel_points_on_order_total'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'comment', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points earned via giving review/comment', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
				<table class="wps_wpr_common_table">
					<thead>
							<tr>
								<th class="wps-wpr-view-log-Date">
									<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
								</th>
								<th class="wps-wpr-view-log-Status">
									<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
								</th>
							</tr>
						</thead> 
					<?php
					foreach ( $point_log['comment'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['comment'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'membership', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Membership Points', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<a class ="wps_wpr_open_toggle"  href="javascript:;"></a>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['membership'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['membership'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'pur_by_points', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Deduction of points as you have purchased your product through points', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['pur_by_points'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['pur_by_points'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'deduction_of_points', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Deduction of points for your return request', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['deduction_of_points'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['deduction_of_points'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table> 
			</div>
			<?php
		}
		if ( array_key_exists( 'return_pur_points', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points returned successfully on your return request', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['return_pur_points'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?> </td>
							<td><?php echo '+' . esc_html( $value['return_pur_points'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'deduction_currency_spent', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Deduct Per Currency Spent Point on your return request', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['deduction_currency_spent'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['deduction_currency_spent'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'cart_subtotal_point', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points Applied on Cart', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['cart_subtotal_point'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['cart_subtotal_point'] ); ?></td>
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
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Order Rewards Points', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['order__rewards_points'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['order__rewards_points'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'api_membership_logs', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Membership updated via API', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['api_membership_logs'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['api_membership_logs'] ); ?></td>
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
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Oops!! Points are expired!', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['expired_details'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['expired_details'] ); ?></td>
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
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Order Points Deducted due to Cancelation of Order', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['deduct_currency_pnt_cancel'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['deduct_currency_pnt_cancel'] ); ?></td>
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
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Assigned Points Deducted due Cancelation of Order', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['deduct_bcz_cancel'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['deduct_bcz_cancel'] ); ?></td>
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
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points Returned due to Cancelation of Order', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['pur_points_cancel'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['pur_points_cancel'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div> 
			<?php
		}
		// WPS CUSTOM CODE.
		if ( array_key_exists( 'pur_pro_pnt_only', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points deducted for purchasing the product', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['pur_pro_pnt_only'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['pur_pro_pnt_only'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div> 
			<?php
		}
		// END OF WPS CUSTOM CODE.
		if ( array_key_exists( 'Sender_point_details', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points deducted successfully as you have shared your points', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Activity">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Shared to', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['Sender_point_details'] as $key => $value ) {
						$user_name = '';
						if ( isset( $value['given_to'] ) && ! empty( $value['given_to'] ) ) {
							$user      = get_user_by( 'ID', $value['given_to'] );
							if ( isset( $user ) && ! empty( $user ) ) {
								$user_name = $user->user_nicename;
							} else {
								$user_name = esc_html__( 'This user doesn\'t exist', 'points-and-rewards-for-woocommerce' );
							}
						}
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?> </td>
							<td><?php echo '-' . esc_html( $value['Sender_point_details'] ); ?></td>
							<td>
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
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points received successfully as someone has shared', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>

				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Activity">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Received Points via ', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['Receiver_point_details'] as $key => $value ) {
						$user_name = '';
						if ( isset( $value['received_by'] ) && ! empty( $value['received_by'] ) ) {
							$user      = get_user_by( 'ID', $value['received_by'] );
							if ( isset( $user ) && ! empty( $user ) ) {
								$user_name = $user->user_nicename;
							} else {
								$user_name = esc_html__( 'This user doesn\'t exist', 'points-and-rewards-for-woocommerce' );
							}
						}
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['Receiver_point_details'] ); ?></td>
							<td>
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
		if ( array_key_exists( 'admin_points', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Updated By Admin', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Activity">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Reason', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['admin_points'] as $key => $value ) {
						$value['sign']   = isset( $value['sign'] ) ? $value['sign'] : '+/-';
						$value['reason'] = isset( $value['reason'] ) ? $value['reason'] : __( 'Updated By Admin', 'points-and-rewards-for-woocommerce' );
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo esc_html( $value['sign'] ) . esc_html( $value['admin_points'] ); ?></td>
							<td><?php echo esc_html( $value['reason'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'points_reset_by_admin', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points Reset By Admin', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['points_reset_by_admin'] as $key => $value ) {
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '-' . esc_html( $value['points_reset_by_admin'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'points_deduct_wallet', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Points Deduct to the wallet', 'points-and-rewards-for-woocommerce' ); ?>
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
					foreach ( $point_log['points_deduct_wallet'] as $key => $value ) {
						?>
						<tr valign="top">
							<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
							<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['points_deduct_wallet'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}
		if ( array_key_exists( 'reset_users_points_logs', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider" ><?php esc_html_e( 'Your points has been reset by Admin', 'points-and-rewards-for-woocommerce' ); ?>
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
					foreach ( $point_log['reset_users_points_logs'] as $key => $value ) {
						?>
						<tr valign="top">
							<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
							<td class="forminp forminp-text"><?php echo '-' . esc_html( $value['reset_users_points_logs'] ); ?></td>
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
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( ' Check Number of Points Earned by Referring Others ', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Activity">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Sign Up by', 'points-and-rewards-for-woocommerce' ); ?></span>
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
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['reference_details'] ); ?></td>
							<td>
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
			<?php
		}
		if ( array_key_exists( 'ref_product_detail', $point_log ) ) {
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php esc_html_e( 'Points earned by the purchase has been made by referrals', 'points-and-rewards-for-woocommerce' ); ?><a class ="wps_wpr_open_toggle"  href="javascript:;"></a></p>
				<table class="wps_wpr_common_table">
					<thead>
						<tr>
							<th class="wps-wpr-view-log-Date">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Status">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
							<th class="wps-wpr-view-log-Activity">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Product purchase by Referred User Points', 'points-and-rewards-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<?php
					foreach ( $point_log['ref_product_detail'] as $key => $value ) {
						$user_name = '';
						if ( isset( $value['refered_user'] ) && ! empty( $value['refered_user'] ) ) {
							$user      = get_user_by( 'ID', $value['refered_user'] );
							if ( isset( $user ) && ! empty( $user ) ) {
								$user_name = $user->user_nicename;
							} else {
								$user_name = esc_html__( 'This user doesn\'t exist', 'points-and-rewards-for-woocommerce' );
							}
						}
						?>
						<tr>
							<td><?php echo esc_html( wps_wpr_set_the_wordpress_date_format( $value['date'] ) ); ?></td>
							<td><?php echo '+' . esc_html( $value['ref_product_detail'] ); ?></td>
							<td>
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
		do_action( 'wps_points_on_first_order', $point_log );

		?>
		<div class="wps_wpr_slide_toggle">
			<table class="wps_wpr_total_points">
				<tr>
					<td><h4><b><?php esc_html_e( 'Total Points', 'points-and-rewards-for-woocommerce' ); ?></b></h4></td>
					<td><h4><b><?php echo esc_html( $total_points ); ?></b></h4></td>
					<td></td>
				</tr>        
			</table>
		</div>
		<?php
	} else {
		echo '<h3>' . esc_html__( 'No Points Generated Yet.', 'points-and-rewards-for-woocommerce' ) . '<h3>';
	}
}
?>
