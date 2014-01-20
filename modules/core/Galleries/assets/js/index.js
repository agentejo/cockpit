(function($){

    App.module.controller("galleries", function($scope, $rootScope, $http){

        $http.post(App.route("/api/galleries/find"), {}).success(function(data){

            $scope.galleries = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, gallery){

            if(confirm(App.i18n.get("Are you sure?"))) {

                $http.post(App.route("/api/galleries/remove"), {

                    "gallery": angular.copy(gallery)

                }, {responseType:"json"}).success(function(data){

                    $scope.galleries.splice(index, 1);

                    App.notify(App.i18n.get("Gallery removed"), "success");

                }).error(App.module.callbacks.error.http);
            }
        };

        $scope.filter = "";

        $scope.matchName = function(name) {
            return (name && name.indexOf($scope.filter) !== -1);
        };
    });

})(jQuery);