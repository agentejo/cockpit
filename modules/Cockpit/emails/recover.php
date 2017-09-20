<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>

    </head>   
    <body>
        <div class="container">

            <h1><center>{{ $app['app.name'] }}</center></h1>

            <center><a href="{{ $target }}?token={{ $token }}">@lang('Reset Password')</a></center>

        </div>
    </body>
</html>