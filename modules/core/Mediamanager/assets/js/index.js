(function($){


    App.module.controller("mediamanager", function($scope, $rootScope, $http, $timeout){

            var container   = $('[data-ng-controller="mediamanager"]'),
                currentpath = location.hash ? location.hash.replace("#", ''):"/",
                apiurl      = App.route('/mediamanager/api'),

                imgpreview  = UIkit.modal("#mm-image-preview"),
                dirlst      = [];

            $scope.dir;
            $scope.breadcrumbs = [];
            $scope.bookmarks   = {"folders":[], "files":[]};

            $scope.viewfilter = 'all';
            $scope.namefilter = '';

            $scope.mode       = 'table';
            $scope.dirlist    = false;
            $scope.selected   = {};

            $scope.updatepath = function(path) {
                loadPath(path);
            };

            $scope.action = function(cmd, item) {

                switch(cmd) {

                    case "remove":

                        App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                            $timeout(function(){
                                requestapi({"cmd":"removefiles", "paths": item.path});

                                var index = $scope.dir[item.is_file ? "files":"folders"].indexOf(item);
                                $scope.dir[item.is_file ? "files":"folders"].splice(index, 1);

                                App.notify("File(s) deleted", "success");
                            }, 0);
                        });
                        break;

                    case "rename":

                        var name = prompt(App.i18n.get("Please enter new name:"), item.name);

                        if (name!=item.name && $.trim(name)) {
                            requestapi({"cmd":"rename", "path": item.path, "name":name});
                            item.path = item.path.replace(item.name, name);
                            item.name = name;
                        }

                        break;

                    case "createfolder":

                        var name = prompt(App.i18n.get("Please enter a name:"), "");

                        if ($.trim(name)) {
                            requestapi({"cmd":"createfolder", "path": currentpath, "name":name}, function(){
                                loadPath(currentpath);
                            });
                        }

                        break;

                    case "createfile":

                        var name = prompt(App.i18n.get('Please enter a filename:'), "");

                        if ($.trim(name)) {
                            requestapi({"cmd":"createfile", "path": currentpath, "name":name}, function(){
                                loadPath(currentpath);
                            });
                        }

                        break;

                    case "download":
                        location.href = apiurl+"?cmd=download&path="+encodeURI(item.path);
                        break;

                    case "unzip":

                        requestapi({"cmd": "unzip", "path": currentpath, "zip": item.path}, function(resp){

                            if (resp) {

                                if (resp.success) {
                                    App.notify("Archive extracted!", "success");
                                } else {
                                    App.notify("Extracting archive failed!", "error");
                                }
                            }

                            loadPath(currentpath);

                        });

                        break;
                }

            };

            $scope.open = function(file) {

                var media = false;

                if (file.name.match(/\.(jpg|jpeg|png|gif)$/i)) {
                    media = "image";
                }

                if (file.name.match(/\.(htaccess|txt|md|php|js|css|scss|sass|less|htm|html|json|xml|svg)$/i)) {
                    media = "text";
                }

                switch(media){
                    case "image":
                        imgpreview.element.find('.modal-content').html('<img src="'+file.url+'" style="max-width:100%;height:auto;">');
                        imgpreview.show();
                        break;
                    case "text":

                        requestapi({"cmd":"readfile", "path": file.path}, function(content){
                            MMEditor.show(file, content);
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

                if (!name || !$scope.namefilter) {
                    return true;
                }

                return (name.toLowerCase().indexOf($scope.namefilter.toLowerCase()) !== -1);
            };

            $scope.addBookmark = function(item) {

                var bookmark = {"name": item.name, "path": item.path},
                    cat      = item.is_dir ? "folders":"files";

                for(var i=0;i<$scope.bookmarks[cat].length;i++) {

                    if ($scope.bookmarks[cat][i].path == bookmark.path) {
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
                    $scope.selected    = {};
                    $scope.selectAll   = false;

                    if (currentpath && currentpath != '/' && currentpath != '.'){
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


            // fuzzy dirsearch
            // - load async dirlist
            setTimeout(function(){
                requestapi({"cmd":"getfilelist"}, function(list){

                    $timeout(function(){

                        dirlst = list || [];
                        $scope.dirlist = true;


                        var dirsearch = UIkit.autocomplete('#dirsearch', {
                            source: function(release) {
                                var data = FuzzySearch.filter(dirlst, dirsearch.input.val(), {key:'path', maxResults: 10});

                                release(data);
                            },
                            renderer: function(data) {

                                if (data && data.length) {

                                    var lst      = $('<ul class="uk-nav uk-nav-autocomplete uk-autocomplete-results">'),
                                        fileicon = '<i class="uk-icon-file uk-text-small"></i>',
                                        diricon  = '<i class="uk-icon-folder uk-text-small"></i>',
                                        li;

                                    data.forEach(function(item){
                                        li = $('<li><a><strong>'+(item.is_dir ? diricon:fileicon)+' &nbsp;'+item.name+'</strong><div class="uk-text-truncate">'+item.path+'</div></a></li>').data(item);
                                        lst.append(li);
                                    });

                                    this.dropdown.append(lst);
                                    this.show();
                                }

                            }
                        });

                        dirsearch.element.on('select.uk.autocomplete', function(e, data){
                            loadPath(data.dir);
                            dirsearch.input.val('');
                            $scope.open(data);
                        });

                    }, 0);
                });
            }, 0);

            // upload

            var progessbar     = $('body').loadie(),
                uploadsettings = {

                    action: apiurl,
                    params: {"cmd":"upload"},
                    type: 'json',
                    before: function(options) {
                        options.params.path = currentpath;
                    },
                    loadstart: function() {

                    },
                    progress: function(percent) {
                        progessbar.loadie(Math.ceil(percent)/100);
                    },
                    allcomplete: function(response) {

                        if (response && response.failed && response.failed.length) {
                            App.notify(App.i18n.get("%s File(s) failed to uploaded.", response.failed.length), "danger");
                        }

                        if (response && response.uploaded && response.uploaded.length) {
                            App.notify(App.i18n.get("%s File(s) uploaded.", response.uploaded.length), "success");
                            loadPath(currentpath);
                        }

                        if (!response) {
                            App.module.callbacks.error.http();
                        }
                    }
            };

            var uploadselect = new UIkit.uploadSelect($('#js-upload-select'), uploadsettings);

            $("body").on("drop", function(e){

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

            // bookmarks

            $("#mmbookmarks").on("dragend", "a[draggable]", function(e){
                e.stopPropagation();
                e.preventDefault();

                var ele = $(this),
                    cat = ele.data("group"),
                    idx = ele.data("idx");

                App.Ui.confirm(App.i18n.get("Do you really want to remove %s ?", $scope.bookmarks[cat][idx].name), function() {

                    $timeout(function(){
                        $scope.bookmarks[cat].splice(idx, 1);
                        $http.post(App.route("/mediamanager/savebookmarks"), {"bookmarks": angular.copy($scope.bookmarks)}).success(function(data){
                            //App.notify("Bookmarks updated.", "success");
                        }).error(App.module.callbacks.error.http);
                    }, 0);
                });
            });

            // batch delete

            $scope.selectAll       = false;

            $scope.selectAllToggle = function() {
                $scope.dir.files.forEach(function(file){
                    $scope.selected[file.path] = $scope.selectAll;
                });
                $scope.dir.folders.forEach(function(folder){
                    $scope.selected[folder.path] = $scope.selectAll;
                });
            };

            $scope.deleteSelected  = function() {

                var paths = getSelectedPaths();

                if (paths.length) {

                    App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                        requestapi({"cmd":"removefiles", "paths": paths}, function(){

                            loadPath(currentpath);
                            App.notify("File(s) deleted", "success");
                        });
                    });
                }
            };

            $scope.hasSelected = function() {
                return getSelectedPaths().length;
            };

            function getSelectedPaths() {

                var paths = [];

                Object.keys($scope.selected).forEach(function(path){
                    if ($scope.selected[path]===true) {
                        paths.push(path);
                    }
                });

                return paths;
            }


            App.assets.require(['modules/core/Mediamanager/assets/Editor.js'], function(){
                MMEditor.init($scope);
            });

    });

})(jQuery);
