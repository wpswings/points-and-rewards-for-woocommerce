<?php
/**
 * Campaign Templates Manager
 *
 * Handles campaign template metadata, retrieval, and organization.
 *
 * @link       https://wpswings.com/
 * @since      2.10.0
 *
 * @package    Points_Rewards_For_WooCommerce
 * @subpackage Points_Rewards_For_WooCommerce/admin/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPS_WPR_Campaign_Templates
 *
 * Manages campaign template library, metadata, and helper functions.
 */
class WPS_WPR_Campaign_Templates {

	/**
	 * Get all campaign templates with metadata.
	 *
	 * @since 2.10.0
	 * @return array Array of templates organized by category.
	 */
	public static function get_all_templates() {

		$base_url = WPS_RWPR_DIR_URL . 'admin/camp-images/';

		$templates = array(
			'summer_sale'     => array(
				'label'       => __( 'Summer Sale', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Hot deals for the summer season', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'summer',
				'templates'   => array(
					'SS1' => array(
						'file'             => 'SS1.webp',
						'heading'          => __( 'Hot Summer Deals — Cool Down with Huge Savings!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FF6B35',
						'color_secondary'  => '#F7931E',
						'keywords'         => array( 'summer', 'hot', 'orange', 'warm' ),
					),
					'SS2' => array(
						'file'             => 'SS2.webp',
						'heading'          => __( 'Beat the Heat with Unbeatable Summer Offers!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#00B4D8',
						'color_secondary'  => '#90E0EF',
						'keywords'         => array( 'summer', 'cool', 'blue', 'beach' ),
					),
					'SS3' => array(
						'file'             => 'SS3.webp',
						'heading'          => __( 'Sizzling Summer Savings Start Now!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FFB703',
						'color_secondary'  => '#FB8500',
						'keywords'         => array( 'summer', 'vibrant', 'orange', 'savings' ),
					),
					'SS4' => array(
						'file'             => 'SS4.webp',
						'heading'          => __( 'Make Waves with Our Summer Sale — Dive In!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#06FFA5',
						'color_secondary'  => '#00D9FF',
						'keywords'         => array( 'summer', 'tropical', 'teal', 'ocean' ),
					),
					'SS5' => array(
						'file'             => 'SS5.webp',
						'heading'          => __( 'Sun\'s Out, Deals Out — Shop the Summer Sale!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FF006E',
						'color_secondary'  => '#FFBE0B',
						'keywords'         => array( 'summer', 'bold', 'pink', 'yellow' ),
					),
				),
			),
			'flash_deal'      => array(
				'label'       => __( 'Flash Deal', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Urgent, time-sensitive promotions', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'year-round',
				'templates'   => array(
					'FD1' => array(
						'file'             => 'FD1.webp',
						'heading'          => __( 'Lightning Deals — Grab Them Before They\'re Gone!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FF0054',
						'color_secondary'  => '#FF4D00',
						'keywords'         => array( 'flash', 'urgent', 'red', 'lightning' ),
					),
					'FD2' => array(
						'file'             => 'FD2.webp',
						'heading'          => __( 'Flash Sale Alert — Limited Time Only!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FFD60A',
						'color_secondary'  => '#FFC300',
						'keywords'         => array( 'flash', 'alert', 'yellow', 'limited' ),
					),
					'FD3' => array(
						'file'             => 'FD3.webp',
						'heading'          => __( 'Act Fast — Flash Deals Disappear in Hours!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#7209B7',
						'color_secondary'  => '#F72585',
						'keywords'         => array( 'flash', 'fast', 'purple', 'pink' ),
					),
					'FD4' => array(
						'file'             => 'FD4.webp',
						'heading'          => __( 'Blink and You\'ll Miss It — Flash Sale Now!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#00F5FF',
						'color_secondary'  => '#00B4D8',
						'keywords'         => array( 'flash', 'quick', 'cyan', 'blue' ),
					),
					'FD5' => array(
						'file'             => 'FD5.webp',
						'heading'          => __( 'Don\'t Wait — Flash Deals End Tonight!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#F72585',
						'color_secondary'  => '#B5179E',
						'keywords'         => array( 'flash', 'tonight', 'magenta', 'urgent' ),
					),
				),
			),
			'back_to_school'  => array(
				'label'       => __( 'Back to School', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Educational season promotions', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'fall',
				'templates'   => array(
					'BTS1' => array(
						'file'             => 'BTS1.webp',
						'heading'          => __( 'Back to School Savings — Get Ready to Ace the Year!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#4361EE',
						'color_secondary'  => '#3A0CA3',
						'keywords'         => array( 'school', 'education', 'blue', 'royal' ),
					),
					'BTS2' => array(
						'file'             => 'BTS2.webp',
						'heading'          => __( 'Smart Savings for Smart Students — Shop Now!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#2EC4B6',
						'color_secondary'  => '#FF9F1C',
						'keywords'         => array( 'school', 'smart', 'teal', 'orange' ),
					),
					'BTS3' => array(
						'file'             => 'BTS3.webp',
						'heading'          => __( 'Gear Up for Success — Back to School Deals Inside!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#06FFA5',
						'color_secondary'  => '#4CC9F0',
						'keywords'         => array( 'school', 'success', 'cyan', 'bright' ),
					),
					'BTS4' => array(
						'file'             => 'BTS4.webp',
						'heading'          => __( 'Hit the Books — Not Your Budget!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#6A4C93',
						'color_secondary'  => '#1982C4',
						'keywords'         => array( 'school', 'budget', 'purple', 'blue' ),
					),
					'BTS5' => array(
						'file'             => 'BTS5.webp',
						'heading'          => __( 'School Essentials at Unbeatable Prices!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#F77F00',
						'color_secondary'  => '#D62828',
						'keywords'         => array( 'school', 'essentials', 'orange', 'red' ),
					),
				),
			),
			'vip_member'      => array(
				'label'       => __( 'VIP Member Reveal', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Exclusive membership announcements', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'year-round',
				'templates'   => array(
					'VIP1' => array(
						'file'             => 'VIP1.webp',
						'heading'          => __( 'Welcome to the VIP Club — Exclusive Perks Await!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#D4AF37',
						'color_secondary'  => '#C9A227',
						'keywords'         => array( 'vip', 'exclusive', 'gold', 'luxury' ),
					),
					'VIP2' => array(
						'file'             => 'VIP2.webp',
						'heading'          => __( 'You\'re VIP Now — Unlock Premium Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#9D4EDD',
						'color_secondary'  => '#7209B7',
						'keywords'         => array( 'vip', 'premium', 'purple', 'royal' ),
					),
					'VIP3' => array(
						'file'             => 'VIP3.webp',
						'heading'          => __( 'Exclusive Access Granted — VIP Benefits Inside!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#14213D',
						'color_secondary'  => '#FCA311',
						'keywords'         => array( 'vip', 'exclusive', 'navy', 'gold' ),
					),
					'VIP4' => array(
						'file'             => 'VIP4.webp',
						'heading'          => __( 'VIP Treatment — Because You Deserve the Best!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#6A040F',
						'color_secondary'  => '#DC2F02',
						'keywords'         => array( 'vip', 'best', 'red', 'premium' ),
					),
					'VIP5' => array(
						'file'             => 'VIP5.webp',
						'heading'          => __( 'Join the Elite — VIP Membership Activated!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#03045E',
						'color_secondary'  => '#0077B6',
						'keywords'         => array( 'vip', 'elite', 'blue', 'premium' ),
					),
				),
			),
			'christmas'       => array(
				'label'       => __( 'Christmas', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Holiday season celebrations', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'winter',
				'templates'   => array(
					'Chr1' => array(
						'file'             => 'Chr1.webp',
						'heading'          => __( 'Merry Christmas — Unwrap Amazing Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#C41E3A',
						'color_secondary'  => '#0C6E42',
						'keywords'         => array( 'christmas', 'holiday', 'red', 'green' ),
					),
					'Chr2' => array(
						'file'             => 'Chr2.webp',
						'heading'          => __( 'Holiday Cheer and Exclusive Deals Are Here!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#165B33',
						'color_secondary'  => '#BB2528',
						'keywords'         => array( 'christmas', 'cheer', 'green', 'red' ),
					),
					'Chr3' => array(
						'file'             => 'Chr3.webp',
						'heading'          => __( 'Tis the Season to Save Big!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#D4AF37',
						'color_secondary'  => '#C41E3A',
						'keywords'         => array( 'christmas', 'season', 'gold', 'red' ),
					),
					'Chr4' => array(
						'file'             => 'Chr4.webp',
						'heading'          => __( 'Christmas Magic Meets Massive Savings!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#0C6E42',
						'color_secondary'  => '#FFFFFF',
						'keywords'         => array( 'christmas', 'magic', 'green', 'white' ),
					),
					'Chr5' => array(
						'file'             => 'Chr5.webp',
						'heading'          => __( 'Deck the Halls with Deals and Discounts!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#BB2528',
						'color_secondary'  => '#146B3A',
						'keywords'         => array( 'christmas', 'halls', 'red', 'green' ),
					),
				),
			),
			'mothers_day'     => array(
				'label'       => __( 'Mother\'s Day', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Celebrate moms with special offers', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'spring',
				'templates'   => array(
					'MD1' => array(
						'file'             => 'MD1.webp',
						'heading'          => __( 'Celebrate Mom with Exclusive Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FF6B9D',
						'color_secondary'  => '#C44569',
						'keywords'         => array( 'mother', 'mom', 'pink', 'love' ),
					),
					'MD2' => array(
						'file'             => 'MD2.webp',
						'heading'          => __( 'Show Mom Some Love — Special Offers Inside!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#E91E63',
						'color_secondary'  => '#9C27B0',
						'keywords'         => array( 'mother', 'love', 'pink', 'purple' ),
					),
					'MD3' => array(
						'file'             => 'MD3.webp',
						'heading'          => __( 'Because Mom Deserves the Best!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FFB6C1',
						'color_secondary'  => '#FF69B4',
						'keywords'         => array( 'mother', 'best', 'pink', 'soft' ),
					),
					'MD4' => array(
						'file'             => 'MD4.webp',
						'heading'          => __( 'Mother\'s Day Magic — Earn Bonus Points!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#F06292',
						'color_secondary'  => '#BA68C8',
						'keywords'         => array( 'mother', 'magic', 'pink', 'purple' ),
					),
					'MD5' => array(
						'file'             => 'MD5.webp',
						'heading'          => __( 'Treat Mom to Something Special!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#C2185B',
						'color_secondary'  => '#7B1FA2',
						'keywords'         => array( 'mother', 'treat', 'pink', 'purple' ),
					),
				),
			),
			'thanksgiving'    => array(
				'label'       => __( 'Thanksgiving', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Gratitude and harvest season deals', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'fall',
				'templates'   => array(
					'TG1' => array(
						'file'             => 'TG1.webp',
						'heading'          => __( 'Give Thanks and Get Rewarded!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#D2691E',
						'color_secondary'  => '#8B4513',
						'keywords'         => array( 'thanksgiving', 'thanks', 'orange', 'brown' ),
					),
					'TG2' => array(
						'file'             => 'TG2.webp',
						'heading'          => __( 'Harvest Huge Savings This Thanksgiving!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FF8C00',
						'color_secondary'  => '#CD853F',
						'keywords'         => array( 'thanksgiving', 'harvest', 'orange', 'gold' ),
					),
					'TG3' => array(
						'file'             => 'TG3.webp',
						'heading'          => __( 'Thankful for You — Exclusive Points Inside!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#B8860B',
						'color_secondary'  => '#A0522D',
						'keywords'         => array( 'thanksgiving', 'thankful', 'gold', 'brown' ),
					),
					'TG4' => array(
						'file'             => 'TG4.webp',
						'heading'          => __( 'Feast on Amazing Deals!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#D2691E',
						'color_secondary'  => '#FF6347',
						'keywords'         => array( 'thanksgiving', 'feast', 'orange', 'red' ),
					),
					'TG5' => array(
						'file'             => 'TG5.webp',
						'heading'          => __( 'Grateful Customers Get Bonus Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#CD853F',
						'color_secondary'  => '#8B4513',
						'keywords'         => array( 'thanksgiving', 'grateful', 'tan', 'brown' ),
					),
				),
			),
			'womens_day'      => array(
				'label'       => __( 'Women\'s Day', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Celebrate women empowerment', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'spring',
				'templates'   => array(
					'Wo1' => array(
						'file'             => 'Wo1.webp',
						'heading'          => __( 'Empower. Celebrate. Reward. Happy Women\'s Day!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#9C27B0',
						'color_secondary'  => '#E91E63',
						'keywords'         => array( 'women', 'empower', 'purple', 'pink' ),
					),
					'Wo2' => array(
						'file'             => 'Wo2.webp',
						'heading'          => __( 'Exclusive Offers for Extraordinary Women!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#8E24AA',
						'color_secondary'  => '#D81B60',
						'keywords'         => array( 'women', 'extraordinary', 'purple', 'pink' ),
					),
					'Wo3' => array(
						'file'             => 'Wo3.webp',
						'heading'          => __( 'Celebrate Her Strength — Earn Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#7B1FA2',
						'color_secondary'  => '#C2185B',
						'keywords'         => array( 'women', 'strength', 'purple', 'pink' ),
					),
					'Wo4' => array(
						'file'             => 'Wo4.webp',
						'heading'          => __( 'Women\'s Day Special — Unlock Bonus Points!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#AB47BC',
						'color_secondary'  => '#EC407A',
						'keywords'         => array( 'women', 'special', 'purple', 'pink' ),
					),
					'Wo5' => array(
						'file'             => 'Wo5.webp',
						'heading'          => __( 'Strong Women. Strong Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#6A1B9A',
						'color_secondary'  => '#AD1457',
						'keywords'         => array( 'women', 'strong', 'purple', 'pink' ),
					),
				),
			),
			'valentines_day'  => array(
				'label'       => __( 'Valentine\'s Day', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Love and romance themed promotions', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'winter',
				'templates'   => array(
					'Va1' => array(
						'file'             => 'Va1.webp',
						'heading'          => __( 'Fall in Love with Our Valentine\'s Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FF1744',
						'color_secondary'  => '#F50057',
						'keywords'         => array( 'valentine', 'love', 'red', 'pink' ),
					),
					'Va2' => array(
						'file'             => 'Va2.webp',
						'heading'          => __( 'Spread the Love — Earn Bonus Points!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#E91E63',
						'color_secondary'  => '#F06292',
						'keywords'         => array( 'valentine', 'spread', 'pink', 'red' ),
					),
					'Va3' => array(
						'file'             => 'Va3.webp',
						'heading'          => __( 'Valentine\'s Exclusive — Rewards Made with Love!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#C2185B',
						'color_secondary'  => '#EC407A',
						'keywords'         => array( 'valentine', 'exclusive', 'pink', 'red' ),
					),
					'Va4' => array(
						'file'             => 'Va4.webp',
						'heading'          => __( 'Love is in the Air — And So Are Savings!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#D81B60',
						'color_secondary'  => '#F48FB1',
						'keywords'         => array( 'valentine', 'air', 'pink', 'red' ),
					),
					'Va5' => array(
						'file'             => 'Va5.webp',
						'heading'          => __( 'Share the Love — Double the Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#AD1457',
						'color_secondary'  => '#F06292',
						'keywords'         => array( 'valentine', 'share', 'pink', 'red' ),
					),
				),
			),
			'black_friday'    => array(
				'label'       => __( 'Black Friday', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Biggest sale event of the year', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'fall',
				'templates'   => array(
					'bf1' => array(
						'file'             => 'bf1.webp',
						'heading'          => __( 'Black Friday Blowout — Massive Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#000000',
						'color_secondary'  => '#FFD700',
						'keywords'         => array( 'black friday', 'blowout', 'black', 'gold' ),
					),
					'bf2' => array(
						'file'             => 'bf2.webp',
						'heading'          => __( 'The Wait is Over — Black Friday Starts Now!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#1A1A1A',
						'color_secondary'  => '#FF0000',
						'keywords'         => array( 'black friday', 'wait', 'black', 'red' ),
					),
					'bf3' => array(
						'file'             => 'bf3.webp',
						'heading'          => __( 'Black Friday Frenzy — Earn Big Points!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#000000',
						'color_secondary'  => '#FF4500',
						'keywords'         => array( 'black friday', 'frenzy', 'black', 'orange' ),
					),
					'bf4' => array(
						'file'             => 'bf4.webp',
						'heading'          => __( 'Unlock Black Friday Exclusive Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#0D0D0D',
						'color_secondary'  => '#FFFFFF',
						'keywords'         => array( 'black friday', 'unlock', 'black', 'white' ),
					),
					'bf5' => array(
						'file'             => 'bf5.webp',
						'heading'          => __( 'Shop Black Friday — Get Rewarded Big!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#000000',
						'color_secondary'  => '#00FF00',
						'keywords'         => array( 'black friday', 'shop', 'black', 'green' ),
					),
				),
			),
			'easter'          => array(
				'label'       => __( 'Easter', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Spring celebrations and egg hunts', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'spring',
				'templates'   => array(
					'eas1' => array(
						'file'             => 'eas1.webp',
						'heading'          => __( 'Hop into Easter Savings — Egg-citing Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#87CEEB',
						'color_secondary'  => '#FFD700',
						'keywords'         => array( 'easter', 'hop', 'blue', 'gold' ),
					),
					'eas2' => array(
						'file'             => 'eas2.webp',
						'heading'          => __( 'Crack Open Easter Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#98D8C8',
						'color_secondary'  => '#F7DC6F',
						'keywords'         => array( 'easter', 'crack', 'mint', 'yellow' ),
					),
					'eas3' => array(
						'file'             => 'eas3.webp',
						'heading'          => __( 'Easter Egg Hunt for Points — Join Now!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FFB6C1',
						'color_secondary'  => '#9370DB',
						'keywords'         => array( 'easter', 'hunt', 'pink', 'purple' ),
					),
					'eas4' => array(
						'file'             => 'eas4.webp',
						'heading'          => __( 'Easter Bunny Approved Deals!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#7FFFD4',
						'color_secondary'  => '#FF69B4',
						'keywords'         => array( 'easter', 'bunny', 'aqua', 'pink' ),
					),
					'eas5' => array(
						'file'             => 'eas5.webp',
						'heading'          => __( 'Celebrate Easter with Bonus Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FFE4B5',
						'color_secondary'  => '#DA70D6',
						'keywords'         => array( 'easter', 'celebrate', 'peach', 'orchid' ),
					),
				),
			),
			'halloween'       => array(
				'label'       => __( 'Halloween', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Spooky season specials', 'points-and-rewards-for-woocommerce' ),
				'season'      => 'fall',
				'templates'   => array(
					'hal1' => array(
						'file'             => 'hal1.webp',
						'heading'          => __( 'Spooktacular Rewards Await — Happy Halloween!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FF6600',
						'color_secondary'  => '#000000',
						'keywords'         => array( 'halloween', 'spooky', 'orange', 'black' ),
					),
					'hal2' => array(
						'file'             => 'hal2.webp',
						'heading'          => __( 'Trick or Treat Yourself to Bonus Points!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#9400D3',
						'color_secondary'  => '#FF8C00',
						'keywords'         => array( 'halloween', 'trick', 'purple', 'orange' ),
					),
					'hal3' => array(
						'file'             => 'hal3.webp',
						'heading'          => __( 'Frighteningly Good Deals This Halloween!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#1C1C1C',
						'color_secondary'  => '#FF4500',
						'keywords'         => array( 'halloween', 'frightening', 'black', 'orange' ),
					),
					'hal4' => array(
						'file'             => 'hal4.webp',
						'heading'          => __( 'Boo! Unlock Scary-Good Rewards!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#8B008B',
						'color_secondary'  => '#FFD700',
						'keywords'         => array( 'halloween', 'boo', 'purple', 'gold' ),
					),
					'hal5' => array(
						'file'             => 'hal5.webp',
						'heading'          => __( 'Halloween Haul — Earn Spooky Points!', 'points-and-rewards-for-woocommerce' ),
						'color_primary'    => '#FF6347',
						'color_secondary'  => '#2F4F4F',
						'keywords'         => array( 'halloween', 'haul', 'red', 'gray' ),
					),
				),
			),
		);

		// Allow developers to add custom template categories.
		return apply_filters( 'wps_wpr_campaign_templates', $templates, $base_url );
	}

	/**
	 * Get list of template categories.
	 *
	 * @since 2.10.0
	 * @return array Category list with labels.
	 */
	public static function get_template_categories() {
		$templates  = self::get_all_templates();
		$categories = array();

		foreach ( $templates as $key => $category ) {
			$categories[ $key ] = array(
				'label'       => $category['label'],
				'description' => isset( $category['description'] ) ? $category['description'] : '',
				'season'      => isset( $category['season'] ) ? $category['season'] : 'year-round',
				'count'       => count( $category['templates'] ),
			);
		}

		return $categories;
	}

	/**
	 * Get templates by category.
	 *
	 * @since 2.10.0
	 * @param string $category Category key.
	 * @return array Templates in the category.
	 */
	public static function get_templates_by_category( $category ) {
		$all_templates = self::get_all_templates();

		if ( ! isset( $all_templates[ $category ] ) ) {
			return array();
		}

		return $all_templates[ $category ]['templates'];
	}

	/**
	 * Get full URL for a template image.
	 *
	 * @since 2.10.0
	 * @param string $category  Category key.
	 * @param string $template_id Template ID (e.g., 'SS1', 'FD2').
	 * @return string|false Full URL or false if not found.
	 */
	public static function get_template_url( $category, $template_id ) {
		$templates = self::get_templates_by_category( $category );

		if ( ! isset( $templates[ $template_id ] ) ) {
			return false;
		}

		$base_url = WPS_RWPR_DIR_URL . 'admin/camp-images/';
		return $base_url . $templates[ $template_id ]['file'];
	}

	/**
	 * Get template metadata by ID.
	 *
	 * @since 2.10.0
	 * @param string $category  Category key.
	 * @param string $template_id Template ID.
	 * @return array|false Template metadata or false.
	 */
	public static function get_template_metadata( $category, $template_id ) {
		$templates = self::get_templates_by_category( $category );

		if ( ! isset( $templates[ $template_id ] ) ) {
			return false;
		}

		$template = $templates[ $template_id ];

		// Add full URL.
		$template['url'] = self::get_template_url( $category, $template_id );

		return $template;
	}

	/**
	 * Search templates by keyword.
	 *
	 * @since 2.10.0
	 * @param string $keyword Search term.
	 * @return array Matching templates.
	 */
	public static function search_templates( $keyword ) {
		$all_templates = self::get_all_templates();
		$results       = array();
		$keyword       = strtolower( $keyword );

		foreach ( $all_templates as $category_key => $category ) {
			foreach ( $category['templates'] as $template_id => $template ) {

				// Search in heading.
				if ( stripos( $template['heading'], $keyword ) !== false ) {
					$results[] = array(
						'category'    => $category_key,
						'template_id' => $template_id,
						'metadata'    => self::get_template_metadata( $category_key, $template_id ),
					);
					continue;
				}

				// Search in keywords.
				if ( isset( $template['keywords'] ) && is_array( $template['keywords'] ) ) {
					foreach ( $template['keywords'] as $tag ) {
						if ( stripos( $tag, $keyword ) !== false ) {
							$results[] = array(
								'category'    => $category_key,
								'template_id' => $template_id,
								'metadata'    => self::get_template_metadata( $category_key, $template_id ),
							);
							break;
						}
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Get templates by season.
	 *
	 * @since 2.10.0
	 * @param string $season Season (summer, fall, winter, spring, year-round).
	 * @return array Categories matching the season.
	 */
	public static function get_templates_by_season( $season ) {
		$all_templates = self::get_all_templates();
		$results       = array();

		foreach ( $all_templates as $category_key => $category ) {
			if ( isset( $category['season'] ) && $category['season'] === $season ) {
				$results[ $category_key ] = $category;
			}
		}

		return $results;
	}
}
