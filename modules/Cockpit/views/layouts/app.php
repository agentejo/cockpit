<?php

    $modules = new \SplPriorityQueue();
    $menuorder = $app->storage->getKey('cockpit/options', 'app.menu.order.'.$app["user"]["_id"], []);

    if ($app('admin')->data['menu.modules']->count()) {

        foreach($app('admin')->data['menu.modules'] as &$item) {
            $modules->insert($item, -1* intval(\Lime\fetch_from_array($menuorder, $item['route'], 0)));
        }
    }

?><!doctype html>
<html lang="{{ $app('i18n')->locale }}" data-base="@base('/')" data-route="@route('/')" data-version="{{ $app['cockpit/version'] }}" data-locale="{{ $app('i18n')->locale }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $app['app.name'] }}</title>
    <link rel="icon" href="@base('/favicon.ico')" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <script>
        // App constants
        var SITE_URL   = '{{ rtrim($app->pathToUrl('site:'), '/') }}';
        var ASSETS_URL = '{{ rtrim($app->pathToUrl('#uploads:'), '/') }}';
    </script>
    <script src="@base('assets:lib/fuc.js.php')"></script>
    {{ $app->assets($app('admin')->data->get('assets'), $app['debug'] ? time() : $app['cockpit/version']) }}

    <script src="@route('/cockpit.i18n.data')"></script>

    <script>
        App.$data = {{ json_encode($app('admin')->data->get('extract')) }};
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

                        <div class="uk-display-inline-block" data-uk-dropdown="delay:400,mode:'click'">

                            <a href="@route('/')" class="uk-link-muted uk-text-bold">
                                <i class="uk-icon-bars"></i>
                                <span class="app-name">{{ $app['app.name'] }}</span>
                            </a>

                            <div class="uk-dropdown app-panel-dropdown">

                                <div class="uk-grid uk-grid-gutter uk-grid-small uk-grid-divider">

                                    <div class="uk-width-medium-1-3">

                                        <div class="uk-margin">
                                            <span class="uk-badge uk-badge-primary">@lang('System')</span>
                                        </div>

                                        <ul class="uk-nav uk-nav-side uk-nav-dropdown app-nav">

                                            <li class="{{ $app['route'] == '/cockpit/dashboard' ? 'uk-active':'' }}"><a href="@route('/cockpit/dashboard')"><img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/dashboard.svg')" width="30" height="30" data-uk-svg alt="assets" /> @lang('Dashboard')</a></li>

                                            <li class="{{ strpos($app['route'],'/assetsmanager')===0 ? 'uk-active':'' }}"><a href="@route('/assetsmanager')"><img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/assets.svg')" width="30" height="30" data-uk-svg alt="assets" /> @lang('Assets')</a></li>

                                            @hasaccess?('cockpit', 'accounts')
                                            <li class="uk-nav-divider"></li>
                                            <li class="{{ strpos($app['route'],'/accounts')===0 ? 'uk-active':'' }}"><a href="@route('/accounts')"><img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/accounts.svg')" width="30" height="30" data-uk-svg alt="assets" /> @lang('Accounts')</a></li>
                                            @end

                                            @hasaccess?('cockpit', 'finder')
                                            <li class="uk-nav-divider"></li>
                                            <li class="{{ strpos($app['route'],'/finder')===0 ? 'uk-active':'' }}"><a href="@route('/finder')"><img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/finder.svg')" width="30" height="30" data-uk-svg alt="assets" /> @lang('Finder')</a></li>
                                            @end

                                            @hasaccess?('cockpit', 'settings')
                                            <li class="{{ strpos($app['route'],'/settings')===0 ? 'uk-active':'' }}"><a href="@route('/settings')"><img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/settings.svg')" width="30" height="30" data-uk-svg alt="assets" /> @lang('Settings')</a></li>
                                            @end

                                        </ul>

                                        @trigger('cockpit.menu.aside')

                                    </div>

                                    <div class="uk-grid-margin uk-width-medium-2-3">

                                        <div class="uk-margin">
                                            <span class="uk-badge uk-badge-primary">@lang('Modules')</span>
                                        </div>

                                        @if($app('admin')->data['menu.modules']->count())
                                        <ul class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-grid-gutter uk-text-center" data-modules-menu data-uk-sortable>

                                            @foreach(clone $modules as $item)
                                            <li class="uk-width-1-2 uk-width-medium-1-3" data-route="{{ $item['route'] }}">
                                                <a class="uk-display-block uk-panel-box {{ (@$item['active']) ? 'uk-bg-primary uk-contrast':'uk-panel-framed' }}" href="@route($item['route'])">
                                                    <div class="uk-svg-adjust">
                                                        @if(preg_match('/\.svg$/i', $item['icon']))
                                                        <img src="@url($item['icon'])" alt="@lang($item['label'])" data-uk-svg width="30px" height="30px" />
                                                        @else
                                                        <img src="@url('assets:app/media/icons/module.svg')" alt="@lang($item['label'])" data-uk-svg width="30px" height="30px" />
                                                        @endif
                                                    </div>
                                                    <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang($item['label'])</div>
                                                </a>
                                            </li>
                                            @endforeach

                                        </ul>
                                        @endif

                                        @trigger('cockpit.menu.main')

                                    </div>

                                </div>

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
                                <a class="uk-svg-adjust {{ (@$item['active']) ? 'uk-active':'' }}" href="@route($item['route'])" title="@lang($item['label'])" data-uk-tooltip="offset:10">
                                    @if(preg_match('/\.svg$/i', $item['icon']))
                                    <img src="@url($item['icon'])" alt="@lang($item['label'])" data-uk-svg width="20px" height="20px" />
                                    @else
                                    <img src="@url('assets:app/media/icons/module.svg')" alt="@lang($item['label'])" data-uk-svg width="20px" height="20px" />
                                    @endif

                                    @if($item['active'])
                                    <span class="uk-text-small uk-margin-small-left uk-text-bolder">{{ $item['label'] }}</span>
                                    @endif
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div>

                        <div data-uk-dropdown="delay:150">

                            <a class="uk-display-block" href="@route('/accounts/account')" style="width:30px;height:30px;" riot-mount>
                                <cp-gravatar email="{{ $app['user']['email'] }}" size="30" alt="{{ $app["user"]["name"] ? $app["user"]["name"] : $app["user"]["user"] }}"></cp-gravatar>
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

    <div class="app-main">
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
    <script type="riot/tag" src="@base($component)"></script>
    @endforeach

</body>
</html>
