(function($){

    App.module.controller("galleries", function($scope, $rootScope, $http, $timeout){

        $scope.mode = App.storage.get("cockpit.view.listmode", 'list');

        $http.post(App.route("/api/galleries/find"), {}).success(function(data){

            $scope.galleries = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, gallery){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/galleries/remove"), { "gallery": angular.copy(gallery) }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.galleries.splice(index, 1);
                        App.notify(App.i18n.get("Gallery removed"), "success");
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