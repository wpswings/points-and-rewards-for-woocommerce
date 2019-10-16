<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Ultimate_Woocommerce_Points_And_Rewards_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ultimate-woocommerce-points-and-rewards',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
