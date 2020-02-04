<div>

    <div class="uk-panel-box uk-panel-card date-time-widget">

        <?php
            $i18ndata = $app->helper('i18n')->data($app("i18n")->locale);
            $weekdays = $i18ndata['@meta']['date']['shortdays'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $weekday  = date('N') + 0;
            $uid      = uniqid('weekdays');
        ?>

        <style type="text/css">

            .date-widget-weekdays span {
                margin-right: 5px;
            }
            .date-widget-weekdays span.active {
                color: #000;
                font-weight: bold;
            }

        </style>

        <div class="uk-panel-box-header">
            <strong class="uk-h3 uk-text-uppercase">{{ date('d. M Y') }}</strong>
        </div>

        <div id="{{ $uid }}" class="uk-grid">

            <div class="uk-width-medium-1-1">

                <div ref="weekdays" class="uk-text-small uk-text-muted uk-margin uk-text-uppercase date-widget-weekdays uk-margin">
                    <span class="{{ $weekday == 1 ? 'active' : '' }}">{{ $weekdays[0] }}</span>
                    <span class="{{ $weekday == 2 ? 'active' : '' }}">{{ $weekdays[1] }}</span>
                    <span class="{{ $weekday == 3 ? 'active' : '' }}">{{ $weekdays[2] }}</span>
                    <span class="{{ $weekday == 4 ? 'active' : '' }}">{{ $weekdays[3] }}</span>
                    <span class="{{ $weekday == 5 ? 'active' : '' }}">{{ $weekdays[4] }}</span>
                    <span class="{{ $weekday == 6 ? 'active' : '' }}">{{ $weekdays[5] }}</span>
                    <span class="{{ $weekday == 7 ? 'active' : '' }}">{{ $weekdays[6] }}</span>
                </div>

            </div>
        </div>

    </div>
</div>
