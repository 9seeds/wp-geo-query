<?php

class WP_Geo_Query {
	public $queries = array();

	public function __construct( $geo_query = false ) {
		if ( ! $geo_query )
			return;

		$this->queries = array();

		foreach ( $geo_query as $key => $query ) {
			if ( ! is_array( $query ) )
				continue;

			$this->queries[] = $query;
		}
	}

	public function parse_query_vars( $qv ) {
		$geo_query = array();

		if ( ! empty( $qv['geo_query'] ) && is_array( $qv['geo_query'] ) ) {
			$geo_query = array_merge( $geo_query, $qv['geo_query'] );
		}

		$this->__construct( $geo_query );
	}

	public function get_sql( $type, $primary_table, $primary_id_column, $context = null ) {
		global $wpdb;

		if ( ! $meta_table = _get_meta_table( $type ) )
			return false;

		$meta_id_column = sanitize_key( $type . '_id' );

		//get the 1st query
		$geo_query = reset( $this->queries );
		
		//from
		//http://stackoverflow.com/questions/574691/mysql-great-circle-distance-haversine-formula
		//http://www.plumislandmedia.net/mysql/haversine-mysql-nearest-loc/
		//haversine distance (as the crow flies)
		//3956 or 3963.17 miles
		//6371 or 6378.10 km
		//@TODO add switch for kilometers instead of miles

		//@TODO fix this to prevent SQL injection
		$fields = ",
			3956 * 
			acos( 
		      cos(radians( {$geo_query['geo_latitude']} ))
		    * cos(radians( mt_latitude.meta_value ))
		    * cos(radians( {$geo_query['geo_longitude']} ) - radians( mt_longitude.meta_value ))
		    + sin(radians( {$geo_query['geo_latitude']} )) 
		    * sin(radians( mt_latitude.meta_value ))
		  ) as distance";

		$join = array();
		$join[] = "LEFT JOIN $meta_table AS mt_latitude ON ( $primary_table.$primary_id_column = mt_latitude.$meta_id_column AND mt_latitude.meta_key = 'geo_latitude' )";
		$join[] = "LEFT JOIN $meta_table AS mt_longitude ON ( $primary_table.$primary_id_column = mt_longitude.$meta_id_column AND mt_longitude.meta_key = 'geo_longitude' )";
		$join = implode( "\n", $join );

		//@TODO add WHERE distance >= {$distance}
		$where = '';

		$orderby = "distance ASC";
		
		return apply_filters_ref_array( 'get_geo_sql', array( compact( 'fields', 'join', 'where', 'orderby' ), $geo_query, $type, $primary_table, $primary_id_column, $context ) );
	}

}
