/**
 * Map field.
 */

(function($){

    angular.module('cockpit.fields').directive("locationfield", ['$timeout', '$compile', function($timeout, $compile){


        var uuid = 0,
            loadApi = (function(){

                var p, fn = function(){

                    if (!p) {

                        p = new Promise(function(resolve){

                            var script = document.createElement('script');

                            script.async = true;

                            script.onload = function() {

                                google.load("maps", "3", {other_params:'sensor=false&libraries=places', callback: function(){
                                  if (google && google.maps.places) resolve();
                                }});
                            };

                            script.onerror = function() {
                                alert('Failed loading google maps api.');
                            };

                            script.src = 'https://www.google.com/jsapi';

                            document.getElementsByTagName('head')[0].appendChild(script);
                        });
                    }

                    return p;
                };

                return fn;
            })();

        return {
            restrict    : 'EA',
            require     : '?ngModel',
            scope       : {latlng: '@'},
            replace     : true,
            template    : '<div>\
                                <div class="uk-form uk-form-icon uk-margin-small-bottom uk-width-1-1">\
                                    <i class="uk-icon-search"></i><input class="uk-width-1-1">\
                                </div>\
                                <div class="js-map" style="min-height:300px;"> \
                                Loading map... \
                                </div> \
                                <div class="uk-text-small uk-margin-small-top">LAT: <span class="uk-text-muted">{{ latlng.lat }}</span> LNG: <span class="uk-text-muted">{{ latlng.lng }}</span></div> \
                           </div>',
            link        : function (scope, elm, attrs, ngModel) {

                loadApi().then(function(){

                    $timeout(function() {

                        var map, marker, mapId = 'google-maps-location-'+(++uuid), point = new google.maps.LatLng(53.55909862554551, 9.998652343749995),
                            input, autocomplete;

                        scope.latlng = ngModel.$viewValue || {lat: point.lat(), lng:point.lng()};

                        elm.find('.js-map').attr('id', mapId);

                        map = new google.maps.Map(document.getElementById(mapId), {
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
                            updateScope({lat: point.lat(), lng:point.lng()} );
                            input.value = "";
                        });

                        jQuery.UIkit.$win.on('resize', function(){
                            google.maps.event.trigger(map,'resize');
                            map.setCenter(marker.getPosition());
                        });

                        input = elm.find('input')[0];

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
                            input.value = "";

                            var point = marker.getPosition();
                            updateScope({lat: point.lat(), lng:point.lng()} );
                        });

                        google.maps.event.addDomListener(input, 'keydown', function(e) {
                            if (e.keyCode == 13) {
                                e.preventDefault();
                            }
                        });

                        ngModel.$render = function() {

                            try {

                                if (ngModel.$viewValue && ngModel.$viewValue.lat && ngModel.$viewValue.lng) {

                                    var point = new google.maps.LatLng(ngModel.$viewValue.lat, ngModel.$viewValue.lng);

                    				marker.setPosition(point);
                    				map.setCenter(point);
                                }

                            } catch(e) {}
                        };

                        ngModel.$render();
                    });
                });

                function updateScope(latlng) {

                    ngModel.$setViewValue(latlng);

                    scope.latlng = latlng;

                    if (!scope.$root.$$phase) {
                        scope.$apply();
                    }
                }
            }
        };
    }]);

})(jQuery);
