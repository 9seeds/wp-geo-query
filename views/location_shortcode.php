<form id='wp-geo-search' method='get' action=''>
	<label>Enter ZIP code or enter city, state.</label>
	<div>
		<i id='wp-geo-arrow' class='fa fa-location-arrow fa-2x'></i> 
		<input id='wp-geo-location' class='form-control' type='text' size='15' name='location' placeholder='<?php echo $placeholder ?>' value='<?php echo $best_location ?>' />
		<button type='submit'>
			<i class='fa fa-search'></i>
		</button>
	</div>
</form>
<input type='hidden' id='ip_postal_code' name='ip_postal_code' value='<?php echo $this->locations[WP_Geo_IP::CACHE_IP]['postal_code'] ?>' />
<input type='hidden' id='ua_postal_code' name='ua_postal_code' value='<?php $this->locations[WP_Geo_IP::CACHE_UA]['postal_code'] ?>' />