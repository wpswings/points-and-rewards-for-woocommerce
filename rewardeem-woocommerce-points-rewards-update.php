<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'Rewardeem_woocommerce_Points_Rewards_Update' ) ) 
{
	class Rewardeem_woocommerce_Points_Rewards_Update 
	{
		public function __construct() {
			register_activation_hook(REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_BASE_FILE, array($this, 'mwb_check_activation'));
			add_action('mwb_check_event', array($this, 'mwb_check_update'));
			add_filter( 'http_request_args', array($this, 'mwb_updates_exclude'), 5, 2 );
			register_deactivation_hook(REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_BASE_FILE, array($this, 'mwb_check_deactivation'));
		}	

		public function mwb_check_deactivation() 
		{
			wp_clear_scheduled_hook('mwb_check_event');
		}

		public function mwb_check_activation() 
		{
			wp_schedule_event(time(), 'daily', 'mwb_check_event');
		}

		public function mwb_check_update() 
		{
			global $wp_version;
			global $update_check;
			$plugin_folder = plugin_basename( dirname( REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_BASE_FILE ) );
			$plugin_file = basename( ( REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_BASE_FILE ) );
			if ( defined( 'WP_INSTALLING' ) )
			{
				return false;
			} 
			$postdata = array(
				'action' => 'check_update',
				'license_key' => REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_LICENSE_KEY
			);
			
			$args = array(
				'method' => 'POST',
				'body' => $postdata,
			);
			
			$response = wp_remote_post( $update_check, $args );

			if( is_wp_error( $response ) ) {

				return false;
			}
			
			list($version, $url) = explode('~', $response['body']);
			if($this->mwb_plugin_get("Version") == $version) 
			{
				return false;
			}
			
			$plugin_transient = get_site_transient('update_plugins');
			$a = array(
				'slug' => $plugin_folder,
				'new_version' => $version,
				'url' => $this->mwb_plugin_get("AuthorURI"),
				'package' => $url
			);
			$o = (object) $a;
			$plugin_transient->response[$plugin_folder.'/'.$plugin_file] = $o;
			set_site_transient('update_plugins', $plugin_transient);
		}

		public function mwb_updates_exclude( $r, $url ) 
		{
			if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) )
			{
				return $r; 
			}	
			$plugins = unserialize( $r['body']['plugins'] );
			unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
			unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
			$r['body']['plugins'] = serialize( $plugins );
			return $r;
		}

		//Returns current plugin info.
		public function mwb_plugin_get($i) 
		{
			if ( ! function_exists( 'get_plugins' ) )
			{
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}	
			$plugin_folder = get_plugins( '/' . plugin_basename( dirname( REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_BASE_FILE ) ) );
			$plugin_file = basename( ( REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_BASE_FILE ) );
			return $plugin_folder[$plugin_file][$i];
		}
	}
	new Rewardeem_woocommerce_Points_Rewards_Update();
}		
?>