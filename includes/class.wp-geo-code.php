<?php

class WP_Geo_Code {
	private $key;
	private $geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json';
	private $directions_url = 'https://maps.googleapis.com/maps/api/directions/json';

	public function get_postal_code( $lat, $lon ) {
		$args = array(
			'latlng' => "{$lat},{$lon}",
			'result_type' => 'postal_code',
			'key' => $this->get_key(),
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
			'key' => $this->get_key(),
		);
		$url = add_query_arg( $args, $this->geocode_url );
		
		$result = wp_remote_get( $url );

		if ( is_wp_error( $result ) )
			return $result;
		
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

	/**
	 * @see https://developers.google.com/maps/documentation/staticmaps/
	 */
	public function get_static_map( $args ) {
		$defaults = array(
			'maptype' => 'roadmap', //terrain
			'width' => 480,
			'height' => 320,
			'api_key' => $this->get_key(),
			//path options
			'polyline' => NULL,
			'summary_polyline' => NULL,
			'color' => '0xFF0000BF',
			'weight' => 2,
		);

		extract( wp_parse_args( $args, $defaults ) );
		
		$url = "//maps.google.com/maps/api/staticmap?maptype={$maptype}&size={$width}x{$height}&sensor=false";
		$key = "&key={$api_key}";
		
		if ( isset( $polyline ) || isset( $summary_polyline ) ) {
			$url .= "&path=color:{$color}|weight:{$weight}|enc:";

			if ( isset( $polyline ) && isset( $summary_polyline ) ) {
				$url_len = strlen( $url ) + strlen( $key );
				$max_chars = 1860;

				if ( $url_len + strlen( $polyline ) < $max_chars )
					$url .= $polyline;
				else
					$url .= $summary_polyline;
			} else {
				//no summary, no options
				$url .= $polyline;
			}
			
		}

		$url .= $key;
		
		return $url;
	}

	/**
	 * @example https://maps.googleapis.com/maps/api/directions/json?origin=Chicago,IL&destination=Los+Angeles,CA&key=API_KEY
	 */
	public function get_directions( $origin, $destination ) {
		$args = array(
			'origin' => urlencode( $origin ),
			'destination' => urlencode( $destination ),
			'key' => $this->get_key(),
		);
		$url = add_query_arg( $args, $this->directions_url );
		
		$result = wp_remote_get( $url );

		if ( is_wp_error( $result ) )
			return $result;
		
		if ( isset( $result['response']['code'] ) && $result['response']['code'] == 200 ) {
			$body = json_decode( $result['body'] );
			if ( isset( $body->routes ) )
				return $body->routes;
		}
		return NULL;
	}
}