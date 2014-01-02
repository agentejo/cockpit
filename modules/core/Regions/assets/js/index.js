(function($){

    App.module.controller("regions", function($scope, $rootScope, $http){

        $http.post(App.route("/api/regions/find"), {}).success(function(data){

            $scope.regions = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, region){

            if(confirm(App.i18n.get("Are you sure?"))) {

                $http.post(App.route("/api/regions/remove"), {

                    "region": angular.copy(region)

                }, {responseType:"json"}).success(function(data){

                    $scope.regions.splice(index, 1);

                    App.notify(App.i18n.get("Region removed"), "success");

                }).error(App.module.callbacks.error.http);
            }
        };

        $scope.filter = "";

        $scope.matchName = function(name) {
            return (name && name.indexOf($scope.filter) !== -1);
        };
    });

})(jQuery);