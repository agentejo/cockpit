@start('header')

    {{ $app->assets(['assets:js/angular/fields/codearea.js'], $app['cockpit/version']) }}

@end('header')


<h1><a href="@route('/settingspage')">@lang('Settings')</a> / @lang('General')</h1>

<div class="uk-grid" data-uk-grid-margin data-ng-controller="general-settings" ng-cloak>

    <div class="uk-width-medium-1-4">
        <ul class="uk-nav uk-nav-side" data-uk-switcher="{connect:'#settings-general'}">
            <li><a href="#LOCALES">@lang('Locales')</a></li>
            <li><a href="#REGISTRY">@lang('Registry')</a></li>
            <li><a href="#SYSTEM">@lang('API')</a></li>
        </ul>
    </div>

    <div class="uk-width-medium-3-4">
        <div class="app-panel">
            <div id="settings-general" class="uk-switcher">

                <div>
                    <span class="uk-badge app-badge">@lang('Locales')</span>
                    <hr>

                    <div class="uk-text-center" data-ng-show="!locales.length">
                        <h2><i class="uk-icon-language"></i></h2>
                        <p class="uk-text-large">
                            @lang('No locales added yet.')
                        </p>
                        <p>
                            <button class="uk-button uk-button-large uk-button-primary" type="button" ng-click="editLocale()"><i class="uk-icon-pencil"></i></button>
                        </p>
                    </div>

                    <div data-ng-show="locales.length">
                        <table class="uk-table">
                            <tbody>
                                <tr class="uk-form" ng-repeat="locale in locales">
                                    <td>
                                        @@ lstlocales[locale] @@
                                    </td>
                                    <td width="30" class="uk-text-muted">
                                        @@ locale @@
                                    </td>
                                    <td width="20">
                                        <a href="#" class="uk-text-danger" ng-click="removeLocale($index)"><i class="uk-icon-trash-o"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p>
                            <button class="uk-button uk-button-large uk-button-primary" type="button" ng-click="editLocale()"><i class="uk-icon-pencil"></i></button>
                        </p>
                    </div>

                    <div id="modalocales" class="uk-modal">
                        <div class="uk-modal-dialog">
                            <button type="button" class="uk-modal-close uk-close"></button>
                            <h1>@lang('Languages')</h1>
                            <div class="uk-overflow-container">
                                <table class="uk-table">
                                    @foreach(\Lime\Helper\I18n::$locals as $short => $long)
                                    <tr>
                                        <td width="5%"><input type="checkbox" ng-model="languages['{{ $short }}']"></td>
                                        <td>{{ $long }}</td>
                                        <td class="uk-text-muted" width="10%">{{ $short }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            <hr>
                            <p class="uk-text-center">
                                <button class="uk-button uk-button-primary">@lang('Save')</button>
                            </p>
                        </div>
                    </div>

                </div>

                <div>
                    <span class="uk-badge app-badge">@lang('Registry')</span>
                    <hr>

                    <div class="uk-text-center" data-ng-show="!(registry|count)">
                        <h2><i class="uk-icon-flag"></i></h2>
                        <p class="uk-text-large">
                            @lang('The registry is empty.')
                        </p>

                        <p>
                            <button class="uk-button uk-button-large uk-button-primary" type="button" ng-click="addRegistryKey()"><i class="uk-icon-plus-circle"></i></button>
                        </p>

                        <p class="uk-text-muted">
                            @lang('The registry is just a global key/value storage you can reuse as global options for your app or site.')
                        </p>
                    </div>

                    <div class="uk-margin" ng-show="(registry|count)">
                        <h3>@lang('Entries')</h3>

                        <table class="uk-table">
                            <tbody>
                                <tr class="uk-form" ng-repeat="(key, value) in registry">
                                    <td>
                                        <i class="uk-icon-flag"></i>
                                        @@ key @@
                                    </td>
                                    <td class="uk-width-3-4">
                                        <textarea class="uk-width-1-1" placeholder="key value..." ng-model="registry[key]"></textarea>
                                    </td>
                                    <td width="20">
                                        <a href="#" class="uk-text-danger" ng-click="removeRegistryKey(key)"><i class="uk-icon-trash-o"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="uk-margin">
                            <button ng-show="!emptyRegistry()" class="uk-button uk-button-large uk-button-success" type="button" ng-click="saveRegistry()">@lang('Save')</button>
                            <button class="uk-button uk-button-large uk-button-primary" type="button" ng-click="addRegistryKey()"><i class="uk-icon-plus-circle"></i></button>
                        </div>
                    </div>


                    <hr ng-show="(registry|count)">

                    <div class="uk-margin" ng-show="(registry|count)">
                        <p>
                            <strong>@lang('Access the registry values'):</strong>
                        </p>

                        <span class="uk-badge uk-margin-small-bottom">PHP</span>
                        <highlightcode>&lt;?php $value = get_registry('keyname' [, default]); ?&gt;</highlightcode>

                        <span class="uk-badge uk-margin-small-bottom">Javascript</span>
                        <highlightcode>var value = Cockpit.registry.keyname || default; // with Cockpit.js API</highlightcode>
                    </div>
                </div>

                <div>
                    <span class="uk-badge app-badge">@lang('API')</span>
                    <hr>

                    <div ng-if="!(tokens|count)" class="uk-margin uk-text-large uk-text-muted uk-text-strong">
                        @lang('You have no api token generated yet.')
                    </div>

                    <div ng-repeat="(token, rules) in tokens" ng-if="(tokens|count)">

                        <div class="uk-text-small">@lang('Token'):</div>
                        <div class="uk-text-large uk-margin">
                            <strong ng-if="token">@@ token @@ <a class="uk-text-danger" ng-click="removeToken(token)"><i class="uk-icon-trash-o"></i></a></strong>
                        </div>

                        <div class="uk-margin uk-form" ng-if="token">
                            <label class="uk-badge uk-margin-small-bottom">@lang('Access rules')</label>
                            <textarea codearea class="uk-width-1-1" placeholder="@lang('Allow all')" style="min-height:300px;" ng-bind="rules" ng-model="tokens[token]"></textarea>
                        </div>

                        <hr>
                    </div>

                    <button ng-show="(tokens|count)" class="uk-button uk-button-large uk-button-success" type="button" ng-click="saveTokens()">@lang('Save')</button>
                    <button class="uk-button uk-button-large" ng-click="generateToken()">@lang('Generate api token')</button>
                </div>


            </div>
        </div>
    </div>

</div>


<script>
    App.module.controller("general-settings", function($scope, $rootScope, $http, $timeout){

        $scope.tokens    = {{ json_encode($tokens) }};
        $scope.registry  = {{ $registry }};
        $scope.locales   = {{ $locales }};

        $scope.languages = {};

        $scope.lstlocales = {{ json_encode(\Lime\Helper\I18n::$locals) }};

        var modalocales = UIkit.modal('#modalocales');

        modalocales.element.on('click', '.uk-button', function() {

            $scope.locales = [];

            $timeout(function() {
                angular.forEach($scope.languages, function(value, key) {
                    if (value === true) {
                        $scope.locales.push(key)
                    }
                });

                modalocales.hide();

                $scope.saveLocals();
            }, 0);

        });

        $scope.addRegistryKey = function(){

            var key = prompt("Key name");

            if (!key) return;

            if ($scope.registry[key]) {
                App.Ui.alert('"'+key+'" already exists!');
                return;
            }

            $timeout(function(){
                $scope.registry[key] = "";
            });
        };

        $scope.removeRegistryKey = function(key){

            App.Ui.confirm("@lang('Are you sure?')", function() {
                $timeout(function(){
                    delete $scope.registry[key];
                    $scope.saveRegistry();
                }, 0);
            });
        };

        $scope.saveRegistry = function() {

            $http.post(App.route("/settings/saveRegistry"), {"registry": angular.copy($scope.registry)}).success(function(data){
                App.notify("@lang('Registry updated!')", "success");
            }).error(App.module.callbacks.error.http);
        };

        $scope.emptyRegistry = function() {
            return !Object.keys($scope.registry).length;
        };

        $scope.editLocale = function() {

            // reset list
            angular.forEach($scope.languages, function(value, key) {
                if (value === true) $scope.locales[key] = false;
            });

            $scope.locales.forEach(function(locale){
                $scope.languages[locale] = true;
            });

            $timeout(function(){
                modalocales.show();
            }, 0);
        };

        $scope.removeLocale = function(index) {
            $scope.locales.splice(index, 1);
            $scope.saveLocals();
        };

        $scope.saveLocals = function() {

            $http.post(App.route("/settings/saveLocals"), {"locals": angular.copy($scope.locales)}).success(function(data){
                App.notify("@lang('Locales updated!')", "success");
            }).error(App.module.callbacks.error.http);
        };

        $scope.generateToken = function() {
            $scope.tokens[buildToken(95)] = '';
        };

        $scope.removeToken = function(token) {

            if ($scope.tokens[token] !== undefined) {

                App.Ui.confirm("@lang('Are you sure?')", function() {
                    $timeout(function(){
                        delete $scope.tokens[token];
                        $scope.saveTokens();
                    }, 0);
                });
            }
        };

        $scope.saveTokens = function() {
            $http.post(App.route("/settings/saveTokens"), {"tokens": angular.copy($scope.tokens)}).success(function(data){
                App.notify("@lang('Tokens updated!')", "success");
            }).error(App.module.callbacks.error.http);
        };

        function buildToken(bits, base) {
            if (!base) base = 16;
            if (bits === undefined) bits = 128;
            if (bits <= 0) return '0';

            var digits = Math.log(Math.pow(2, bits)) / Math.log(base);
            for (var i = 2; digits === Infinity; i *= 2) {
                digits = Math.log(Math.pow(2, bits / i)) / Math.log(base) * i;
            }

            var rem = digits - Math.floor(digits), res = '';

            for (var i = 0; i < Math.floor(digits); i++) {
                var x = Math.floor(Math.random() * base).toString(base);
                res = x + res;
            }

            if (rem) {
                var b = Math.pow(base, rem);
                var x = Math.floor(Math.random() * b).toString(base);
                res = x + res;
            }

            var parsed = parseInt(res, base);

            if (parsed !== Infinity && parsed >= Math.pow(2, bits)) {
                return hat(bits, base)
            }
            else return res;
        };


    });
</script>
