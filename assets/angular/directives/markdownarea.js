/**
 * Binds a UIkit markdownarea widget to <markdown> elements.
 */

(function($){
  
    function autocomplete(cm) {
        var doc = cm.getDoc(), 
            cur = cm.getCursor(),
            toc = cm.getTokenAt(cur), 
            mode = CodeMirror.innerMode(cm.getMode(), toc.state).mode.name;

        if(!toc.string.trim()) return;

        if (mode == 'xml') { //html depends on xml

            if(toc.string.charAt(0) == "<" || toc.type == "attribute") {
                CodeMirror.showHint(cm, CodeMirror.hint.html, {completeSingle:false});
            }
        } else {
            if(toc.string.charAt(0) != "<") {
              CodeMirror.showHint(cm, CodeMirror.hint.anyword, {completeSingle:false});  
            } 
        }
    };


    angular.module('cockpit.directives').directive("markdown", function($timeout){

      return {

        require: 'ngModel',
        restrict: 'E',

        link: function (scope, elm, attrs, ngModel) {

          var txt = $('<textarea placeholder="Markdown code..." class="js-markdownarea" style="display:none;"></textarea>'), markdown, options;

          options = $.extend({}, $.UIkit.markdownarea.defaults);

          options.codemirror.autoCloseTags = true;
          options.maxsplitsize = 300;

          elm.after(txt).hide();


          ngModel.$render = function() {
            
            txt.val(ngModel.$viewValue || '')

            if(!markdown) {
              markdown = new $.UIkit.markdownarea(txt, options);

              setTimeout(function(){
                  txt.on("markdownarea-update", function(){
                    ngModel.$setViewValue(txt.val());
                    if (!scope.$root.$$phase) {
                      scope.$apply();
                    }
                  });

                  markdown.editor.on("inputRead", $.UIkit.Utils.debounce(function(){
                    autocomplete(markdown.editor);
                  }, 100));

                  markdown.fit();

              }, 50);

            }
          };
        }
      };

    });

})(jQuery);