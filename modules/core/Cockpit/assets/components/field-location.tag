<field-location>

    <div class="uk-alert" if="{!window.GOOGLE_MAPS_API_KEY}">
        Google Maps API is missing.
    </div>

    <div show="{window.GOOGLE_MAPS_API_KEY}">
        <div class="uk-form uk-form-icon uk-margin-small-bottom uk-width-1-1">
            <i class="uk-icon-search"></i><input name="autocomplete" class="uk-width-1-1" value="{ latlng.address }">
        </div>
        <div name="map" style="min-height:300px;">
            Loading map...
        </div>

        <div class="uk-text-small uk-margin-small-top">
            LAT: <span class="uk-text-muted">{ latlng.lat }</span> LNG: <span class="uk-text-muted">{ latlng.lng }</span>
        </div>
   </div>


    <script>

        var map, marker;

        var locale = document.documentElement.lang.toUpperCase();

        var loadApi = (function(){

            var p, fn = function(){

                if (!p) {

                    p = new Promise(function(resolve){

                        var script = document.createElement('script');

                        script.async = true;

                        script.onload = function() {

                            google.load("maps", "3", {other_params: 'libraries=places&language=' + locale, callback: function(){
                              if (google && google.maps.places) resolve();
                            }});
                        };

                        script.onerror = function() {
                            alert('Failed loading google maps api.');
                        };

                        script.src = 'https://www.google.com/jsapi?key='+(window.GOOGLE_MAPS_API_KEY || '');

                        document.getElementsByTagName('head')[0].appendChild(script);
                    });
                }

                return p;
            };

            return fn;
        })();

        var $this = this;

        this.latlng = {lat:53.55909862554551, lng:9.998652343749995};

        this.$updateValue = function(value) {

            if (!value) {
                value = {lat:53.55909862554551, lng:9.998652343749995};
            }

            if (this.latlng != value) {
                this.latlng = value;

                if (marker) {
                    marker.setPosition(new google.maps.LatLng(this.latlng.lat, this.latlng.lng));
                }

                this.update();
            }

        }.bind(this);

        this.on('mount', function(){

            loadApi().then(function(){

                var point = new google.maps.LatLng($this.latlng.lat, $this.latlng.lng), input, autocomplete;

                map = new google.maps.Map($this.map, {
                    zoom   : 6,
                    center : point
                });

                marker = new google.maps.Marker({
                    position  : point,
                    map       : map,
                    draggable : true
                });

                google.maps.event.addListener(marker, 'dragend', function() {
                    var point = marker.getPosition();
                    // Reset input value
                    input.value = '';
                    $this.$setValue({lat: point.lat(), lng: point.lng(), address: input.value});
                });

                App.$(window).on('resize', function(){
                    google.maps.event.trigger(map,'resize');
                    map.setCenter(marker.getPosition());
                });


                input = $this.autocomplete;

                autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.bindTo('bounds', map);

                google.maps.event.addListener(autocomplete, 'place_changed', function(e) {

                    var place = autocomplete.getPlace();

                    if (!place.geometry) {
                      return;
                    }

                    // If the place has a geometry, then present it on a map.
                    if (place.geometry.viewport) {
                      map.fitBounds(place.geometry.viewport);
                    } else {
                      map.setCenter(place.geometry.location);
                    }

                    marker.setPosition(place.geometry.location);

                    var point = marker.getPosition();
                    $this.$setValue({lat: point.lat(), lng: point.lng(), address: input.value});
                });

                google.maps.event.addDomListener(input, 'keydown', function(e) {
                    if (e.keyCode == 13) {
                        e.preventDefault();
                    }
                });


            });

        });


    </script>

</field-location>
