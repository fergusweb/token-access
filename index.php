<?php
/**
 * The main plugin file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 * 
 *
 * Plugin Name:		Token Access
 * Plugin URI:		https://www.ferguson.codes/software/token-access/
 * Description:		Limit access to the site to those with a cookie token, set through a special link. Visitors without the cookie see a customisable "coming soon" style of page. To remove protection, simply disable this plugin.
 * Version:			1.8.0
 * Author:			Anthony Ferguson
 * Author URI:		http://www.ferguson.codes
 * License:     	GPLv3 or later
 * License URI: 	https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * @package		token_access
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'TOKEN_ACCESS_PLUGIN_BASE', plugin_basename( __FILE__ ) );


/**
 * The necessary plugin classes
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-token-access.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-token-access-public.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-token-access-admin.php';

/**
 * Loader
 */
new Token_Access();

add_action('init', function() {
	
}, 1);
