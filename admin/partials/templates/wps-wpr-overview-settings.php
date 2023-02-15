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
?>
<div class="wps-overview__wrapper">
	<div class="wps-overview__banner">
		<img src="<?php echo esc_html( WPS_RWPR_DIR_URL ); ?>admin/images/Points-and-Rewards-for-WooCommerce.jpg" alt="Overview banner image">
	</div>
	<div class="wps-overview__content">
		<div class="wps-overview__content-description">
			<h3><?php echo esc_html_e( 'What is Points And Rewards For WooCommerce? ', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<p>
				<?php
				esc_html_e(
					'Points And Rewards For WooCommerce is a customer-oriented solution that aims to engage customers by offering them points for activities they perform on your WooCommerce store. For instance, signup, purchase, referrals, etc. Each Points earned can be redeemed by the shoppers on additional purchases. These reward points make the customers eligible for on-purchase discounts and participation in the membership program',
					'points-and-rewards-for-woocommerce'
				);
				?>
			</p>
			<h3><?php esc_html_e( 'As a store owner, you get to:', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<ul class="wps-overview__features">
				<li><?php esc_html_e( 'Engage customers by awarding points to shoppers on signup, referrals, and purchase', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Create membership programs for loyal customers to reward them with exclusive offers', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Set conversion rule to determine the value of each point', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Allow points referral via social media platforms like Whatsapp, email, Facebook, Twitter, etc.', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Track points transaction of each customer with point log report', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( "Update the customer's point manually from the points table.", 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Email notification for customer point transactions', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'WPML Multilingual support to reward points in a different language', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Alter point value for the order total and cart redemption', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Global Point assign a feature to assign a similar point value to all products', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Give specific points for a price range of order value', 'points-and-rewards-for-woocommerce' ); ?></li>
				<li>
			</ul>
		</div>
		<h3> <?php esc_html_e( 'The Free Plugin Benefits', 'points-and-rewards-for-woocommerce' ); ?></h3>
		<div class="wps-overview__keywords">
			<div class="wps-overview__keywords-item">
				<div class="wps-overview__keywords-card">
					<div class="wps-overview__keywords-image">
						<img src="<?php echo esc_html( WPS_RWPR_DIR_URL . 'admin/images/Reward-Points-for-Different-Actions.png' ); ?>" alt="Advanced-report image">
					</div>
					<div class="wps-overview__keywords-text">
						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' Reward Points for Different Actions', 'points-and-rewards-for-woocommerce' ); ?></h3>
						<p class="wps-overview__keywords-description">
							<?php
							esc_html_e(
								'Admin can assign and reward points to users on different actions. These include Signups, Referrals, Total Amount Spent, Products purchase, Order Total Range, etc
							 ',
								'points-and-rewards-for-woocommerce'
							);
							?>
						</p>
					</div>
				</div>
			</div>
			<div class="wps-overview__keywords-item">
				<div class="wps-overview__keywords-card">
					<div class="wps-overview__keywords-image">
						<img src="<?php echo esc_html( WPS_RWPR_DIR_URL . 'admin/images/Easy-Points-Redemption.png' ); ?>" alt="Workflow image">
					</div>
					<div class="wps-overview__keywords-text">
						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' Easy Points Redemption', 'points-and-rewards-for-woocommerce' ); ?></h3>
						<p class="wps-overview__keywords-description">
							<?php
							esc_html_e(
								'Users can redeem points by entering points in the "Apply point" field on the checkout or cart pages. Admin decides the conversion of each point to currency.
							 ',
								'points-and-rewards-for-woocommerce'
							);
							?>
						</p>
					</div>
				</div>
			</div>
			<div class="wps-overview__keywords-item">
				<div class="wps-overview__keywords-card">
					<div class="wps-overview__keywords-image">
						<img src="<?php echo esc_html( WPS_RWPR_DIR_URL . 'admin/images/Membership-System-for-Exclusive-Offers.png' ); ?>" alt="Variable product image">
					</div>
					<div class="wps-overview__keywords-text">
						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' Membership System for Exclusive Offers ', 'points-and-rewards-for-woocommerce' ); ?></h3>
						<p class="wps-overview__keywords-description">
							<?php
							esc_html_e(
								'Admin can create a membership system depending on reward points. Users can buy exclusive products at discounted prices based on their total collected points on subscribing to the membership.
',
								'points-and-rewards-for-woocommerce'
							);
							?>
						</p>
					</div>
				</div>
			</div>
			<div class="wps-overview__keywords-item">
				<div class="wps-overview__keywords-card">
					<div class="wps-overview__keywords-image">
						<img src="<?php echo esc_html( WPS_RWPR_DIR_URL . 'admin/images/Customer-Notification-Feature.png' ); ?>" alt="Variable product image">
					</div>
					<div class="wps-overview__keywords-text">
						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' Customer Notification Feature ', 'points-and-rewards-for-woocommerce' ); ?></h3>
						<p class="wps-overview__keywords-description">
							<?php
							esc_html_e(
								'Admin can edit email template to send users as a notification for points transaction on users registered email..
',
								'points-and-rewards-for-woocommerce'
							);
							?>
						</p>
					</div>
				</div>
			</div>
			<div class="wps-overview__keywords-item">
				<div class="wps-overview__keywords-card">
					<div class="wps-overview__keywords-image">
						<img src="<?php echo esc_html( WPS_RWPR_DIR_URL . 'admin/images/WPML-Multilingual-Compatibility.png' ); ?>" alt="Variable product image">
					</div>
					<div class="wps-overview__keywords-text">
						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' WPML Multilingual Compatibility ', 'points-and-rewards-for-woocommerce' ); ?></h3>
						<p class="wps-overview__keywords-description">
							<?php
							esc_html_e(
								'Admin can reward users in their local language since the Points and Rewards for WooCommerce plugin is WPML Multilingual plugin compatible. .
',
								'points-and-rewards-for-woocommerce'
							);
							?>
						</p>
					</div>
				</div>
			</div>
			<div class="wps-overview__keywords-item">
				<div class="wps-overview__keywords-card">
					<div class="wps-overview__keywords-image wps_youtube">	
					<iframe width="170%" height="200" src="https://www.youtube.com/embed/8W9K6avWESE" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>

				</div>
					<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' Watch Video: Learn More About the Points and Reward  ', 'points-and-rewards-for-woocommerce' ); ?></h3>
					<p class="wps-overview__keywords-description">
						<?php
						esc_html_e(
							'This video tutorial helps the better utilization of the plugin and leverage the maximum benefits.
',
							'points-and-rewards-for-woocommerce'
						);
						?>
					</p>
			</div>
		</div>
	</div>
</div>

