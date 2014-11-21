(function($){

    App.module.controller("updater", function($scope, $rootScope, $http, $timeout){

        $scope.loading = true;
        $scope.error   = false;
        $scope.data    = false;

        // check
        $http.post(App.route("/api/updater/check"), {}).success(function(data){

            $timeout(function(){

                $scope.data = data;
                $scope.loading = false;

            }, 300);

        }).error(App.module.callbacks.error.http);


        $scope.install = function(version) {

            version = version || $scope.data.stable.version;

            var info = UIkit.notify(['<i class="uk-icon-spinner uk-icon-spin"></i> &nbsp; ', App.i18n.get('Downloading archive...Grab some coffee!')].join(' '), {timeout:0});

            // download file
            $http.post(App.route("/api/updater/update/1"), {"version":version}).success(function(data){

                if (!data.success) {
                    info.close();

                    if(data.message) {
                        App.notify(data.message, "danger");
                    } else {
                        App.module.callbacks.error.http();
                    }
                    return;
                }

                info.content(['<i class="uk-icon-spinner uk-icon-spin"></i> &nbsp; ', App.i18n.get('Extracting archive...')].join(' '));

                // extract file
                $http.post(App.route("/api/updater/update/2"), {"version":version}).success(function(data){

                    if (!data.success) {
                        info.close();

                        if(data.message) {
                            App.notify(data.message, "danger");
                        } else {
                            App.module.callbacks.error.http();
                        }
                        return;
                    }

                    info.content(['<i class="uk-icon-spinner uk-icon-spin"></i> &nbsp; ', App.i18n.get('Updating files...')].join(' '));

                    // override
                    $http.post(App.route("/api/updater/update/3"), {"version":version}).success(function(data){

                        if (!data.success) {
                            info.close();

                            if(data.message) {
                                App.notify(data.message, "danger");
                            } else {
                                App.module.callbacks.error.http();
                            }
                            return;
                        }

                        info.content(['<i class="uk-icon-spinner uk-icon-spin"></i> &nbsp; ', App.i18n.get('Cleanup...')].join(' '));

                        // cleanup
                        $http.post(App.route("/api/updater/update/4"), {"version":version}).success(function(data){

                            if (!data.success) {
                                info.close();
                                App.module.callbacks.error.http();
                                return;
                            }

                            setTimeout(function(){
                                info.close();
                                location.href = App.route("/");
                            }, 300);

                        }).error(App.module.callbacks.error.http);
                    }).error(App.module.callbacks.error.http);
                }).error(App.module.callbacks.error.http);
            }).error(App.module.callbacks.error.http);
        };
    });

})(jQuery);
