(function($){

    var Editor = {
        
        init: function($scope) {

            if (this.element) {
                return;
            }

            var $this = this;

            this.scope   = $scope;

            this.element = $("#mm-editor");
            this.toolbar = this.element.find("nav");
            this.code    = CodeMirror.fromTextArea(this.element.find("textarea")[0], {
                               lineNumbers: true,
                               styleActiveLine: true,
                               matchBrackets: true,
                               theme: 'pastel-on-dark'
                           });

            this.filename = this.element.find(".filename");

            this.resize();

            $(window).on("resize", $.UIkit.Utils.debounce(function(){
                $this.resize();
            }, 150));


            this.element.on("click", "[data-editor-action]", function(){

                switch($(this).data("editorAction")) {
                    case "close":
                        $this.close();
                        break;
                    case "save":
                        $this.save();
                        break;
                }
            });

            // key mappings

            this.code.addKeyMap({
                'Ctrl-S': function(){ Editor.save(); }, 
                'Cmd-S': function(){ Editor.save(); },
                'Esc': function(){ Editor.close(); }
            });
        },

        resize: function(){

            if(!this.element.is(":visible")) {
                return;
            }

            var wrap = this.code.getWrapperElement();

            wrap.style.height = (this.element.height() - this.toolbar.height())+"px";
            this.code.refresh();
        },

        save: function(){
            
            if(!this.file) {
                return;
            }

            if(!this.file.is_writable) {
                App.notify(App.i18n.get("This file is not writable!"), "danger");
                return;
            }

            this.scope.saveFile(this.file, this.code.getValue());
        },

        show: function(file, content){
            
            var ext  = file.name.split('.').pop().toLowerCase(),
                mode = "text";

            this.code.setOption("mode", "text");

            switch(ext) {
                case 'css':
                case 'less':
                case 'sql':
                case 'xml':
                case 'markdown':
                    mode = ext;
                    break;
                case 'js':
                case 'json':
                    mode = 'javascript';
                    break;
                case 'md':
                    mode = 'markdown';
                    break;
                case 'php':
                    mode = 'php';
                    break;
            }

            // autoload modes
            if(mode!='text') {
                App.assets.require(['/assets/vendor/codemirror/mode/%N/%N.js'.replace(/%N/g, mode)], function(){
                    
                    switch(mode) {
                        case "php":
                            Editor.code.setOption("mode", "application/x-httpd-php");
                            break;
                        default:
                          Editor.code.setOption("mode", mode);  
                    }
                });
            }

            this.filename.text(file.name);

            this.code.setValue(content);

            this.element.show();
            this.resize();

            setTimeout(function(){
                Editor.code.focus();
            }, 50);

            this.file = file;
        },

        close: function(){
            this.file = null;
            this.element.hide();
        }
    };



    App.module.controller("mediamanager", function($scope, $rootScope, $http){

            var currentpath = location.hash ? location.hash.replace("#", ''):"/",
                apiurl      = App.route('/mediamanager/api'),

                imgpreview  = new $.UIkit.modal.Modal("#mm-image-preview");

            $scope.dir;
            $scope.breadcrumbs = [];
            $scope.bookmarks   = {"folders":[], "files":[]};

            $scope.viewfilter = 'all';
            $scope.namefilter = '';

            $scope.mode       = 'table';

            $scope.updatepath = function(path) {
                loadPath(path);
            };

            $scope.action = function(cmd, item) {

                switch(cmd) {

                    case "remove":

                        if(confirm(App.i18n.get("Are you sure?"))) {

                            requestapi({"cmd":"removefiles", "paths": [item.path]});

                            var index = $scope.dir[item.is_file ? "files":"folders"].indexOf(item);
                            $scope.dir[item.is_file ? "files":"folders"].splice(index, 1);

                            App.notify("File(s) deleted", "success");
                        }
                        break;

                    case "rename":

                        var name = prompt(App.i18n.get("Please enter new name:"), item.name);

                        if(name!=item.name && $.trim(name)) {
                            requestapi({"cmd":"rename", "path": item.path, "name":name});
                            item.path = item.path.replace(item.name, name);
                            item.name = name;
                        }

                        break;

                    case "createfolder":

                        var name = prompt(App.i18n.get("Please enter a name:"), "");

                        if($.trim(name)) {
                            requestapi({"cmd":"createfolder", "path": currentpath, "name":name}, function(){
                                loadPath(currentpath);
                            });
                        }

                        break;

                    case "createfile":

                        var name = prompt("Please enter a filename:", "");

                        if($.trim(name)) {
                            requestapi({"cmd":"createfile", "path": currentpath, "name":name}, function(){
                                loadPath(currentpath);
                            });
                        }

                        break;

                    case "download":
                        location.href = apiurl+"?cmd=download&path="+encodeURI(item.path);
                        break;
                }

            };

            $scope.open = function(file) {

                var media = false;

                if(file.name.match(/\.(jpg|jpeg|png|gif)$/i)) {
                    media = "image";
                }

                if(file.name.match(/\.(txt|md|php|js|css|scss|sass|less|htm|html|json|xml|svg)$/i)) {
                    media = "text";
                }

                switch(media){
                    case "image":
                        imgpreview.element.find('.modal-content').html('<img src="'+file.url+'" style="max-width:100%;height:auto;">');
                        imgpreview.show();
                        break;
                    case "text":
                        
                        requestapi({"cmd":"readfile", "path": file.path}, function(content){
                            Editor.show(file, content);
                        }, "text");

                        
                        break;
                    default:
                        App.notify("Sorry, this file type is not supported.");
                }
            };


            $scope.saveFile = function(file, content) {

                requestapi({"cmd":"writefile", "path": file.path, "content":content}, function(status){
                    App.notify("File saved", "success");
                }, "text");
            };

            $scope.matchName = function(name) {

                if(!name || !$scope.namefilter) {
                    return true;
                }

                return (name.toLowerCase().indexOf($scope.namefilter.toLowerCase()) !== -1);
            };

            $scope.addBookmark = function(item) {
                
                var bookmark = {"name": item.name, "path": item.path},
                    cat      = item.is_dir ? "folders":"files";

                for(var i=0;i<$scope.bookmarks[cat].length;i++) {

                    if($scope.bookmarks[cat][i].path == bookmark.path) {
                        App.notify(App.i18n.get("%s is already bookmarked.", item.name));
                        return;
                    }
                }

                $scope.bookmarks[cat].push(bookmark);

                $http.post(App.route("/mediamanager/savebookmarks"), {"bookmarks": angular.copy($scope.bookmarks)}).success(function(data){
                    App.notify(App.i18n.get("%s bookmarked.", item.name), "success");
                }).error(App.module.callbacks.error.http);
            }

            function requestapi(data, fn, type) {

                data = $.extend({"cmd":""}, data);

                $.post(apiurl, data, fn, type || "json");
            }

            function loadPath(path) {

                requestapi({"cmd":"ls", "path": path}, function(data){

                    currentpath = path;

                    $scope.breadcrumbs = [];

                    if(currentpath!='/'){
                        var parts   = currentpath.split('/'),
                            tmppath = [],
                            crumbs  = [];

                        for(var i=0;i<parts.length;i++){
                            tmppath.push(parts[i]);
                            crumbs.push({'name':parts[i],'path':tmppath.join("/")});
                        }

                        $scope.breadcrumbs = crumbs;
                    }

                    $scope.$apply(function(){
                        $scope.dir = data;
                    });

                }, "json");
            }

            // get bookmarks
            $http.post(App.route("/mediamanager/loadbookmarks"), {}).success(function(data){
                $scope.bookmarks = data;
            }).error(App.module.callbacks.error.http);

            loadPath(currentpath);

            var progessbar     = $('body').loadie(),
                uploadsettings = {
                    "action": apiurl,
                    "single": true,
                    "params": {"cmd":"upload"},
                    "before": function(o) {
                        o.params["path"] = currentpath;
                    },
                    "loadstart": function(){

                    },
                    "progress": function(percent){
                        progessbar.loadie(percent/100);
                    },
                    "allcomplete": function(){
                        loadPath(currentpath);
                    },
                    "complete": function(res){

                    }
                };

            $("body").uploadOnDrag(uploadsettings);
            $("#frmMediaUpload").ajaxform(uploadsettings);

            $("#mmbookmarks").on("dragend", "a[draggable]", function(e){
                e.stopPropagation();
                e.preventDefault();

                var ele = $(this),
                    cat = ele.data("group"),
                    idx = ele.data("idx");

                if(!confirm(App.i18n.get("Do you really want to remove %s ?", $scope.bookmarks[cat][idx].name))) {
                    return;
                }

                $scope.$apply(function(){
                    $scope.bookmarks[cat].splice(idx, 1);
                    $http.post(App.route("/mediamanager/savebookmarks"), {"bookmarks": angular.copy($scope.bookmarks)}).success(function(data){
                        //App.notify("Bookmarks updated.", "success");
                    }).error(App.module.callbacks.error.http);
                });
            });

            Editor.init($scope);

    });

})(jQuery);