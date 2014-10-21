<?php
/**
 * Plugin Name: WP Geo Query
 * Plugin URI: http://wordpress.org/extend/plugins/jetpack/
 * Description: Bring the power of the WordPress.com cloud to your self-hosted WordPress. Jetpack enables you to connect your blog to a WordPress.com account to use the powerful features normally only available to WordPress.com users.
 * Version: 1.0
 * Author: 9seeds
 * Author URI: 9seeds.com
 * License: GPL2+
 * Text Domain: wpgeo
 * Domain Path: /languages/
 */

define( 'WP_GEO_DIR', plugin_dir_path( __FILE__ ) );

require_once WP_GEO_DIR . 'includes/class.wp-geo-controller.php';
require_once WP_GEO_DIR . 'includes/class.wp-geo-query.php';

function geo_get_posts( $args = null ) {
	$defaults = array(
		'numberposts' => 5, 'offset' => 0,
		'category' => 0, 'orderby' => 'date',
		'order' => 'DESC', 'include' => array(),
		'exclude' => array(), 'meta_key' => '',
		'meta_value' =>'', 'post_type' => 'post',
	);

	$r = wp_parse_args( $args, $defaults );
	if ( empty( $r['post_status'] ) )
		$r['post_status'] = ( 'attachment' == $r['post_type'] ) ? 'inherit' : 'publish';
	if ( ! empty( $r['numberposts'] ) && empty( $r['posts_per_page'] ) )
		$r['posts_per_page'] = $r['numberposts'];
	if ( ! empty( $r['category'] ) )
		$r['cat'] = $r['category'];
	if ( ! empty( $r['include'] ) ) {
		$incposts = wp_parse_id_list( $r['include'] );
		$r['posts_per_page'] = count( $incposts );  // only the number of posts included
		$r['post__in'] = $incposts;
	} elseif ( ! empty( $r['exclude'] ) )
		$r['post__not_in'] = wp_parse_id_list( $r['exclude'] );

	$r['ignore_sticky_posts'] = true;
	$r['no_found_rows'] = true;
	$r['suppress_filters'] = false;
	
	$geo_posts = new WP_Query();
	return $geo_posts->query( $r );
}

function wp_geo_controller() {
	return WP_Geo_Controller::get_instance();
}

function geo_query_init() {
	$controller = wp_geo_controller();
	$controller->hook();
}
add_action( 'init', 'geo_query_init' );

/*
$args = array(
	'post_type' => 'post',
	'geo_query' => array(
		'lat' => 45,
		'lon' => 99,
	),
	'orderby' => 'distance'
);
$posts = geo_get_posts( $args );
OR
$geo_query = new WP_Query();
$posts = $geo_query->query( $args );
*/