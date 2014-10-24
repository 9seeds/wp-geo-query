<?php

class WP_Geo_Code {
	private $key = 'AIzaSyDqcwoYBESLz_dVgcHFIygL_3RLlpw9srg';
	private $geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json';
	
	public function get_postal_code( $lat, $lon ) {

		$args = array(
			'latlng' => "{$lat},{$lon}",
			'result_type' => 'postal_code',
			'key' => $this->key
		);
		$url = add_query_arg( $args, $this->geocode_url );
		
		$result = wp_remote_get( $url );

		if ( isset( $result['response']['code'] ) && $result['response']['code'] == 200 ) {
			$body = json_decode( $result['body'] );
			if ( $body->results )
				return $this->find_postal_code( $body->results );
		}
		return NULL;
	}

	private function find_postal_code( $results ) {
		foreach ( $results as $result ) {
			foreach ( $result->address_components as $component ) {
				$type = reset( $component->types );
				if ( $type == 'postal_code' )
					return $component->long_name;
			}
		}
		return NULL;
	}

}