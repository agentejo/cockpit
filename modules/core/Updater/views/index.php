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

        <div ng-if="(data.current.version == data.local.version)">
            <h2><i class="uk-icon-thumbs-o-up"></i></h2>
            <p class="uk-text-large">
                <strong>@@ data.current.version @@</strong>
            </p>
            <p class="uk-text-large">
                @lang('You\'re running the latest version.')
            </p>
            <p>
                <button class="uk-button uk-button-large uk-button-primary" ng-click="installCurrent()"><i class="uk-icon-refresh"></i> &nbsp; @lang('Re-Install')</button>
            </p>
        </div>

        <div ng-if="(data.current.version != data.local.version)">
            <h2><i class="uk-icon-bullhorn"></i></h2>
            <p class="uk-text-large">
                <span class="uk-text-muted">@@ data.local.version @@</span> / <strong>@@ data.current.version @@</strong>
            </p>
            <p class="uk-text-large">
                @lang('A newer version exists.')
            </p>
            <p>
                <button class="uk-button uk-button-large uk-button-primary" ng-click="installCurrent()"><i class="uk-icon-magic"></i> &nbsp; @lang('Update now')</button>
            </p>
        </div>

    </div>

</div>