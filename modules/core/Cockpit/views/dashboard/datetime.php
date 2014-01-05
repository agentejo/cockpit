
<?php 
    
    $i18ndata = $app("i18n")->data($app("i18n")->locale);
    $weekdays = isset($i18ndata["@meta"]["date"]["shortdays"]) ? $i18ndata["@meta"]["date"]["shortdays"] : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
?>

<div id="date-widget-weekdays" class="uk-text-small uk-text-muted uk-margin uk-text-uppercase date-widget-weekdays">
    <span data-day="1">{{ $weekdays[0] }}</span>
    <span data-day="2">{{ $weekdays[1] }}</span>
    <span data-day="3">{{ $weekdays[2] }}</span>
    <span data-day="4">{{ $weekdays[3] }}</span>
    <span data-day="5">{{ $weekdays[4] }}</span>
    <span data-day="6">{{ $weekdays[5] }}</span>
    <span data-day="0">{{ $weekdays[6] }}</span>
</div>

<div>
    <span app-clock="d. M Y"></span>
</div>

<div style="font-size:35px;margin-top:20px;margin-bottom:20px;">
    <strong app-clock="h:i A"></strong>
</div>

<hr>

<div class="date-widget-account">
    <a class="uk-display-block" href="@route('/accounts/account')" class="uk-clearfix" title="@lang('Edit account settings')" data-uk-tooltip="{pos:'bottom-left', offset:10}">
        <img class="uk-rounded uk-float-left uk-margin-right" src="http://www.gravatar.com/avatar/{{ md5($app['user']['email']) }}?d=mm&s=35" width="35" height="35" alt="avatar">
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
        transition: opacity 0.2s ease-in-out;
    }
    .date-widget-account:hover { opacity: 1; }
</style>

<script>
    $("#date-widget-weekdays").find('span[data-day="'+(new Date().getDay())+'"]').addClass('active');
</script>