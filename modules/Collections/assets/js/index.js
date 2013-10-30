(function($){

    App.module.controller("collections", function($scope, $rootScope, $http){

        $http.post(App.route("/api/collections/find"), {extended:true}).success(function(data){

            $scope.collections = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, collection){
            if(confirm("Are you sure?")) {

                $http.post(App.route("/api/collections/remove"), {

                    "collection": angular.copy(collection)

                }, {responseType:"json"}).success(function(data){

                    $scope.collections.splice(index, 1);

                }).error(App.module.callbacks.error.http);
            }
        };

        $scope.filter = "";

        $scope.matchName = function(name) {

            return (name.indexOf($scope.filter) !== -1);
        };
    });

})(jQuery);