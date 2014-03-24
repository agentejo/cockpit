/**
 * Gallery field.
 */

(function($){

    var site_base  = COCKPIT_SITE_BASE_URL.replace(/^\/+|\/+$/g, ""),
        media_base = COCKPIT_MEDIA_BASE_URL.replace(/^\/+|\/+$/g, ""),
        site2media = media_base.replace(site_base, "").replace(/^\/+|\/+$/g, "");

    var tpl = [
        '<div>',
            '<div class="uk-grid uk-grid-small uk-grid-width-medium-1-5 uk-grid-width-1-3 gallery-list"></div>',
            '<button class="uk-button uk-margin-top" type="button"><i class="uk-icon-plus-circle"></i></button>',
        '</div>'
    ].join('')


    angular.module('cockpit.directives').directive("gallery", function($timeout){

      return {

        require: 'ngModel',
        restrict: 'E',

        link: function (scope, elm, attrs, ngModel) {

            var $gal = $(tpl),
                $container = $gal.find('.uk-grid'),
                images;

            $gal.find(">button").on("click", function(){
                new PathPicker(function(path){

                    if(String(path).match(/\.(jpg|png|gif)$/i)){

                        images.push(path);
                        App.notify(App.i18n.get("%s image(s) added", 1));
                        updateSope();
                        renderImages();

                    } else {

                        $.post(App.route('/mediamanager/api'), {"cmd":"ls", "path": String(path).replace("site:"+site2media, "")}, function(data){

                            var count = 0;

                            if (data && data.files && data.files.length) {

                                data.files.forEach(function(file) {

                                    if(file.name.match(/\.(jpg|png|gif)$/i)) {
                                        images.push("site:"+site2media+'/'+file.path);
                                        count = count + 1;
                                    }
                                });

                                updateSope();
                                renderImages();
                            }

                            App.notify(App.i18n.get("%s image(s) added", count));

                        }, "json");
                    }

                }, "*");
            });

            $gal.on("click", ".js-remove", function(){

                var ele = $(this);

                App.Ui.confirm(App.i18n.get("Are you sure?"), function(){
                    var item  = ele.closest('div[data-path]'),
                        index = $container.children().index(item);

                    images.splice(index, 1);
                    item.fadeOut(function(){ item.remove(); });

                    updateSope();
                });
            });


            App.assets.require(window.nativesortable ? []:['assets/vendor/nativesortable.js'], function(){

                $container.on("dragend", "[draggable]",function(){

                    var imgs = [];

                    $container.children().each(function(){
                        imgs.push($(this).data("path"));
                    });

                    images = imgs;

                    updateSope()

                });

                nativesortable($container[0]);
            });

            ngModel.$render = function() {

                if(!images) {
                    images = ngModel.$viewValue || [];
                }

                setTimeout(function(){
                    renderImages();
                }, 10);
            };

            function updateSope() {

                ngModel.$setViewValue(images);

                if (!scope.$root.$$phase) {
                    scope.$apply();
                }
            }

            function renderImages() {

                $container.empty();

                if (images && images.length) {
                    images.forEach(function(path, index){

                        $container.append([
                            '<div class="uk-grid-margin" data-path="'+path+'" draggable="true">',
                                '<div class="uk-thumbnail"><img src="'+App.route("/mediamanager/thumbnail/"+btoa(path))+'/150/150"></div>',
                                '<div class="gallery-list-actions"><button class="uk-button uk-button-small uk-button-danger js-remove" type="button"><i class="uk-icon-trash-o"></i></button></div>',
                            '</div>'
                        ].join(''));
                    });
                } else {
                    $container.append('<div class="uk-width-1-1 uk-grid-margin"><p class="uk-text-muted uk-text-small">'+App.i18n.get('No images')+'</p></div>');
                }
            }

            elm.replaceWith($gal);
        }
      };

    });

})(jQuery);
