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
		//add_action( 'parse_query', array( $this, 'parse_query' ) ); //50
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) ); //50
	}

	/*
	public function parse_query( $query ) {
		//set any query flags
	}
	*/

	public function pre_get_posts( $query ) {

		$this->geo_query->parse_query_vars( $query->query_vars );
		if ( $this->geo_query->queries ) {
			add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 10, 2 );
		}
	}

	public function posts_clauses( $pieces, $query ) {
		global $wpdb;
		die(print_r($pieces,true));
		$clauses = $this->geo_query->get_sql(  'post', $wpdb->posts, 'ID', $query );
		$pieces['join'] .= $clauses['join'];
		$pieces['where'] .= $clauses['where'];

		return $pieces;
	}
}
