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

        <div class="uk-grid">
            <div class="uk-hidden-small uk-text-center">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Your_Icon" x="0px" y="0px" width="100px" height="100px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve">
                    <g>
                        <path d="M89.421,65.965l-7.014,7.024c-0.58,0.57-1.354,0.896-2.158,0.896c-0.814,0-1.588-0.326-2.168-0.896l-7.014-7.024   c-1.201-1.191-1.201-3.125,0-4.316c1.191-1.201,3.125-1.201,4.316,0l1.517,1.517c-1.405-7.238-7.798-12.725-15.443-12.725   c-4.204,0-8.154,1.639-11.116,4.611c-1.201,1.191-3.135,1.191-4.326,0c-1.191-1.201-1.191-3.135,0-4.326   c4.123-4.123,9.61-6.393,15.443-6.393c11.177,0,20.421,8.429,21.693,19.27l1.955-1.955c1.191-1.201,3.125-1.201,4.316,0   C90.622,62.84,90.622,64.774,89.421,65.965z"/>
                    </g>
                    <g>
                        <g>
                            <path d="M76.9,81.611c-4.123,4.123-9.61,6.403-15.443,6.403c-11.177,0-20.411-8.439-21.683-19.281l-1.965,1.965    c-1.191,1.191-3.125,1.191-4.316,0c-1.191-1.191-1.191-3.125,0-4.316l7.024-7.024c1.14-1.15,3.176-1.15,4.316,0l7.024,7.024    c1.191,1.191,1.191,3.125,0,4.316c-0.601,0.59-1.374,0.896-2.158,0.896c-0.784,0-1.568-0.305-2.158-0.896l-1.527-1.527    c1.405,7.248,7.798,12.735,15.443,12.735c4.204,0,8.154-1.639,11.127-4.611c1.191-1.191,3.125-1.191,4.316,0    C78.091,78.486,78.091,80.42,76.9,81.611z"/>
                        </g>
                    </g>
                    <g>
                        <path d="M29.305,82.43C15.904,82.43,5,71.527,5,58.125c0-12.676,9.754-23.116,22.15-24.211c5.372-13.2,18.228-21.928,32.627-21.928   C79.199,11.986,95,27.786,95,47.209c0,1.518-0.097,3.043-0.289,4.532c-0.216,1.672-1.75,2.854-3.42,2.638   c-1.673-0.216-2.854-1.747-2.638-3.42c0.159-1.231,0.24-2.493,0.24-3.751c0-16.054-13.061-29.115-29.115-29.115   c-12.488,0-23.572,7.941-27.58,19.761l-0.703,2.073h-2.189c-10.034,0-18.197,8.164-18.197,18.197s8.164,18.197,18.197,18.197   c1.687,0,3.054,1.367,3.054,3.054C32.359,81.063,30.992,82.43,29.305,82.43z"/>
                    </g>
                </svg>
            </div>
            <div class="uk-width-medium-1-2">
                <div class="uk-text-bold uk-text-muted">Local</div>
                <div class="uk-h1 uk-text-muted">@@ data.local.version @@</div>

                <div class="uk-text-bold uk-margin-top">Latest stable</div>
                <div class="uk-h1">@@ data.stable.version @@</div>
                <hr>
                <div>
                    <button class="uk-button uk-button-primary" ng-click="install()">
                        <span class="tn" ng-if="(data.local.version==data.stable.version)"><i class="uk-icon-refresh"></i>&nbsp; @lang('Re-Install')</span>
                        <span class="tn" ng-if="(data.local.version!=data.stable.version)"><i class="uk-icon-magic"></i>&nbsp; @lang('Update')</span>
                    </button>

                    or
                    <a class="uk-text-danger uk-text-bold" ng-click="install('master')">@lang('Install latest development version')</a>
                </div>
            </div>
        </div>

    </div>

</div>
