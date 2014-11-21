(function($){

    App.module.controller("region", function($scope, $rootScope, $http, $timeout, Contentfields){

        var id       = $("[data-ng-controller='region']").data("id"),
            template = $("#region-template"),
            locales  = LOCALES || [];

        $scope.mode       = "tpl";
        $scope.manageform = false;
        $scope.versions   = [];
        $scope.groups     = [];
        $scope.locale     = '';

        $scope.contentfields = Contentfields.fields();

        // get groups
        $http.post(App.route("/api/regions/getGroups"), {}).success(function(groups){

            $scope.groups = groups;

        }).error(App.module.callbacks.error.http);

        // get collections
        $scope.collections = [];

        $http.post(App.route("/api/collections/find"), {}).success(function(data){
            $scope.collections = data;
        });


        $scope.loadVersions = function() {

            if (!$scope.region._id) {
                return;
            }

            $http.post(App.route("/api/regions/getVersions"), {"id":$scope.region._id}).success(function(data){

                if (data) {
                    $scope.versions = data;
                }

            }).error(App.module.callbacks.error.http);
        };

        $scope.clearVersions = function() {

            if (!$scope.region._id) {
                return;
            }

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {
                $http.post(App.route("/api/regions/clearVersions"), {"id":$scope.region._id}).success(function(data){
                    $timeout(function(){
                        $scope.versions = [];
                        App.notify(App.i18n.get("Version history cleared!"), "success");
                    }, 0);
                }).error(App.module.callbacks.error.http);
            })
        };

        $scope.restoreVersion = function(versionId) {

            if (!versionId || !$scope.region._id) {
                return;
            }

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                var msg = UIkit.notify(['<i class="uk-icon-spinner uk-icon-spin"></i>', App.i18n.get("Restoring version...")].join(" "), {timeout:0});

                $http.post(App.route("/api/regions/restoreVersion"), {"docId":$scope.region._id, "versionId":versionId}).success(function(data){

                    setTimeout(function(){
                        msg.close();
                        location.href = App.route("/regions/region/"+$scope.region._id);
                    }, 1500);
                }).error(App.module.callbacks.error.http);
            });
        };


        if (id) {

            $http.post(App.route("/api/regions/findOne"), {filter: {"_id":id}}, {responseType:"json"}).success(function(data){

                if (data && Object.keys(data).length) {

                    $scope.region = data;

                    if ($scope.region.fields.length) {
                        $scope.mode = "form";
                    }

                    $scope.loadVersions();

                    checklocales();
                }

            }).error(App.module.callbacks.error.http);

        } else {

            $scope.region = {
                name: "",
                fields: [],
                tpl: "",
                group: ""
            };

            checklocales();
        }

        $scope.addfield = function(){

            if (!$scope.region.fields) {
                $scope.region.fields = [];
            }

            var field = {
                "name"  : "",
                "type"  : "text",
                "value" : ""
            };

            if (locales.length) {

                locales.forEach(function(locale){
                    if (!field['value_'+locale]) {
                        field['value_'+locale] = '';
                    }
                });
            }

            $scope.region.fields.push(field);
        };

        $scope.remove = function(field) {

            var index = $scope.region.fields.indexOf(field);

            if (index > -1) {
                $scope.region.fields.splice(index, 1);
            }

        };

        $scope.insertfield = function(fieldname) {
            template.data("codearea").replaceSelection('{{ $'+fieldname+' }}', 'end');
        };

        $scope.save = function() {

            var region = angular.copy($scope.region);

            $http.post(App.route("/api/regions/save"), {"region": region, "createversion": true}).success(function(data){

                if (data && Object.keys(data).length) {
                    $scope.region._id = data._id;
                    App.notify(App.i18n.get("Region saved!"), "success");

                    $scope.loadVersions();
                }

            }).error(App.module.callbacks.error.http);
        };

        $scope.getFieldname = function(field) {
            return $scope.locale && field.localize ? field.name + '_' + $scope.locale : field.name;
        };

        $scope.toggleOptions = function(index) {
            $("#options-field-"+index).toggleClass('uk-hidden');
        };

        $scope.switchFieldsForm = function(refresh) {

            if (refresh) {
                $scope.region.fields = angular.copy($scope.region.fields);
            }

            $scope.manageform = !$scope.manageform;
        };


        $scope.$watch('locale', function(newValue, oldValue){

            if (locales.length && $scope.region && newValue !== oldValue) {
                $scope.region = angular.copy($scope.region);
            }
        });


        $scope.$watch("mode", function(val){

            setTimeout(function(){
                refreshcodeareas();
            }, 50);
        });

        $scope.$watch("manageform", function(val){

            checklocales();

            setTimeout(function(){
                refreshcodeareas();
            }, 50);
        });

        // after sorting list
        $(function(){

            var list = $("#manage-fields-list").on("stop.uk.nestable", function(){
                var fields = [];

                list.children('.ng-scope').each(function(){
                    fields.push(angular.copy($(this).scope().field));
                });

                $scope.$apply(function(){
                    $scope.region.fields = fields;
                });
            });

        });

        function refreshcodeareas() {
            $("textarea[codearea]").each(function(){
                var data = $(this).data();
                if (data["codearea"]) data.codearea.refresh();
            });
        }

        function checklocales() {

            $scope.hasLocals = false;

            if ($scope.region && $scope.region.fields && $scope.region.fields.length) {

                $scope.region.fields.forEach(function(field){

                    // localize fields
                    if (locales.length && field["localize"]) {

                        $scope.hasLocals = true;
                    }
                });
            }
        }

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
