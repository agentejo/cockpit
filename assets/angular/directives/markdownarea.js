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

    $.UIkit.markdownarea.addPlugin('images', /(?:\{<(.*?)>\})?!(?:\[([^\n\]]*)\])(?:\(([^\n\]]*)\))?$/gim, function (marker) {

        var img;

        if (marker.found[3] && 'http://'!==marker.found[3].trim()) {
          img = '<img src="'+marker.found[3]+'" alt="">';
        } else {
          img = [
            '<div class="uk-placeholder uk-placeholder-large uk-text-center uk-vertical-align">',
              '<div class="uk-vertical-align-middle"><i class="uk-icon-picture-o"></i></div>',
            '</div>'
          ].join("");
        }

        var replacement = [
            '<div id="'+marker.uid+'" class="uk-overlay uk-display-block">',
                img,
                '<div class="uk-overlay-area">',
                    '<div class="uk-overlay-area-content">',
                        '<div><span class="uk-badge">'+(marker.found[2] || 'Image')+'</span></div>',
                        '<div class="uk-button-group uk-margin-top">',
                            '<button class="uk-button uk-button-primary js-config" type="button" title="Pick image"><i class="uk-icon-hand-o-up"></i></button>',
                            '<button class="uk-button uk-button-danger js-remove" type="button" title="Remove image"><i class="uk-icon-trash-o"></i></button>',
                        '</div>',
                    '</div>',
                '</div>',
            '</div>'
        ].join("");

        marker.area.preview.on('click', '#' + marker.uid + ' .js-config', function () {
            new PathPicker(function(path){
                marker.replace('![' + marker.found[2] + '](' + path.replace('site:', COCKPIT_SITE_BASE_URL) + ')');
            }, "*.(jpg|png|gif)");
        });

        marker.area.preview.on('click', '#' + marker.uid + ' .js-remove', function () {
            marker.replace('');
        });

        return replacement;
    });

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

            txt.val(ngModel.$viewValue || '');

            if(!markdown) {
              markdown = new $.UIkit.markdownarea(txt, options);

              setTimeout(function(){

                  markdown.editor.on("inputRead", $.UIkit.Utils.debounce(function(){
                    autocomplete(markdown.editor);
                  }, 100));

                  markdown.editor.on("change", $.UIkit.Utils.debounce(function(){

                    ngModel.$setViewValue(markdown.editor.getValue());

                    if (!scope.$root.$$phase) {
                      scope.$apply();
                    }
                  }, 100));

                  markdown.fit();

              }, 50);

            }
          };
        }
      };

    });

})(jQuery);