/**
 * Binds a TinyMCE widget to <textarea> elements.
 */

(function($){
  angular.module('cockpit.directives').directive("mediaPreview", function($timeout){
      
      return {
        
        restrict: 'A',

        compile: function() {

          return function link(scope, elm, attrs) {

              attrs.$observe('mediaPreview', function(url){

                if(url) {
                    if(url.match(/\.(jpg|jpeg|png|gif|svg)$/i)) {
                      
                      var $r = $('<div class="media-url-preview" style="background-image:url('+url+')"></div>');

                      elm.replaceWith($r);
                    }
                }
              });
          }
        }
      };
  });

})(jQuery);