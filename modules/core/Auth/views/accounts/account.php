{{ $app->assets(['assets:vendor/uikit/js/components/form-password.min.js']) }}

<h1>
    <a href="@route('/settingspage')">@lang('Settings')</a> / <a href="@route('/accounts/index')">@lang('Accounts')</a> / @lang('Account')
</h1>

<div class="uk-grid" data-ng-controller="account" data-uk-margin ng-cloak>

    <div class="uk-width-medium-2-4">

        <div class="app-panel">


        <div class="uk-panel app-panel-box docked uk-text-center">
            <div class="uk-thumbnail uk-rounded">
                <img src="http://www.gravatar.com/avatar/{{ md5(@$account['email']) }}?d=mm&s=100" width="100" height="100" alt="">
            </div>

            <h2 class="uk-text-truncate">@@ account.name @@</h2>
        </div>


            <div class="uk-grid" data-uk-margin>

                <div class="uk-width-medium-1-1">

                    <form class="uk-form" data-ng-submit="save()" data-ng-show="account">


                        <div class="uk-form-row">
                            <label class="uk-text-small">@lang('Name')</label>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="account.name">
                        </div>

                        <div class="uk-form-row">
                            <label class="uk-text-small">@lang('Username')</label>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="account.user">
                        </div>

                        <div class="uk-form-row">
                            <label class="uk-text-small">@lang('Email')</label>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="account.email">
                        </div>

                        <hr>

                        <div class="uk-form-row">
                            <label class="uk-text-small">@lang('New Password')</label>
                            <div class="uk-form-password uk-width-1-1">
                                <input class="uk-form-large uk-width-1-1" type="password" placeholder="@lang('Password')" data-ng-model="account.password">
                                <a href="" class="uk-form-password-toggle" data-uk-form-password>@lang('Show')</a>
                            </div>
                            <div class="uk-alert">
                                @lang('Leave the password field empty to keep your current password.')
                            </div>
                        </div>

                        <div class="uk-form-row">
                            <button class="uk-button uk-button-large uk-button-primary uk-width-1-2">@lang('Save')</button>
                        </div>

                    </form>

                </div>

            </div>
        </div>

    </div>

    <div class="uk-width-medium-1-4 uk-form">
        <h3>@lang('System')</h3>
        <div class="uk-form-row">
            <label class="uk-text-small">@lang('Language')</label>

            <div class="uk-form-controls uk-margin-small-top">
                <div class="uk-form-select">
                    <a>@@ languages[account.i18n] @@</a>
                    <select class="uk-width-1-1 uk-form-large" data-ng-model="account.i18n">
                        @foreach($languages as $lang)
                        <option value="{{ $lang['i18n'] }}">{{ $lang['language'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($app["user"]["group"]=="admin" AND @$account["_id"]!=$app["user"]["_id"])
        <div class="uk-form-row">
            <label class="uk-text-small">@lang('Group')</label>

            <div class="uk-form-controls uk-margin-small-top">
                <div class="uk-form-select">
                    <i class="uk-icon-sitemap uk-margin-small-right"></i>
                    <a>@@ account.group @@</a>
                    <select class="uk-width-1-1 uk-form-large" data-ng-model="account.group">
                        @foreach($groups as $group)
                        <option value="{{ $group }}">{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
        @endif

    </div>
</div>
<script>

    App.module.controller("account", function($scope, $rootScope, $http){

        $scope.account = {{ json_encode($account) }};

        $scope.save = function(){

            var account = angular.copy($scope.account),
                isnew   = account["_id"] ? false:true;

            $http.post(App.route("/accounts/save"), {"account": account}).success(function(data){

                if (data && Object.keys(data).length) {
                    App.notify("@lang('Account saved!')");

                    $scope.account = data;
                    $scope.account.password = "";
                }

            }).error(App.module.callbacks.error.http);
        };

        $scope.languages = {};

        @foreach($languages as $lang)
        $scope.languages['{{ $lang['i18n'] }}'] = '{{ $lang['language'] }}';
        @endforeach

    });
</script>