/**
 * Gallery field.
 */

(function($){

    var site_base  = COCKPIT_SITE_BASE_URL.replace(/^\/+|\/+$/g, ""),
        media_base = COCKPIT_MEDIA_BASE_URL.replace(/^\/+|\/+$/g, ""),
        site2media = media_base.replace(site_base, "").replace(/^\/+|\/+$/g, "");

    var tpl = [
        '<div>',
            '<ul class="uk-grid uk-grid-small uk-grid-width-medium-1-5 uk-grid-width-1-3 gallery-list uk-sortable"></ul>',
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
                media;

            $gal.find(">button").on("click", function(){
                new PathPicker(function(path){

                    if(String(path).match(/\.(jpg|jpeg|png|gif|mp4|mpeg|webm|ogv|wmv)$/i)){

                        media.push(path);
                        App.notify(App.i18n.get("%s media file(s) added", 1));
                        updateSope();
                        rendermedia();

                    } else {

                        $.post(App.route('/mediamanager/api'), {"cmd":"ls", "path": String(path).replace("site:"+site2media, "")}, function(data){

                            var count = 0;

                            if (data && data.files && data.files.length) {

                                data.files.forEach(function(file) {

                                    if(file.name.match(/\.(jpg|jpeg|png|gif|mp4|mpeg|webm|ogv|wmv)$/i)) {
                                        media.push("site:"+site2media+'/'+file.path);
                                        count = count + 1;
                                    }
                                });

                                updateSope();
                                rendermedia();
                            }

                            App.notify(App.i18n.get("%s media file(s) added", count));

                        }, "json");
                    }

                }, "*");
            });

            $gal.on("click", ".js-remove", function(){

                var ele = $(this);

                App.Ui.confirm(App.i18n.get("Are you sure?"), function(){
                    var item  = ele.closest('li[data-path]'),
                        index = $container.children().index(item);

                    media.splice(index, 1);
                    item.fadeOut(function(){
                        item.remove();

                        if (!media.length) {
                            rendermedia();
                        }
                    });

                    updateSope();
                });
            });


            App.assets.require($.UIkit.sortable ? []:['assets/vendor/uikit/js/addons/sortable.min.js'], function(){

                $container.on("sortable-change",function(){

                    var imgs = [];

                    $container.children().each(function(){
                        imgs.push($(this).data("path"));
                    });

                    media = imgs;

                    updateSope()

                });

                $.UIkit.sortable($container);

            });

            ngModel.$render = function() {

                if(!media) {
                    media = ngModel.$viewValue || [];
                }

                setTimeout(function(){
                    rendermedia();
                }, 10);
            };

            function updateSope() {

                ngModel.$setViewValue(media);

                if (!scope.$root.$$phase) {
                    scope.$apply();
                }
            }

            function rendermedia() {

                $container.empty();

                var mediatpl;

                if (media && media.length) {
                    media.forEach(function(path, index){

                        if(path.match(/\.(jpg|jpeg|png|gif|svg)$/i)) {
                            mediatpl = '<img src="'+App.route("/mediamanager/thumbnail/"+btoa(path))+'/150/150">';
                        }

                        if(path.match(/\.(mp4|mpeg|ogv|webm|wmv)$/i)) {
                            mediatpl = '<video src="'+path.replace("site:", COCKPIT_SITE_BASE_URL)+'" style="max-width:100%;height:auto;"></video>';
                        }

                        $container.append([
                            '<li class="uk-grid-margin" data-path="'+path+'" draggable="true" title="'+path+'" stype="position:relative;">',
                                '<div class="uk-thumbnail" style="min-height:180px;">'+mediatpl+'</div>',
                                '<div class="gallery-list-actions"><button class="uk-button uk-button-small uk-button-danger js-remove" type="button"><i class="uk-icon-trash-o"></i></button></div>',
                            '</li>'
                        ].join(''));
                    });
                } else {
                    $container.append('<div class="uk-width-1-1 uk-grid-margin"><p class="uk-text-muted uk-text-small">'+App.i18n.get('No media files.')+'</p></div>');
                }
            }

            elm.replaceWith($gal);
        }
      };

    });

})(jQuery);
