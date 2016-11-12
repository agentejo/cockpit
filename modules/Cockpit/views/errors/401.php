<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unauthorized</title>
    <link rel="icon" href="@base('/favicon.ico')" type="image/x-icon">
    {{ $app->assets($app['app.assets.base'], $app['cockpit/version']) }}
</head>
<body class="uk-height-viewport uk-flex uk-flex-middle">

    <div class="uk-container uk-container-center uk-text-center uk-animation-slide-bottom">

        <h1><strong>401</strong></h1>

        <img src="@base('assets:app/media/icons/lock.svg')" width="100" height="100">

        <p class="uk-text-large uk-margin-large">Sorry, you are not authorized.</p>
        <p><a class="uk-button uk-button-outline uk-button-primary uk-button-large" href="@route('/')">Get back</a></p>

    </div>
</body>
</html>
