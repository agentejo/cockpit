(function($){


    App.module.directive("linkCollection", function($timeout, $http){

        return {
            require: '?ngModel',
            restrict: 'A',

            compile: function(element, attrs) {

                return function link(scope, elm, attrs, ngModel) {

                    $element = $(elm);



                };
            }
        };

    });



})(jQuery);