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


        $scope.installCurrent = function() {

            var version = $scope.data.current.version,
                info    = $.UIkit.notify(['<i class="uk-icon-spinner uk-icon-spin"></i> &nbsp; ', App.i18n.get('Downloading archive...')].join(' '), {timeout:0});

            // download file
            $http.post(App.route("/api/updater/update/1"), {"version":version}).success(function(data){

                if(!data.success) {
                    info.close();
                    App.module.callbacks.error.http();
                    return;
                }

                info.content(['<i class="uk-icon-spinner uk-icon-spin"></i> &nbsp; ', App.i18n.get('Extracting archive...')].join(' '));

                // extract file
                $http.post(App.route("/api/updater/update/2"), {"version":version}).success(function(data){

                    if(!data.success) {
                        info.close();
                        App.module.callbacks.error.http();
                        return;
                    }

                    info.content(['<i class="uk-icon-spinner uk-icon-spin"></i> &nbsp; ', App.i18n.get('Updating files...')].join(' '));

                    // override
                    $http.post(App.route("/api/updater/update/3"), {"version":version}).success(function(data){
                        console.log(data);
                        if(!data.success) {
                            info.close();
                            App.module.callbacks.error.http();
                            return;
                        }

                        info.content(['<i class="uk-icon-spinner uk-icon-spin"></i> &nbsp; ', App.i18n.get('Cleanup...')].join(' '));

                        // cleanup
                        $http.post(App.route("/api/updater/update/4"), {"version":version}).success(function(data){

                            if(!data.success) {
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

        $scope.version_compare = version_compare;
    });

    // helper

    function version_compare(v1, v2, operator) {

        if (!operator) {
            return compare;
        }

        var i = 0, x = 0, compare = 0,

        vm = { 'dev': -6, 'alpha': -5, 'a': -5, 'beta': -4, 'b': -4, 'RC': -3, 'rc': -3, '#': -2, 'p': 1, 'pl': 1 },

        prepVersion = function (v) {
            v = ('' + v).replace(/[_\-+]/g, '.');
            v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.');
            return (!v.length ? [-8] : v.split('.'));
        },

        numVersion = function (v) {
            return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10));
        };

        v1 = prepVersion(v1);
        v2 = prepVersion(v2);
        x  = Math.max(v1.length, v2.length);

        for (i = 0; i < x; i++) {

            if (v1[i] == v2[i]) continue;

            v1[i] = numVersion(v1[i]);
            v2[i] = numVersion(v2[i]);

            if (v1[i] < v2[i]) {
                compare = -1;
                break;
            } else if (v1[i] > v2[i]) {
                compare = 1;
                break;
            }
        }

        switch (operator) {
            case '>':
                return (compare > 0);
            case '>=':
                return (compare >= 0);
            case '<=':
                return (compare <= 0);
            case '==':
                return (compare === 0);
            case '!=':
                return (compare !== 0);
            case '<':
                return (compare < 0);
            default:
                return null;
        }
    }

})(jQuery);