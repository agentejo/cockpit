<?php 
    
    $i18ndata = $app("i18n")->data($app("i18n")->locale);
    $weekdays = isset($i18ndata["@meta"]["date"]["shortdays"]) ? $i18ndata["@meta"]["date"]["shortdays"] : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    $uid = uniqid('weekdays');
?>

<div class="uk-grid uk-grid-divider">

    <div class="uk-width-medium-2-3">
        <div id="{{ $uid }}" class="uk-text-small uk-text-muted uk-margin uk-text-uppercase date-widget-weekdays">
            <span data-day="1">{{ $weekdays[0] }}</span>
            <span data-day="2">{{ $weekdays[1] }}</span>
            <span data-day="3">{{ $weekdays[2] }}</span>
            <span data-day="4">{{ $weekdays[3] }}</span>
            <span data-day="5">{{ $weekdays[4] }}</span>
            <span data-day="6">{{ $weekdays[5] }}</span>
            <span data-day="0">{{ $weekdays[6] }}</span>
        </div>

        <div class="uk-text-small">
            <span app-clock="d. M Y"></span>
        </div>

        <div class="date-widget-clock">
            <i class="uk-icon-clock-o"></i> <span app-clock="h:i A"></span>
        </div>
    </div>
    <div class="uk-width-medium-1-3 uk-hidden-small uk-text-center">

        <div class="date-widget-account">
            <a class="uk-display-block" href="@route('/accounts/account')" class="uk-clearfix" title="@lang('Edit account settings')" data-uk-tooltip="{pos:'bottom', offset:10}">
                <div class="uk-margin-bottom">
                    <div class="uk-thumbnail uk-rounded">
                        <img src="http://www.gravatar.com/avatar/{{ md5($app['user']['email']) }}?d=mm&s=55" width="55" height="55" alt="avatar">
                    </div>
                </div>
                <div class="uk-text-truncate"><strong>{{ $app["user"]["name"] ? $app["user"]["name"] : $app["user"]["user"] }}</strong></div>
                <div class="uk-text-small uk-text-muted uk-text-truncate">{{ (isset($app["user"]["email"]) ? $app["user"]["email"] : 'no email') }}</div>
            </a>
        </div>

    </div>
</div>

<style type="text/css">

    .date-widget-weekdays span {
        margin-right: 5px;
    }
    .date-widget-weekdays span.active {
        color: #000;
        font-weight: bold;
    }
    .date-widget-clock {
        font-size: 30px;
        margin-top:20px;
        font-weight: bold;
    }
    .date-widget-account { 
        opacity: 0.8;
        transition: all 0.2s ease-in-out;
    }
    .date-widget-account:hover { 
        opacity: 1;
    }
</style>

<script>
    $("#{{ $uid }}").find('span[data-day="'+(new Date().getDay())+'"]').addClass('active');
</script>