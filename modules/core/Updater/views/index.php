{{ $app->assets(['updater:assets/js/index.js'], $app['cockpit/version']) }}

<h1><a href="@route('/settingspage')">@lang('Settings')</a> / @lang('Update')</h1>

<div class="uk-margin-large-top" data-ng-controller="updater" ng-cloak>

    <div class="uk-text-center uk-width-medium-1-2 uk-container-center" ng-show="loading">

        <h2><i class="uk-icon-spinner uk-icon-spin"></i></h2>
        <p class="uk-text-large">
            @lang('Getting information...')
        </p>

    </div>

    <div class="uk-text-center uk-width-medium-1-2 uk-container-center uk-animation-shake" ng-if="data && data.error">

        <h2><i class="uk-icon-bolt"></i></h2>
        <p class="uk-text-large">
            @@ data.error @@
        </p>

    </div>


    <div ng-if="data && !data.error">

        <div class="uk-grid" data-uk-grid-margin>

            <div class="uk-width-medium-1-2">

                <div class="app-panel">
                    <div class="uk-text-bold uk-text-muted">Local</div>
                    <div class="uk-h1 uk-text-muted">@@ data.local.version @@</div>

                    <div class="uk-text-bold uk-margin-top">Latest stable</div>
                    <div class="uk-h1">@@ data.stable.version @@</div>

                    <div class="uk-alert">
                        @lang('Don\'t forget to backup the cockpit folder before any update.')
                    </div>
                </div>

                <div class="uk-margin-top">
                    <button class="uk-button uk-button-primary" ng-click="install()">
                        <span class="tn" ng-if="(data.local.version==data.stable.version)"><i class="uk-icon-refresh"></i>&nbsp; @lang('Re-Install')</span>
                        <span class="tn" ng-if="(data.local.version!=data.stable.version)"><i class="uk-icon-cloud-download"></i>&nbsp; @lang('Update')</span>
                    </button>

                    or
                    <a ng-click="install('master')">@lang('Install latest development version')</a> <span class="uk-badge app-badge">@lang('Danger')</span>
                </div>
            </div>
        </div>

    </div>

</div>
