<!doctype html>
<html class="uk-height-1-1" lang="en" data-base="@base('/')" data-route="@route('/')">
<head>
    <meta charset="UTF-8">
    <title>@lang('Authenticate Please!')</title>
    <link rel="icon" href="@base('/favicon.ico')" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    {{ $app->assets($app['app.assets.base'], $app['cockpit/version']) }}
    {{ $app->assets(['assets:lib/uikit/js/components/form-password.min.js'], $app['cockpit/version']) }}

    <style>

        html, body {
            background: #0e0f19; 
        }

        .login-container {
            width: 360px;
            max-width: 90%;
        }

        .uk-panel-box-header {
            border-bottom: none;
        }

        svg path,
        svg rect,
        svg circle {
            fill: currentColor;
        }

    </style>

</head>
<body class="login-page uk-height-viewport uk-flex uk-flex-middle uk-flex-center">

    <div class="uk-position-relative login-container uk-animation-scale" riot-view>

        <form class="uk-form" method="post" action="@route('/auth/check')" onsubmit="{ submit }">

            <div class="uk-panel-box uk-panel-space uk-panel-card uk-nbfc uk-text-center uk-animation-slide-bottom" if="{$user}">

                <h2 class="uk-text-bold uk-text-truncate">@lang('Welcome back!')</h2>

                <p>
                    <cp-gravatar email="{ $user.email }" size="80" alt="{ $user.name || $user.user }" if="{$user}"></cp-gravatar>
                </p>

            </div>

            <div id="login-dialog" class="uk-panel-box uk-panel-space uk-panel-card uk-nbfc" show="{!$user}">

                <div name="header" class="uk-panel-box-header uk-text-bold uk-text-center">

                    <p>
                        <img src="@url('assets:app/media/icons/login.svg')" width="80" alt="Login" data-uk-svg />
                    </p>

                    <h2 class="uk-text-bold uk-text-truncate"><span>{{ $app['app.name'] }}</span></h2>

                    <div class="uk-animation-shake uk-margin-top" if="{ error }">
                        <strong>{ error }</strong>
                    </div>
                </div>

                <div class="uk-form-row">
                    <input ref="user" class="uk-form-large uk-width-1-1" type="text" placeholder="@lang('Username')" required>
                </div>

                <div class="uk-form-row">
                    <div class="uk-form-password uk-width-1-1">
                        <input ref="password" class="uk-form-large uk-width-1-1" type="password" placeholder="@lang('Password')" required>
                        <a href="#" class="uk-form-password-toggle" data-uk-form-password>@lang('Show')</a>
                    </div>
                </div>

                <div class="uk-margin-large-top">
                    <button class="uk-button uk-button-outline uk-button-large uk-button-primary uk-width-1-1">@lang('Authenticate')</button>
                </div>
            </div>

            <p class="uk-text-center" if="{!$user}"><a href="@route('/auth/forgotpassword')">@lang('Forgot Password?')</a></p>


        </form>


        <script type="view/script">

            this.error = false;
            this.$user  = null;

            submit(e) {

                e.preventDefault();

                this.error = false;

                App.request('/auth/check', {"auth":{"user":this.refs.user.value, "password":this.refs.password.value}}).then(function(data){

                    if (data && data.success) {

                        this.$user = data.user;

                        setTimeout(function(){
                            App.reroute('/');
                        }, 2000)
                        
                    } else {

                        this.error = 'Login failed';

                        App.$(this.header).addClass('uk-bg-danger uk-contrast');
                        App.$('#login-dialog').removeClass('uk-animation-shake');

                        setTimeout(function(){
                            App.$('#login-dialog').addClass('uk-animation-shake');
                        }, 50);
                    }

                    this.update();

                }.bind(this));

                return false;
            }

        </script>

    </div>

</body>
</html>
