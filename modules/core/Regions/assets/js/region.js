(function($){

    App.module.controller("region", function($scope, $rootScope, $http){

        var id       = $("[data-ng-controller='region']").data("id"),
            template = $("#region-template");

        $scope.mode       = "tpl";
        $scope.manageform = false;
        $scope.versions   = [];


        $scope.loadVersions = function() {

            if(!$scope.region["_id"]) {
                return;
            }

            $http.post(App.route("/api/regions/getVersions"), {"id":$scope.region["_id"]}).success(function(data){

                if(data) {
                    $scope.versions = data;
                }

            }).error(App.module.callbacks.error.http);
        };

        $scope.clearVersions = function() {

            if(!$scope.region["_id"]) {
                return;
            }

            if(confirm(App.i18n.get("Are you sure?"))) {
                $http.post(App.route("/api/regions/clearVersions"), {"id":$scope.region["_id"]}).success(function(data){
                    $scope.versions = [];
                }).error(App.module.callbacks.error.http);
            }
        };

        $scope.restoreVersion = function(versionId) {

            if(!versionId || !$scope.region["_id"]) {
                return;
            }

            var msg = $.UIkit.notify(['<i class="uk-icon-spinner uk-icon-spin"></i>', App.i18n.get("Restoring region...")].join(" "), {timeout:0});

            if(confirm(App.i18n.get("Are you sure?"))) {
                $http.post(App.route("/api/regions/restoreVersion"), {"docId":$scope.region["_id"], "versionId":versionId}).success(function(data){

                    setTimeout(function(){
                        msg.close();
                        location.href = App.route("/regions/region/"+$scope.region["_id"]);
                    }, 1500);
                }).error(App.module.callbacks.error.http);
            }
        };


        if(id) {

            $http.post(App.route("/api/regions/findOne"), {filter: {"_id":id}}, {responseType:"json"}).success(function(data){

                if(data && Object.keys(data).length) {

                    $scope.region = data;

                    if($scope.region.fields.length) {
                        $scope.mode = "form";
                    }

                    $scope.loadVersions();
                }

            }).error(App.module.callbacks.error.http);

        } else {

            $scope.region = {
                name: "",
                fields: [],
                tpl: ""
            };
        }

        $scope.addfield = function(){

            if(!$scope.region.fields) {
                $scope.region.fields = [];
            }

            $scope.region.fields.push({
                "name"  : "",
                "type"  : "text",
                "value" : ""
            });
        };

        $scope.remove = function(field) {

            var index = $scope.region.fields.indexOf(field);

            if(index > -1) {
                $scope.region.fields.splice(index, 1);
            }

        };

        $scope.insertfield = function(fieldname) {
            template.data("codearea").replaceSelection('{{ $'+fieldname+' }}', 'end');
        };

        $scope.save = function() {

            var region = angular.copy($scope.region);

            $http.post(App.route("/api/regions/save"), {"region": region, "createversion": true}).success(function(data){

                if(data && Object.keys(data).length) {
                    $scope.region = data;
                    App.notify(App.i18n.get("Region saved!"), "success");

                    $scope.loadVersions();
                }

            }).error(App.module.callbacks.error.http);
        };

        $scope.$watch("mode", function(val){

            setTimeout(function(){

                if(val=="tpl" && template.data("codearea")) {
                    template.data("codearea").refresh();
                }

            }, 100);
        });

    });

})(jQuery);