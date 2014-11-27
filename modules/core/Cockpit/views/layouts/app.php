<!doctype html>
<html lang="{{ $app("i18n")->locale }}" data-base="@base('/')" data-route="@route('/')" data-version="{{ $app['cockpit/version'] }}" data-locale="{{ $app("i18n")->locale }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $app['app.name'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="icon" href="@base("/assets/images/favicon.ico")" type="image/x-icon">

    @assets($app['app.assets.base'], 'app.base'.$app['cockpit/version'], 'cache:assets', 360, $app['cockpit/version'])
    @assets($app['app.assets.backend'], 'app.backend'.$app['cockpit/version'], 'cache:assets', 360, $app['cockpit/version'])

    {{ $app->assets(["assets:js/angular/cockpit.js"], $app['cockpit/version']) }}

    @trigger('app.layout.header')

    @block('header')
</head>
<body>

    <nav class="uk-navbar app-top-navbar">

        <div class="app-wrapper">

            <ul class="uk-navbar-nav">
                <li class="uk-parent" data-uk-dropdown>
                    <a href="@route('/dashboard')"><i class="uk-icon-bars"></i><strong class="uk-hidden-small"> &nbsp;{{ $app['app.name'] }}</strong></a>
                    <div class="uk-dropdown uk-dropdown-navbar">
                        <ul class="uk-nav uk-nav-navbar uk-nav-parent-icon">
                            <li>
                                <a href="@route('/accounts/account')" class="uk-clearfix">
                                    <img class="uk-rounded uk-float-left uk-margin-right" src="http://www.gravatar.com/avatar/{{ md5($app['user']['email']) }}?d=mm&s=40" width="40" height="40" alt="avatar">
                                    <div class="uk-text-truncate"><strong>{{ $app["user"]["name"] ? $app["user"]["name"] : $app["user"]["user"] }}</strong></div>
                                    <div class="uk-text-small uk-text-truncate">{{ (isset($app["user"]["email"]) ? $app["user"]["email"] : 'no email') }}</div>
                                </a>
                            </li>
                            <li class="uk-nav-divider"></li>
                            <li><a href="@route('/dashboard')"><i class="uk-icon-dashboard icon-spacer"></i> @lang('Dashboard')</a></li>

                            <li class="uk-nav-header uk-text-truncate">@lang('General')</li>

                            <li><a href="@route('/settingspage')"><i class="uk-icon-cog icon-spacer"></i> @lang('Settings')</a></li>
                            @if($app["user"]["group"]=="admin")
                            <li><a href="@route('/settings/addons')"><i class="uk-icon-code-fork icon-spacer"></i> @lang('Addons')</a></li>
                            @endif
                            @trigger("navbar")
                            <li class="uk-nav-divider"></li>
                            <li><a href="@route('/auth/logout')"><i class="uk-icon-power-off icon-spacer"></i> @lang('Logout')</a></li>
                        </ul>
                    </div>
                </li>
            </ul>

            <div class="uk-navbar-content uk-hidden-small">
                <form id="frmCockpitSearch" class="uk-search" data-uk-search="{source:'@route('/cockpit-globalsearch')', msgMoreResults:false, msgResultsHeader: '@lang('Search Results')', msgNoResults: '@lang('No results found')'}" onsubmit="return false;">
                    <input class="uk-search-field" type="search" placeholder="@lang('Search...')" autocomplete="off">
                </form>
            </div>

            <div class="uk-navbar-flip">

                <ul class="uk-navbar-nav app-top-navbar-links">
                    @foreach($app("admin")->menu('top') as $item)
                    <li class="{{ (isset($item["active"]) && $item["active"]) ? 'uk-active':'' }}" {{ (isset($item["children"]) && count($item["children"])) ? 'data-uk-dropdown':'' }}>
                        <a href="{{ $item["url"] }}" title="{{ $item["title"] }}" data-uk-tooltip>{{ $item["label"] }}</a>
                        @if(isset($item["children"]) && count($item["children"]))
                            <div class="uk-dropdown uk-dropdown-navbar">
                                <ul class="uk-nav uk-nav-navbar uk-nav-parent-icon">

                                @foreach($item["children"] as $child)
                                    @if(isset($child["header"]))
                                        <li class="uk-nav-header uk-text-truncate">{{ $child["header"] }}</li>
                                    @endif
                                    @if(isset($child["divider"]))
                                        <li class="uk-nav-divider"></li>
                                    @endif
                                    <li><a href="{{ $child["url"] }}">{{ $child["label"] }}</a></li>
                                @endforeach
                                </ul>
                            </div>
                        @endif
                    </li>
                    @endforeach

                    @trigger("navbar-primary")
                </ul>
            </div>
        </div>
    </nav>

    <div class="app-main">
        <div class="app-wrapper">
            {{ $content_for_layout }}
        </div>
    </div>

    <script charset="utf-8" src="@route('/i18n-js')"></script>

    @trigger("app.layout.footer")
    @block('footer')

</body>
</html>
