(function($){

    App.module.controller("gallery", function($scope, $rootScope, $http, $timeout, Contentfields){

        var id         = $("[data-ng-controller='gallery']").data("id"),
            dialog     = $.UIkit.modal("#meta-dialog"),
            site_base  = COCKPIT_SITE_BASE_URL.replace(/^\/+|\/+$/g, ""),
            media_base = COCKPIT_MEDIA_BASE_URL.replace(/^\/+|\/+$/g, ""),
            site2media = media_base.replace(site_base, "").replace(/^\/+|\/+$/g, "");

        $scope.groups        = [];
        $scope.metaimage     = {};
        $scope.contentfields = Contentfields.fields();

        if (id) {

            $http.post(App.route("/api/galleries/findOne"), {filter: {"_id":id}}, {responseType:"json"}).success(function(data){

                if (data && Object.keys(data).length) {
                    $scope.gallery = data;
                }

            }).error(App.module.callbacks.error.http);

        } else {

            $scope.gallery = {
                name: "",
                fields:[{"name":"caption","type":"html"}, {"name":"url","type":"text"}],
                images: [],
                group: ""
            };
        }

        // get groups
        $http.post(App.route("/api/galleries/getGroups"), {}).success(function(groups){

            $scope.groups = groups;

        }).error(App.module.callbacks.error.http);


        $scope.managefields = false;


        $scope.save = function() {

            var gallery = angular.copy($scope.gallery);

            gallery.images.forEach(function(image){
                gallery.fields.forEach(function(field){
                    if (!image.data[field.name]) image.data[field.name] = "";
                });
            });

            $http.post(App.route("/api/galleries/save"), {"gallery": gallery}).success(function(data){

                if (data && Object.keys(data).length) {
                    $scope.gallery = data;
                    App.notify(App.i18n.get("Gallery saved!"));
                }

            }).error(App.module.callbacks.error.http);
        };

        $scope.importFromFolder = function(){

            new CockpitPathPicker(function(path){

                if (String(path).match(/\.(jpg|png|gif)$/i)){
                    $scope.$apply(function(){
                        $scope.gallery.images.push({"path":path, data:{}});
                        App.notify(App.i18n.get("%s image(s) imported", 1));
                    });
                } else {

                    $.post(App.route('/mediamanager/api'), {"cmd":"ls", "path": String(path).replace("site:"+site2media, "")}, function(data){

                        var count = 0;

                        if (data && data.files && data.files.length) {

                            data.files.forEach(function(file) {

                                if (file.name.match(/\.(jpg|png|gif)$/i)) {
                                    var full_path = site2media ? site2media+'/'+file.path : file.path;
                                    $scope.gallery.images.push({"path":"site:"+full_path, data:{}});

                                    count = count + 1;
                                }
                            });

                            $scope.$apply();

                        }

                        App.notify(App.i18n.get("%s image(s) imported", count));

                    }, "json");
                }

            }, "*");
        };

        $scope.selectImage = function(){

            new CockpitPathPicker(function(path){
                $scope.$apply(function(){
                    $scope.gallery.images.push({"path":path, data:{}});
                    App.notify(App.i18n.get("%s image(s) imported", 1));
                });
            }, "*.(jpg|png|gif)");
        };

        $scope.removeImage = function(index) {

            App.Ui.confirm(App.i18n.get("Are you sure?"), function(){
                $timeout(function(){
                    $scope.gallery.images.splice(index, 1);
                },0);
            });
        };

        $scope.imgurl = function(image) {
            return encodeURI(image.path.replace('site:', window.COCKPIT_SITE_BASE_URL));
        };

        $scope.showMeta = function(index){
            $scope.metaimage = $scope.gallery.images[index];
            dialog.show();
        };

        $scope.addfield = function(){

            if (!$scope.gallery.fields) {
                $scope.gallery.fields = [];
            }

            $scope.gallery.fields.push({
                "name"  : "",
                "type"  : "text",
                "value" : ""
            });
        };

        $scope.removefield = function(field) {

            var index = $scope.gallery.fields.indexOf(field);

            if (index > -1) {
                $scope.gallery.fields.splice(index, 1);
            }

        };

        $scope.switchFieldsForm = function(refresh) {

            if (refresh) {
                $scope.gallery.fields = angular.copy($scope.gallery.fields);
            }

            $scope.managefields = !$scope.managefields;
        };

        $scope.toggleOptions = function(index) {
            $("#options-field-"+index).toggleClass('uk-hidden');
        };

        var imglist = $("#images-list").on("sortable-change",function(){

            var images = [];

            imglist.children().each(function(){
                images.push(angular.copy($(this).scope().image));
            });

            $scope.$apply(function(){
                $scope.gallery.images = images;
            });
        });

        // after sorting list

        var list = $("#manage-fields-list").on("nestable-stop", function(){
            var fields = [];

            list.children('.ng-scope').each(function(){
                fields.push(angular.copy($(this).scope().field));
            });

            $scope.$apply(function(){
                $scope.gallery.fields = fields;
            });
        });

        // bind clobal command + save
        Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            } else {
                e.returnValue = false; // ie
            }
            $scope.save();
            return false;
        });

    });

})(jQuery);
