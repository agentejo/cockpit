(function(module, $){

    var site_base  = COCKPIT_SITE_BASE_URL.replace(/^\/+|\/+$/g, ""),
        media_base = COCKPIT_MEDIA_BASE_URL.replace(/^\/+|\/+$/g, ""),
        site2media = media_base.replace(site_base, "").replace(/^\/+|\/+$/g, "");


    var Picker = function(onselect, type) {

        var $this = this;

        var modal = $([
            '<div class="uk-modal media-path-picker">',
                '<div class="uk-modal-dialog uk-modal-dialog-large">',
                    '<button type="button" class="uk-modal-close uk-close"></button>',
                    '<h4>Mediapicker</h4>',
                    '<div class="uk-clearfix">',
                    '<div class="caption uk-float-left">&nbsp;</div>',
                    '<div class="uk-float-right">',
                        '<button class="uk-button uk-form-file" data-uk-tooltip title="'+App.i18n.get('Upload files')+'">',
                            '<input class="js-upload-select" type="file" multiple="true" title="">',
                            '<i class="uk-icon-plus"></i>',
                        '</button>',
                    '</div>',
                    '</div>',
                    '<div class="uk-overflow-container uk-margin-top">',
                        '<ul class="dir-view uk-grid uk-grid-width-1-5 uk-grid-small uk-clearfix"></ul>',
                    '</div>',
                    '<div class="uk-modal-buttons"><button class="media-select uk-button uk-button-large uk-button-primary" type="button">Select</button> <button class="uk-button uk-button-large uk-modal-close" type="button">Cancel</button></div>',
                '</div>',
            '</div>'
        ].join('')).appendTo('body');


        App.assets.require($.UIkit.Utils.xhrupload ? [] : ['assets/vendor/uikit/js/addons/upload.min.js'], function(){

            var uploadsettings = {
                    "action": App.route('/mediamanager/api'),
                    "single": true,
                    "params": {"cmd":"upload"},
                    "before": function(o) {
                        o.params["path"] = $this.currentpath;
                    },
                    "loadstart": function(){

                    },
                    "progress": function(percent){
                        $this.caption.html('<span>'+Math.ceil(percent)+"%</span>");
                    },
                    "allcomplete": function(){
                        $this.loadPath($this.currentpath);
                    }
                };

            var uploadselect = new $.UIkit.upload.select(modal.find('input.js-upload-select'), uploadsettings);

            modal.on("drop", function(e){

                if (e.dataTransfer && e.dataTransfer.files) {

                    e.stopPropagation();
                    e.preventDefault();

                    $.UIkit.Utils.xhrupload(e.dataTransfer.files, uploadsettings);
                }

            }).on("dragenter", function(e){
                    e.stopPropagation();
                    e.preventDefault();
            }).on("dragover", function(e){
                    e.stopPropagation();
                    e.preventDefault();
            }).on("dragleave", function(e){
                    e.stopPropagation();
                    e.preventDefault();
            });
        });


        var picker = new $.UIkit.modal.Modal(modal);


        this.type    = type || '*';
        this.modal   = modal;
        this.dirview = modal.find('.dir-view');
        this.caption = modal.find('.caption');
        this.btnOk   = modal.find('button.media-select').attr("disabled", true);

        picker.show();

        this.dirview.on("click", "li", function(){
            var data = $(this).data();

            $this.dirview.children().removeClass("active").filter(this).addClass("active");

            $this.mediapath = 'site:'+[site2media, data.path].join('/').replace(/^\/+|\/+$/g, "");

            $this.btnOk.prop("disabled", !matchName($this.type, data.name));

        });

        this.dirview.on("dblclick", "li", function(){
            var data = $(this).blur().data();

            if (data.is_dir) {
                $this.loadPath(data.path);
            }

        });

        $this.caption.on("click", "[data-path]", function(){
            $this.loadPath($(this).data("path"));
        });

        this.btnOk.on("click", function(){
            if($this.mediapath) onselect($this.mediapath);
            picker.hide();
        });

        this.loadPath('/');


    };

    $.extend(Picker.prototype, {

        mediapath: false,

        loadPath: function(path) {

            var $this = this;

            App.request("/mediamanager/api", {"cmd":"ls", "path": path}, function(data){

                $this.currentpath = path;

                if(data) {

                    $this.dirview.html('');

                    $.each(data.folders, function(index, folder){
                       $this.dirview.append($('<li class="uk-grid-margin"><div class="app-panel"><i class="uk-icon-folder"></i><div class="uk-margin-small-top uk-text-truncate">'+folder.name+'</div></div></li>').data(folder));
                    });

                    $.each(data.files, function(index, file){
                       $this.dirview.append($('<li class="uk-grid-margin"><div class="app-panel"><i class="uk-icon-file-o" data-file="'+file.url+'"></i><div class="uk-margin-small-top uk-text-truncate">'+file.name+'</div></div></li>').data(file));
                    });

                    $this.caption.html('');

                    var parts   = path.split('/'),
                        tmppath = [];

                    $this.caption.append('<span data-path="/"><i class="uk-icon-home"></i> <strong>media:</strong></span>');

                    for(var i=0;i<parts.length;i++){

                        if(!parts[i]) continue;

                        tmppath.push(parts[i]);

                        if(i<parts.length-1) {
                            $this.caption.append('<span data-path="'+tmppath.join("/")+'"><i class="uk-icon-folder-o"></i> '+parts[i]+'</span>');
                        } else {
                            $this.caption.append('<span class="uk-text-muted"><i class="uk-icon-folder-o"></i> '+parts[i]+'</span>');
                        }
                    }

                }

                $this.mediapath = false;
                $this.btnOk.attr("disabled", true);

                setTimeout(function(){
                    $this.dirview.find('[data-file]').each(function(){
                        var element = $(this);

                        if(element.data("file").match(/\.(jpg|jpeg|png|gif|svg)$/i)) {
                            var $r = $('<div class="media-url-preview" style="background-image:url('+element.data("file")+');margin:0 auto;"></div>');

                            element.replaceWith($r.css({width:element.width(),height:element.height()}));
                        }
                    })
                }, 0);

            }, "json");
        }
    });

    window.PathPicker = Picker;

    App.module.directive("mediaPathPicker", function($timeout){

        return {
            require: '?ngModel',
            restrict: 'A',

            compile: function(element, attrs) {

                $(element).hide();

                return function link(scope, elm, attrs, ngModel) {

                    $element = $(elm);

                    var $tpl   = $('<div><div class="uk-margin" data-preview=""></div><button class="uk-button uk-button-small app-button-secondary js-select" type="button"><i class="uk-icon-code-fork"></i> Pick Media path</button> <button class="uk-button uk-button-small app-button-secondary uk-hidden js-clear" type="button"><i class="uk-icon-trash-o"></i></button></div>'),
                        $btn   = $tpl.find('.js-select'),
                        $prv   = $tpl.find('[data-preview]'),
                        $clear = $tpl.find('.js-clear');

                    $element.after($tpl);

                    function setPath(path) {

                        if(!path) {
                           return $prv.html('<span class="uk-text-muted uk-text-small"><i class="uk-icon-info-circle"></i> '+App.i18n.get('Nothing selected')+'</span>');
                        }

                        if(path && path.match(/\.(jpg|jpeg|png|gif)$/i)) {
                            $prv.html('<div class="uk-margin" title="'+path+'"><img class="auto-size" src="'+path.replace('site:', window.COCKPIT_SITE_BASE_URL)+'"></div>');

                        } else if(path && path.match(/\.(mp4|ogv|wmv|webm|mpeg|avi)$/i)) {
                            $prv.html('<div class="uk-margin" title="'+path+'"><video class="auto-size" src="'+path.replace('site:', window.COCKPIT_SITE_BASE_URL)+'"></video></div>');

                        } else {
                            $prv.html(path ? '<div class="uk-trunkate" title="'+path+'">'+path+'</div>':'<div class="uk-alert">No path selected</div>');
                        }
                    }

                    $btn.on("click", function(){

                        new Picker(function(path){

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

                            ngModel.$render = function () {
                                setPath(ngModel.$viewValue);

                                if (ngModel.$viewValue) {
                                    $clear.removeClass('uk-hidden');
                                }
                            };

                            ngModel.$render();
                        }, 0);
                    }

                };
            }
        };

    });


    if(window.tinymce) {
        tinymce.PluginManager.add('mediapath', function(editor) {

            var picker = function(){
                new Picker(function(path){

                    var content = '';

                    if(!path) {
                       return;
                    }

                    if(path && path.match(/\.(jpg|jpeg|png|gif)/i)) {
                        content = '<img class="auto-size" src="'+path.replace('site:', window.COCKPIT_SITE_BASE_URL)+'">';

                    } else if(path && path.match(/\.(mp4|ogv|wmv|webm|mpeg|avi)$/i)) {
                        content = '<video class="auto-size" src="'+path.replace('site:', window.COCKPIT_SITE_BASE_URL)+'"></video>';

                    } else {
                        content = path;
                    }
                    editor.insertContent(content);
                }, "*");
            };

            editor.addMenuItem('mediapath', {
                icon: false,
                text: 'Insert media',
                onclick: picker,
                context: 'insert',
                prependToContext: true
            });

        });
    }

    function matchName(pattern, path) {

        var parsedPattern = '^' + pattern.replace(/\//g, '\\/').
            replace(/\*\*/g, '(\\/[^\\/]+)*').
            replace(/\*/g, '[^\\/]+').
            replace(/((?!\\))\?/g, '$1.') + '$';

        parsedPattern = '^' + parsedPattern + '$';

        return (path.match(new RegExp(parsedPattern)) !== null);
    }

})(App.module, jQuery);