<?php

class WP_Geo_Admin {

	private $session_dir;
	private $session_plugin;

	public function __construct() {
		$this->session_dir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WP_GEO_SESSION_DIR;
		$this->session_plugin = WP_GEO_SESSION_DIR . DIRECTORY_SEPARATOR . WP_GEO_SESSION_FILE;
	}
	
	public function hook() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
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
	

}