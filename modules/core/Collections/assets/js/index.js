(function($){

    App.module.controller("collections", function($scope, $rootScope, $http, $timeout){

        $scope.mode = App.storage.get("cockpit.view.listmode", 'list');

        $http.post(App.route("/api/collections/find"), {extended:true}).success(function(data){

            $scope.collections = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, collection){
            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/collections/remove"), { "collection": angular.copy(collection) }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.collections.splice(index, 1);
                        App.notify(App.i18n.get("Collection removed"), "success");
                    }, 0);
                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.filter = "";

        $scope.matchName = function(name) {

            return (name.indexOf($scope.filter) !== -1);
        };

        $scope.setListMode = function(mode) {
            $scope.mode = mode;

            App.storage.set("cockpit.view.listmode", mode);
        };
    });

})(jQuery);