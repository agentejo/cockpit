<!doctype html>
<html class="uk-height-1-1" lang="en" data-base="@base('/')" data-route="@route('/')">
<head>
    <meta charset="UTF-8">
    <title>@lang('Authenticate Please!')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    {{ $app->assets($app['app.assets.base'], $app['cockpit/version']) }}
    {{ $app->assets(['assets:lib/uikit/js/components/form-password.min.js'], $app['cockpit/version']) }}

</head>
<body class="uk-height-viewport uk-flex uk-flex-middle uk-bg-light-radial">

    <div class="uk-width-medium-1-2 uk-width-large-1-3 uk-container-center" riot-view>

        <div class="uk-container uk-container-center">

            <form class="uk-form" method="post" action="@route('/auth/check')" onsubmit="{ submit }">

                <div class="uk-panel-box uk-panel-space uk-panel-card uk-animation-scale">

                    <div name="header" class="uk-panel-box-header uk-text-bold uk-text-center">

                        <h2 class="uk-text-bold">{{ $app['app.name'] }}</h2>

                        <div class="uk-animation-shake uk-margin-top" if="{ error }">
                            <strong>{ error }</strong>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <input name="user" class="uk-form-large uk-width-1-1" type="text" placeholder="@lang('Username')" required>
                    </div>

                    <div class="uk-form-row">
                        <div class="uk-form-password uk-width-1-1">
                            <input name="password" class="uk-form-large uk-width-1-1" type="password" placeholder="@lang('Password')" required>
                            <a href="#" class="uk-form-password-toggle" data-uk-form-password>@lang('Show')</a>
                        </div>
                    </div>

                    <div class="uk-margin-large-top">
                        <button class="uk-button uk-button-large uk-button-primary uk-width-1-1">@lang('Authenticate')</button>
                    </div>
                </div>

            </form>

        </div>

        <script type="view/script">

            this.error = false;

            submit() {

                this.error = false;

                App.request('/auth/check', {"auth":{"user":this.user.value, "password":this.password.value}}).then(function(data){

                    if (data && data.success) {

                        App.reroute('/');

                    } else {

                        this.error = 'Login failed';

                        App.$(this.header).addClass('uk-bg-danger uk-contrast');
                    }

                    this.update();

                }.bind(this));

                return false;
            }

        </script>
    </div>

</body>
</html>
