<!doctype html>
<html class="uk-height-1-1" lang="en" data-base="@base('/')" data-route="@route('/')">
<head>
    <meta charset="UTF-8">
    <title>@lang('Authenticate Please!')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="icon" href="@base("/favicon.ico")" type="image/x-icon">

    @assets($app['app.assets.base'], 'app.base', 'cache:assets', 30, $app['cockpit/version'])
    {{ $app->assets(['assets:vendor/uikit/js/addons/form-password.min.js'], $app['cockpit/version']) }}

    <script>
        $(function(){

            var loginbox  = $(".app-login-box"),
                container = loginbox.find(".app-login-box-container"),
                profile   = $("#profile"),
                form      = $("form").on("submit", function(e){
                                e.preventDefault();

                                loginbox.addClass("app-loading");

                                $.post(form.attr("action"), form.serialize(), function(data){

                                    setTimeout(function(){

                                        loginbox.removeClass("app-loading");
                                        container.removeClass("uk-animation-shake");

                                        if(data && data.success) {

                                            form.hide();
                                            profile.find("img").attr("src", "http://www.gravatar.com/avatar/"+data.avatar+"?d=mm&s=60");
                                            profile.find("span").html(data.user.name);
                                            profile.removeClass('uk-hidden');

                                            setTimeout(function(){

                                                container.addClass("uk-animation-slide-top uk-animation-reverse").width();

                                                setTimeout(function(){
                                                    location.href = $("html").data("route");
                                                }, 500);

                                            }, 550);

                                        }else{

                                            setTimeout(function(){
                                                container.addClass("uk-animation-shake");
                                                $.UIkit.notify("@lang('Login failed')", 'danger');
                                            }, 50);
                                        }

                                    }, 450);

                                }, 'json');
                            });
        });
    </script>
</head>
<body>
    <div>
        <div class="uk-animation-fade app-login-box">
            <div class="app-login-box-container">

                <form class="uk-form" method="post" action="@route('/auth/check')">

                    <p class="uk-text-center uk-margin-large-bottom app-login-logo" style="position:relative;">
                        <i class="uk-icon-spinner uk-icon-spin"></i>
                        <img src="@base('/assets/images/cockpit.png')" width="50" height="50" alt="logo">
                    </p>

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
                <div id="profile" class="uk-text-center uk-animation-fade uk-hidden">
                    <p>
                        <div class="uk-thumbnail uk-rounded"><img alt="avatar" width="60" height="60" style="width:60px;height:60px;"></div>
                    </p>
                    <p class="uk-text-large"><strong>@lang('Welcome back!')</strong></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>