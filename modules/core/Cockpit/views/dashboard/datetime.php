<div id="date-widget-weekdays" class="uk-text-small uk-text-muted uk-margin date-widget-weekdays" data-day="5">
    <span data-day="1">MON</span>
    <span data-day="2">TUE</span>
    <span data-day="3">WED</span>
    <span data-day="4">THU</span>
    <span data-day="5">FRI</span>
    <span data-day="6">SAT</span>
    <span data-day="7">SUN</span>
</div>

<div>
    <span app-clock="d. M Y"></span>
</div>

<div style="font-size:35px;margin-top:20px;margin-bottom:20px;">
    <strong app-clock="h:i A"></strong>
</div>

<hr>

<div class="date-widget-account">
    <a href="@route('/accounts/account')" class="uk-clearfix">
        <img class="uk-rounded uk-float-left uk-margin-right" src="http://www.gravatar.com/avatar/{{ md5($app['user']['email']) }}?d=mm&s=40" width="40" height="40" alt="avatar">
        <div class="uk-text-truncate"><strong>{{ $app["user"]["user"] }}</strong></div>
        <div class="uk-text-small uk-text-muted uk-text-truncate">{{ (isset($app["user"]["email"]) ? $app["user"]["email"] : 'no email') }}</div>
    </a>
</div>

<style type="text/css">

    .date-widget-weekdays span {
        margin-right: 5px;
    }
    .date-widget-weekdays span.active {
        color: #000;
        font-weight: bold;
    }
    .date-widget-account { 
        opacity: 0.3; 
        transition: opacity 0.3s ease-in-out;
    }
    .date-widget-account:hover { opacity: 1; }
</style>

<script>
    $("#date-widget-weekdays").find('span[data-day="'+(new Date().getDay())+'"]').addClass('active');
</script>