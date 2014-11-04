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

	$route = reset( $routes );

	$defaults = array(
		'polyline' => $route->overview_polyline->points,
	);

	$args = wp_parse_args( $args, $defaults );
	
	return $code->get_static_map( $args );
}
