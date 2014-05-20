(function($){

    angular.module('cockpit.directives').directive("mediaPreview", function($timeout){

        return {

            restrict: 'A',

            compile: function() {

                return function link(scope, elm, attrs) {

                    attrs.$observe('mediaPreview', function(url){

                        if(url) {

                            var $r;

                            if(url.match(/\.(jpg|jpeg|png|gif|svg)$/i)) {

                                $r = '<div class="media-url-preview" style="background-image:url('+url+')"></div>';
                            }

                            if(url.match(/\.(mp4|mpeg|ogv|webm|wmv)$/i)) {

                                $r = '<i class="uk-icon-play-circle"></i>';
                            }

                            if($r) elm.replaceWith($r);
                        }
                    });
                }
            }
        };
    });

})(jQuery);