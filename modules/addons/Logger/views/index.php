<div id="logger" ng-controller="logger">

    <div class="uk-navbar">
        <span class="uk-navbar-brand">@lang('logger')</span>
        <ul class="uk-navbar-nav">
            <li><a href ng-click="addLogentry()" title="@lang('Add logoentry')" data-uk-tooltip="{pos:'right'}" data-cached-title="Add collection"><i class="uk-icon-plus-circle"></i></a></li>
        </ul>
    </div>

    <div class="app-panel uk-margin uk-text-center ng-hide" data-ng-show="logger && !logger.length">
        <h2><i class="uk-icon-list-alt"></i></h2>
        <p class="uk-text-large">
            @lang('It seems you don\'t have any log entries.')
        </p>
    </div>

    <div class="uk-grid" data-uk-grid-margin="" data-ng-show="logger && logger.length">
        <div class="uk-width-medium-4-5">
            <div class="app-panel">
                <table class="uk-table uk-table-striped">
                    <thead>
                        <tr>
                            <th width="10"></th>
                            <th>
                                @lang('Message')                            
                            </th>
                            <th width="15%">@lang('Created')</th>
                            <th width="10%">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-ng-repeat="logentry in logger">
                            <td><input type="checkbox" ng-model="logentry.done" ng-change="logentryDone(logentry)" /></td>
                            <td>
                                @@ logentry.message @@
                            </td>
                            <td class="ng-binding">@@ logentry.created | fmtdate:'d M, Y' @@</td>
                            <td class="uk-text-right">
                                <a href data-ng-click="editLogentry(logentry)" title="@lang('Edit entry')"><i class="uk-icon-pencil"></i></a>
                                <a href data-ng-click="removeLogentry(logentry)" title="@lang('Delete entry')"><i class="uk-icon-trash-o"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>

    App.module.controller("logger", function($scope, $rootScope, $http, $timeout) {

        fetchlogger();

        $scope.logger;

        $scope.newLogentry = '';

        $scope.addLogentry = function() {
            var message = prompt(App.i18n.get("Please enter a Message:"), "");

            if (!message.length) {
                return;
            }

            saveLogentry({
                message: message
            });
        };

         $scope.editLogentry = function(logentry) {
             var message = prompt(App.i18n.get("Please enter a Message:"), logentry.message);
             
             if(!message.length) {
                 return;
             }
             
             logentry.message = message;
             
             saveLogentry(logentry);
         };

        $scope.removeLogentry = function(logentry) {
            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {
                removeLogentry(logentry);
            });
        };

        $scope.logentryDone = function(logentry) {
            saveLogentry(logentry);
        };

        function saveLogentry(logentry) {
            $http.post(App.route("/api/logger/save"), {"logentry": logentry}).success(function(data) {
                if (!logentry._id) {
                    $scope.logger.push(data);
                }
            });
        }
        ;

        function removeLogentry(logentry) {
            $http.post(App.route("/api/logger/remove"), {"id": logentry._id}).success(function(data) {
                $scope.logger.splice($scope.logger.indexOf(logentry), 1);
            });
        }

        function fetchlogger() {
            $http.post(App.route("/api/logger/find")).success(function(data) {
                $scope.logger = data;
            });
        }
        ;

    });

</script>