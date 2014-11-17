/**
 * Tags field.
 */

(function($){


    angular.module('cockpit.fields').directive("tags", ['$timeout', function($timeout) {

        return {

            require  : 'ngModel',
            restrict : 'E',
            scope    : {
                tags: '@'
            },
            templateUrl : App.base('/assets/js/angular/fields/tpl/tags.html'),

            link: function (scope, elm, attrs, ngModel) {

                $timeout(function(){

                    scope.tags  = ngModel.$viewValue;

                    if (!angular.isArray(scope.tags)) {
                        scope.tags = [];
                    }

                    scope.removeTag = function(index) {
                        scope.tags.splice(index, 1);
                    };


                    elm.find('input:first').on("keydown", function(e) {

                        if (e.which && e.which == 13) {

                            var tag = this.value.trim();

                            if (scope.tags.indexOf(tag) === -1 ) {
                                scope.tags.push(tag);
                            }

                            e.preventDefault();
                            this.value = "";

                            scope.$apply();
                        }

                    });

                    scope.$watch('tags', function() {
                        ngModel.$setViewValue(scope.tags);
                    });
                });
            }
        };

    }]);

})(jQuery);
