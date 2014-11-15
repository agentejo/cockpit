/**
 * field repeater.
 */

(function($){

    angular.module('cockpit.fields').run(['Contentfields', function(Contentfields) {

       Contentfields.register('fieldrepeater', {
           label: 'Field repeater',
           template: function(model, options) {
               return '<fieldrepeater ng-model="'+model+'"></fieldrepeater>';
           }
       });

    }]);

    angular.module('cockpit.fields').directive("fieldrepeater", ['$timeout', '$compile', 'Contentfields', function($timeout, $compile, Contentfields) {

        var contentfields = [],
            exclude       = ['select', 'boolean', 'link-collection', 'fieldrepeater'];

        Contentfields.fields().forEach(function(field){
            if(exclude.indexOf(field.name) == -1) {
                contentfields.push(field);
            }
        });

        return {
            require: 'ngModel',
            scope: {
                repeaterfields: '@',
                addrepeaterfield: '@',
            },
            restrict: 'E',
            replace: true,
            templateUrl: App.base('/assets/js/angular/fields/tpl/fieldrepeater.html'),

            link: function (scope, elm, attrs, ngModel) {

                $timeout(function(){

                    scope.contentfields   = contentfields;
                    scope.repeaterfields  = ngModel.$viewValue;

                    if(!scope.repeaterfields.push) {
                        scope.repeaterfields = [];
                    }

                    scope.addrepeaterfield = function(type) {

                        scope.repeaterfields.push({
                            "type": type || 'text',
                            "value": ''
                        });
                    };

                    scope.removerepeaterfield = function(index) {

                        scope.repeaterfields.splice(index, 1);
                    };


                    scope.$watch('repeaterfields', function() {
                        ngModel.$setViewValue(scope.repeaterfields);
                    });

                });
            }
        };

    }]);

})(jQuery);
