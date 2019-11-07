<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
 * Addon Setting Template
 */

$mwb_addon_data = wp_remote_get( wp_upload_dir()['baseurl'] . '/mwb_wpr_json/api.json' );
if ( isset( $_POST['mwb_wpr_submit_suggestion'] ) ) {
	$mwb_wpr_addon_name = $_POST['mwb_wpr_subject'];
	$mwb_wpr_suggestion = $_POST['mwb_wpr_suggestion_for_addon'];
	if ( isset( $mwb_wpr_addon_name ) && ! empty( $mwb_wpr_addon_name ) ) {
		$mwb_admin_email = get_option( 'admin_email', null );
		$subject = __( 'Suggestion for Points and Reward Addon', MWB_WPR_Domain );
		$mail_header = __( 'Suggestion for ', MWB_WPR_Domain );
		$mail_footer = '';
		$message = '<html>
				<body>
					<style>
						body {
							box-shadow: 2px 2px 10px #ccc;
							color: #767676;
							font-family: Arial,sans-serif;
							margin: 80px auto;
							max-width: 700px;
							padding-bottom: 30px;
							width: 100%;
						}

						h2 {
							font-size: 30px;
							margin-top: 0;
							color: #fff;
							padding: 40px;
							background-color: #557da1;
						}

						h4 {
							color: #557da1;
							font-size: 20px;
							margin-bottom: 10px;
						}

						.content {
							padding: 0 40px;
						}

						.Customer-detail ul li p {
							margin: 0;
						}

						.details .Shipping-detail {
							width: 40%;
							float: right;
						}

						.details .Billing-detail {
							width: 60%;
							float: left;
						}

						.details .Shipping-detail ul li,.details .Billing-detail ul li {
							list-style-type: none;
							margin: 0;
						}

						.details .Billing-detail ul,.details .Shipping-detail ul {
							margin: 0;
							padding: 0;
						}

						.clear {
							clear: both;
						}

						table,td,th {
							border: 2px solid #ccc;
							padding: 15px;
							text-align: left;
						}

						table {
							border-collapse: collapse;
							width: 100%;
						}
						.info {
							display: inline-block;
						}

						.bold {
							font-weight: bold;
						}

						.footer {
							margin-top: 30px;
							text-align: center;
							color: #99B1D8;
							font-size: 12px;
						}
						dl.variation dd {
							font-size: 12px;
							margin: 0;
						}
					</style>

					<div style="padding: 36px 48px; background-color:#557DA1;color: #fff; font-size: 30px; font-weight: 300; font-family:helvetica;" class="header">
						' . $mail_header . $mwb_wpr_addon_name . '
					</div>		

					<div class="content">
						<div class="Order">
							<table>
								<tbody>' . $mwb_wpr_suggestion . '</tbody>
							</table>
						</div>
					</div>
					<div style="text-align: center; padding: 10px;" class="footer">
						' . $mail_footer . '
					</div>
				</body>
				</html>';

		$to = 'support@makewebbetter.com';
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		wc_mail( $to, $subject, $message, $headers );
	}
}
?>
<div class="mwb_wpr_addons_wrapper">
	<?php
	if ( array_key_exists( 'body', $mwb_addon_data ) ) {

		if ( isset( $mwb_addon_data['body'] ) && ! empty( $mwb_addon_data['body'] ) ) {
			$mwb_addon_data = json_decode( $mwb_addon_data['body'] );
			if ( isset( $mwb_addon_data ) && ! empty( $mwb_addon_data ) ) {

				foreach ( $mwb_addon_data as $key => $value ) {
					?>
				<div class="mwb_wpr_addon">
					<?php if ( $value->status == 'comingsoon' ) { ?>
					<div class="mwb_wpr_coming_soon_mode"><span class="mwb_wpr_soon_txt">coming soon</span></div>
						<?php
					}
					if ( $value->status == 'featured' ) {
						?>
						<div class="mwb_wpr_featured_mode"><img src="<?php echo MWB_WPR_URL . 'assets/images/trending.png'; ?>" alt=""></div>
						<?php
					}
					?>
					<div class="mwb_wpr_addon_heading_img">
						<?php
						if ( empty( $value->image_link ) ) {
							$value->image_link = $value->placeholder;
						}
						?>
						<a href="<?php echo $value->url; ?>"><img src="<?php echo $value->image_link; ?>" alt=""></a>
					</div>
					<div class="mwb_wpr_addon_heading">
						<span class="mwb_wpr_addon_title"><?php echo $value->name; ?></span>
						<span class="mwb_wpr_addon_desc"><?php echo $value->description; ?></span>
					</div>
					<div class="mwb_wpr_addon_buy_wrap">
						<div class="mwb_wpr_addon_buy">
							<?php if ( $value->status != 'comingsoon' ) { ?>
								<span class="mwb_wpr_addon_buy_now"><a href="<?php echo $value->url; ?>">Buy</a></span>
							<?php } ?>
							<span class="mwb_wpr_addon_buy_price"><?php echo $value->price; ?></span>
						</div>
						<div class="mwb_wpr_addon_suggest">
							<input type="button" name="mwb_wpr_addon_suggestion" id="mwb_wpr_addon_suggestion" class="mwb_wpr_addon_suggestion" value="<?php _e( 'Suggestion', MWB_WPR_Domain ); ?>">
						</div>
					</div>
				</div>
					<?php
				}
			}
		}
	}
	?>
</div>
<div class="mwb_suggestion_form" style="display: none;">
	<div class="mwb_suggestion_form_content">
		<h2>Suggestion or Query</h2>
		<div class="mwb_wpr_close_modal">×</div>
		<form action="" method="post">
			<label class="mwb_wpr_label"><?php _e( 'Addon Name: ', MWB_WPR_Domain ); ?></label>
			<input type="text" name="mwb_wpr_subject" class="mwb_wpr_subject" required="required">
			<label class="mwb_wpr_label"><?php _e( 'Suggestion: ', MWB_WPR_Domain ); ?></label>
			<textarea name="mwb_wpr_suggestion_for_addon" class="mwb_wpr_suggestion_for_addon" required="required"></textarea>
			<div class="mwb_wpr_submit_wrap">
				<input type="submit" name="mwb_wpr_submit_suggestion" class="mwb_wpr_submit_suggestion">
			</div>
		</form>
	</div>
</div>
