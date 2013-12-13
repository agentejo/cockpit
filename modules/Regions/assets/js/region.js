(function($){

    App.module.controller("region", function($scope, $rootScope, $http){

        var id = $("[data-ng-controller='region']").data("id");

        if(id) {

            $http.post(App.route("/api/regions/findOne"), {filter: {"_id":id}}, {responseType:"json"}).success(function(data){

                if(data && Object.keys(data).length) {
                    $scope.region = data;

                    if($scope.region.fields.length) $scope.mode = "form";
                }

            }).error(App.module.callbacks.error.http);

        } else {

            $scope.region = {
                name: "",
                fields: [],
                tpl: ""
            };
        }

        $scope.mode = "tpl";
        $scope.manageform = false;

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

        $scope.save = function() {

            var region = angular.copy($scope.region);

            $http.post(App.route("/api/regions/save"), {"region": region}).success(function(data){

                if(data && Object.keys(data).length) {
                    $scope.region = data;
                    App.notify("Region saved!", "success");
                }

            }).error(App.module.callbacks.error.http);
        };

    });

})(jQuery);