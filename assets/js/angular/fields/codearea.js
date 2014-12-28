(function($){

    var reqassets = [];

    if (!window.CodeMirror) {
        reqassets = ['assets/vendor/codemirror/codemirror.js','assets/vendor/codemirror/codemirror.css','assets/vendor/codemirror/pastel-on-dark.css'];
    }


    function autocomplete(cm) {
        var doc  = cm.getDoc(),
            cur  = cm.getCursor(),
            toc  = cm.getTokenAt(cur),
            mode = CodeMirror.innerMode(cm.getMode(), toc.state).mode.name;

        if(!toc.string.trim()) return;

        if (mode == 'xml') { //html depends on xml

            if(toc.string.charAt(0) == "<" || toc.type == "attribute") {
                CodeMirror.showHint(cm, CodeMirror.hint.html, {completeSingle:false});
            }

        } else if (mode == 'javascript') {
            CodeMirror.showHint(cm, CodeMirror.hint.javascript, {completeSingle:false});
        } else if (mode == 'css' || mode == 'less') {
            CodeMirror.showHint(cm, CodeMirror.hint.css, {completeSingle:false});
        } else {
            CodeMirror.showHint(cm, CodeMirror.hint.anyword, {completeSingle:false});
        }
    };

   angular.module('cockpit.fields').directive("codearea", ['$timeout', function($timeout) {

        var events = ["cursorActivity", "viewportChange", "gutterClick", "focus", "blur", "scroll", "update"];

        return {
            require: '?ngModel',
            restrict: 'A',

            link: function (scope, elm, attrs, ngModel) {

                var opts, onChange, deferCodeMirror, codeMirror;

                if (elm[0].type !== 'textarea') {
                    throw new Error('Codemirror can only be applied to a textarea element');
                }

                opts = angular.extend({
                    lineNumbers: true,
                    styleActiveLine: true,
                    matchBrackets: true,
                    matchTags: true,
                    autoCloseBrackets: true,
                    autoCloseTags: true,
                    smartIndent: false,
                    mode: 'text',
                    theme: 'pastel-on-dark'
                }, scope.$eval(attrs.codearea));


                var onChange = function (aEvent) {
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

                var deferCodeMirror = function () {

                    App.assets.require(reqassets, function() {

                        switch(opts.mode) {
                            case 'js':
                            case 'json':
                                opts.mode = 'javascript';
                                break;
                            case 'md':
                                opts.mode = 'markdown';
                                break;
                            case 'php':
                                opts.mode = 'application/x-httpd-php';
                                break;
                            case 'less':
                            case 'scss':
                                mode = 'css';
                                break;
                        }

                        codeMirror = CodeMirror.fromTextArea(elm[0], opts);

                        if(elm.data) {
                            elm.data("codearea", codeMirror);
                        }

                        codeMirror.setOption("mode", opts.mode);
                        codeMirror.setOption("theme", opts.theme);

                        codeMirror.on("inputRead", UIkit.Utils.debounce(function() {
                            autocomplete(codeMirror);
                        }, 200));

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
                                } else if (angular.isObject(value) || angular.isArray(value)) {
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

                        elm.attr('data-uk-check-display', 1).on('display.uk.check', function(e) {

                            if (codeMirror.getWrapperElement().style.height == '0px') {
                                codeMirror.setSize(null, elm.css('height'));
                                codeMirror.refresh();
                            }
                        });
                    });
                };

                $timeout(deferCodeMirror);

            }
        };
    }]);


})(jQuery);
