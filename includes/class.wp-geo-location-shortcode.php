<?php

class WP_Geo_Location_Shortcode {

	private static $instance;
	private $query;
	private $locations;
	private $best_location;

	private function __construct() {
		add_shortcode( 'wp_geo_location', array( $this, 'do_shortcode' ) );
		add_action( 'pre_get_posts',  array( $this, 'pre_get_posts' ) );
		add_action( 'wp_ajax_geolocation', array( $this, 'ajax_location' ) );
		add_action( 'wp_ajax_nopriv_geolocation', array( $this, 'ajax_location' ) );
		
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

	public function do_shortcode( $atts, $content='' ) {
		$placeholder = isset( $atts['placeholder'] ) ? esc_attr( $atts['placeholder'] ) : '';

		ob_start();

		include WP_GEO_DIR . 'views/location_shortcode.php';

		do_action( 'wp_geo_location_after' );
		
		return ob_get_clean();;
	}

	public function pre_get_posts( $query ) {		
		if ( is_admin() || ! is_main_query() )
			return;

		$this->maybe_process_form();

		$this->query = $query;
		add_action( 'wp', array( $this, 'maybe_enqueue' ) );
	}

	private function maybe_process_form() {
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

				//@TODO maybe look for option here to not load GeoIP
				$this->load_locations();
				
				//enqueue
				wp_enqueue_style( 'font-awesome' );
				wp_localize_script( 'location-shortcode', 'wp_geo',
							array(
								'ajaxurl' => admin_url( 'admin-ajax.php' ),
								'has_ua_cache' => (bool)isset( $this->locations[WP_Geo_IP::CACHE_UA] ),
				) );
				wp_enqueue_script( 'location-shortcode' );
				return;
			}
		}
	}

	private function load_locations() {
		$ip = new WP_Geo_IP();
		$this->best_location = $ip->get_best_location();
		$this->locations = $ip->get_cache_location();
		//die('<pre>'.print_r($this->locations,true));
	}

	public function ajax_location() {
		//file_put_contents( '/tmp/ajax.txt', print_r($_REQUEST,true) . print_r($_COOKIE,true));

		$ua_location = $_POST['location'];
		
		$code = new WP_Geo_Code();
		$ua_location['postal_code'] = $code->get_postal_code( $ua_location['latitude'], $ua_location['longitude'] );

		$ip = new WP_Geo_IP();
		$ip->update_cache_location( $ua_location, WP_Geo_IP::CACHE_UA );
		
		die( $ua_location['postal_code'] );
	}

	public function hook_debug( $name ) {
		echo "<!-- {$name} -->\n";
	}
}