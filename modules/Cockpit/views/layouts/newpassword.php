<!doctype html>
<html class="uk-height-1-1 uk-bg-dark" lang="en" data-base="@base('/')" data-route="@route('/')">
<head>
    <meta charset="UTF-8">
    <title>@lang('Password Reset!')</title>
    <link rel="icon" href="@base('/favicon.ico')" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    {{ $app->assets($app['app.assets.base'], $app['cockpit/version']) }}
    {{ $app->assets(['assets:lib/uikit/js/components/form-password.min.js'], $app['cockpit/version']) }}

    <style>

        .container {
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
<body class="newpassword-page uk-height-viewport uk-flex uk-flex-middle uk-flex-center">

    <div class="uk-position-relative container uk-animation-slide-bottom" riot-view>

        <form class="uk-form" method="post" action="@route('/auth/check')" onsubmit="{ submit }">

            <div id="newpassword-dialog" class="uk-panel-box uk-panel-space uk-panel-card uk-nbfc" show="{!$user}">

                <div name="header" class="uk-panel-box-header uk-text-bold uk-text-center">

                    <p>
                        <img src="@url('assets:app/media/icons/password-reset.svg')" width="80" alt="User" data-uk-svg />
                    </p>

                    <h2 class="uk-text-bold uk-text-truncate"><span>{{ $app['app.name'] }}</span></h2>

                    <p class="uk-text-primary">{ user.name || user.user}</p>

                    <div class="uk-animation-shake uk-margin-top" if="{ error }">
                        <strong>{ error }</strong>
                    </div>
                </div>

                <div class="uk-text-center uk-animation-slide-bottom" if="{ reset }">
                    <a class="uk-button uk-button-outline uk-button-large uk-button-primary uk-width-1-1" href="{{ $app->retrieve('cockpit.login.url', $app->routeUrl('/auth/login')) }}">@lang('Go to login')</a>
                </div>

                <div class="uk-form-row" show="{ !reset }">
                    <input ref="password" class="uk-form-large uk-width-1-1 uk-text-center" type="password" placeholder="@lang('New Password')" required>
                </div>

                <div class="uk-margin-top" show="{ !reset }">
                    <button class="uk-button uk-button-outline uk-button-large uk-button-primary uk-width-1-1">@lang('Reset')</button>
                </div>
            </div>

        </form>


        <script type="view/script">

            this.error = false;
            this.reset = false;
            this.user  = {{ json_encode($user) }};

            submit(e) {

                e.preventDefault();

                this.error = false;
                this.reset = false;

                App.request('/auth/resetpassword', {
                    token: '{{ $token }}',
                    password:this.refs.password.value
                }).then(function(data){

                    this.reset = true;
                    this.update();

                }.bind(this)).catch(function(data) {
                    
                    this.error = 'Reset failed';

                    App.$('#login-dialog').removeClass('uk-animation-shake');

                    setTimeout(function(){
                        App.$('#newpassword-dialog').addClass('uk-animation-shake');
                    }, 50);

                    this.update();

                }.bind(this));

                return false;
            }

        </script>

    </div>

</body>
</html>
