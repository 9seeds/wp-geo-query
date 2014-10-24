var ua_zip;
var ua_location;

(function($){

	function wp_geo_get_zip() {
		var data = {
			action : 'geolocation',
			location : ua_location
		};

		jQuery.post(
			wp_geo.ajaxurl,
			data,
			function ( response ) {
				if ( response != $('#wp-geo-location').val() ) {
					$('#wp-geo-location').val( response );
				}
			}		
		);
	}

	function wp_geo_show_ip() {
		$('#wp-geo-location').val( $('#ip_postal_code').val() );
	}

	function wp_geo_position(position) {
		ua_location = position.coords;
		//alert(JSON.stringify(position, null, 4));

		//send this result to cache via ajax
		if ( jQuery.isReady ) {
			wp_geo_get_zip();
		} else {
		    $(document).ready(wp_geo_get_zip);
		}
		//x.innerHTML = "Latitude: " + position.coords.latitude + "<br>Longitude: " + position.coords.longitude; 
	}

	function wp_geo_error(error) {
		//show IP determined location instead
		if ( jQuery.isReady ) {
			wp_geo_show_ip();
		} else {
		    $(document).ready(wp_geo_show_ip);
		}

		/*
		  switch(error.code) {
          case error.PERMISSION_DENIED:
          break;
          case error.POSITION_UNAVAILABLE:
          break;
          case error.TIMEOUT:
          break;
          case error.UNKNOWN_ERROR:
          break;
		  }
		*/
	}

	if ( wp_geo.has_ua_cache != "1" ) {
		//ask for location right away	
		if (navigator.geolocation) {
			alert('asking');
			navigator.geolocation.getCurrentPosition(wp_geo_position, wp_geo_error);
		} else {
			//Geolocation is not supported by this browser.
		}
	}

}(jQuery));
