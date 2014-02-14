(function($){

    App.module.controller("entries", function($scope, $rootScope, $http, $timeout){

        $scope.collection = COLLECTION || {};
        $scope.fields = [];

        $scope.fields = (COLLECTION.fields.length ? COLLECTION.fields : [COLLECTION.fields]).filter(function(field){
            return field.lst;
        });

        $http.post(App.route("/api/collections/entries"), {

            "collection": angular.copy($scope.collection)

        }, {responseType:"json"}).success(function(data){

            if(data) $scope.entries = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, entryId){
            App.Ui.confirm(App.i18n.get("Are you sure?"), function(){

                $http.post(App.route("/api/collections/removeentry"), {

                    "collection": angular.copy($scope.collection),
                    "entryId": entryId

                }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.entries.splice(index, 1);
                        $scope.collection.count -= 1;

                        App.notify(App.i18n.get("Entry removed"), "success");
                    }, 0);
                }).error(App.module.callbacks.error.http);
            });
        };

    });

})(jQuery);