<?php

/**
 * Handles session management and caching of guessed (IP) asked
 * (User-Agent) and input (User) geo locations
 *
 * Uses WP_Session
 */
class WP_Geo_Cache {

	const USER = 'user'; //location provided by direct input from user
	const UA = 'ua'; //location provided by user agent (permission given by user)
	const IP = 'ip'; //location gleamed from user agent reported IP address

	private static $instance;
	private $session;
	private $order;
	
	private function __construct() {
		$this->session = WP_Session::get_instance(); //starts a session if not already started
		$this->order = array( self::USER, self::UA, self::IP );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	public function update( $data, $source = NULL ) {
		if ( $source ) {
			$this->session['location_' . $source] = $data;
		} else {
			//overwrite everything
			foreach ( $data as $index => $location ) {
				$this->session['location_' . $index] = $location;
			}
		}
	}
	
	public function get( $source = NULL ) {
		if ( $source )
			return $this->session['location_' . $source];

		$locations = array();
		foreach ( $this->order as $index ) {
			$value = $this->session['location_' . $index];
			if ( ! empty( $value ) )
				$locations[$index] = $value;
		}
		return $locations;
	}

	public function has( $source ) {
		$value = $this->session['location_' . $source];
		return ! (bool)empty( $value );
	}
	
	public function get_best_location() {
		foreach ( $this->order as $index ) {
			$value = $this->session['location_' . $index];
			if ( ! empty( $value ) )
				return $value;
		}
		return NULL;
	}

}