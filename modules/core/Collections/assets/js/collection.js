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

        $scope.collections = [];
        $scope.groups      = [];

        // get collections
        $http.post(App.route("/api/collections/find"), {}).success(function(data){
            $scope.collections = data;
        });

        // get groups
        $http.post(App.route("/api/collections/getGroups"), {}).success(function(groups){

            $scope.groups = groups;

        }).error(App.module.callbacks.error.http);

        $scope.addfield = function(){

            if(!$scope.collection.fields) {
                $scope.collection.fields = [];
            }

            $scope.collection.fields.push({
                "name": "",
                "type": "text",
                "lst": false,
                "required": false
            });
        };

        $scope.remove = function(field) {

            var index = $scope.collection.fields.indexOf(field);

            if(index > -1) {
                $scope.collection.fields.splice(index, 1);
            }

        };

        $scope.toggleOptions = function(index) {
            $("#options-field-"+index).toggleClass('uk-hidden');
        };

        $scope.save = function() {

            var collection = angular.copy($scope.collection);

            $http.post(App.route("/api/collections/save"), {"collection": collection}).success(function(data){

                if(data && Object.keys(data).length) {
                    $scope.collection._id = data._id;
                    App.notify(App.i18n.get("Collection saved!"), "success");
                }

            }).error(App.module.callbacks.error.http);
        };

        $scope.getSortFields = function() {

            return [{'name':'created'}, {'name':'modified'}].concat($scope.collection && $scope.collection.fields ? angular.copy($scope.collection.fields):[]);
        }

        // after sorting list
        $(function(){

            var list = $("#fields-list").on("nestable-stop", function(){
                var fields = [];

                list.children('.ng-scope').each(function(){
                    fields.push(angular.copy($(this).scope().field));
                });

                $scope.$apply(function(){
                    $scope.collection.fields = fields;
                });
            });

        });


        // bind clobal command + save
        Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            } else {
                e.returnValue = false; // ie
            }
            $scope.save();
            return false;
        });

    });

})(jQuery);
