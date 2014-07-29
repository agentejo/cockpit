(function($){

    App.module.controller("form", function($scope, $rootScope, $http){

        var id = $("[data-ng-controller='form']").data("id");


        if (id) {

            $http.post(App.route("/api/forms/findOne"), {filter: {"_id":id}}, {responseType:"json"}).success(function(data){

                if (data && Object.keys(data).length) {
                    $scope.form = data;
                }

            }).error(App.module.callbacks.error.http);

        } else {

            $scope.form = {
                name: "",
                email: "",
                entry: true,
                before: "",
                after: ""
            };
        }


        $scope.save = function() {

            var form = angular.copy($scope.form);

            $http.post(App.route("/api/forms/save"), {"form": form}).success(function(data){

                if (data && Object.keys(data).length) {
                    $scope.form = data;
                    App.notify(App.i18n.get("Form saved!"), "success");
                }

            }).error(App.module.callbacks.error.http);
        };

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