<?php

class WP_Geo_IP {

	private $db;
	private $prefix;
	
	public function __construct() {
		global $wpdb, $table_prefix;
		$this->db = $wpdb;
		$this->prefix = $table_prefix;
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
   		return $location;
	}


}