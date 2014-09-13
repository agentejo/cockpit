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
            '<hr>',
            '<button class="uk-button uk-margin-small-right js-gallery-import" type="button"><i class="uk-icon-plus-circle"></i></button>',
            '<a class="uk-text-danger js-gallery-empty" type="button"><i class="uk-icon-trash-o"></i> '+App.i18n.get("Empty")+'</a>',
        '</div>'
    ].join('');


    angular.module('cockpit.fields').directive("gallery", ['$timeout', function($timeout){

        return {

            require: 'ngModel',
            restrict: 'E',

            link: function (scope, elm, attrs, ngModel) {

                var $gal = $(tpl),
                    $container = $gal.find('.uk-grid'),
                    media;

                $gal.find(".js-gallery-import").on("click", function(){

                    App.assets.require(window.CockpitPathPicker ? [] : 'modules/core/Mediamanager/assets/pathpicker.js', function() {

                        new CockpitPathPicker(function(path){

                            if(String(path).match(/\.(jpg|jpeg|png|gif|mp4|mpeg|webm|ogv|wmv)$/i)){

                                media.push({"path":path, "title":""});
                                App.notify(App.i18n.get("%s media file(s) added", 1));
                                updateSope();
                                rendermedia();

                            } else {

                                $.post(App.route('/mediamanager/api'), {"cmd":"ls", "path": String(path).replace("site:"+site2media, "")}, function(data){

                                    var count = 0;

                                    if (data && data.files && data.files.length) {

                                        data.files.forEach(function(file) {

                                            if(file.name.match(/\.(jpg|jpeg|png|gif|mp4|mpeg|webm|ogv|wmv)$/i)) {
                                                media.push({"path":("site:"+site2media+'/'+file.path), "title":""});
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
                });

                $gal.find(".js-gallery-empty").on("click", function(){

                    App.Ui.confirm(App.i18n.get("Are you sure?"), function(){
                        media = [];
                        updateSope();
                        rendermedia();
                    });
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

                $gal.on("click", ".js-title", function(){

                    var item  = $(this).closest('li[data-path]'),
                        title = prompt(App.i18n.get("Title"), item.data('item').title),
                        index = $container.children().index(item);

                    if (title!==null) {

                        media[index].title = title;
                        updateSope();
                    }
                });


                App.assets.require($.UIkit.sortable ? []:['assets/vendor/uikit/js/addons/sortable.min.js'], function(){

                    $container.on("sortable-change",function(){

                        var imgs = [], img;

                        $container.children().each(function(){
                            img = $(this);
                            imgs.push({'path':img.data("path"),'title':img.data("title")});
                        });

                        media = imgs;

                        updateSope();

                    });

                    $.UIkit.sortable($container);

                });

                ngModel.$render = function() {

                    if(!media) {
                        media = ngModel.$viewValue || [];

                        // try to port legacy data, will be removed someday
                        if (media[0] && typeof(media[0])=='string') {

                            media.forEach(function(path, i){
                                media[i] = {'path':path,'title':''};
                            });

                            updateSope();
                        }
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
                        media.forEach(function(item, index){

                            if(item.path.match(/\.(jpg|jpeg|png|gif|svg)$/i)) {
                                mediatpl = '<img src="'+App.route("/mediamanager/thumbnail/"+btoa(item.path))+'/150/150">';
                            }

                            if(item.path.match(/\.(mp4|mpeg|ogv|webm|wmv)$/i)) {
                                mediatpl = '<video src="'+item.path.replace("site:", COCKPIT_SITE_BASE_URL)+'" style="max-width:100%;height:auto;"></video>';
                            }

                            var li = $([
                                '<li class="uk-grid-margin" data-path="'+item.path+'" data-title="'+item.title+'" draggable="true" title="'+(item.title || item.path)+'" stype="position:relative;">',
                                    '<div class="uk-thumbnail" style="min-height:180px;">'+mediatpl+'</div>',
                                    '<div class="gallery-list-actions uk-button-group">',
                                        '<button class="uk-button uk-button-small js-title" type="button"><i class="uk-icon-pencil"></i></button>',
                                        '<button class="uk-button uk-button-small uk-button-danger js-remove" type="button"><i class="uk-icon-trash-o"></i></button></div>',
                                    '</div>',
                                '</li>'
                            ].join('')).data('item', item);

                            $container.append(li);
                        });
                    } else {
                        $container.append('<div class="uk-width-1-1 uk-grid-margin"><p class="uk-text-muted uk-text-small">'+App.i18n.get('No media files.')+'</p></div>');
                    }
                }

                elm.replaceWith($gal);
            }
        };

    }]);

})(jQuery);
