<?php

class WP_Geo_Controller {

	private static $instance;
	private $geo_query;

	private function __construct() {
		$this->geo_query = new WP_Geo_Query();
	}

	public function get_instance() {
		if ( ! self::$instance ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	public function hook() {
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) ); //50
	}

	public function pre_get_posts( $query ) {

		$this->geo_query->parse_query_vars( $query->query_vars );
		if ( $this->geo_query->queries ) {
			add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 10, 2 );
			//add_filter( 'posts_request', array( $this, 'posts_request' ), 10, 2 );
		}
	}

	public function posts_clauses( $pieces, $query ) {
		global $wpdb;

		//@TODO could also use usermeta, or buddypress xprofile
		$clauses = $this->geo_query->get_sql( 'post', $wpdb->posts, 'ID', $query );
		
		$pieces['fields'] .= $clauses['fields'];
		$pieces['join'] .= $clauses['join'];
		$pieces['where'] .= $clauses['where'];
		//override orderby
		$pieces['orderby'] = $clauses['orderby'];

		return $pieces;
	}

	public function posts_request( $request, $query ) {
		die(print_r($request,true));
	}
}
