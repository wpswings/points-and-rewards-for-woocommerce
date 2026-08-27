<?php
/**
 * Free vs Pro comparison template
 *
 * @since      2.10.2
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials/templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wps_wpr_is_pro_active = function_exists( 'wps_wpr_is_active' ) ? wps_wpr_is_active() : false;

$wps_free_vs_pro_features = array(
	array(
		'feature'     => __( 'Points Earning System', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Reward customers with points for purchases and actions', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Points Redemption', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Allow customers to redeem points for discounts at checkout', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Sign-up Points', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Reward new customers with points upon registration', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Referral System', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Basic referral tracking and points allocation', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Points Notification Emails', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Automated email notifications for points activities', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Points Table & Logs', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'View and manage customer points from admin panel', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Basic Membership Levels', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Create simple membership tiers based on points', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Product-Specific Points', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Assign custom points to individual products', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Order Total Points Rules', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Set points based on order value ranges', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Basic Gamification', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Simple gamification features to engage customers', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Subscription Integration', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Full integration with WooCommerce Subscriptions', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Multi-Currency Support', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Advanced multi-currency points conversion and management', 'points-and-rewards-for-woocommerce' ),
		'free'        => true,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Advanced Membership Levels', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Membership tiers with advanced rules and exclusive benefits', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Category-Based Points', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Assign points based on product categories', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Points Expiry System', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Set expiration dates for earned points with automated notifications', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Advanced Referral Program', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Multi-level referral tracking with lifetime commissions', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Purchase through Points', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Allow customers to buy through points', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Comment Points/Review Points ', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Reward customers for leaving product reviews and comments', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Birthday Points', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Send birthday points to customers automatically', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'At-Risk Customer', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Recover abandoned carts by offering bonus points', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Points Import/Export', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Bulk import/export customer points via CSV', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	
	array(
		'feature'     => __( 'API & Webhook Support', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'REST API endpoints and webhooks for custom integrations', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Advanced Gamification', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Spin wheel, scratch cards, advanced badges, and challenges', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'SMS/WhatsApp Notifications', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Send points notifications via SMS and WhatsApp', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Advanced Campaign System', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Create time-limited point campaigns with custom rules', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Priority Support', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Get priority email and ticket support', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Display Total Eraning Point', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Display the total points earned by each customer', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	array(
		'feature'     => __( 'Advanced redemption', 'points-and-rewards-for-woocommerce' ),
		'description' => __( 'Advanced point redemption options', 'points-and-rewards-for-woocommerce' ),
		'free'        => false,
		'pro'         => true,
	),
	
);

$wps_pro_upgrade_url = 'https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro';
?>
<div class="wps-free-vs-pro__wrapper">
	<?php if ( ! $wps_wpr_is_pro_active ) : ?>
	<section class="wps-free-vs-pro__hero">
		<div class="wps-free-vs-pro__hero-content">
			<span class="wps-free-vs-pro__badge"><?php esc_html_e( 'UPGRADE', 'points-and-rewards-for-woocommerce' ); ?></span>
			<h2><?php esc_html_e( 'Unlock the Full Power of Points and Rewards', 'points-and-rewards-for-woocommerce' ); ?></h2>
			<p><?php esc_html_e( 'Take your loyalty program to the next level with advanced features, integrations, and priority support.', 'points-and-rewards-for-woocommerce' ); ?></p>
			<div class="wps-free-vs-pro__hero-actions wp-core-ui">
				<a href="<?php echo esc_url( $wps_pro_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero free_vs_pro_button_design">
					<?php esc_html_e( 'Upgrade to Pro', 'points-and-rewards-for-woocommerce' ); ?>
				</a>
				<a href="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/?utm_source=wpswings-par-demo&utm_medium=par-org-backend&utm_campaign=demo" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero free_vs_pro_button_design">
					<?php esc_html_e( 'View Live Demo', 'points-and-rewards-for-woocommerce' ); ?>
				</a>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section class="wps-free-vs-pro__comparison">
		<div class="wps-free-vs-pro__table-wrapper">
			<table class="wps-free-vs-pro__table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th class="wps-free-vs-pro__feature-col"><?php esc_html_e( 'Feature', 'points-and-rewards-for-woocommerce' ); ?></th>
						<th class="wps-free-vs-pro__free-col">
							<span class="wps-free-vs-pro__version-badge wps-free-vs-pro__version-badge--free">
								<?php esc_html_e( 'Free', 'points-and-rewards-for-woocommerce' ); ?>
							</span>
						</th>
						<th class="wps-free-vs-pro__pro-col">
							<span class="wps-free-vs-pro__version-badge wps-free-vs-pro__version-badge--pro">
								<?php esc_html_e( 'Pro', 'points-and-rewards-for-woocommerce' ); ?>
							</span>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $wps_free_vs_pro_features as $feature ) : ?>
					<tr>
						<td class="wps-free-vs-pro__feature-cell">
							<strong><?php echo esc_html( $feature['feature'] ); ?></strong>
							<span class="wps-free-vs-pro__feature-desc"><?php echo esc_html( $feature['description'] ); ?></span>
						</td>
						<td class="wps-free-vs-pro__status-cell">
							<?php if ( $feature['free'] ) : ?>
								<span class="wps-free-vs-pro__icon wps-free-vs-pro__icon--yes dashicons dashicons-yes-alt"></span>
							<?php else : ?>
								<span class="wps-free-vs-pro__icon wps-free-vs-pro__icon--no dashicons dashicons-minus"></span>
							<?php endif; ?>
						</td>
						<td class="wps-free-vs-pro__status-cell">
							<?php if ( $feature['pro'] ) : ?>
								<span class="wps-free-vs-pro__icon wps-free-vs-pro__icon--yes dashicons dashicons-yes-alt"></span>
							<?php else : ?>
								<span class="wps-free-vs-pro__icon wps-free-vs-pro__icon--no dashicons dashicons-minus"></span>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>

	<?php if ( ! $wps_wpr_is_pro_active ) : ?>
	<section class="wps-free-vs-pro__cta">
		<div class="wps-free-vs-pro__cta-content">
			<h3><?php esc_html_e( 'Ready to Upgrade Your Loyalty Program?', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Join thousands of store owners who have upgraded to Pro and seen their customer engagement skyrocket.', 'points-and-rewards-for-woocommerce' ); ?></p>
			<a href="<?php echo esc_url( $wps_pro_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero free_vs_pro_button_design">
				<?php esc_html_e( 'Get Pro Now', 'points-and-rewards-for-woocommerce' ); ?>
			</a>
		</div>
		<div class="wps-free-vs-pro__cta-features">
			<ul>
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( '30-Day Money Back Guarantee', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'SMS/WhatsApp Notifications', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Priority Email Support', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'No Hidden Fees', 'points-and-rewards-for-woocommerce' ); ?></li>
			</ul>
		</div>
	</section>
	<?php endif; ?>
</div>

<style>
.wps-free-vs-pro__wrapper {
	max-width: 100%;
	margin: 0 auto;
	padding: 20px 0;
}

.wps-free-vs-pro__hero {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	padding: 40px;
	border-radius: 8px;
	margin-bottom: 30px;
	text-align: center;
}

.wps-free-vs-pro__hero-content h2 {
	color: #fff;
	font-size: 28px;
	margin: 15px 0;
}

.wps-free-vs-pro__hero-content p {
	font-size: 16px;
	margin-bottom: 25px;
	opacity: 0.95;
}

.wps-free-vs-pro__badge {
	display: inline-block;
	background: rgba(255, 255, 255, 0.2);
	padding: 5px 15px;
	border-radius: 20px;
	font-size: 12px;
	font-weight: 600;
	letter-spacing: 1px;
}

.wps-free-vs-pro__hero-actions {
	display: flex;
	gap: 15px;
	justify-content: center;
	flex-wrap: wrap;
}

.wps-free-vs-pro__comparison {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 0;
	margin-bottom: 30px;
	overflow: hidden;
}

.wps-free-vs-pro__table-wrapper {
	overflow-x: auto;
}

.wps-free-vs-pro__table {
	margin: 0;
	border: none;
}

.wps-free-vs-pro__table thead th {
	background: #f8f9fa;
	border-bottom: 2px solid #ddd;
	padding: 20px 15px;
	font-weight: 600;
	text-align: center;
}

.wps-free-vs-pro__feature-col {
	width: 50%;
	text-align: left !important;
}

.wps-free-vs-pro__free-col,
.wps-free-vs-pro__pro-col {
	width: 25%;
}

.wps-free-vs-pro__version-badge {
	display: inline-block;
	padding: 8px 20px;
	border-radius: 20px;
	font-size: 14px;
	font-weight: 600;
}

.wps-free-vs-pro__version-badge--free {
	background: #e3f2fd;
	color: #1976d2;
}

.wps-free-vs-pro__version-badge--pro {
	background: #f3e5f5;
	color: #7b1fa2;
}

.wps-free-vs-pro__feature-cell {
	padding: 15px;
}

.wps-free-vs-pro__feature-cell strong {
	display: block;
	margin-bottom: 5px;
	font-size: 14px;
}

.wps-free-vs-pro__feature-desc {
	display: block;
	color: #666;
	font-size: 13px;
	line-height: 1.5;
}

.wps-free-vs-pro__status-cell {
	text-align: center;
	padding: 15px;
}

.wps-free-vs-pro__icon {
	font-size: 24px;
	width: 24px;
	height: 24px;
	display: inline-block;
}

.wps-free-vs-pro__icon--yes {
	color: #4caf50;
}

.wps-free-vs-pro__icon--no {
	color: #ccc;
}

.wps-free-vs-pro__cta {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	padding: 40px;
	border-radius: 8px;
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 40px;
	align-items: center;
}

.wps-free-vs-pro__cta-content h3 {
	color: #fff;
	font-size: 24px;
	margin: 0 0 15px 0;
}

.wps-free-vs-pro__cta-content p {
	margin-bottom: 20px;
	opacity: 0.95;
	font-size: 15px;
}

.wps-free-vs-pro__cta-features ul {
	list-style: none;
	margin: 0;
	padding: 0;
}

.wps-free-vs-pro__cta-features li {
	padding: 10px 0;
	font-size: 15px;
	display: flex;
	align-items: center;
	gap: 10px;
}

.wps-free-vs-pro__cta-features .dashicons {
	color: #fff;
	font-size: 20px;
	width: 20px;
	height: 20px;
}

@media (max-width: 782px) {
	.wps-free-vs-pro__hero {
		padding: 30px 20px;
	}

	.wps-free-vs-pro__hero-content h2 {
		font-size: 22px;
	}

	.wps-free-vs-pro__hero-actions {
		flex-direction: column;
	}

	.wps-free-vs-pro__hero-actions .button {
		width: 100%;
	}

	.wps-free-vs-pro__cta {
		grid-template-columns: 1fr;
		padding: 30px 20px;
		gap: 20px;
	}

	.wps-free-vs-pro__feature-col {
		width: 40%;
	}

	.wps-free-vs-pro__free-col,
	.wps-free-vs-pro__pro-col {
		width: 30%;
	}

	.wps-free-vs-pro__feature-desc {
		display: none;
	}
}
</style>
