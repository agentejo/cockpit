<!doctype html>
<html lang="en" data-base="@base('/')" data-route="@route('/')">
<head>
    <meta charset="UTF-8">
    <title>@lang('Authenticate Please!')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="icon" href="@base("/favicon.ico")" type="image/x-icon">

    @assets($app['app.assets.base'], 'app.base', 'cache:assets', 30)
    @assets(['assets:vendor/uikit/addons/js/form-password.min.js','assets:vendor/uikit/addons/css/form-password.min.css','cockpit:assets/css/login.less'], 'app.login', 'cache:assets', 30)

    <script>
        $(function(){

            var loginbox  = $(".app-login-box"),
                container = loginbox.find(".app-login-box-container"),
                form      = $("form").on("submit", function(e){
                                e.preventDefault();

                                $.post(form.attr("action"), form.serialize(), function(data){

                                    if(data && data.success) {
                                        location.href = $("html").data("route");
                                    }else{

                                        container.removeClass("uk-animation-shake");

                                        setTimeout(function(){
                                            container.addClass("uk-animation-shake");
                                            $.UIkit.notify("@lang('Login failed')", 'danger');
                                        }, 50);
                                    }

                                }, 'json');
                            });
        });
    </script>
</head>
<body>
    <div>
        <div class="uk-animation-fade app-login-box">
            <div class="app-login-box-container">
                <h1>@lang('Welcome back!')</h1>

                <p class="uk-text-muted">
                    @lang('Please login by using your auth credentials.')
                </p>

                <form class="uk-form" method="post" action="@route('/auth/check')">

                    <div class="uk-form-row">

                        <input name="auth[user]" class="uk-form-large uk-width-1-1" type="text" placeholder="@lang('Username')">
                    </div>
                    <div class="uk-form-row">
                        <div class="uk-form-password uk-width-1-1">
                            <input name="auth[password]" class="uk-form-large uk-width-1-1" type="password" placeholder="@lang('Password')">
                            <a href="" class="uk-form-password-toggle" data-uk-form-password>Show</a>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <button class="uk-button uk-button-large uk-button-primary uk-width-1-1">@lang('Authenticate')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>