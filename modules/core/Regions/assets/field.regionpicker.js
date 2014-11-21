(function($) {

    angular.module('cockpit.fields').run(['Contentfields', function(Contentfields) {

        Contentfields.register('region', {
            label: 'Region',
            template: function(model) {
                return '<input class="uk-width-1-1 uk-form-large" type="text" region-picker ng-model="'+model+'">';
            }
        });

    }]);

    var regions = [];

    angular.module('cockpit.directives').directive("regionPicker", function($timeout, $http){

        return {
            require: '?ngModel',
            restrict: 'A',

            compile: function(element, attrs) {

                return function link(scope, elm, attrs, ngModel) {

                    $element = $(elm);

                    $http.post(App.route("/api/regions/find"), {}).success(function(data){

                        regions = data, options = [];

                        $element.wrap('<div class="uk-autocomplete uk-width-1-1"><div class="uk-form-icon uk-width-1-1"></div></div>').before('<i class="uk-icon-th-large"></i>');

                        regions.forEach(function(region){
                            options.push({value:region.name});
                        });

                        var autocompleter = UIkit.autocomplete($element.parent(), {source:options, minLength:1});

                        if (ngModel) {

                            $timeout(function(){

                                $element.on('blur', function(data){

                                    if (angular.isDefined(ngModel)) {
                                        ngModel.$setViewValue($element.val());
                                        if (!scope.$$phase) scope.$apply();
                                    }
                                });

                            }, 0);
                        }

                    });

                };
            }
        };

    });


})(jQuery);
