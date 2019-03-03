<field-location>

    <div class="uk-alert" if="{!apiready}">
        Loading maps api...
    </div>

    <div show="{apiready}">
        <div class="uk-form uk-position-relative uk-margin-small-bottom uk-width-1-1" style="z-index:1001">
            <input ref="autocomplete" class="uk-width-1-1" placeholder="{ latlng.address || [latlng.lat, latlng.lng].join(', ') }">
        </div>
        <div ref="map" style="min-height:300px; z-index:0;">
            Loading map...
        </div>
   </div>


    <script>

        var map, marker;

        var locale = document.documentElement.lang.toUpperCase();

        var loadApi = App.assets.require([
            'https://cdn.jsdelivr.net/npm/leaflet@1.3.1/dist/leaflet.min.css',
            'https://cdn.jsdelivr.net/npm/leaflet@1.3.1/dist/leaflet.min.js',
            'https://cdn.jsdelivr.net/npm/places.js@1.7.2/dist/cdn/places.min.js'
        ]);

        var $this = this, defaultpos = {lat:53.55909862554551, lng:9.998652343749995};

        this.latlng = defaultpos;

        this.$updateValue = function(value) {

            if (!value) {
                value = defaultpos;
            }

            if (this.latlng != value) {
                this.latlng = value;

                if (marker) {
                    marker.setLatLng([this.latlng.lat, this.latlng.lng]).update();
                    map.panTo(marker.getLatLng());
                }

                this.update();
            }

        }.bind(this);

        this.on('mount', function() {

            loadApi.then(function() {

                $this.apiready = true;

                setTimeout(function(){

                    var map = L.map($this.refs.map).setView([$this.latlng.lat, $this.latlng.lng], opts.zoomlevel || 13);

                    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    var marker = new L.marker([$this.latlng.lat, $this.latlng.lng], {draggable:'true'});

                    marker.on('dragend', function(e) {
                        $this.$setValue(marker.getLatLng());
                    });

                    map.addLayer(marker);

                    var pla = places({
                        container: $this.refs.autocomplete
                    }).on('change', function(e) {
                        e.suggestion.latlng.address = e.suggestion.value;
                        $this.$setValue(e.suggestion.latlng);
                        marker.setLatLng(e.suggestion.latlng).update();
                        map.panTo(marker.getLatLng());
                        pla.close();
                        pla.setVal('');
                    }).on('suggestions', function (e) {
                      var coords = e.query.match(/^(\-?\d+(?:\.\d+)?),\s*(\-?\d+(?:\.\d+)?)$/);

                      if (!coords) {
                        return;
                      }

                      var latlng = {
                        lat: parseFloat(coords[1]),
                        lng: parseFloat(coords[2])
                      };

                      $this.$setValue(latlng);
                      marker.setLatLng(latlng).update();
                      map.panTo(marker.getLatLng());
                      pla.close();
                      pla.setVal('');
                    });

                }, 50);

                $this.update();
            });

        });


    </script>

</field-location>
