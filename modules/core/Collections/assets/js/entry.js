(function($){

    App.module.controller("entry", function($scope, $rootScope, $http, $timeout){

        var collection = COLLECTION,
            entry      = COLLECTION_ENTRY || {};

        $scope.collection = collection;
        $scope.entry      = entry;
        $scope.versions   = [];

        // init entry with default values
        if(!entry["_id"] && collection.fields && collection.fields.length) {
            collection.fields.forEach(function(field){
                if(field["default"]) entry[field.name] = field["default"];
            });
        }

        $scope.loadVersions = function() {

            if(!$scope.entry["_id"]) {
                return;
            }

            $http.post(App.route("/api/collections/getVersions"), {"id":$scope.entry["_id"], "colId":$scope.collection["_id"]}).success(function(data){

                if(data) {
                    $scope.versions = data;
                }

            }).error(App.module.callbacks.error.http);
        };

        $scope.clearVersions = function() {

            if(!$scope.entry["_id"]) {
                return;
            }

            App.Ui.confirm(App.i18n.get("Are you sure?"), function(){

                $http.post(App.route("/api/collections/clearVersions"), {"id":$scope.entry["_id"], "colId":$scope.collection["_id"]}).success(function(data){
                    $timeout(function(){
                        $scope.versions = [];
                        App.notify(App.i18n.get("Version history cleared!"), "success");
                    }, 0);
                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.restoreVersion = function(versionId) {

            if(!versionId || !$scope.entry["_id"]) {
                return;
            }


            App.Ui.confirm(App.i18n.get("Are you sure?"), function(){

                var msg = $.UIkit.notify(['<i class="uk-icon-spinner uk-icon-spin"></i>', App.i18n.get("Restoring version...")].join(" "), {timeout:0});

                $http.post(App.route("/api/collections/restoreVersion"), {"docId":$scope.entry["_id"], "colId":$scope.collection["_id"],"versionId":versionId}).success(function(data){

                    setTimeout(function(){
                        msg.close();
                        location.href = App.route("/collections/entry/"+$scope.collection["_id"]+'/'+$scope.entry["_id"]);
                    }, 1000);
                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.save = function(){

            var entry = angular.copy($scope.entry);

            if ($scope.validateForm(entry)) {
                $http.post(App.route("/api/collections/saveentry"), {"collection": collection, "entry":entry, "createversion": true}).success(function(data){

                    if(data && Object.keys(data).length) {
                        $scope.entry = data;
                        App.notify(App.i18n.get("Entry saved!"));

                        $scope.loadVersions();
                    }

                }).error(App.module.callbacks.error.http);
            }
        };

        $scope.validateForm = function(entry){
            var valid = true;

            $scope.collection.fields.forEach(function(field){
                delete field.error;
                if (field.required && (entry[field.name] === undefined || entry[field.name] === '')) {
                    field.error = App.i18n.get('This field is required.');
                    valid = false;
                }
            });

            return valid;
        };

        $scope.fieldsInArea = function(area) {

            var fields = [];

            if(area=="main") {

                fields = $scope.collection.fields.filter(function(field){

                    return (['text','html', 'markdown','code','wysiwyg','markdown', 'gallery'].indexOf(field.type) > -1);
                });

            }

            if(area=="side"){
                fields = $scope.collection.fields.filter(function(field){
                    return ['select','date','time','media','boolean','tags'].indexOf(field.type) > -1;
                });
            }

            return fields;
        };

        $scope.loadVersions();
    });

})(jQuery);
