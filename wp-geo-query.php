<?php
/**
 * Plugin Name: WP Geo Query
 * Plugin URI: http://wordpress.org/extend/plugins/jetpack/
 * Description: Bring the power of the WordPress.com cloud to your self-hosted WordPress. Jetpack enables you to connect your blog to a WordPress.com account to use the powerful features normally only available to WordPress.com users.
 * Version: 0.8
 * Author: 9seeds
 * Author URI: 9seeds.com
 * License: GPL2+
 * Text Domain: wpgeo
 * Domain Path: /languages/
 */

define( 'WP_GEO_VERSION', '0.8' );
define( 'WP_GEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_GEO_URL', plugins_url( '/' , __FILE__ ) );
//for session requirements
define( 'WP_GEO_SESSION_DIR', 'wp-session-manager' );
define( 'WP_GEO_SESSION_FILE', 'wp-session-manager.php' );

require_once WP_GEO_DIR . 'includes/class.wp-geo-controller.php';
require_once WP_GEO_DIR . 'includes/class.wp-geo-cache.php';

require_once WP_GEO_DIR . 'includes/class.wp-geo-query.php';
require_once WP_GEO_DIR . 'includes/class.wp-geo-ip.php';
require_once WP_GEO_DIR . 'includes/class.wp-geo-location-shortcode.php';
require_once WP_GEO_DIR . 'includes/class.wp-geo-code.php';

function wp_geo_load() {

	if ( is_admin() ) {
		require_once WP_GEO_DIR . 'includes/class.wp-geo-admin.php';
		$admin = new WP_Geo_Admin();
		$admin->hook();
	} else {
		//front-end only
	}
	//load for front-end & wp-admin

	//init the query controller
	$controller = WP_Geo_Controller::get_instance();
	$controller->hook();
	//init the shortcode
	WP_Geo_Location_Shortcode::get_instance();	
}
add_action( 'plugins_loaded', 'wp_geo_load' );

//@TODO make setting for this
add_filter( 'wp_session_expiration', function() { return 15 * MINUTE_IN_SECONDS; } ); // Set expiration to 15 minutes

//remove session creation on every page to prevent un-caching
function wp_geo_prevent_session_start() {
	remove_action( 'plugins_loaded', 'wp_session_start' );
	remove_action( 'shutdown', 'wp_session_write_close' );
}
add_action( 'plugins_loaded', 'wp_geo_prevent_session_start', 9 ); //run before WP_Session's plugins_loaded

/*
$args = array(
	'post_type' => 'post',
	'geo_query' => array(
		'lat' => 45,
		'lon' => 99,
		'distance' => '25',
		'operator' => '<', // ('>', '>=', '<', or '<=') Default value is '<'.
	),
	'orderby' => 'distance', //assumed if 'orderby' not set
	'order' => 'ASC', //assumed ASC if 'order' not set AND orderby = 'distance'
);
$posts = get_posts( $args );
OR
$geo_query = new WP_Query();
$posts = $geo_query->query( $args );
*/