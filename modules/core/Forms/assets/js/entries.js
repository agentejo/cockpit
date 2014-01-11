(function($){

    App.module.controller("entries", function($scope, $rootScope, $http){

        $scope.form = $("[data-ng-controller='entries']").data("form");

        $http.post(App.route("/api/forms/entries"), {

            "form": angular.copy($scope.form),
            "sort": {"created":-1}

        }, {responseType:"json"}).success(function(data){

            if(data) $scope.entries = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, entryId){
            if(confirm(App.i18n.get("Are you sure?"))) {

                $http.post(App.route("/api/forms/removeentry"), {

                    "form": angular.copy($scope.form),
                    "entryId": entryId

                }, {responseType:"json"}).success(function(data){

                    $scope.entries.splice(index, 1);
                    $scope.form.count -= 1;

                    App.notify(App.i18n.get("Entry removed"), "success");

                }).error(App.module.callbacks.error.http);
            }
        };

    });

})(jQuery);