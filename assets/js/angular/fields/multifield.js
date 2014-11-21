/**
 * field repeater.
 */

(function($){

    angular.module('cockpit.fields').run(['Contentfields', function(Contentfields) {

       Contentfields.register('multifield', {
           label: 'Multi field',
           template: function(model, options) {
               return '<multifield ng-model="'+model+'" allowedfields=\''+JSON.stringify(options.allowedfields || false)+'\'></multifield>';
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
                addrepeaterfield: '@'
            },
            restrict: 'E',
            replace: true,
            templateUrl: App.base('/assets/js/angular/fields/tpl/multifield.html'),

            link: function (scope, elm, attrs, ngModel) {

                var allowedfields;

                $timeout(function(){

                    if (attrs.allowedfields){

                        try {
                            allowedfields = JSON.parse(attrs.allowedfields);
                        } catch(e) {}
                    }

                    scope.contentfields = [];

                    if (allowedfields) {

                        contentfields.forEach(function(field){
                            if(allowedfields[field.name]) scope.contentfields.push(field);
                        });
                    }

                    if (!allowedfields || !scope.contentfields.length) {
                        scope.contentfields = contentfields;
                    }

                    scope.repeaterfields  = ngModel.$viewValue;

                    if (!angular.isArray(scope.repeaterfields)) {
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

                    App.assets.require(UIkit.sortable ? []:['assets/vendor/uikit/js/components/sortable.min.js'], function(){

                        var $list = elm.find('.js-repeaterfields').on("change.uk.sortable",function(e, sortable, ele){

                            ele = angular.element(ele);

                            if(!ele.parent().is('.js-repeaterfields')) return;

                            $timeout(function(){
                                scope.repeaterfields.splice(ele.index(), 0, scope.repeaterfields.splice(scope.repeaterfields.indexOf(ele.scope().repeaterfield), 1)[0]);
                            });
                        });

                        UIkit.sortable($list, {handleClass:'js-repeaterfield-drag', dragCustomClass:'uk-form'});
                    });


                    scope.$watch('repeaterfields', function() {
                        ngModel.$setViewValue(scope.repeaterfields);
                    });

                });
            }
        };

    }]);

})(jQuery);
