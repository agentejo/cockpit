<!doctype html>
<html lang="en" data-base="@base('/')" data-route="@route('/')">
<head>
    <meta charset="UTF-8">
    <title>{{ $app['app.name'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="icon" href="@base("/assets/images/favicon.ico")" type="image/x-icon">

    @assets($app['app.assets.base'], 'app.base', 'cache:assets', 0)
    @assets($app['app.assets.backend'], 'app.backend', 'cache:assets', 0)

    @trigger('app.layout.header')

    {{ $app->assets(["assets:angular/cockpit.js"]) }}
</head>
<body>

    <nav class="uk-navbar app-top-navbar">

        <div class="app-wrapper">

            <ul class="uk-navbar-nav">
                <li class="uk-parent" data-uk-dropdown>
                    <a href="@route('/dashboard')"><i class="uk-icon-bars"></i><strong class="uk-hidden-small"> &nbsp;{{ $app['app.name'] }}</strong></a>
                    <div class="uk-dropdown uk-dropdown-navbar">
                        <ul class="uk-nav uk-nav-navbar">
                            <li>
                                <a href="@route('/accounts/account')" class="uk-clearfix">
                                    <img class="uk-rounded uk-float-left uk-margin-right" src="http://www.gravatar.com/avatar/{{ md5($app['user']['email']) }}?d=mm&s=40" width="40" height="40" alt="avatar">
                                    <div class="uk-text-truncate"><strong>{{ $app["user"]["user"] }}</strong></div>
                                    <div class="uk-text-small uk-text-muted uk-text-truncate">{{ (isset($app["user"]["email"]) ? $app["user"]["email"] : 'no email') }}</div>
                                </a>
                            </li>
                            <li class="uk-nav-divider"></li>
                            <li><a href="@route('/dashboard')"><i class="uk-icon-dashboard icon-spacer"></i> Dashboard</a></li>
                            
                            <li class="uk-nav-header uk-text-truncate">General</li>
                            
                            <li><a href="@route('/settingspage')"><i class="uk-icon-cog icon-spacer"></i> Settings</a></li>
                            <li><a href="@route('/settings/addons')"><i class="uk-icon-code-fork icon-spacer"></i> Addons</a></li>
                            @trigger("navbar")
                            <li class="uk-nav-divider"></li>
                            <li><a href="@route('/auth/logout')"><i class="uk-icon-power-off icon-spacer"></i> Logout</a></li>
                        </ul>
                    </div>
                </li>
            </ul>

            <div class="uk-navbar-flip">

                <ul class="uk-navbar-nav">
                    @foreach($app("admin")->menu('top') as $item)
                    <li class="{{ (isset($item["active"]) && $item["active"]) ? 'uk-active':'' }}">
                        <a href="{{ $item["url"] }}" title="{{ $item["title"] }}" data-uk-tooltip>{{ $item["label"] }}</a>
                    </li>
                    @endforeach

                    @trigger("navbar-primary")
                </ul>


                <div class="uk-navbar-content uk-hidden-small">
                    <i class="uk-icon-time"></i> <strong app-clock="h:i A">00:00</strong>
                </div>
            </div>
        </div>
    </nav>

    <div class="app-main">
        <div class="app-wrapper">
            {{ $content_for_layout }}
        </div>
    </div>

    <div id="app-note" class="uk-modal">
        <div class="uk-modal-dialog uk-modal-dialog-slide">
                <a href="" class="uk-modal-close uk-close"></a>
                <h3>Notice</h3>

                <div class="app-notices"></div>
        </div>
    </div>

    @trigger("app.layout.footer")

</body>
</html>