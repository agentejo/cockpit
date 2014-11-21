(function($){

    App.module.controller("galleries", function($scope, $rootScope, $http, $timeout){

        $scope.mode        = App.storage.get("cockpit.view.listmode", 'list');
        $scope.groups      = [];
        $scope.activegroup = '-all';

        // get galleries
        $http.post(App.route("/api/galleries/find"), {}).success(function(data){

            $scope.galleries = data;

        }).error(App.module.callbacks.error.http);

        // get groups
        $http.post(App.route("/api/galleries/getGroups"), {}).success(function(groups){

            $scope.groups = groups;

        }).error(App.module.callbacks.error.http);


        $scope.remove = function(index, gallery){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/galleries/remove"), { "gallery": angular.copy(gallery) }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.galleries.splice(index, 1);
                        App.notify(App.i18n.get("Gallery removed"), "success");
                    }, 0);
                }).error(App.module.callbacks.error.http);
            });
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
                        row     = $scope.selected[i],
                        scope   = $(row).scope(),
                        gallery = scope.gallery,
                        $index  = scope.$index;

                        (function(row, scope, gallery, $index){

                            $http.post(App.route("/api/galleries/remove"), { "gallery": angular.copy(gallery) }, {responseType:"json"}).success(function(data){

                            }).error(App.module.callbacks.error.http);

                            $ids.push(gallery._id);

                        })(row, scope, gallery, $index);
                    }

                    $scope.galleries = $scope.galleries.filter(function(gallery){
                        return ($ids.indexOf(gallery._id)===-1);
                    });
                });
            }
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

            var name = prompt("Group name");

            if (name && $scope.groups.indexOf(name)==-1) {
                $scope.groups.push(name);
                $scope.updateGroups();
            }
        };

        $scope.updateGroups = function(){

            $http.post(App.route("/api/galleries/updateGroups"), {

                "groups": angular.copy($scope.groups)

            }, {responseType:"json"}).success(function(data){

                $timeout(function(){
                    App.notify(App.i18n.get("Groups updated"), "success");
                }, 0);

            }).error(App.module.callbacks.error.http);
        };

        $scope.removeGroup = function(index){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/galleries/update"), {"criteria":{"group":$scope.groups[index]}, "data":{"group":""}});

                $scope.$apply(function(){
                    $scope.groups.splice(index, 1);
                    $scope.activegroup = '-all';
                    $scope.updateGroups();
                });
            });
        };

        $scope.editGroup = function(group, index){

            var name = prompt("Group name", $scope.groups[index]);

            if (name && $scope.groups.indexOf(name)==-1) {

                var oldname = $scope.groups[index];

                $scope.groups[index] = name;
                $scope.activegroup   = name;

                $scope.galleries.forEach(function(region){
                    if (region.group === oldname) region.group = name;
                });

                $http.post(App.route("/api/galleries/update"), {"criteria":{"group":oldname}, "data":{"group":name}});

                $scope.updateGroups();
            }
        };

        var grouplist = $("#groups-list").on("change.uk.sortable",function(){

            if ($scope.groups.length==1) return;

            var groups = [];

            grouplist.children().each(function(){
                groups.push($(this).scope().group);
            });

            $scope.$apply(function(){
                $scope.groups = groups;

                $scope.updateGroups();
            });
        });
    });

})(jQuery);
