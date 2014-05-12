(function($){

    var collections = false, loaded;

    function render($element, $data) {

        if (!$data.value) {
            $element.html([
                '<div class="uk-placeholder uk-text-center">',
                    '<strong class="uk-text-small">'+$data.collection.name+'</strong>',
                    '<p class="uk-text-muted">'+[App.i18n.get('No item selected.')].join(' ')+'</p>',
                    '<button type="button" class="uk-button uk-button-primary"><i class="uk-icon-link"></i></button>',
                '</div>'
            ].join(''));
        }

    }


    angular.module('cockpit.directives').directive("linkCollection", function($timeout, $http){


        loaded = $http.post(App.route("/api/collections/find"), {}).success(function(data){

            collections = {};

            data.forEach(function(collection){
                collections[collection._id] = collection;
            });
        });


        return {
            require: '?ngModel',
            restrict: 'A',

            compile: function(element, attrs) {

                return function link(scope, elm, attrs, ngModel) {

                    var $element = $(elm).html(''),
                        data     = {};

                    ngModel.$render = function() {

                        loaded.then(function() {
                            data.value = ngModel.$viewValue || '';
                            data.collection = collections[attrs.linkCollection] || {};
                            render($element, data);
                        });
                    };


                };
            }
        };

    });



})(jQuery);