/**
 * Binds a TinyMCE widget to <textarea> elements.
 */

(function($){

    var reqassets = [];

    if (!window.tinymce) {
        reqassets.push('assets/vendor/tinymce/tinymce.min.js');
    }

    angular.module('cockpit.fields').directive("wysiwyg", ['$timeout', function($timeout){

        var generatedIds  = 0,
            defaultConfig = {
            plugins: [
                "link image lists preview hr anchor",
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
                        // Update model on tinymce's event ObjectResized
                        ed.on('ObjectResized', function (e) {
                            ed.save();
                            updateView();
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

                var deferTinyMCE = function() {

                    App.assets.require(reqassets, function() {

                        App.assets.require('modules/core/Mediamanager/assets/pathpicker.js', function() {

                            if (!tinymce.PluginManager.lookup["mediapath"]) {
                                registerMediaPathPlugin();
                            }

                            if (defaultConfig.plugins && defaultConfig.plugins.length && tinymce.PluginManager.lookup["mediapath"]) {
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

                        });
                    });
                };

                $timeout(deferTinyMCE);
            }

        };

    }]);

    function registerMediaPathPlugin() {

        tinymce.PluginManager.add('mediapath', function(editor) {

            var picker = function(){
                new CockpitPathPicker(function(path){

                    var content = '';

                    if (!path) {
                       return;
                    }

                    if (path && path.match(/\.(jpg|jpeg|png|gif)/i)) {
                        content = '<img class="auto-size" src="'+path.replace('site:', window.COCKPIT_SITE_BASE_URL)+'">';

                    } else if (path && path.match(/\.(mp4|ogv|wmv|webm|mpeg|avi)$/i)) {
                        content = '<video class="auto-size" src="'+path.replace('site:', window.COCKPIT_SITE_BASE_URL)+'"></video>';

                    } else {
                        content = path;
                    }
                    editor.insertContent(content);
                }, "*");
            };

            editor.addMenuItem('mediapath', {
                icon: 'image',
                text: 'Insert media',
                onclick: picker,
                context: 'insert',
                prependToContext: true
            });

        });
    }

})(jQuery);
