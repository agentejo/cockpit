(function($){

    App.module.controller("regions", function($scope, $rootScope, $http, $timeout){

        $scope.mode = App.storage.get("cockpit.view.listmode", 'list');

        $http.post(App.route("/api/regions/find"), {}).success(function(data){

            $scope.regions = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, region){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/regions/remove"), {

                    "region": angular.copy(region)

                }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.regions.splice(index, 1);
                        App.notify(App.i18n.get("Region removed"), "success");
                    }, 0);

                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.filter = "";

        $scope.matchName = function(name) {
            return (name && name.indexOf($scope.filter) !== -1);
        };

        $scope.setListMode = function(mode) {
            $scope.mode = mode;

            App.storage.set("cockpit.view.listmode", mode);
        };
    });

})(jQuery);