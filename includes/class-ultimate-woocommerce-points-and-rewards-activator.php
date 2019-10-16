<?php

/**
 * Fired during plugin activation
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Ultimate_Woocommerce_Points_And_Rewards_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$timestamp = get_option( 'ultimate_woocommerce_points_and_rewards_lcns_thirty_days', 'not_set' );

		if( 'not_set' === $timestamp ){

			$current_time = current_time( 'timestamp' );

			$thirty_days =  strtotime( '+30 days', $current_time );

			update_option( 'ultimate_woocommerce_points_and_rewards_lcns_thirty_days', $thirty_days );
		}

		// Validate license daily cron.
		wp_schedule_event( time(), 'daily', 'ultimate_woocommerce_points_and_rewards_license_daily' );

	}

}
