<div data-ng-controller="backups" ng-cloak>

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand"><a href="@route('/settingspage')">@lang('Settings')</a> / @lang('Backups')</span>
    </nav>

    <div class="uk-grid" data-uk-grid-margin>

        <div class="uk-width-medium-2-3">

            <div class="app-panel">

                <table class="uk-table uk-table-striped" data-ng-show="backups.length">
                    <thead>
                        <th width="20">&nbsp;</th>
                        <th>@lang('Date')</th>
                        <th>@lang('Size')</th>
                        <th width="20">&nbsp;</th>
                    </thead>
                    <tbody>
                        <tr data-ng-repeat="backup in backups">
                            <td class="uk-text-center"><i class="uk-icon-archive"></i></td>
                            <td>
                                @@ backup.timestamp |  fmtdate:'d M, Y H:i:s' @@
                                <div class="uk-text-small uk-text-muted" ng-if="backup.info">@@ backup.info @@</div>
                            </td>
                            <td>@@ backup.size @@</td>
                            <td class="uk-text-right">
                                <div data-uk-dropdown>

                                    <i class="uk-icon-bars"></i>

                                    <div class="uk-dropdown uk-dropdown-flip uk-text-left">
                                        <ul class="uk-nav uk-nav-dropdown">
                                            <li><a href="{{ $app->pathToUrl('#backups:') }}@@ backup.file @@.zip"><i class="uk-icon-cloud-download"></i> @lang('Download backup')</a></li>
                                            <li><a href="#" data-ng-click="remove($index, backup)"><i class="uk-icon-trash-o"></i> @lang('Delete backup')</li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="uk-text-center" data-ng-show="backups && !backups.length">
                    <h2><i class="uk-icon-archive"></i></h2>
                    <p class="uk-text-large">
                        @lang('You don\'t have any backups created.')
                    </p>
                </div>

            </div>
        </div>
        <div class="uk-width-medium-1-3">

            <div data-uk-dropdown>
                <button class="uk-button uk-button-large uk-button-primary">
                    @lang('Create a new backup')
                </button>
                <div class="uk-dropdown">
                    <ul class="uk-nav uk-nav-dropdown uk-nav-parent-icon">
                        <li><a data-ng-click="create('site')">Site</a></li>
                        <li><a data-ng-click="create('cockpit')">Cockpit</a></li>
                    </ul>
                </div>
            </div>

            <hr>

            <div class="uk-text-truncate uk-text-small">
                @lang('Backups are located here'):
                <p class="uk-margin">
                    <strong><i class="uk-icon-folder-open"></i> {{ $app->pathToUrl("#backups:") }}</strong>
                </p>
            </div>
        </div>

    </div>

</div>

<script>

    App.module.controller("backups", function($scope, $rootScope, $http, $timeout){

        $scope.backups = {{ json_encode($backups) }};

        $scope.create = function(target){

            var info = UIkit.notify(['<i class="uk-icon-spinner uk-icon-spin"></i>', App.i18n.get('Creating backup...')].join(' '), {timeout:0});

            $http.post(App.route("/backups/create"), {'target':target}, {responseType:"json"}).success(function(data){

                info.close();

                if (data && data.timestamp) {
                    App.notify(App.i18n.get("Backup created"), "success");

                    $scope.backups.unshift(data);
                } else {
                    App.module.callbacks.error.http();
                }

            }).error(App.module.callbacks.error.http);

        };

        $scope.remove = function(index, backup){


            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/backups/remove"), {

                    "file": backup.file

                }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.backups.splice(index, 1);
                        App.notify(App.i18n.get("Backup deleted"), "success");
                    }, 0);

                }).error(App.module.callbacks.error.http);
            });
        };

    });

</script>