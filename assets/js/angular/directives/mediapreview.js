(function($){

    angular.module('cockpit.directives').directive("mediaPreview", ['$timeout', function($timeout){

        return {

            restrict: 'A',

            compile: function() {

                return function link(scope, elm, attrs) {

                    attrs.$observe('mediaPreview', function(url){

                        if(url) {

                            var $r;

                            if (url.match(/\.(jpg|jpeg|png|gif|svg)$/i)) {
                                $r = '<div class="media-url-preview" style="background-image:url('+encodeURI(url)+')"></div>';
                            }

                            if (url.match(/\.(mp4|mpeg|ogv|webm|wmv)$/i)) {
                                $r = '<i class="uk-icon-file-video-o"></i>';
                            }

                            if (url.match(/\.(zip|rar|gz|7zip|bz2)$/i)) {
                                $r = '<i class="uk-icon-file-archive-o"></i>';
                            }

                            if (url.match(/\.(pdf)$/i)) {
                                $r = '<i class="uk-icon-file-pdf-o"></i>';
                            }

                            if (url.match(/\.(sqlite|db)$/i)) {
                                $r = '<i class="uk-icon-database"></i>';
                            }

                            if($r) elm.replaceWith($r);
                        }
                    });
                }
            }
        };
    }]);

})(jQuery);
