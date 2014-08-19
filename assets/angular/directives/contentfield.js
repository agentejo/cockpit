(function($){

    angular.module('cockpit.services').factory('Contentfields', function Contentfields() {

        var fields = {

            'text': {
                label: 'Text',
                assets:[],
                options: [],
                template: function(model) {
                    return '<input class="uk-width-1-1 uk-form-large" type="text"  ng-model="'+model+'">';
                }
            },

            'select': {
                label: 'Select',
                assets:[],
                options: [],
                template: function(model, settings) {

                    var options = settings.options || [],
                        output  = ['<select class="uk-width-1-1 uk-form-large" data-ng-model="'+model+'">'];

                    for (var i=0;i < options.length;i++) {
                        output.push('<option value="'+options[i]+'">'+options[i]+'</option>');
                    }

                    output.push('</select>');

                    return output.join("\n");
                }
            },

            'boolean': {
                label: 'Boolean',
                assets:[],
                options: [],
                template: function(model) {
                    return '<input type="checkbox" ng-model="'+model+'">';
                }
            },

            'html': {
                label: 'Html',
                assets:['assets/vendor/codemirror/codemirror.js','assets/vendor/codemirror/codemirror.css','assets/vendor/uikit/js/addons/htmleditor.min.js'],
                options: [],
                template: function(model) {

                    var d = $.Deferred();

                    App.assets.require(['assets/angular/directives/htmleditor.js'], function(){
                        d.resolve('<htmleditor ng-model="'+model+'"></htmleditor>');
                    });

                    return d;
                }
            },

            'markdown': {
                label: 'Markdown',
                assets:['assets/vendor/codemirror/codemirror.js','assets/vendor/codemirror/codemirror.css','assets/vendor/marked.js','assets/vendor/uikit/js/addons/htmleditor.min.js'],
                options: [],
                template: function(model) {

                    var d = $.Deferred();

                    App.assets.require(['assets/angular/directives/htmleditor.js'], function(){
                        d.resolve('<htmleditor ng-model="'+model+'" options="{markdown:true}"></htmleditor>');
                    });

                    return d;
                }
            },

            'wysiwyg': {
                label: 'Wysiwyg',
                assets:['assets/vendor/tinymce/tinymce.min.js','assets/angular/directives/wysiwyg.js'],
                options: [],
                template: function(model) {

                    var d = $.Deferred();

                    App.assets.require(['modules/core/Mediamanager/assets/pathpicker.directive.js'], function(){
                        d.resolve('<textarea wysiwyg class="uk-width-1-1 uk-form-large" ng-model="'+model+'"></textarea>');
                    });

                    return d;
                }
            },

            'code': {
                label: 'code',
                assets:['assets/vendor/codemirror/codemirror.js','assets/vendor/codemirror/codemirror.css','assets/vendor/codemirror/pastel-on-dark.css', 'assets/angular/directives/codearea.js'],
                options: [],
                template: function(model, options) {
                    return '<textarea codearea="{mode:\''+(options.syntax || 'text')+'\'}" class="uk-width-1-1" ng-model="'+model+'" style="height:350px !important;"></textarea>';
                }
            },

            'date': {
                label: 'Date',
                assets:['assets/vendor/uikit/js/addons/datepicker.min.js'],
                options: [],
                template: function(model) {
                    return '<div class="uk-form-icon uk-width-1-1"> \
                                <i class="uk-icon-calendar"></i> \
                                <input class="uk-width-1-1 uk-form-large" type="text" data-uk-datepicker="{format:\'YYYY-MM-DD\'}" ng-model="'+model+'"> \
                            </div>';
                }
            },

            'time': {
                label: 'Time',
                assets:['assets/vendor/uikit/js/addons/timepicker.min.js'],
                options: [],
                template: function(model) {
                    return '<div class="uk-form-icon uk-width-1-1" data-uk-timepicker> \
                                <i class="uk-icon-clock-o"></i> \
                                <input class="uk-width-1-1 uk-form-large" type="text" ng-model="'+model+'"> \
                            </div>';
                }
            },

            'media': {
                label: 'Media',
                assets:['modules/core/Mediamanager/assets/pathpicker.directive.js'],
                options: [],
                template: function(model, options) {
                    return '<input type="text" media-path-picker="'+(options.allowed || '*')+'" ng-model="'+model+'">';
                }
            },

            'region': {
                label: 'Region',
                assets:['modules/core/Regions/assets/regionpicker.directive.js'],
                options: [],
                template: function(model) {
                    return '<input class="uk-width-1-1 uk-form-large" type="text" region-picker ng-model="'+model+'">';
                }
            },

            'link-collection': {
                label: 'Collection link',
                assets:['modules/core/Collections/assets/linkcollection.directive.js'],
                options: [],
                template: function(model, options) {
                    return '<div link-collection="'+options.collection+'" ng-model="'+model+'" data-multiple="'+(options.multiple ? 'true':'false')+'">Linking '+options.collection+'</div>';
                }
            },

            'gallery': {
                label: 'Gallery',
                assets:['assets/angular/directives/gallery.js','modules/core/Mediamanager/assets/pathpicker.directive.js'],
                options: [],
                template: function(model) {
                    return '<gallery ng-model="'+model+'"></gallery>';
                }
            },

            'tags': {
                label: 'Tags',
                assets:['assets/angular/directives/tags.js'],
                options: [],
                template: function(model) {
                    return '<tags ng-model="'+model+'"></tags>';
                }
            }
        };

        return {

            register: function(field, settings) {
                fields[field] = angular.extend({
                    label: field,
                    assets:[],
                    options: [],
                    template: function() {

                    }
                }, settings);
            },

            exists: function(field) {
                return fields[field] ? true : false;
            },

            get: function(field) {
                return fields[field];
            }
        };
    });


    angular.module('cockpit.directives').directive("contentfield", function($timeout, $compile, Contentfields) {


        return {

            require: '?ngModel',
            restrict: 'E',

            link: function(scope, elm, attrs, ngModel) {

                var options = $.extend({type: 'text'}, JSON.parse(attrs.options || '{}'));

                if (Contentfields.exists(options.type)) {

                    var field = Contentfields.get(options.type), content;

                    App.assets.require(field.assets, function() {

                        content = field.template(attrs.ngModel, options);

                        if (content['then']) {

                            content.then(function(markup){
                                $compile(elm.html(markup).contents())(scope);
                            });
                        } else {
                            $compile(elm.html(content).contents())(scope);
                        }
                    });

                } else {
                    $compile(elm.html(Contentfields.get('text').template(attrs.ngModel)).contents())(scope);
                }
            }
        };

    });


})(jQuery);