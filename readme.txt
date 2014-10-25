This product includes GeoLite data created by MaxMind, available from 
<a href="http://www.maxmind.com">http://www.maxmind.com</a>.

http://dev.maxmind.com/geoip/legacy/geolite/

https://developers.google.com/loader/?csw=1#ClientLocation

http://stackoverflow.com/questions/2577305/how-to-get-gps-location-from-the-web-browser


to get a geocoding key (convert addresses to lat/lon)
https://developers.google.com/maps/documentation/geocoding/#api_key


GeoCoder

curl -i "https://maps.googleapis.com/maps/api/geocode/json?address=Route+1+42+K+Devol%2C+OK+73531&key=AIzaSyDqcwoYBESLz_dVgcHFIygL_3RLlpw9srg"
            "location" : {
               "lat" : 34.1959244,
               "lng" : -98.59005959999999
            },

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=API_KEY">
geocoder = new google.maps.Geocoder();
geocoder.geocode(request, callback)


Places

Place lookup (more accurate if address has added a placemarker)
curl -i "https://maps.googleapis.com/maps/api/place/textsearch/json?query=Route+1+42+K%2C+Devol%2C+OK+73531&key=AIzaSyDqcwoYBESLz_dVgcHFIygL_3RLlpw9srg"
            "location" : {
               "lat" : 34.163206,
               "lng" : -98.523832
            }

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places"></script>

service = new google.maps.places.PlacesService(map);
service.textSearch(request, callback);

http://mikejolley.com/2013/12/problems-with-cart-sessions-and-woocommerce/
https://github.com/woothemes/woocommerce/issues/3513
https://eamann.com/tech/wp_session-a-proposal/
