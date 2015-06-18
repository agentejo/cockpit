<!doctype html>
<html lang="{{ $app('i18n')->locale }}" data-base="@base('/')" data-route="@route('/')" data-version="{{ $app['cockpit/version'] }}" data-locale="{{ $app('i18n')->locale }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $app['app.name'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <script>

        // App constants

        var SITE_URL = '{{ $app->pathToUrl('site:') }}';

    </script>

    {{ $app->assets($app['app.assets.backend'], $app['cockpit/version']) }}

    <script>

        (function(App){

            App = App || {};

            App.$user = {{ json_encode($app["user"]) }};

        })(App);

    </script>

    @trigger('app.layout.header')
    @block('app.layout.header')

</head>
<body>

    <div class="app-header">

        <div class="app-header-topbar">

            <div class="uk-container uk-container-center">

                <div class="uk-grid">

                    <div>

                        <div class="uk-position-inline-block" data-uk-dropdown>

                            <strong class="uk-contrast">
                                <a href="#">
                                    <i class="uk-icon-bars"></i>
                                    <span>{{ $app['app.name'] }}</span>
                                </a>
                            </strong>

                            <div class="uk-dropdown app-panel-dropdown">

                                <div class="uk-grid uk-grid-gutter uk-grid-small uk-grid-divider">

                                    <div class="uk-grid-margin uk-width-medium-1-3">

                                        <div class="uk-margin">
                                            <span class="uk-badge uk-badge-primary">@lang('System')</span>
                                        </div>

                                        <ul class="uk-nav uk-nav-dropdown">

                                            <li><a href="@route('/')"><i class="uk-icon-justify uk-icon-dashboard"></i> Dashboard</a></li>

                                            @hasaccess?('cockpit', 'manage.media')
                                            <li><a href="@route('/finder')"><i class="uk-icon-justify uk-icon-folder"></i> Finder</a></li>
                                            @end

                                            <li class="uk-nav-divider"></li>

                                            @hasaccess?('cockpit', 'manage.settings')
                                            <li><a href="@route('/settings')"><i class="uk-icon-justify uk-icon-cog"></i> Settings</a></li>
                                            @end
                                        </ul>

                                    </div>

                                    <div class="uk-grid-margin uk-width-medium-2-3">

                                        <div class="uk-margin">
                                            <span class="uk-badge uk-badge-primary">@lang('Modules')</span>
                                        </div>

                                        @if($app['app.menu.modules']->count())
                                        <div class="uk-grid uk-grid-small uk-grid-gutter uk-grid-width-1-2 uk-grid-width-medium-1-3 uk-text-center">

                                            @foreach($app['app.menu.modules'] as $item)
                                            <div class="uk-grid-margin">
                                                <a class="uk-panel-framed" href="@route($item['route'])">
                                                    <div class="uk-text-large">
                                                        <i class="uk-icon-{{ isset($item['icon']) ? $item['icon']:'cube' }}"></i>
                                                    </div>
                                                    <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang($item['label'])</div>
                                                </a>
                                            </div>
                                            @endforeach
                                            
                                        </div>
                                        @endif

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <nav class="uk-navbar">

            <div class="uk-container uk-container-center">

                <div class="uk-navbar-content uk-hidden-small" riot-mount>
                    <cockpit-search></cockpit-search>
                </div>

                <div class="uk-navbar-flip">

                    <div class="uk-navbar-content" data-uk-dropdown="{delay:150}">

                        <a href="@route('/accounts/account')"><img class="uk-border-circle uk-margin-right" src="{{ $app('utils')->gravatar($app['user']['email'], 30) }}" width="30" height="30" alt="avatar"></a>

                        <div class="uk-dropdown uk-dropdown-navbar uk-dropdown-flip">
                            <ul class="uk-nav uk-nav-navbar">
                                <li class="uk-nav-header uk-text-truncate">{{ $app["user"]["name"] ? $app["user"]["name"] : $app["user"]["user"] }}</li>
                                <li><a href="@route('/accounts/account')">@lang('Account')</a></li>
                                <li class="uk-nav-divider"></li>
                                <li><a href="@route('/auth/logout')">@lang('Logout')</a></li>
                            </ul>
                        </div>

                    </div>
                </div>

            </div>
        </nav>
    </div>

    <div class="app-main">
        <div class="uk-container uk-container-center">
            {{ $content_for_layout }}
        </div>
    </div>

    @trigger('app.layout.footer')
    @block('app.layout.footer')


    <!-- WEB COMPONENTS -->
    @foreach($app->retrieve('app.assets.components', []) as $component)
    <script type="riot/tag" src="@base($component)"></script>
    @endforeach

</body>
</html>
