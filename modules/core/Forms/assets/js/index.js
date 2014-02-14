(function($){

    App.module.controller("forms", function($scope, $rootScope, $http, $timeout){

        $http.post(App.route("/api/forms/find"), {}).success(function(data){

            $scope.forms = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, form){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function(){

                $http.post(App.route("/api/forms/remove"), { "form": angular.copy(form) }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.forms.splice(index, 1);
                        App.notify(App.i18n.get("Form removed"), "success");
                    }, 0);
                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.filter = "";

        $scope.matchName = function(name) {
            return (name && name.indexOf($scope.filter) !== -1);
        };
    });

})(jQuery);