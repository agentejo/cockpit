(function($){

    App.module.controller("collection", function($scope, $rootScope, $http){
        
        var id = $("[data-ng-controller='collection']").data("id");


        if(id) {

            $http.post(App.route("/api/collections/findOne"), {filter: {"_id":id}}, {responseType:"json"}).success(function(data){

                if(data && Object.keys(data).length) {
                    $scope.collection = data;
                }

            }).error(App.module.callbacks.error.http);

        } else {
            
            $scope.collection = {
                name: "",
                fields: [],
                sortfield: "created",
                sortorder: "-1"
            };
        }

        $scope.addfield = function(){
            
            if(!$scope.collection.fields) {
                $scope.collection.fields = [];
            }

            $scope.collection.fields.push({
                "name": "",
                "type": "text",
                "lst": false
            });
        };

        $scope.remove = function(field) {

            var index = $scope.collection.fields.indexOf(field);

            if(index > -1) {
                $scope.collection.fields.splice(index, 1);
            }

        };

        $scope.save = function() {
            
            var collection = angular.copy($scope.collection);

            $http.post(App.route("/api/collections/save"), {"collection": collection}).success(function(data){

                if(data && Object.keys(data).length) {
                    $scope.collection = data;
                    App.notify(App.i18n.get("Collection saved!"));
                }

            }).error(App.module.callbacks.error.http);
        };

        // after sorting list
        $(function(){

            var list = $("#fields-list").on("sortable-change", function(){
                var fields = [];

                list.children().each(function(){
                    fields.push(angular.copy($(this).scope().field));
                });

                $scope.$apply(function(){
                    $scope.collection.fields = fields;
                });
            });

        });


    });

})(jQuery);