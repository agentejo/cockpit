(function(module, $){

    if (window.CockpitPathPicker) {
        return;
    }


    var site_base  = COCKPIT_SITE_BASE_URL.replace(/^\/+|\/+$/g, ""),
        media_base = COCKPIT_MEDIA_BASE_URL.replace(/^\/+|\/+$/g, ""),
        site2media = media_base.replace(site_base, "").replace(/^\/+|\/+$/g, "");


    var Picker = function(onselect, type) {

        var $this = this;

        var modal = $([
            '<div class="uk-modal media-path-picker">',
                '<div class="uk-modal-dialog uk-modal-dialog-large">',
                    '<button type="button" class="uk-modal-close uk-close"></button>',
                    '<h4>'+App.i18n.get('Mediapicker')+'</h4>',
                    '<div class="uk-clearfix">',
                    '<div class="caption uk-float-left">&nbsp;</div>',
                    '<div class="uk-float-right">',
                        '<span class="uk-button uk-form-file uk-margin-small-right" data-uk-tooltip title="'+App.i18n.get('Upload files')+'">',
                            '<input class="js-upload-select" type="file" multiple="true" title="">',
                            '<i class="uk-icon-plus"></i>',
                        '</span>',
                        '<button type="button" class="uk-button js-refresh" data-uk-tooltip title="'+App.i18n.get('Refresh')+'"><i class="uk-icon-refresh"></i></button>',
                    '</div>',
                    '</div>',
                    '<div class="uk-overflow-container uk-margin-top">',
                        '<ul class="dir-view uk-grid uk-grid-width-1-5 uk-grid-small uk-clearfix"></ul>',
                    '</div>',
                    '<div class="uk-modal-buttons"><button class="media-select uk-button uk-button-large uk-button-primary" type="button">'+App.i18n.get('Select')+'</button> <button class="uk-button uk-button-large uk-modal-close" type="button">Cancel</button></div>',
                '</div>',
            '</div>'
        ].join('')).appendTo('body');


        App.assets.require(UIkit.Utils.xhrupload ? [] : ['assets/vendor/uikit/js/components/upload.min.js'], function(){

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
                    "allcomplete": function(response){

                        if (response && response.failed && response.failed.length) {
                            App.notify(App.i18n.get("%s File(s) failed to uploaded.", response.failed.length), "danger");
                        }

                        if (response && response.uploaded && response.uploaded.length) {
                            App.notify(App.i18n.get("%s File(s) uploaded.", response.uploaded.length), "success");
                        }

                        if (!response) {
                            App.module.callbacks.error.http();
                        }

                        $this.loadPath($this.currentpath);
                    }
                };

            var uploadselect = new UIkit.uploadSelect(modal.find('input.js-upload-select'), uploadsettings);

            modal.on("drop", function(e){

                if (e.dataTransfer && e.dataTransfer.files) {

                    e.stopPropagation();
                    e.preventDefault();

                    UIkit.Utils.xhrupload(e.dataTransfer.files, uploadsettings);
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

            modal.on('click', '.js-refresh', function() {
                $this.loadPath($this.currentpath);
            });
        });


        var picker = UIkit.modal(modal);


        this.type    = type || '*';
        this.modal   = modal;
        this.dirview = modal.find('.dir-view');
        this.caption = modal.find('.caption');
        this.btnOk   = modal.find('button.media-select').attr("disabled", true);

        picker.show();

        this.dirview.on("click", "li", function(){

            var data = $(this).data();

            $this.mediapath = 'site:'+[site2media, data.path].join('/').replace(/^\/+|\/+$/g, "");

            $this.dirview.children().removeClass("active").filter(this).addClass("active");
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
            if ($this.mediapath) onselect($this.mediapath);
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

                if (data) {

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

                        if (!parts[i]) continue;

                        tmppath.push(parts[i]);

                        if (i<parts.length-1) {
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
                        var element = $(this), url = element.data("file"), $r;

                        if (url.match(/\.(jpg|jpeg|png|gif|svg)$/i)) {
                            $r = $('<div class="media-url-preview" style="background-image:url('+encodeURI(element.data("file"))+');margin:0 auto;"></div>').css({width:element.width(), height:element.height()});
                        }

                        if (url.match(/\.(mp4|mpeg|ogv|webm|wmv)$/i)) {
                            $r = '<i class="uk-icon-file-video-o"></i>';
                        }

                        if (url.match(/\.(zip|rar|gz|7zip|bz2)$/i)) {
                            $r = '<i class="uk-icon-file-archive-o"></i>';
                        }

                        if (url.match(/\.(pdf)$/i)) {
                            $r = '<i class="uk-icon-file-pdf-o"></i>';
                        }

                        if (url.match(/\.(sqlite|db)$/i)) {
                            $r = '<i class="uk-icon-database"></i>';
                        }

                        if ($r) element.replaceWith($r);
                    })
                }, 0);

            }, "json");
        }
    });


    function matchName(pattern, path) {

        var parsedPattern = '^' + pattern.replace(/\//g, '\\/').
            replace(/\*\*/g, '(\\/[^\\/]+)*').
            replace(/\*/g, '[^\\/]+').
            replace(/((?!\\))\?/g, '$1.') + '$';

        parsedPattern = '^' + parsedPattern + '$';

        return (path.match(new RegExp(parsedPattern)) !== null);
    }

    window.CockpitPathPicker = Picker;

})(App.module, jQuery);
