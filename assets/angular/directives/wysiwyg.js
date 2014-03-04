/**
 * Binds a TinyMCE widget to <textarea> elements.
 */

(function($){
  angular.module('cockpit.directives').directive("wysiwyg", function($timeout){

      var generatedIds  = 0,
          defaultConfig = {
            plugins: [
                     "link image lists print preview hr anchor",
                     "code fullscreen media",
                     "table contextmenu paste"
            ],
            height: 350
          };

      return {

        require: 'ngModel',
        restrict: 'A',

        link: function (scope, elm, attrs, ngModel) {

          var expression, options, tinyInstance,
              updateView = function () {
                ngModel.$setViewValue(elm.val());
                if (!scope.$root.$$phase) {
                  scope.$apply();
                }
              };

          // generate an ID if not present
          if (!attrs.id) {
            attrs.$set('id', 'wysiwyg' + generatedIds++);
          }

          if (attrs.wysiwyg) {
            expression = scope.$eval(attrs.wysiwyg);
          } else {
            expression = {};
          }

          options = {
            // Update model when calling setContent (such as from the source editor popup)
            setup: function (ed) {
              var args;
              ed.on('init', function(args) {
                ngModel.$render();
              });
              // Update model on button click
              ed.on('ExecCommand', function (e) {
                ed.save();
                updateView();
              });
              // Update model on keypress
              ed.on('KeyUp', function (e) {
                ed.save();
                updateView();
              });
              // Update model on change, i.e. copy/pasted text, plugins altering content
              ed.on('SetContent', function (e) {
                if(!e.initial){
                  ed.save();
                  updateView();
                }
              });
              if (expression.setup) {
                scope.$eval(expression.setup);
                delete expression.setup;
              }
            },
            mode: 'exact',
            elements: attrs.id
          };

          // extend options with initial defaultConfig and options from directive attribute value
          angular.extend(options, defaultConfig, expression);

          if(defaultConfig.plugins && defaultConfig.plugins.length && tinymce.PluginManager.lookup["mediapath"]) {
            defaultConfig.plugins[0] = "mediapath "+defaultConfig.plugins[0];
          }

          setTimeout(function () {
            tinymce.init(options);
          });

          ngModel.$render = function() {
            if (!tinyInstance) {
              tinyInstance = tinymce.get(attrs.id);
            }
            if (tinyInstance) {
              tinyInstance.setContent(ngModel.$viewValue || '');
            }
          };
        }
      };

    });

})(jQuery);