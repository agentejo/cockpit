(function($){

    App.module.controller("datastore", function($scope, $rootScope, $http, $timeout){

        $scope.mode   = App.storage.get("cockpit.view.listmode", 'list');
        $scope.tables = false;


        $http.post(App.route("/api/datastore/find"), {extended: true}).success(function(data){

            $scope.tables = data;

        }).error(App.module.callbacks.error.http);


        $scope.remove = function(index, table){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function(){

                $http.post(App.route("/api/datastore/remove"), { "table": angular.copy(table) }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.tables.splice(index, 1);
                        App.notify(App.i18n.get("Table removed"), "success");
                    }, 0);
                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.filter = "";

        $scope.matchName = function(name) {
            return (name && name.indexOf($scope.filter) !== -1);
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
                        table   = scope.table,
                        $index = scope.$index;

                        (function(row, scope, table, $index){

                            $http.post(App.route("/api/datastore/remove"), { "table": angular.copy(table) }, {responseType:"json"}).success(function(data){

                            }).error(App.module.callbacks.error.http);

                            $ids.push(table._id);

                        })(row, scope, table, $index);
                    }

                    $scope.tables = $scope.tables.filter(function(form){
                        return ($ids.indexOf(table._id)===-1);
                    });
                });
            }
        };

    });

})(jQuery);