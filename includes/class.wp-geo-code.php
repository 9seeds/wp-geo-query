<?php

class WP_Geo_Code {
	private $key;
	private $geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json';
	
	public function get_postal_code( $lat, $lon ) {
		$args = array(
			'latlng' => "{$lat},{$lon}",
			'result_type' => 'postal_code',
			'key' => $this->get_key()
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
					return $component->short_name;
			}
		}
		return NULL;
	}

	public function get_location( $fuzzy_address ) {
		$args = array(
			'address' => urlencode( $fuzzy_address ),
			'key' => $this->get_key()
		);
		$url = add_query_arg( $args, $this->geocode_url );
		
		$result = wp_remote_get( $url );

		if ( isset( $result['response']['code'] ) && $result['response']['code'] == 200 ) {
			$body = json_decode( $result['body'] );
			if ( $body->results )
				return $this->parse_components( $body->results );
		}
		return NULL;
	}

	private function parse_components( $results ) {
		$components = array();
		//only examine the first result
		$result = reset( $results );

		$capture = array( 'street_number', 'route', 'locality', 'administrative_area_level_1', 'country', 'postal_code' );
		
		$components['formatted_address'] = $result->formatted_address;

		foreach ( $result->address_components as $component ) {
			$type = reset( $component->types );
			if ( in_array( $type, $capture ) )
				$components[$type] = $component->short_name;
 		}

		$components['latitude'] = $result->geometry->location->lat;
		$components['longitude'] = $result->geometry->location->lng;

		return $components;
	}

	private function get_key() {
		if ( ! $this->key )
			$this->key = get_option( 'wp_geo_api_key' );
		return $this->key;
	}

}