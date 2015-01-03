(function(module, $){


    angular.module('cockpit.fields').run(['Contentfields', function(Contentfields) {

       Contentfields.register('media', {
           label: 'Media',
           template: function(model, options) {
               return '<input type="text" media-path-picker="'+(options.allowed || '*')+'" ng-model="'+model+'" caption="'+(options.caption || '')+'">';
           }
       });

    }]);

    angular.module('cockpit.fields').directive("mediaPathPicker", ['$timeout', function($timeout){

        return {
            require: '?ngModel',
            restrict: 'A',

            compile: function(element, attrs) {

                $(element).hide();

                return function link(scope, elm, attrs, ngModel) {

                    $element = $(elm);

                    var caption = attrs.caption || App.i18n.get('Pick Media path'),
                        $tpl    = $('<div><div class="uk-margin" data-preview=""></div><button class="uk-button uk-button-small app-button-secondary js-select" type="button"><i class="uk-icon-code-fork"></i> '+caption+'</button> <button class="uk-button uk-button-small app-button-secondary uk-hidden js-clear" type="button"><i class="uk-icon-trash-o"></i></button></div>'),
                        $btn    = $tpl.find('.js-select'),
                        $prv    = $tpl.find('[data-preview]'),
                        $clear  = $tpl.find('.js-clear');

                    $element.after($tpl);


                    function setPath(path) {

                        if (!path) {
                           return $prv.html('<span class="uk-text-muted uk-text-small"><i class="uk-icon-info-circle"></i> '+App.i18n.get('Nothing selected')+'</span>');
                        }

                        if (path && path.match(/\.(jpg|jpeg|png|gif)$/i)) {
                            $prv.html('<div class="uk-margin" title="'+path+'"><img class="uk-responsive-width" src="'+encodeURI(path.replace('site:', window.COCKPIT_SITE_BASE_URL))+'"></div>');

                        } else if (path && path.match(/\.(mp4|ogv|wmv|webm|mpeg|avi)$/i)) {
                            $prv.html('<div class="uk-margin" title="'+path+'"><video class="uk-responsive-width" src="'+encodeURI(path.replace('site:', window.COCKPIT_SITE_BASE_URL))+'"></video></div>');

                        } else {
                            $prv.html(path ? '<div class="uk-trunkate" title="'+path+'">'+path+'</div>':'<div class="uk-alert">No path selected</div>');
                        }
                    }

                    $btn.on("click", function(){

                        new CockpitPathPicker(function(path){

                            setPath(path);

                            if (path) {
                                $clear.removeClass('uk-hidden');
                            }

                            if (angular.isDefined(ngModel)) {
                                ngModel.$setViewValue(path);
                                if (!scope.$$phase) scope.$apply();
                            }
                        }, $element.attr("media-path-picker") || "*");

                    });

                    $clear.on("click", function(){

                        setPath(false);
                        $clear.addClass('uk-hidden');

                        if (angular.isDefined(ngModel)) {
                            ngModel.$setViewValue('');
                            if (!scope.$$phase) scope.$apply();
                        }

                    });

                    if (ngModel) {

                        $timeout(function(){

                            App.assets.require(window.CockpitPathPicker ? [] : 'modules/core/Mediamanager/assets/pathpicker.js', function() {

                                ngModel.$render = function () {
                                    setPath(ngModel.$viewValue);

                                    if (ngModel.$viewValue) {
                                        $clear.removeClass('uk-hidden');
                                    }
                                };

                                ngModel.$render();
                            });

                        }, 0);
                    }

                };
            }
        };

    }]);

})(App.module, jQuery);
