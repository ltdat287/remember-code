<?php
/*
Plugin Name: Feed Them Social Premium
Plugin URI: http://slickremix.com/
Description: This is the Premium Extension for the Free version of Feed Them Social.
Version: 1.3.6
Author: SlickRemix
Author URI: http://slickremix.com/
Requires at least: wordpress 3.4.0
Tested up to: wordpress 4.0.1
Stable tag: 1.3.6

 * @package    			Feed Them Social Premium
 * @category   			Core
 * @author				SlickRemix
 * @copyright  			Copyright (c) 2012-2014 SlickRemix

If you need support or want to tell us thanks please contact us at support@slickremix.com or use our support forum on slickremix.com


This is the main file for building the plugin into wordpress
*/
if ( ! function_exists( 'is_plugin_active' ) )
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    // Makes sure the plugin is defined before trying to use it
	
// Make sure php version is greater than 5.3
if ( function_exists( 'phpversion' ) )
					
					$phpversion = phpversion();
					$phpcheck = '5.2.9';
					
	
if ($phpversion > $phpcheck && is_plugin_active('feed-them-social/feed-them.php')) {
	
	  $fts_plugin_rel_url = plugin_dir_path( __FILE__ );	
	  // Include core files and classes
	  include_once( $fts_plugin_rel_url.'includes/feed-them-premium-functions.php' );
	  include_once( $fts_plugin_rel_url.'feeds/youtube/youtube-feed.php' );
	  include_once(	$fts_plugin_rel_url.'feeds/pinterest/pinterest.php' );
	  include_once( $fts_plugin_rel_url.'includes/updater-check.php' );
	  
	  
	  function fts_premium_action_init()
		{
		// Localization
		load_plugin_textdomain('feed-them-premium', false, basename( dirname( __FILE__ ) ) . '/languages' );
		}
		// Add actions
		add_action('init', 'fts_premium_action_init');

	  
	  // Include our own Settings link to plugin activation and update page.
	  add_filter("plugin_action_links_".plugin_basename(__FILE__), "fts_plugin_actions", 10, 4);
	  
		  function fts_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {
			  array_unshift($actions, "<a href=\"".menu_page_url('feed-them-settings-page', false)."\">".__("Settings")."</a>");
			  return $actions;
	  }
} // end if php version check

else  {
	
	
	// if the php version is not at least 5.3 do action
	deactivate_plugins( 'feed-them-premium/feed-them-premium.php' ); 
	
	
    	if ($phpversion < $phpcheck) {
		
	add_action( 'admin_notices', 'fts_required_php_check1' );	
	function fts_required_php_check1() {
			echo '<div class="error"><p>' . __( 'Warning: Your version of php must be 5.3 or greater to use this plugin. Please upgrade the php by contacting your host provider. Some host providers will allow you to change this yourself in the hosting control panel too.', 'my-theme' ) . '</p></div>';
			
	} // end fts_required_php_check
		}
		
		
		if (!is_plugin_active('feed-them-social/feed-them.php')) {
			add_action( 'admin_notices', 'fts_required_php_check2' );
			function fts_required_php_check2() {
			echo '<div class="error"><p>' . __( 'Warning: The <strong>Feed Them Social Premium</strong> extension has been deactivated because it needs the <a href="plugin-install.php?tab=search&s=Feed+Them+Social&plugin-search-input=Search+Plugins"><strong>Feed Them Social</strong></a> (Free version) plugin to be <strong>INSTALLED</strong> and <strong>ACTIVATED</strong> to function properly.', 'my-theme' ) . '</p></div>';
			}
		}
	
} // end else php version check


	  
// Include Leave feedback, Get support and Plugin info links to plugin activation and update page.
add_filter("plugin_row_meta", "fts_add_leave_feedback_link", 10, 2);

	function fts_add_leave_feedback_link( $links, $file ) {
		if ( $file === plugin_basename( __FILE__ ) ) {
			$links['feedback'] = '<a href="http://wordpress.org/support/view/plugin-reviews/feed-them-social" target="_blank">' . __( 'Leave feedback', 'gd_quicksetup' ) . '</a>';
			$links['support']  = '<a href="http://www.slickremix.com/support-forum/wordpress-plugins-group3/feed-them-social-forum9/" target="_blank">' . __( 'Get support', 'gd_quicksetup' ) . '</a>';
			// $links['plugininfo']  = '<a href="plugin-install.php?tab=plugin-information&plugin=feed-them-premium&section=changelog&TB_iframe=true&width=640&height=423" class="thickbox">' . __( 'Plugin info', 'gd_quicksetup' ) . '</a>';
		}
		return $links;
}
?>