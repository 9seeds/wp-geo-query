<?php

class WP_Geo_IP {

	const CACHE_USER = 'user'; //location provided by direct input from user
	const CACHE_UA = 'ua'; //location provided by user agent (permission given by user)
	const CACHE_IP = 'ip'; //location gleamed from user agent reported IP address
	
	private $db;
	private $secure;
	private $hash;
	private $expiration;
	private $location_cache;
	
	public function __construct( $expiration = NULL ) {
		global $wpdb, $table_prefix;
		$this->db = $wpdb;
		$this->prefix = $table_prefix;
		$this->secure = is_ssl();

		if ( ! $expiration )
			$this->expiration = 15 * MINUTE_IN_SECONDS;
		
		$cookie_expiration = time() + $this->expiration;

		if ( $this->secure ? isset( $_COOKIE[WP_GEO_SECURE_COOKIE] ) : isset( $_COOKIE[WP_GEO_COOKIE] ) ) {
			$this->hash = $this->secure ? $_COOKIE[WP_GEO_SECURE_COOKIE] : $_COOKIE[WP_GEO_COOKIE];
		} else { //cookie not found
			$this->renew_cookie_hash( $cookie_expiration );
		}

		//always set the cookie to renew expiration alongside transient
		if ( $this->secure ) {
			setcookie( WP_GEO_SECURE_COOKIE, $this->hash, $cookie_expiration, COOKIEPATH, COOKIE_DOMAIN, $this->secure, true );
			$_COOKIE[WP_GEO_SECURE_COOKIE] = $this->hash;
		} else {
			setcookie( WP_GEO_COOKIE, $this->hash, $cookie_expiration, COOKIEPATH, COOKIE_DOMAIN, $this->secure, true );
			$_COOKIE[WP_GEO_COOKIE] = $this->hash;
		}
	}

	private function renew_cookie_hash( $cookie_expiration ) {
		//since we're using a hash in our transient name, we have to keep it under 40 chars per this issue: 
		//https://core.trac.wordpress.org/ticket/15058
		//and since we're adding 'wp_geo_' it should be 33 or less - using md5 to get 32 chars
		$scheme = $this->secure ? 'secure_auth' : 'auth';		
		$token = wp_generate_password( 43, false, false );
		$this->hash = wp_hash( $cookie_expiration . '|' . $token, $scheme );
	}

	public function get_ip() {
		$ip = $_SERVER["REMOTE_ADDR"];
		//@TODO make this more robust
		if ( $ip == '127.0.0.1' )
			$ip = '70.189.128.0'; //Las Vegas, NV
		return $ip;		
	}

	public function get_location( $ip = NULL ) {
		if ( ! $ip )
			$ip = $this->get_ip();
		
		$long_ip = ip2long($ip);
		
    	$qry = "
			SELECT locations.*
			FROM {$this->prefix}geoblocks AS blocks 
			JOIN {$this->prefix}geolocations AS locations ON (blocks.location_id = locations.location_id)
			WHERE %d BETWEEN blocks.start_ip_num AND blocks.end_ip_num
			LIMIT 1
		";

		$location = $this->db->get_row( $this->db->prepare( $qry, $long_ip ), ARRAY_A );
		$this->update_cache_location( $location, self::CACHE_IP );
   		return $location;
	}

	public function get_best_location() {
		$this->get_cache_location();

		if ( ! isset( $this->location_cache[self::CACHE_IP] ) )
			$this->get_location();
		
		$order = array( self::CACHE_USER, self::CACHE_UA, self::CACHE_IP );

		foreach ( $order as $index ) {
			if ( isset( $this->location_cache[$index] ) )
				return $this->location_cache[$index];
		}
		return NULL;
	}

	private function get_transient_key() {
		return 'wp_geo_' . $this->hash;
	}

	public function update_cache_location( $data, $source = NULL ) {
		if ( $source ) {
			$this->get_cache_location( $source );

			if ( ! is_array( $this->location_cache ) )
				$this->location_cache = array();

			$this->location_cache[$source] = $data;
		} else {
			//overwrite everything
			$this->location_cache = $data;
		}
		
		set_site_transient( $this->get_transient_key(), $this->location_cache, $this->expiration );
	}
	
	public function get_cache_location( $source = NULL ) {

		if ( empty( $this->location_cache ) )
			$this->location_cache = get_site_transient( $this->get_transient_key() );
		
		if ( $source && isset( $this->location_cache[$source] ) )
			return $this->location_cache[$source];
		return $this->location_cache;
	}
}