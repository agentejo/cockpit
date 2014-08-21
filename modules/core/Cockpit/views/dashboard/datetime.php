<?php

    $i18ndata = $app("i18n")->data($app("i18n")->locale);
    $weekdays = isset($i18ndata["@meta"]["date"]["shortdays"]) ? $i18ndata["@meta"]["date"]["shortdays"] : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    $uid = uniqid('weekdays');
?>

@start('header')

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
    </style>

    <script>
        jQuery(function($) {

            $("#{{ $uid }}").find('span[data-day="'+(new Date().getDay())+'"]').addClass('active');
        });
    </script>

@end('header')


<div class="uk-grid">

    <div class="uk-width-medium-1-1">
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
            <span app-clock="d. M Y">&nbsp;</span>
        </div>

        <div class="date-widget-clock">
            <i class="uk-icon-clock-o"></i> <span app-clock="h:i A">&nbsp;</span>
        </div>
    </div>
</div>