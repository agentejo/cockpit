<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page not found</title>
    <link rel="icon" href="@base('/favicon.ico')" type="image/x-icon">
    {{ $app->assets($app['app.assets.base'], $app['cockpit/version']) }}
</head>
<body class="uk-height-viewport uk-flex uk-flex-middle">

    <div class="uk-container uk-container-center uk-text-center uk-animation-slide-bottom">

        <h1><strong>500</strong></h1>

        <img src="@base('assets:app/media/icons/emoticon-sad.svg')" width="100" height="100">

        <p class="uk-text-large uk-margin-large">Uuuups, something went wrong.</p>

    </div>
</body>
</html>
