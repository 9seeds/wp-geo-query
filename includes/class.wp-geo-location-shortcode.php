<?php

class WP_Geo_Location_Shortcode {

	private static $instance;
	private $query;
	private $geo_ip;
	private $locations;

	private function __construct() {
		add_shortcode( 'wp_geo_location', array( $this, 'do_shortcode' ) );
		add_action( 'pre_get_posts',  array( $this, 'pre_get_posts' ) );
		add_action( 'wp_ajax_geolocation', array( $this, 'ajax_save_ua_location' ) );
		add_action( 'wp_ajax_nopriv_geolocation', array( $this, 'ajax_save_ua_location' ) );
		
		//add_action( 'all', array( $this, 'hook_debug' ) );
		//add_filter( 'all', array( $this, 'hook_debug' ) );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;		
	}

	public function pre_get_posts( $query ) {
		if ( is_admin() || ! is_main_query() )
			return;

		$this->maybe_process_form();

		$this->query = $query;
		add_action( 'wp', array( $this, 'maybe_enqueue' ) );
	}

	private function maybe_process_form() {
		if ( isset( $_GET['location'] ) ) {
			$user_location = trim( $_GET['location'] );

			//only do comparisons if user entered postal-code
			if ( is_numeric( $user_location ) ) {

				$cache = WP_Geo_Cache::get_instance();
				$locations = $cache->get();

				if ( $user_location != $locations[WP_Geo_Cache::IP]['postal_code'] ) {

				
					if ( isset( $locations[WP_Geo_Cache::UA]['postal_code'] ) ) {

						if ( $user_location != $locations[WP_Geo_Cache::UA]['postal_code'] ) {
							//didn't match UA postal code, save location
							$this->save_user_location( $user_location );
						}
					
					} else { //no UA postal code cached, save this new location
						$this->save_user_location( $user_location );
					}
				}
			}
			//not a postal code, look up and save location
			$this->save_user_location( $user_location );
		}
		//die(print_r($_REQUEST,true));
	}


	public function maybe_enqueue() {
		//@see http://codex.wordpress.org/Function_Reference/get_shortcode_regex
		$pattern = get_shortcode_regex();

		//check all the posts on the current page for our shortcode
		foreach ( $this->query->posts as $post ) {
			if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches )
				 && array_key_exists( 2, $matches )
				 && in_array( 'wp_geo_location', $matches[2] ) ) {

				$cache = WP_Geo_Cache::get_instance();
				

				//@TODO maybe look for option here to not load GeoIP
				if ( ! $cache->has( WP_Geo_Cache::IP ) )					
					$this->save_ip_location();
				
				//enqueue
				wp_enqueue_style( 'font-awesome' );
				wp_enqueue_style( 'location-shortcode' );
				wp_localize_script( 'location-shortcode', 'wp_geo',
							array(
								'ajaxurl' => admin_url( 'admin-ajax.php' ),
								'has_ua_cache' => $cache->has( WP_Geo_Cache::UA ),
				) );
				wp_enqueue_script( 'location-shortcode' );
				return;
			}
		}
	}

	public function do_shortcode( $atts, $content='' ) {
		$placeholder = isset( $atts['placeholder'] ) ? esc_attr( $atts['placeholder'] ) : '';

		$cache = WP_Geo_Cache::get_instance();
		$locations = $cache->get();
		$best_location_info = $cache->get_best_location();
		$best_location = isset( $_GET['location'] ) ? trim( $_GET['location'] ) : $best_location_info['postal_code'];

		ob_start();
		include WP_GEO_DIR . 'views/location_shortcode.php';
		do_action( 'wp_geo_location_after' );
		return ob_get_clean();;
	}

	private function save_ip_location() {
		$geo_ip = new WP_Geo_IP();
		$ip_location = $geo_ip->get_location();

		$cache = WP_Geo_Cache::get_instance();
		$cache->update( $ip_location, WP_Geo_Cache::IP );
	}
	
	private function save_user_location( $fuzzy_address ) {
		$code = new WP_Geo_Code();
		$user_location = $code->get_location( $fuzzy_address );

		$cache = WP_Geo_Cache::get_instance();
		$cache->update( $user_location, WP_Geo_Cache::USER );
	}

	public function ajax_save_ua_location() {
		//file_put_contents( '/tmp/ajax.txt', print_r($_REQUEST,true) . print_r($_COOKIE,true));

		$ua_location = $_POST['location'];
		
		$code = new WP_Geo_Code();
		$ua_location['postal_code'] = $code->get_postal_code( $ua_location['latitude'], $ua_location['longitude'] );

		$cache = WP_Geo_Cache::get_instance();
		$cache->update( $ua_location, WP_Geo_Cache::UA );

		//halt execution with print-out
		die( $ua_location['postal_code'] );
	}

	public function hook_debug( $name ) {
		echo "<!-- {$name} -->\n";
	}
}