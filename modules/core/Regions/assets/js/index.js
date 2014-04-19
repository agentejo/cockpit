(function($){

    App.module.controller("regions", function($scope, $rootScope, $http, $timeout){

        $scope.activegroup = '-all';
        $scope.groups      = [];
        $scope.mode        = App.storage.get("cockpit.view.listmode", 'list');
        $scope.selected    = null;

        // get regions
        $http.post(App.route("/api/regions/find"), {}).success(function(regions){

            $scope.regions = regions;

        }).error(App.module.callbacks.error.http);

        // get groups
        $http.post(App.route("/api/regions/getGroups"), {}).success(function(groups){

            $scope.groups = groups;

        }).error(App.module.callbacks.error.http);

        $scope.remove = function(index, region){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/regions/remove"), {

                    "region": angular.copy(region)

                }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.regions.splice(index, 1);
                        App.notify(App.i18n.get("Region removed"), "success");
                    }, 0);

                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.filter = "";

        $scope.matchName = function(name) {
            return (name && name.indexOf($scope.filter) !== -1);
        };

        $scope.inGroup = function(group) {
            return ($scope.activegroup=='-all' || $scope.activegroup==group);
        };

        $scope.setListMode = function(mode) {
            $scope.mode = mode;

            App.storage.set("cockpit.view.listmode", mode);
        };

        $scope.addGroup = function() {

            var name = prompt(App.i18n.get('Group name'));

            if(name && $scope.groups.indexOf(name)==-1) {
                $scope.groups.push(name);
                $scope.updateGroups();
            }
        };

        $scope.updateGroups = function(){

            $http.post(App.route("/api/regions/updateGroups"), {

                "groups": angular.copy($scope.groups)

            }, {responseType:"json"}).success(function(data){

                $timeout(function(){
                    App.notify(App.i18n.get("Groups updated"), "success");
                }, 0);

            }).error(App.module.callbacks.error.http);
        };

        $scope.removeGroup = function(index){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/regions/update"), {"criteria":{"group":$scope.groups[index]}, "data":{"group":""}});

                $scope.$apply(function(){
                    $scope.groups.splice(index, 1);
                    $scope.activegroup = '-all';
                    $scope.updateGroups();
                });
            });
        };

        $scope.editGroup = function(group, index){

            var name = prompt(App.i18n.get('Group name'), $scope.groups[index]);

            if(name && $scope.groups.indexOf(name)==-1) {

                var oldname = $scope.groups[index];

                $scope.groups[index] = name;
                $scope.activegroup   = name;

                $scope.regions.forEach(function(region){
                    if(region.group === oldname) region.group = name;
                });

                $http.post(App.route("/api/regions/update"), {"criteria":{"group":oldname}, "data":{"group":name}});

                $scope.updateGroups();
            }
        };

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
                        region = scope.region,
                        $index = scope.$index;

                        (function(row, scope, region, $index){

                            $http.post(App.route("/api/regions/remove"), {

                                "region": angular.copy(region)

                            }, {responseType:"json"}).success(function(data){


                            }).error(App.module.callbacks.error.http);

                            $ids.push(region._id);

                        })(row, scope, region, $index);
                    }

                    $scope.regions = $scope.regions.filter(function(region){
                        return ($ids.indexOf(region._id)===-1);
                    });
                });
            }
        };

        var grouplist = $("#groups-list");

        grouplist.on("dragend", "[draggable]",function(){

            if($scope.groups.length==1) return;

            var groups = [];

            grouplist.children().each(function(){
                groups.push($(this).scope().group);
            });

            $scope.$apply(function(){
                $scope.groups = groups;

                $scope.updateGroups();
            });
        });

        nativesortable(grouplist[0]);
    });

})(jQuery);