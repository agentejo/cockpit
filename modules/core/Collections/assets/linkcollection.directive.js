(function($){

    function render($element, $value) {

        if (!$value) {
            $element.html([
                '<div class="uk-placeholder uk-text-center">',
                    '<p class="uk-text-muted">'+App.i18n.get('No item selected.')+'</p>',
                    '<button type="button" class="uk-button uk-button-primary"><i class="uk-icon-link"></i></button>',
                '</div>'
            ].join(''));
        }

    }


    angular.module('cockpit.directives').directive("linkCollection", function($timeout, $http){

        return {
            require: '?ngModel',
            restrict: 'A',

            compile: function(element, attrs) {

                return function link(scope, elm, attrs, ngModel) {

                    var $element = $(elm);

                    ngModel.$render = function() {

                        render($element, ngModel.$viewValue || '');


                        console.log(attrs.linkCollection);
                    };


                };
            }
        };

    });



})(jQuery);