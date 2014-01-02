(function($){

    App.module.controller("entries", function($scope, $rootScope, $http){

        $scope.collection = $("[data-ng-controller='entries']").data("collection");

        $scope.fields     = $scope.collection.fields.filter(function(field){
            return field.lst;
        });

        $http.post(App.route("/api/collections/entries"), {

            "collection": angular.copy($scope.collection)

        }, {responseType:"json"}).success(function(data){

            if(data) $scope.entries = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, entryId){
            if(confirm(App.i18n.get("Are you sure?"))) {

                $http.post(App.route("/api/collections/removeentry"), {

                    "collection": angular.copy($scope.collection),
                    "entryId": entryId

                }, {responseType:"json"}).success(function(data){

                    $scope.entries.splice(index, 1);
                    $scope.collection.count -= 1;

                    App.notify(App.i18n.get("Entry removed"), "success");

                }).error(App.module.callbacks.error.http);
            }
        };

    });

})(jQuery);