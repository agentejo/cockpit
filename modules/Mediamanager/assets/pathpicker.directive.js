(function(app, $){

    var Picker = function(onselect, type) {

        var modal    = '<div class="media-path-picker" style="width:600px;"> \
                          <strong class="caption">&nbsp;</strong> \
                          <ul class="dir-view mt10 mb20 block-grid four-columns" style="height:250px;overflow-y:scroll;"></ul>     \
                          <div class="text-center"><button class="media-select button primary" type="button">Select</button></div> \
                        </div>',
            $this    = this;

        this.type    = type;
        this.modal   = $(modal);
        this.dirview = this.modal.find('.dir-view');
        this.caption = this.modal.find('.caption');
        this.btnOk   = this.modal.find('button.media-select').attr("disabled", true);

        $.baseui.modal(this.modal);

        this.dirview.on("click", "li", function(){
            var data = $(this).data();

            $this.dirview.children().removeClass("active").filter(this).addClass("active");

            $this.mediapath = 'media:'+data.path;

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
            $.baseui.modal.close();
        });

        this.loadPath('/');


        Cockpit.require(['/Cockpit/assets/vendor/ajaxupload.js'], function(){


            var uploadsettings = {
                    "action": Cockpit.baseRoute+"/mediamanager/api",
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

            Cockpit.post("/mediamanager/api", {"cmd":"ls", "path": path}, function(data){

                $this.currentpath = path;

                if(data) {

                    $this.dirview.html('');

                    $.each(data.folders, function(index, folder){
                       $this.dirview.append($('<li class="p10"><i class="icon-folder-close"></i><br>'+folder.name+'</li>').data(folder));
                    });

                    $.each(data.files, function(index, file){
                       $this.dirview.append($('<li class="p10"><i class="icon-file-alt"></i><br>'+file.name+'</li>').data(file));
                    });

                    $this.caption.html('');

                    var parts   = path.split('/'),
                        tmppath = [];

                    $this.caption.append('<span data-path="/">media:</span>');

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

    app.directive("mediaPathPicker", function(){

        return {
            require: '?ngModel',
            restrict: 'A',

            compile: function(element, attrs) {

                $(element).hide();

                return function link(scope, elm, attrs, ngModel) {

                    $element = $(elm);

                    var $tpl = $('<div><div class="mb10" data-preview=""></div><button class="button button-small dark" type="button"><i class="icon-code-fork"></i> Pick Media path</button></div>'),
                        $btn = $tpl.find('button'),
                        $prv = $tpl.find('[data-preview]');

                    $element.after($tpl);

                    function setPath(path) {
                        $prv.html(path ? '<span class="label label-info">'+path+'</span>':'No path selected');

                        if(path && path.match(/\.(jpg|jpeg|png|gif)/i)) {
                            $prv.append('<div class="mt10"><img class="auto-size" src="'+path.replace('media:', window.MEDIA_BASE_URL)+'"></div>');
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
        .media-path-picker .caption { display: block; text-align:center;} \
        .media-path-picker .dir-view { \
            background: -webkit-radial-gradient(50% 0, rgba(0,0,0,.2), transparent 70%) no-repeat; \
            background: radial-gradient(at top, rgba(0,0,0,.2), transparent 70%) no-repeat; \
            background-size: 100% 10px; \
            padding-top: 20px; \
            min-height: 40px; \
            -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; \
        } \
        .media-path-picker .dir-view li {cursor:pointer;text-align:center;border-radius:3px;} \
        .media-path-picker .dir-view li:hover {background: #fcfef0;} \
        .media-path-picker .dir-view li.active {color: #fff;background:#222;} \
        .media-path-picker .caption [data-path] {cursor:pointer;} \
        .media-path-picker .caption [data-path]:first-child { margin-right: 5px; vertical-align: top; } \
        .media-path-picker .caption [data-path]:not(:first-child):after { content: "/"; display: inline-block; margin: 0 5px; vertical-align: top; } \
        .media-path-picker .media-select[disabled] { opacity:0; } \
    </style>');

})(Cockpit.app, jQuery);