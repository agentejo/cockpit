/**
 * Map field.
 */

(function($){

    angular.module('cockpit.fields').directive("locationfield", ['$timeout', '$compile', '$http', function($timeout, $compile, $http) {

        var uuid = 0,
            locale = document.documentElement.lang.toUpperCase(),
            loadApi = (function(){

                var p, fn = function(){

                    if (!p) {

                        p = new Promise(function(resolve){

                            var script = document.createElement('script');

                            script.async = true;

                            script.onload = function() {

                                $http
                                    .post(App.route('/settings/getGmapsKey'))
                                    .success(function(key) {

                                        google.load('maps', '3', {other_params: 'key=' + key + '&libraries=places&language=' + locale, callback: function() {
                                            if (google && google.maps.places) resolve();
                                        }});
                                    });
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
                                    <i class="uk-icon-search"></i>\
                                    <input class="uk-width-1-1" value="{{ latlng.address }}">\
                                </div>\
                                <div class="js-map" style="min-height:300px;"> \
                                    ' + App.i18n.get('Loading map...') + ' \
                                </div>\
                                <div class="uk-form uk-text-small uk-margin-small-top">\
                                    <fieldset class="uk-float-left">\
                                        <label>\
                                            ' + App.i18n.get('LAT') + ':\
                                            <input type="number" min="-90" max="90" step="any" class="uk-form-small" data-ng-model="latlng.lat" data-ng-change="onChangeCoords()" />\
                                        </label>\
                                        <label>\
                                            ' + App.i18n.get('LNG') + ':\
                                            <input type="number" min="-90" max="90" step="any" class="uk-form-small" data-ng-model="latlng.lng" data-ng-change="onChangeCoords()" />\
                                        </label>\
                                    </fieldset>\
                                    <fieldset class="uk-float-right uk-form-row">\
                                        <span class="uk-form-label">\
                                            ' + App.i18n.get('Update address when dragging marker') + ':\
                                        </span>\
                                        <span class="uk-form-controls uk-form-controls-text">\
                                            <label>\
                                                <input name="addressReset" type="radio" data-ng-model="addressReset" value="clear" checked="checked" />\
                                                ' + App.i18n.get('clear') + '\
                                            </label>\
                                            <label>\
                                                <input name="addressReset" type="radio" data-ng-model="addressReset" value="keep" />\
                                                ' + App.i18n.get('keep') + '\
                                            </label>\
                                            <label>\
                                                <input name="addressReset" type="radio" data-ng-model="addressReset" value="geocode" />\
                                                ' + App.i18n.get('geocode') + '\
                                            </label>\
                                        </span>\
                                    </fieldset>\
                                </div>\
                           </div>',
            link        : function (scope, elm, attrs, ngModel) {

                loadApi().then(function(){

                    $timeout(function() {

                        var map, marker, mapId = 'google-maps-location-'+(++uuid), point = new google.maps.LatLng(53.55909862554551, 9.998652343749995),
                            input, autocomplete, geocoder;

                        scope.latlng = ngModel.$viewValue || {lat: point.lat(), lng: point.lng(), address: ''};
                        scope.addressReset = 'clear';

                        input = elm.find('input')[0];

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

                        geocoder = new google.maps.Geocoder;

                        // Change marker position using mouse
                        google.maps.event.addListener(marker, 'dragend', function() {

                            var point = marker.getPosition();
                            map.panTo(point);

                            switch (scope.addressReset) {
                                case 'clear':
                                    updateScope({lat: point.lat(), lng: point.lng(), address: ''});
                                    break;

                                case 'keep':
                                    updateScope({lat: point.lat(), lng: point.lng(), address: scope.latlng.address});
                                    break;

                                case 'geocode':
                                    geocode(geocoder, point);
                                    break;
                            }
                        });

                        // Change marker position using inputs
                        scope.onChangeCoords = function() {

                            var point = new google.maps.LatLng(scope.latlng.lat, scope.latlng.lng);

                            marker.setPosition(point);
                            map.panTo(point);

                            switch (scope.addressReset) {
                                case 'clear':
                                    scope.latlng.address = '';
                                    break;

                                case 'keep':
                                    break;

                                case 'geocode':
                                    geocode(geocoder, point);
                                    break;
                            }

                        };

                        jQuery.UIkit.$win.on('resize', function(){
                            google.maps.event.trigger(map,'resize');
                            map.setCenter(marker.getPosition());
                        });

                        // Configure autocomplete
                        autocomplete = new google.maps.places.Autocomplete(input, {});
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
                            updateScope({lat: point.lat(), lng: point.lng(), address: input.value});
                        });

                        // Configure address input
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

                /**
                 * Update scope with new position data
                 *
                 * @param {Object} latlng
                 * @param {integer} latlng.lat
                 * @param {integer} latlng.lng
                 * @param {string} latlng.address
                 */
                function updateScope(latlng) {

                    ngModel.$setViewValue(latlng);

                    scope.latlng = latlng;

                    if (!scope.$root.$$phase) {
                        scope.$apply();
                    }
                }

                /**
                 * Geocode point and update scope
                 *
                 * @param {new google.maps.Geocoder} geocoder
                 * @param {goole.maps.LatLng} point
                 */
                function geocode(geocoder, point) {

                    geocoder.geocode({location: point}, function(results, status) {

                        var address = '';

                        if (status === google.maps.GeocoderStatus.OK && results.length) {
                            address = results[0].formatted_address;
                        }

                        updateScope({lat: point.lat(), lng: point.lng(), address: address});
                    });
                }
            }
        };
    }]);

})(jQuery);
