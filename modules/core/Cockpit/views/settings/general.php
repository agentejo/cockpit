
<h1><a href="@route('/settingspage')">@lang('Settings')</a> / @lang('General')</h1>

<div class="uk-grid" data-uk-grid-margin data-ng-controller="general-settings" ng-cloak>

    <div class="uk-width-medium-1-4">
        <ul class="uk-nav uk-nav-side" data-uk-switcher="{connect:'#settings-general'}">
            <li><a href="#SYSTEM">@lang('API')</a></li>
            <li><a href="#REGISTRY">@lang('Registry')</a></li>
        </ul>
    </div>

    <div class="uk-width-medium-3-4">
        <div class="app-panel">
            <div id="settings-general" class="uk-switcher">
                <div>
                    <span class="uk-badge app-badge">@lang('API')</span>
                    <hr>

                    <div class="uk-text-small">@lang('Token'):</div>
                    <div class="uk-text-large uk-margin">
                        <strong ng-if="!token" class="uk-text-muted">@lang('You have no api token generated yet.')</strong>
                        <strong ng-if="token">@@ token @@</strong>
                    </div>

                    <button class="uk-button uk-button-large uk-button-primary" ng-click="generateToken()">@lang('Generate api token')</button>
                </div>
                <div>
                    <span class="uk-badge app-badge">@lang('Registry')</span>
                    <hr>

                    <p class="uk-text-muted">
                        @lang('The registry is just a global key/value storage you can reuse as global options for your app or site.')
                    </p>


                    <div class="uk-alert" ng-show="emptyRegistry()">
                        @lang('The registry is empty.')
                    </div>

                    <div class="uk-margin" ng-show="!emptyRegistry()">
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
                    </div>

                    <button ng-show="!emptyRegistry()" class="uk-button uk-button-large uk-button-success" type="button" ng-click="saveRegistry()">@lang('Save')</button>
                    <button class="uk-button uk-button-large uk-button-primary" type="button" ng-click="addRegistryKey()"><i class="uk-icon-plus-circle"></i> @lang('Entry')</button>

                    <hr>

                    <div class="uk-margin">
                        <p>
                            <strong>@lang('Access the registry values'):</strong>
                        </p>

                        <span class="uk-badge">PHP</span>
                        <pre><code>&lt;?php $value = <strong>get_registry</strong>('keyname', [default]); ?&gt;</code></pre>

                        <span class="uk-badge">Javascript</span>
                        <pre><code>var value = Cockpit.registry.<strong>keyname</strong> || default; <span class="uk-text-muted">// with Cockpit.js API</span></code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<script>
    App.module.controller("general-settings", function($scope, $rootScope, $http, $timeout){

        $scope.token    = '{{ $token }}';
        $scope.registry = {{ $registry }};

        $scope.addRegistryKey = function(){

            var key = prompt("Key name");

            if(!key) return;

            if($scope.registry[key]) {
                App.Ui.alert('"'+key+'" already exists!');
                return;
            }

            $scope.registry[key] = "";
        };

        $scope.removeRegistryKey = function(key){

            App.Ui.confirm("@lang('Are you sure?')", function() {
                $timeout(function(){
                    delete $scope.registry[key];
                    $scope.saveRegistry();
                }, 0);
            })
        };

        $scope.saveRegistry = function(){

            $http.post(App.route("/settings/saveRegistry"), {"registry": angular.copy($scope.registry)}).success(function(data){
                App.notify("@lang('Registry updated!')", "success");
            }).error(App.module.callbacks.error.http);
        };

        $scope.emptyRegistry = function(){
            return !Object.keys($scope.registry).length;
        };

        $scope.generateToken = function(){
            $scope.token = buildToken(95);

            $http.post(App.route("/settings/saveToken"), {"token": $scope.token}).success(function(data){
                App.notify("@lang('New api token created!')", "success");
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