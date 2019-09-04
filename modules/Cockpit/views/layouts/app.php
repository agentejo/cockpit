<?php

    // Generate title
    $_title = [];

    foreach (explode('/', $app['route']) as $part) {
        if (trim($part)) $_title[] = $app('i18n')->get(ucfirst($part));
    }

    // sort modules by label
    $modules = $app('admin')->data['menu.modules']->getArrayCopy();

    usort($modules, function($a, $b) {
        return mb_strtolower($a['label']) <=> mb_strtolower($b['label']);
    });

?><!doctype html>
<html lang="{{ $app('i18n')->locale }}" data-base="@base('/')" data-route="@route('/')" data-version="{{ $app['cockpit/version'] }}" data-locale="{{ $app('i18n')->locale }}">
<head>
    <meta charset="UTF-8">
    <title>{{ implode(' &raquo; ', $_title).(count($_title) ? ' - ':'').$app['app.name'] }}</title>
    <link rel="icon" href="@base('/favicon.png')" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <script>
        // App constants
        var SITE_URL   = '{{ rtrim($app->filestorage->getUrl('site://'), '/') }}';
        var ASSETS_URL = '{{ rtrim($app->filestorage->getUrl('assets://'), '/') }}';
    </script>

    {{ $app->assets($app('admin')->data->get('assets'), $app['debug'] ? time() : $app['cockpit/version']) }}

    <script src="@route('/cockpit.i18n.data')"></script>

    <script>
        App.$data = {{ json_encode($app('admin')->data->get('extract')) }};
        UIkit.modal.labels.Ok = App.i18n.get(UIkit.modal.labels.Ok);
        UIkit.modal.labels.Cancel = App.i18n.get(UIkit.modal.labels.Cancel);
    </script>

    @trigger('app.layout.header')
    @block('app.layout.header')

