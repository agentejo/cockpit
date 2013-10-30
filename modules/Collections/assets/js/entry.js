(function($){

    App.module.controller("entry", function($scope, $rootScope, $http){
        
        var controller = $("[data-ng-controller='entry']"),
            collection = controller.data("collection"),
            entry      = controller.data("entry") || {};

        $scope.collection = collection;
        $scope.entry      = entry;


        $scope.save = function(){
            
            var entry = angular.copy($scope.entry);

            $http.post(App.route("/api/collections/saveentry"), {"collection": collection, "entry":entry}).success(function(data){

                if(data && Object.keys(data).length) {
                    $scope.entry = data;
                    App.notify("Entry saved!");
                }

            }).error(App.module.callbacks.error.http);
        };

        $scope.fieldsInArea = function(area) {

            var fields = [];

            if(area=="main") {
                
                fields = $scope.collection.fields.filter(function(field){

                    return (['text','html', 'markdown','code'].indexOf(field.type) > -1);
                });

            }

            if(area=="side"){
                fields = $scope.collection.fields.filter(function(field){
                    return ['select','date','time'].indexOf(field.type) > -1;
                });
            }

            return fields;
        };

        
    });

})(jQuery);