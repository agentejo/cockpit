(function($){

    App.module.controller("forms", function($scope, $rootScope, $http, $timeout){

        $scope.mode = App.storage.get("cockpit.view.listmode", 'list');

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
                        form   = scope.form,
                        $index = scope.$index;

                        (function(row, scope, form, $index){

                            $http.post(App.route("/api/forms/remove"), { "form": angular.copy(form) }, {responseType:"json"}).success(function(data){

                            }).error(App.module.callbacks.error.http);

                            $ids.push(form._id);

                        })(row, scope, form, $index);
                    }

                    $scope.forms = $scope.forms.filter(function(form){
                        return ($ids.indexOf(form._id)===-1);
                    });
                });
            }
        };

    });

})(jQuery);