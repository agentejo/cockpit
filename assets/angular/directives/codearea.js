(function($){

    angular.module('cockpit.directives').directive("codearea", function($timeout){

        var events = ["cursorActivity", "viewportChange", "gutterClick", "focus", "blur", "scroll", "update"];

        return {
            require: '?ngModel',
            restrict: 'A',

            link: function (scope, elm, attrs, ngModel) {

                var opts, onChange, deferCodeMirror, codeMirror;

                if (elm[0].type !== 'textarea') {
                  throw new Error('uiCodemirror3 can only be applied to a textarea element');
                }

                opts = angular.extend({
                    lineNumbers: true,
                    styleActiveLine: true,
                    matchBrackets: true,
                    mode: 'text',
                    theme: 'pastel-on-dark'
                }, scope.$eval(attrs.codearea));


                onChange = function (aEvent) {
                  return function (instance, changeObj) {
                    var newValue = instance.getValue();
                    if (ngModel && newValue !== ngModel.$viewValue) {
                      ngModel.$setViewValue(newValue);
                    }
                    if (typeof aEvent === "function") {
                      aEvent(instance, changeObj);
                    }
                    if (!scope.$$phase) {
                      scope.$apply();
                    }
                  };
                };

                deferCodeMirror = function () {

                  codeMirror = CodeMirror.fromTextArea(elm[0], opts);

                  if(elm.data) {
                    elm.data("codearea", codeMirror);
                  }

                  // autoload modes
                  if(opts.mode && opts.mode!='text' && opts.mode.indexOf('/')==-1) {
                    App.assets.require(['/assets/vendor/codemirror/mode/%N/%N.js'.replace(/%N/g, opts.mode)], function(){
                        codeMirror.setOption("mode", opts.mode);
                    });
                  }

                  if(opts.theme) {
                    App.assets.require(['/assets/vendor/codemirror/theme/%N.css'.replace(/%N/g, opts.theme)], function(){
                        codeMirror.setOption("theme", opts.theme);
                    });
                  }

                  if (angular.isDefined(scope[attrs.codearea])) {
                    scope.$watch(attrs.codearea, function (newValues) {
                      for (var key in newValues) {
                        if (newValues.hasOwnProperty(key)) {
                          codeMirror.setOption(key, newValues[key]);
                        }
                      }
                    }, true);
                  }

                  if(elm.css("height")) {
                    codeMirror.setSize("100%", elm.css("height"));
                  }

                  codeMirror.on("change", onChange(opts.onChange));

                  for (var i = 0, n = events.length, aEvent; i < n; ++i) {
                    aEvent = opts["on" + events[i].charAt(0).toUpperCase() + events[i].slice(1)];
                    if (aEvent === void 0) {
                      continue;
                    }
                    if (typeof aEvent !== "function") {
                      continue;
                    }
                    codeMirror.on(events[i], aEvent);
                  }

                  if(ngModel){
                    // CodeMirror expects a string, so make sure it gets one.
                    // This does not change the model.
                    ngModel.$formatters.push(function (value) {
                      if (angular.isUndefined(value) || value === null) {
                        return '';
                      }
                      else if (angular.isObject(value) || angular.isArray(value)) {
                        throw new Error('ui-codemirror cannot use an object or an array as a model');
                      }
                      return value;
                    });

                    // Update valid and dirty statuses
                    ngModel.$parsers.push(function (value) {
                        var div = elm.next();
                        div
                          .toggleClass('ng-invalid', !ngModel.$valid)
                          .toggleClass('ng-valid', ngModel.$valid)
                          .toggleClass('ng-dirty', ngModel.$dirty)
                          .toggleClass('ng-pristine', ngModel.$pristine);

                        return value;
                    });


                    // Override the ngModelController $render method, which is what gets called when the model is updated.
                    // This takes care of the synchronizing the codeMirror element with the underlying model, in the case that it is changed by something else.
                    ngModel.$render = function () {
                      codeMirror.setValue(ngModel.$viewValue);
                    };

                    if (!ngModel.$viewValue && ngModel.$setViewValue){
                      ngModel.$setViewValue(elm.val());
                      ngModel.$render();
                    }
                  }

                  // onLoad callback
                  if (angular.isFunction(opts.onLoad)) {
                    opts.onLoad(codeMirror);
                  }
                };

                $timeout(deferCodeMirror);
              }
        };
    });


})(jQuery);