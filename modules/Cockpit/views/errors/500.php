<!doctype html>
<html lang="en" class="app-page-500">
<head>
    <meta charset="UTF-8">
    <title>Internal Server Error</title>
    <link rel="icon" href="@base('/favicon.ico')" type="image/x-icon">
    {{ $app->assets(['assets:app/css/style.css'], $app['cockpit/version']) }}
</head>
<body class="uk-height-viewport uk-flex uk-flex-middle">

    <div class="uk-container uk-container-center uk-text-center uk-animation-slide-bottom">

        <img src="@base('assets:app/media/icons/emoticon-sad.svg')" width="150" height="150">

        <p class="uk-text-large uk-margin-large uk-text-bold">Uuuups, something went wrong.</p>

    </div>
</body>
</html>
