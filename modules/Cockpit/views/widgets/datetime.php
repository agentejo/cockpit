<div>

    <div class="uk-panel-box uk-panel-card" riot-view>

        <?php
            $i18ndata = $app("i18n")->data($app("i18n")->locale);
            $weekdays = isset($i18ndata["@meta"]["date"]["shortdays"]) ? $i18ndata["@meta"]["date"]["shortdays"] : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
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
            .date-widget-clock {
                font-size: 30px;
                margin-top:20px;
                font-weight: bold;
            }

        </style>

        <div class="uk-panel-box-header">
            <strong>{{ date('d. M Y') }}</strong>
        </div>

        <div id="{{ $uid }}" class="uk-grid">

            <div class="uk-width-medium-1-1">

                <div ref="weekdays" class="uk-text-small uk-text-muted uk-margin uk-text-uppercase date-widget-weekdays uk-margin">
                    <span data-day="1">{{ $weekdays[0] }}</span>
                    <span data-day="2">{{ $weekdays[1] }}</span>
                    <span data-day="3">{{ $weekdays[2] }}</span>
                    <span data-day="4">{{ $weekdays[3] }}</span>
                    <span data-day="5">{{ $weekdays[4] }}</span>
                    <span data-day="6">{{ $weekdays[5] }}</span>
                    <span data-day="0">{{ $weekdays[6] }}</span>
                </div>

                <div class="date-widget-clock">
                    <i class="uk-icon-clock-o"></i> <span ref="time">&nbsp;</span>
                </div>
            </div>
        </div>


        <script type="view/script">

            this.on('mount', function() {

                var $time     = App.$(this.refs.time),
                    $weekdays = App.$(this.refs.weekdays).find('span[data-day="'+(new Date().getDay())+'"]').addClass('active');
                    timer     = setInterval((function(){

                        var date = new Date(), minutes, hours, fn = function(){

                            hours   = date.getHours();
                            minutes = date.getMinutes();

                            if (hours < 10) hours = '0'+hours;
                            if (minutes < 10) minutes = '0'+minutes;

                            $time.text(hours+":"+minutes);

                            return fn;
                        };

                        return fn();

                    })(), 60000);
            });

        </script>

    </div>
</div>
