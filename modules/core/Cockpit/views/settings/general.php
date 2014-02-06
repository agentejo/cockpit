
<h1><a href="@route('/settingspage')">@lang('Settings')</a> / @lang('General')</h1>

<div class="uk-grid" data-uk-grid-margin data-ng-controller="general-settings">

    <div class="uk-width-medium-1-4">
        <ul class="uk-nav uk-nav-side" data-uk-switcher="{connect:'#settings-general'}">
            <li><a href="#SYSTEM">Api</a></li>
        </ul>
    </div>

    <div class="uk-width-medium-3-4">
        <div class="app-panel">
            <div id="settings-general" class="uk-switcher">
                <div>
                    <span class="uk-badge app-badge">API</span>
                    <hr>

                    <div class="uk-text-small">Token:</div>
                    <div class="uk-text-large uk-margin">
                        <strong ng-if="!token" class="uk-text-muted">@lang('You have no api token generated yet.')</strong>
                        <strong ng-if="token">@@ token @@</strong>
                    </div>

                    <button class="uk-button uk-button-large uk-button-primary" ng-click="generateToken()">@lang('Generate api token')</button>
                </div>
            </div>
        </div>
    </div>

</div>


<script>
    App.module.controller("general-settings", function($scope, $rootScope, $http){

        $scope.token = '{{ $token }}';

        $scope.generateToken = function(){
            $scope.token = buildToken();

            $http.post(App.route("/settings/saveToken"), {"token": $scope.token}).success(function(data){
                App.notify("@lang('New api token saved!')", "success");
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