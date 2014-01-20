(function($){

    App.module.controller("gallery", function($scope, $rootScope, $http){

        var id = $("[data-ng-controller='gallery']").data("id");


        if(id) {

            $http.post(App.route("/api/galleries/findOne"), {filter: {"_id":id}}, {responseType:"json"}).success(function(data){

                if(data && Object.keys(data).length) {
                    $scope.gallery = data;
                }

            }).error(App.module.callbacks.error.http);

        } else {

            $scope.gallery = {
                name: "",
                images: []
            };
        }

        $scope.save = function() {

            var gallery = angular.copy($scope.gallery);

            $http.post(App.route("/api/galleries/save"), {"gallery": gallery}).success(function(data){

                if(data && Object.keys(data).length) {
                    $scope.gallery = data;
                    App.notify(App.i18n.get("Gallery saved!"));
                }

            }).error(App.module.callbacks.error.http);
        };
    });

})(jQuery);