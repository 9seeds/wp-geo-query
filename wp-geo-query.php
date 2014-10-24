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
define( 'WP_GEO_COOKIE', 'wp_geo_' . COOKIEHASH );
define( 'WP_GEO_SECURE_COOKIE', 'wp_geo_sec_' . COOKIEHASH );

require_once WP_GEO_DIR . 'includes/class.wp-geo-controller.php';
require_once WP_GEO_DIR . 'includes/class.wp-geo-query.php';
require_once WP_GEO_DIR . 'includes/class.wp-geo-ip.php';
require_once WP_GEO_DIR . 'includes/class.wp-geo-location-shortcode.php';
require_once WP_GEO_DIR . 'includes/class.wp-geo-code.php';

function wp_geo_controller() {
	return WP_Geo_Controller::get_instance();
}

function wp_geo_query_init() {
	$controller = wp_geo_controller();
	$controller->hook();

	if ( ! is_admin() ) {
		wp_register_style( 'font-awesome', WP_GEO_URL . 'lib/font-awesome/css/font-awesome.min.css', array(), '4.2.0' );
		wp_register_script( 'location-shortcode', WP_GEO_URL . 'js/location_shortcode.js', array( 'jquery' ), WP_GEO_VERSION );
	}

	//init the shortcode
	WP_Geo_Location_Shortcode::get_instance();	
}
add_action( 'init', 'wp_geo_query_init' );

/*
$args = array(
	'post_type' => 'post',
	'geo_query' => array(
		'lat' => 45,
		'lon' => 99,
	),
	'orderby' => 'distance'
);
$posts = wp_geo_get_posts( $args );
OR
$geo_query = new WP_Query();
$posts = $geo_query->query( $args );
*/