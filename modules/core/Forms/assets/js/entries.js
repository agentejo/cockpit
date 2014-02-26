(function($){

    App.module.controller("entries", function($scope, $rootScope, $http, $timeout){

        $scope.form = $("[data-ng-controller='entries']").data("form");

        $http.post(App.route("/api/forms/entries"), {

            "form": angular.copy($scope.form),
            "sort": {"created":-1}

        }, {responseType:"json"}).success(function(data){

            if(data) $scope.entries = data;

        }).error(App.module.callbacks.error.http);

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

        $scope.selected = [];

        $scope.removeSelected = function(){

            if(!$scope.selected.length) return;

            App.Ui.confirm(App.i18n.get("Are you sure?"), function(){

                var form = angular.copy($scope.form);

                $scope.selected.forEach(function(entryId){
                    for(var index=0; index<$scope.entries.length;index++) {
                        if($scope.entries[index]._id == entryId) {

                            $http.post(App.route("/api/forms/removeentry"), {
                                "form": form,
                                "entryId": entryId
                            }, {responseType:"json"}).error(App.module.callbacks.error.http);

                            $scope.entries.splice(index, 1);
                            $scope.form.count -= 1;
                            break;
                        }
                    }
                });

                $scope.selected = [];
            });
        };

        var updateSelected = function(){
            var items = $(".js-select:checked");

            $scope.$apply(function(){
                $scope.selected = [];

                items.each(function(){
                    $scope.selected.push($(this).data("id"));
                });
            });
        };

        var cbAll = $(".js-all").on("click", function(){
            $(".js-select").prop("checked", cbAll.prop("checked"));
            updateSelected();
        });

        $("table").on("click", ".js-select", function(){
            cbAll.prop("checked", false);
            updateSelected();
        });

    });

})(jQuery);