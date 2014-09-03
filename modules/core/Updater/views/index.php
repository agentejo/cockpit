{{ $app->assets(['updater:assets/js/index.js'], $app['cockpit/version']) }}

<h1><a href="@route('/settingspage')">@lang('Settings')</a> / @lang('Update')</h1>

<div class="app-panel" data-ng-controller="updater" ng-cloak>

    <div class="uk-text-center" ng-show="loading">

        <h2><i class="uk-icon-spinner uk-icon-spin"></i></h2>
        <p class="uk-text-large">
            @lang('Getting information...')
        </p>

    </div>

    <div class="uk-text-center uk-animation-shake" ng-if="data && data.error">

        <h2><i class="uk-icon-bolt"></i></h2>
        <p class="uk-text-large">
            @@ data.error @@
        </p>

    </div>


    <div class="uk-text-center uk-animation-fade" ng-if="data && !data.error">

        <div >
            <h2><i class="uk-icon-bullhorn"></i></h2>
            <p class="uk-text-large">
                <span class="uk-text-muted">@@ data.local.version @@</span> / <strong>@@ data.stable.version @@</strong>
            </p>
            <p class="uk-text-large">
                @lang('Choose version to install'):
            </p>
            <p>
                <button class="uk-button uk-button-large uk-button-primary" ng-click="install()"><i class="uk-icon-magic"></i> &nbsp; @lang('Stable') (@@ data.stable.version @@)</button>
                <button class="uk-button uk-button-large uk-button-danger" ng-click="install('master')"><i class="uk-icon-bolt"></i> &nbsp; @lang('Development')</button>
            </p>
        </div>

    </div>

</div>