</head>
<body>

    <div class="app-header" data-uk-sticky="{animation: 'uk-animation-slide-top', showup:true}">

        <div class="app-header-topbar">

            <div class="uk-container uk-container-center">

                <div class="uk-grid uk-flex-middle">

                    <div>

                        <div data-uk-dropdown="delay:400,mode:'click'">

                            <a href="@route('/')" class="uk-link-muted uk-text-bold app-name-link uk-flex uk-flex-middle">
                                <span class="app-logo"></span>
                                <span class="app-name">{{ $app['app.name'] }}</span>
                            </a>

                            <div class="uk-dropdown app-panel-dropdown">

                                @if($app('admin')->data['menu.modules']->count())
                                <div class="uk-visible-small">
                                    <span class="uk-text-upper uk-text-small uk-text-bold">@lang('Modules')</span>
                                </div>

                                <ul class="uk-grid uk-grid-match uk-grid-small uk-text-center uk-visible-small uk-margin-bottom">

                                    @foreach($modules as $item)
                                    <li class="uk-width-1-2 uk-width-medium-1-3 uk-grid-margin" data-route="{{ $item['route'] }}">
                                        <a class="uk-display-block uk-panel-box uk-panel-card-hover uk-panel-space {{ (@$item['active']) ? 'uk-bg-primary uk-contrast':'' }}" href="@route($item['route'])">
                                            <div class="uk-svg-adjust">
                                                @if(preg_match('/\.svg$/i', $item['icon']))
                                                <img src="@url($item['icon'])" alt="@lang($item['label'])" data-uk-svg width="40" height="40" />
                                                @else
                                                <img src="@url('assets:app/media/icons/module.svg')" alt="@lang($item['label'])" data-uk-svg width="40" height="40" />
                                                @endif
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang($item['label'])</div>
                                        </a>
                                    </li>
                                    @endforeach

                                    @trigger('cockpit.menu.modules')

                                </ul>
                                @endif


                                <div>
                                    <span class="uk-text-upper uk-text-small uk-text-bold">@lang('System')</span>
                                </div>

                                <ul class="uk-grid uk-grid-small uk-grid-width-1-2 uk-grid-width-medium-1-4 uk-text-center">

                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-card-hover uk-panel-box uk-panel-space {{ ($app['route'] == '/cockpit/dashboard') ? 'uk-bg-primary uk-contrast':'' }}" href="@route('/cockpit/dashboard')">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/dashboard.svg')" width="40" height="40" data-uk-svg alt="assets" />
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang('Dashboard')</div>
                                        </a>
                                    </li>

                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-card-hover uk-panel-box uk-panel-space {{ (strpos($app['route'],'/assetsmanager')===0) ? 'uk-bg-primary uk-contrast':'' }}" href="@route('/assetsmanager')">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/assets.svg')" width="40" height="40" data-uk-svg alt="assets" /> 
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang('Assets')</div>
                                        </a>
                                    </li>

                                    @hasaccess?('cockpit', 'finder')
                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-card-hover uk-panel-box uk-panel-space {{ (strpos($app['route'],'/finder')===0) ? 'uk-bg-primary uk-contrast':'' }}" href="@route('/finder')">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/finder.svg')" width="40" height="40" data-uk-svg alt="assets" /> 
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang('Finder')</div>
                                        </a>
                                    </li>
                                    @end

                                    @hasaccess?('cockpit', 'settings')
                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-box uk-panel-card-hover uk-panel-space {{ (strpos($app['route'],'/settings')===0) ? 'uk-bg-primary uk-contrast':'' }}" href="@route('/settings')">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/settings.svg')" width="40" height="40" data-uk-svg alt="assets" />
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang('Settings')</div>
                                        </a>
                                    </li>
                                    @end

                                    @hasaccess?('cockpit', 'accounts')
                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-box uk-panel-card-hover uk-panel-space {{ (strpos($app['route'],'/accounts')===0) ? 'uk-bg-primary uk-contrast':'' }}" href="@route('/accounts')">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/accounts.svg')" width="40" height="40" data-uk-svg alt="assets" /> 
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang('Accounts')</div>
                                        </a>
                                    </li>
                                    @end

                                    @trigger('cockpit.menu.system')

                                </ul>

                                @trigger('cockpit.menu')

                            </div>

                        </div>

                    </div>

                    <div class="uk-flex-item-1" riot-mount>
                        <cp-search></cp-search>
                    </div>

                    @if($app('admin')->data['menu.modules']->count())
                    <div class="uk-hidden-small">
                        <ul class="uk-subnav app-modulesbar">
                            @foreach($modules as $item)
                            <li>
                                <a class="uk-svg-adjust {{ (@$item['active']) ? 'uk-active':'' }}" href="@route($item['route'])" title="@lang($item['label'])" aria-label="@lang($item['label'])" data-uk-tooltip="offset:10">
                                    @if(preg_match('/\.svg$/i', $item['icon']))
                                    <img src="@url($item['icon'])" alt="@lang($item['label'])" data-uk-svg width="20px" height="20px" />
                                    @else
                                    <img src="@url('assets:app/media/icons/module.svg')" alt="@lang($item['label'])" data-uk-svg width="20px" height="20px" />
                                    @endif

                                    @if($item['active'])
                                    <span class="uk-text-small uk-margin-small-left uk-text-bolder">@lang($item['label'])</span>
                                    @endif
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div>

                        <div data-uk-dropdown="mode:'click'">

                            <a class="uk-display-block" href="@route('/accounts/account')" style="width:30px;height:30px;" aria-label="@lang('Edit account')" riot-mount>
                                <cp-gravatar email="{{ $app['user/email'] }}" size="30" alt="{{ $app["user/name"] ?? $app["user/user"] }}"></cp-gravatar>
                            </a>

                            <div class="uk-dropdown uk-dropdown-navbar uk-dropdown-flip">
                                <ul class="uk-nav uk-nav-navbar">
                                    <li class="uk-nav-header uk-text-truncate">{{ $app["user"]["name"] ? $app["user"]["name"] : $app["user"]["user"] }}</li>
                                    <li><a href="@route('/accounts/account')">@lang('Account')</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li class="uk-nav-item-danger"><a href="@route('/auth/logout')">@lang('Logout')</a></li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="app-main" role="main">
        <div class="uk-container uk-container-center">
            @trigger('app.layout.contentbefore')
            {{ $content_for_layout }}
            @trigger('app.layout.contentafter')
        </div>
    </div>

    @trigger('app.layout.footer')
    @block('app.layout.footer')

    <!-- RIOT COMPONENTS -->
    @foreach($app('admin')->data['components'] as $component)
    <script type="riot/tag" src="@base($component)?nc={{ $app['debug'] ? time() : $app['cockpit/version'] }}"></script>
    @endforeach

    @foreach($app('fs')->ls('*.tag', '#config:tags') as $component)
    <script type="riot/tag" src="{{$app->pathToUrl('#config:tags/'.$component->getBasename())}}?nc={{ $app['debug'] ? time() : $app['cockpit/version'] }}"></script>
    @endforeach

    @render('cockpit:views/_partials/logincheck.php')

</body>
</html>
