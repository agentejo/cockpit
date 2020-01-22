<!doctype html>
<html lang="en" class="app-page-401">
<head>
    <meta charset="UTF-8">
    <title>Unauthorized</title>
    <link rel="icon" href="@base('/favicon.ico')" type="image/x-icon">
    {{ $app->assets(['assets:app/css/style.css'], $app['cockpit/version']) }}
</head>
<body class="uk-height-viewport uk-flex uk-flex-middle">

    <div class="uk-container uk-container-center uk-text-center uk-animation-slide-bottom">

        <img src="@base('assets:app/media/icons/lock.svg')" width="150" height="150">

        <p class="uk-text-large uk-margin-large uk-text-bold">Sorry, you are not authorized.</p>
        <p><a class="uk-button uk-button-link" href="@route('/')">Get back</a></p>

    </div>
</body>
</html>
