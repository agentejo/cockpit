(function($){

    App.module.controller("groups", function($scope, $rootScope, $http, $timeout){

        $scope.acl           = angular.copy(ACL_DATA);
        $scope.groupsettings = angular.copy(ACL_GROUP_SETTINGS);
        $scope.active        = "admin";

        Object.keys(ACL_DATA).forEach(function(group){
            $scope.groupsettings[group] = ACL_GROUP_SETTINGS[group] || {};
        });

        if (!Object.keys($scope.groupsettings).length) {
            $scope.groupsettings['admin'] = {};
        }

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

                App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                    $http.post(App.route("/accounts/deleteGroup"), {"name": oldname}).success(function(data){

                        App.notify(App.i18n.get("Group removed!"));

                        location.hash = "";

                        $timeout(function(){
                            location.reload();
                        }, 500);

                    }).error(App.module.callbacks.error.http);
                });
                return;
            }


            var name = prompt(App.i18n.get("Please enter a groupname") + ":", oldname ? oldname:"");

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

            $http.post(App.route("/accounts/saveAcl"), {
                "acl": angular.copy($scope.acl), 
                "aclSettings": angular.copy($scope.groupsettings)
            }).success(function(data){

                App.notify(App.i18n.get("Settings saved!"));

            }).error(App.module.callbacks.error.http);
        };
    });

})(jQuery);