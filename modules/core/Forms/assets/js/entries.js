(function($){

    App.module.controller("entries", function($scope, $rootScope, $http, $timeout){

        $scope.form = FORMDATA || {};

        $scope.remove = function(index, entryId){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/forms/removeentry"), {

                    "form": angular.copy($scope.form),
                    "entryId": entryId

                }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.entries.splice(index, 1);
                        $scope.form.count -= 1;
                        App.notify(App.i18n.get("Entry removed"), "success");
                    }, 0);

                }).error(App.module.callbacks.error.http);
            });
        };

        // batch actions

        $scope.selected = null;

        $scope.$on('multiple-select', function(e, data){
            $timeout(function(){
                $scope.selected = data.items.length ? data.items : null;
            }, 0);
        });

        $scope.removeSelected = function(){
            if ($scope.selected && $scope.selected.length) {

                App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                    var row, scope, $index, $ids = [], form = angular.copy($scope.form);

                    for(var i=0;i<$scope.selected.length;i++) {
                        row    = $scope.selected[i],
                        scope  = $(row).scope(),
                        entry  = scope.entry,
                        $index = scope.$index;

                        (function(row, scope, entry, $index){

                            $http.post(App.route("/api/forms/removeentry"), {
                                "form": form,
                                "entryId": entry._id
                            }, {responseType:"json"}).error(App.module.callbacks.error.http);

                            $ids.push(entry._id);
                            $scope.form.count -= 1;

                        })(row, scope, entry, $index);
                    }

                    $scope.entries = $scope.entries.filter(function(entry){
                        return ($ids.indexOf(entry._id)===-1);
                    });
                });
            }
        };

        $scope.emptytable = function() {

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {
                $http.post(App.route("/api/forms/emptytable"), {

                    "form": angular.copy($scope.form)

                }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.entries = [];
                        $scope.form.count = 0;
                        App.notify(App.i18n.get("Done."), "success");
                    }, 0);

                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.loadmore = function() {

            var limit  = 25;

            $http.post(App.route("/api/forms/entries"), {

                "form" : angular.copy($scope.form),
                "sort" : {"created":-1},
                "limit": limit,
                "skip" : $scope.entries ? $scope.entries.length : 0

            }, {responseType:"json"}).success(function(data){

                if (data) {

                    if (!$scope.entries) {
                        $scope.entries = [];
                    }

                    if (data.length) {

                        if (data.length < limit) {
                            $scope.nomore = true;
                        }

                        $scope.entries = $scope.entries.concat(data);

                    } else {
                       $scope.nomore = true;
                    }

                }

            }).error(App.module.callbacks.error.http);

        };

        $scope.loadmore();

    });

})(jQuery);