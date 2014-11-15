/**
 * field repeater.
 */

(function($){

    angular.module('cockpit.fields').run(['Contentfields', function(Contentfields) {

       Contentfields.register('multifield', {
           label: 'Multi field',
           template: function(model, options) {
               return '<multifield ng-model="'+model+'"></multifield>';
           }
       });

    }]);

    angular.module('cockpit.fields').directive("multifield", ['$timeout', '$compile', 'Contentfields', function($timeout, $compile, Contentfields) {

        var contentfields = [],
            exclude       = ['select', 'boolean', 'link-collection', 'multifield'];

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
            templateUrl: App.base('/assets/js/angular/fields/tpl/multifield.html'),

            link: function (scope, elm, attrs, ngModel) {

                $timeout(function(){

                    scope.contentfields   = contentfields;
                    scope.repeaterfields  = ngModel.$viewValue;

                    if(!scope.repeaterfields) {
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
