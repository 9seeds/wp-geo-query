<?php

/**
 * @TODO notice to add API Key
 */
class WP_Geo_Admin {

	private $session_dir;
	private $session_plugin;
	private $settings_page_name = 'wp-geo-options';
	private $settings_option_page = 'wp-geo-settings-group';

	public function __construct() {
		$this->session_dir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WP_GEO_SESSION_DIR;
		$this->session_plugin = WP_GEO_SESSION_DIR . DIRECTORY_SEPARATOR . WP_GEO_SESSION_FILE;
	}
	
	public function hook() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	public function admin_notices() {
		if ( ! $this->is_session_installed() ) {
			$url = $this->get_session_link();
		    ?>
		    <div class="updated">
				 <p><?php echo sprintf( __( "WP Geo Query requires the <a href='%s'>WP Session Manager Plugin. Click to install</a>", 'wpgeo' ), $url ); ?></p>
		    </div>
    		<?php
			return; //terminate here
		}

		if ( ! is_plugin_active( $this->session_plugin ) ) {
			$url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $this->session_plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $this->session_plugin );
		    ?>
		    <div class="updated">
				 <p><?php echo sprintf( __( "WP Geo Query requires the <a href='%s'>WP Session Manager Plugin. Click to activate</a>", 'wpgeo' ), $url ); ?></p>
		    </div>
    		<?php
			return; //terminate here
		}
	}
	
	private function is_session_installed() {
		return is_dir( $this->session_dir );
	}
	
	private function get_session_link() {
		$slug = WP_GEO_SESSION_DIR;
		return wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $slug ), 'install-plugin_' . $slug );
	}
	
	public function register_settings() {
		register_setting( $this->settings_option_page, 'wp_geo_api_key', array( $this, 'sanitize_key' ) );
		add_settings_section( 'wp_geo_options', __( 'Google API', 'wpgeo' ), array( $this, 'print_instructions' ), 'wp-geo' ); 
		add_settings_field( 'api_key', __( 'Google API Key', 'wpgeo' ), array( $this, 'print_key_input' ), 'wp-geo', 'wp_geo_options' );
	}

	public function add_menu() {
		add_options_page( __( 'WP Geo Query Settings', 'wpgeo' ),
						  __( 'Geo Query', 'wpgeo' ),
						  'manage_options',
						  $this->settings_page_name,
						  array( $this, 'print_options' ) );
	}
	
	public function print_options() {
		?>
		<div class="wrap">
   			<div id="icon-options-general" class="icon32"><br/></div>
			<h2><?php _e( 'WP Geo Settings', 'wpgeo' ); ?></h2>
					
			<form method="post" action="<?php echo admin_url( 'options.php' ); ?>">
				<?php settings_fields( $this->settings_option_page ); ?>
				<?php do_settings_sections( 'wp-geo' ); ?>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}
	
	public function print_instructions() {
		$console_url = 'https://code.google.com/apis/console/';
	   	printf( __( "
			<p>
			<ol>
				<li>Visit the APIs console at <a href='%s' target='_blank'>%s</a> and log in with your Google Account.</li>
				<li>Click the <strong>APIs</strong> link in the left-hand menu under <strong>APIs & auth</strong>, then activate the following:</li>
				<ul>
					<li>Directions API</li>
					<li>Geocoding API</li>
					<li>Places API</li>
					<li>Static Maps API</li>
				</ul>
				<li>Once the services have been activated, your API key is available from the <strong>Credentials</strong> page under <strong>APIs & auth</strong>, in the <strong>Public API Access</strong> section.</li>
			</ol>
			</p>", 'wpgeo' ), $console_url, $console_url );
	}
	
	public function print_key_input() {
		?><input type="text" id="wp_geo_api_key" name="wp_geo_api_key" size="40" value="<?php echo get_option('wp_geo_api_key'); ?>" /><?php
	}
	
	public function sanitize_key( $key ) {
		return $key;
	}

}