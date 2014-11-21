(function($){

    angular.module('cockpit.fields').factory('Contentfields', function Contentfields() {

        var fields = {

            'text': {
                label: 'Text',
                template: function(model) {
                    return '<input class="uk-width-1-1 uk-form-large" type="text"  ng-model="'+model+'">';
                }
            },

            'select': {
                label: 'Select',
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
                template: function(model) {
                    return '<div><input type="checkbox" ng-model="'+model+'"></div>';
                }
            },

            'html': {
                label: 'Html',
                template: function(model) {
                    return '<htmleditor ng-model="'+model+'"></htmleditor>';
                }
            },

            'markdown': {
                label: 'Markdown',
                template: function(model) {
                    return '<htmleditor ng-model="'+model+'" options="{markdown:true}"></htmleditor>';
                }
            },

            'location': {
                label: 'Location',
                template: function(model) {
                    return '<locationfield ng-model="'+model+'"></locationfield>';
                }
            },

            'wysiwyg': {
                label: 'Html (WYSIWYG)',
                template: function(model) {
                    return '<textarea wysiwyg class="uk-width-1-1 uk-form-large" ng-model="'+model+'" style="visibility:hidden;"></textarea>';
                }
            },

            'code': {
                label: 'code',
                template: function(model, options) {
                    return '<textarea codearea="{mode:\''+(options.syntax || 'text')+'\'}" class="uk-width-1-1" ng-model="'+model+'" style="height:350px !important;"></textarea>';
                }
            },

            'date': {
                label: 'Date',
                template: function(model) {

                    var tpl = '<div class="uk-form-icon uk-width-1-1"> \
                                    <i class="uk-icon-calendar"></i> \
                                    <input class="uk-width-1-1 uk-form-large" type="text" data-uk-datepicker="{format:\'YYYY-MM-DD\'}" ng-model="'+model+'"> \
                                </div>';

                    if (!UIkit.datepicker) {

                        App.assets.require(['assets/vendor/uikit/js/components/datepicker.min.js'], function() {

                        });
                    }

                    return tpl;
                }
            },

            'time': {
                label: 'Time',
                template: function(model) {

                    var tpl = $('<div class="uk-form-icon uk-width-1-1"> \
                                    <i class="uk-icon-clock-o"></i> \
                                    <input class="uk-width-1-1 uk-form-large" type="text" ng-model="'+model+'"> \
                                </div>');

                    if (!UIkit.timepicker) {

                        App.assets.require(['assets/vendor/uikit/js/components/timepicker.min.js'], function() {
                            UIkit.timepicker(tpl);
                            tpl.parent().addClass('uk-width-1-1').find('.uk-dropdown').css('width','100%');
                        });
                    }

                    return tpl;
                }
            },

            'gallery': {
                label: 'Gallery',
                template: function(model) {
                    return '<gallery ng-model="'+model+'"></gallery>';
                }
            },

            'tags': {
                label: 'Tags',
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
                    template: function() {

                    }
                }, settings);
            },

            exists: function(field) {
                return fields[field] ? true : false;
            },

            get: function(field) {
                return fields[field];
            },

            fields: function() {
                var ret = [];

                Object.keys(fields).forEach(function(f) {
                    ret.push({name: f, label: fields[f].label});
                });

                return ret;
            }
        };
    });

    angular.module('cockpit.directives').directive("contentfield", ['$timeout', '$compile', 'Contentfields', function($timeout, $compile, Contentfields) {

        return {

            require: '?ngModel',
            restrict: 'E',

            link: function(scope, elm, attrs, ngModel) {

                var defer = function() {

                    var options = $.extend({type: 'text'}, JSON.parse(attrs.options || '{}'));

                    if (Contentfields.exists(options.type)) {

                        var field = Contentfields.get(options.type), content;

                        content = field.template(attrs.ngModel, options);

                        if (content.then) {

                            content.then(function(markup){
                                $compile(elm.html(markup).contents())(scope);
                            });

                        } else {
                            $compile(elm.html(content).contents())(scope);
                        }

                    } else {
                        $compile(elm.html(Contentfields.get('text').template(attrs.ngModel)).contents())(scope);
                    }
                };

                $timeout(defer);
            }
        };

    }]);

})(jQuery);
