(function($){

    App.module.controller("groups", function($scope, $rootScope, $http, $timeout){
        
        $scope.acl    = angular.copy(ACL_DATA);
        $scope.active = "admin";

        if(location.hash && $scope.acl[location.hash.replace("#", "")]) {
            $scope.active = location.hash.replace("#", "");
        }

        $scope.setActive = function(group){
            $scope.active = group;
        };

        $scope.setAcl = function(acl){
            $scope.acl = angular.copy(acl);
        };

        $scope.addOrEditGroup = function(oldname, remove) {

            if(remove) {

                if(confirm(App.i18n.get("Are you sure?"))) {
                    
                    $http.post(App.route("/accounts/deleteGroup"), {"name": oldname}).success(function(data){

                        App.notify(App.i18n.get("Group removed!"));

                        location.hash = "";

                        $timeout(function(){
                            location.reload();
                        }, 500);

                    }).error(App.module.callbacks.error.http);
                }
                return;
            }


            var name = prompt("Please enter a groupname:", oldname ? oldname:"");

            if($.trim(name)) {

                $http.post(App.route("/accounts/addOrEditGroup"), {"name": name, "oldname":oldname}).success(function(data){

                    App.notify(App.i18n.get("Group saved!"));

                    location.hash = name;

                    $timeout(function(){
                        location.reload();
                    }, 500);

                }).error(App.module.callbacks.error.http);
            }

        };


        $scope.save = function() {

            $http.post(App.route("/accounts/saveAcl"), {"acl": angular.copy($scope.acl)}).success(function(data){

                App.notify(App.i18n.get("Settings saved!"));

            }).error(App.module.callbacks.error.http);
        };
    });

})(jQuery);