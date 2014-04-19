(function($){

    App.module.controller("collections", function($scope, $rootScope, $http, $timeout){

        $scope.mode = App.storage.get("cockpit.view.listmode", 'list');


        $http.post(App.route("/api/collections/find"), {extended:true}).success(function(data){

            $scope.collections = data;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, collection){
            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/collections/remove"), { "collection": angular.copy(collection) }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.collections.splice(index, 1);
                        App.notify(App.i18n.get("Collection removed"), "success");
                    }, 0);
                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.filter = "";

        $scope.matchName = function(name) {

            return (name.indexOf($scope.filter) !== -1);
        };

        $scope.setListMode = function(mode) {
            $scope.mode = mode;

            App.storage.set("cockpit.view.listmode", mode);
        };


        $scope.selected = null;

        $scope.$on('multiple-select', function(e, data){
            $timeout(function(){
                $scope.selected = data.items.length ? data.items : null;
            }, 0);
        });

        $scope.removeSelected = function(){
            if ($scope.selected && $scope.selected.length) {

                App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                    var row, scope, $index, $ids = [];

                    for(var i=0;i<$scope.selected.length;i++) {
                        row    = $scope.selected[i],
                        scope  = $(row).scope(),
                        collection = scope.collection,
                        $index = scope.$index;

                        (function(row, scope, collection, $index){

                            $http.post(App.route("/api/collections/remove"), { "collection": angular.copy(collection) }, {responseType:"json"}).success(function(data){

                            }).error(App.module.callbacks.error.http);

                            $ids.push(collection._id);

                        })(row, scope, collection, $index);
                    }

                    $scope.collections = $scope.collections.filter(function(collection){
                        return ($ids.indexOf(collection._id)===-1);
                    });
                });
            }
        };

    });

})(jQuery);