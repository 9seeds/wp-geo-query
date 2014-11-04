<?php

/**
 * Should be called within context of a post with coordinates
 */
function wp_geo_get_map_img( $args = NULL, $post = NULL ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}
	
	$cache = WP_Geo_Cache::get_instance();
	$location = $cache->get_best_location();

	$start = "{$location['latitude']},{$location['longitude']}";
	$end = "{$post->geo_latitude},{$post->geo_longitude}";
	
	//get polyline for directions
	$code = new WP_Geo_Code();
	$routes = $code->get_directions( $start, $end );

	//@TODO error checking if no routes
	$route = reset( $routes );

	$defaults = array();
	
	if ( isset( $route->overview_polyline->points ) )
		$defaults['polyline'] = $route->overview_polyline->points;
	else
		$defaults['markers'] = array( '&middot;' => $end );

	$args = wp_parse_args( $args, $defaults );
	
	return $code->get_static_map( $args );
}
