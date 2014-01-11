(function($){

    App.module.controller("forms", function($scope, $rootScope, $http){

        $http.post(App.route("/api/forms/find"), {}).success(function(data){

            $scope.forms = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, form){

            if(confirm(App.i18n.get("Are you sure?"))) {

                $http.post(App.route("/api/forms/remove"), {

                    "form": angular.copy(form)

                }, {responseType:"json"}).success(function(data){

                    $scope.forms.splice(index, 1);

                    App.notify(App.i18n.get("Form removed"), "success");

                }).error(App.module.callbacks.error.http);
            }
        };

        $scope.filter = "";

        $scope.matchName = function(name) {
            return (name && name.indexOf($scope.filter) !== -1);
        };
    });

})(jQuery);