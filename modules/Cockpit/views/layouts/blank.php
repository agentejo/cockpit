<!doctype html>
<html class="uk-height-1-1" lang="{{ $app('i18n')->locale }}" data-base="@base('/')" data-route="@route('/')">
    <head>
        <meta charset="UTF-8">
        <title>@lang('Authenticate Please!')</title>
        <link rel="icon" href="@base('/favicon.png')" type="image/png">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        {{ $app->assets($app['app.assets.base'], $app['cockpit/version']) }}
        {{ $app->assets(['assets:lib/uikit/js/components/form-password.min.js'], $app['cockpit/version']) }}

    </head>
    <body class="login-page uk-height-viewport uk-flex uk-flex-middle uk-flex-center">

    {{ $content_for_layout }}

    </body>
</html>
