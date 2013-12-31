(function(module, $){

    var Picker = function(onselect, type) {

        var modal    = '<div class="media-path-picker" style="width:600px;"> \
                          <div class="caption">&nbsp;</div> \
                          <ul class="dir-view uk-list uk-clearfix" style="height:250px;overflow-y:scroll;"></ul>     \
                          <div class="uk-text-center"><button class="media-select uk-button uk-button-primary" type="button">Select</button></div> \
                        </div>',
            $this    = this;

        this.type    = type;
        this.modal   = $(modal);
        this.dirview = this.modal.find('.dir-view');
        this.caption = this.modal.find('.caption');
        this.btnOk   = this.modal.find('button.media-select').attr("disabled", true);

        $.UIkit.modalbox(this.modal);

        this.dirview.on("click", "li", function(){
            var data = $(this).data();

            $this.dirview.children().removeClass("active").filter(this).addClass("active");

            $this.mediapath = 'site:'+data.path;

            $this.btnOk.attr("disabled", !matchName($this.type, data.path));

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
            $.UIkit.modalbox.close();
        });

        this.loadPath('/');


        App.assets.require(['assets/vendor/ajaxupload.js'], function(){


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
                    },
                    "complete": function(res){

                    }
                };

            $this.modal.uploadOnDrag(uploadsettings);
        });


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
                       $this.dirview.append($('<li class="uk-width-1-4 uk-float-left"><i class="uk-icon-folder-close"></i><br>'+folder.name+'</li>').data(folder));
                    });

                    $.each(data.files, function(index, file){
                       $this.dirview.append($('<li class="uk-width-1-4 uk-float-left"><i class="uk-icon-file-alt"></i><br>'+file.name+'</li>').data(file));
                    });

                    $this.caption.html('');

                    var parts   = path.split('/'),
                        tmppath = [];

                    $this.caption.append('<span data-path="/"><strong>site:</strong></span>');

                    for(var i=0;i<parts.length;i++){

                        if(!parts[i]) continue;

                        tmppath.push(parts[i]);

                        if(i<parts.length-1) {
                            $this.caption.append('<span data-path="'+tmppath.join("/")+'">'+parts[i]+'</span>');
                        } else {
                            $this.caption.append('<span class="soft">'+parts[i]+'</span>');
                        }
                    }

                }

                $this.mediapath = false;
                $this.btnOk.attr("disabled", true);

            }, "json");
        }
    });

    App.module.directive("mediaPathPicker", function(){

        return {
            require: '?ngModel',
            restrict: 'A',

            compile: function(element, attrs) {

                $(element).hide();

                return function link(scope, elm, attrs, ngModel) {

                    $element = $(elm);

                    var $tpl = $('<div><div class="uk-margin" data-preview=""></div><button class="uk-button uk-button-small uk-button-primary" type="button"><i class="uk-icon-code-fork"></i> Pick Media path</button></div>'),
                        $btn = $tpl.find('button'),
                        $prv = $tpl.find('[data-preview]');

                    $element.after($tpl);

                    function setPath(path) {
                        $prv.html(path ? '<div class="uk-trunkate" title="'+path+'">'+path+'</div>':'<div class="uk-alert">No path selected</div>');

                        if(path && path.match(/\.(jpg|jpeg|png|gif)/i)) {
                            //$prv.append('<div class="uk-margin"><img class="auto-size" src="'+path.replace('media:', window.MEDIA_BASE_URL)+'"></div>');
                        }
                    }

                    $btn.on("click", function(){

                        new Picker(function(path){

                            setPath(path);

                            if (angular.isDefined(ngModel)) {
                                ngModel.$setViewValue(path);
                                if (!scope.$$phase) scope.$apply();
                            }
                        }, $element.attr("media-path-picker") || "*");

                    });

                    if (angular.isDefined(ngModel)) {
                        ngModel.$render = function () {
                            setPath(ngModel.$viewValue);
                        };
                    }

                };
            }
        };

    });

    function matchName(pattern, path) {

        var parsedPattern = '^' + pattern.replace(/\//g, '\\/').
            replace(/\*\*/g, '(\\/[^\\/]+)*').
            replace(/\*/g, '[^\\/]+').
            replace(/((?!\\))\?/g, '$1.') + '$';

        parsedPattern = '^' + parsedPattern + '$';

        return (path.match(new RegExp(parsedPattern)) !== null);
    }

    $("head").append('<style> \
        .media-path-picker .dir-view { \
            padding-top: 20px; \
            min-height: 40px; \
        } \
        .media-path-picker .dir-view li {cursor:pointer;text-align:center;border-radius:3px;padding:10px;-moz-user-select:none;-webkit-user-select:none;user-select:none;} \
        .media-path-picker .dir-view li:hover {background: #fcfef0;} \
        .media-path-picker .dir-view li.active {color: #fff;background:#222;} \
        .media-path-picker .caption [data-path] {cursor:pointer;} \
        .media-path-picker .caption [data-path]:first-child { margin-right: 5px; vertical-align: top; } \
        .media-path-picker .caption [data-path]:not(:first-child):after { content: "/"; display: inline-block; margin: 0 5px; vertical-align: top; } \
        .media-path-picker .media-select[disabled] { opacity:0; } \
    </style>');

})(App.module, jQuery);