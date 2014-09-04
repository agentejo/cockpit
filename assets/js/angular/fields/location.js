/**
 * Map field.
 */

(function($){

    angular.module('cockpit.fields').directive("locationfield", ['$timeout', '$compile', function($timeout, $compile){

        var mapId = 0;

        return {
            require: 'ngModel',
            restrict: 'E',

            link: function (scope, elm, attrs, ngModel) {

                var fieldId    = 'mapfield-'+(mapId++),
                    $container = $(['<div>',
                                        '<div id="'+fieldId+'" style="min-height:350px;margin-bottom:10px;"></div>',
                                        '<div class="uk-grid uk-grid-width-medium-1-2">',
                                            '<div><span class="uk-text-small">Lat:</span> <input type="text" class="uk-width-1-1" data-ref="lat"></div>',
                                            '<div><span class="uk-text-small">Lng:</span> <input type="text" class="uk-width-1-1" data-ref="lng"></div>',
                                        '</div>',
                                    '</div>'].join('\n')),

                    $inputLat     = $container.find('input[data-ref="lat"]'),
                    $inputLng     = $container.find('input[data-ref="lng"]'),

                    map, marker, start = [51.505, -0.09];

                $container.on('change', 'input[data-ref]', function() {

                    updateScope({
                        lat: $inputLat.val(),
                        lng: $inputLng.val()
                    });
                });

                var deferMap = function() {

                    App.assets.require(window.L ? [] : ['assets/vendor/leaflet/leaflet.js', 'assets/vendor/leaflet/leaflet.css'], function() {

                        map = L.map(fieldId, {
                            center: start,
                            zoom: 6
                        });

                        marker = L.marker(start, {draggable:true}).on('dragend', function() {
                            var latlng = marker.getLatLng();

                            updateScope({
                                lat: latlng.lat,
                                lng: latlng.lng
                            });

                        }).addTo(map);

                        L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                            maxZoom: 18,
                            type: 'xyz'
                        }).addTo(map);


                        ngModel.$render = function() {

                            try {
                                if(ngModel.$viewValue && ngModel.$viewValue.lat && ngModel.$viewValue.lng) {
                                    marker.setLatLng(L.latLng(ngModel.$viewValue.lat, ngModel.$viewValue.lng));
                                    $inputLat.val(ngModel.$viewValue.lat);
                                    $inputLng.val(ngModel.$viewValue.lng);

                                    map.setView([ngModel.$viewValue.lat, ngModel.$viewValue.lng]);
                                }
                            } catch(e) {}
                        };

                        ngModel.$render();

                    });
                };

                elm.replaceWith($container);
                $timeout(deferMap);

                function updateScope(latlng) {

                    ngModel.$setViewValue(latlng);
                    ngModel.$render();

                    if (!scope.$root.$$phase) {
                        scope.$apply();
                    }
                }
            }
        };

    }]);

})(jQuery);
