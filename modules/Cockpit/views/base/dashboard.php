
<div class="dashboard-header-panel uk-container-breakout">

    <div class="uk-container uk-container-center">

        <div class="uk-grid uk-grid-width uk-grid-margin" data-uk-grid-margin>

            <div class="uk-width-medium-1-2">

                <div class="uk-flex">
                    <div riot-mount>
                        <cp-gravatar email="{{ $app['user']['email'] }}" size="55" alt="{{ $app["user"]["name"] ? $app["user"]["name"] : $app["user"]["user"] }}"></cp-gravatar>
                    </div>
                    <div class="uk-flex-item-1 uk-margin-left">
                        <h2 class="uk-margin-remove">@lang('Welcome back')</h2>
                        <div class="uk-h3 uk-text-bold uk-margin-small-top">
                            {{ $app['user/name'] ? $app['user/name'] : $app['user/user'] }}
                        </div>
                    </div>
                </div>

                <hr>

                <div>
                    <span class="uk-badge uk-badge-outline uk-text-primary uk-margin-small-right">{{ $app['user/group'] }}</span>
                    <a class="uk-button uk-button-link uk-text-muted" href="@route('/accounts/account')"><img class="uk-margin-small-right inherit-color" src="@base('assets:app/media/icons/settings.svg')" width="20" height="20" data-uk-svg alt="assets" /> @lang('Account')</a>
                </div>

            </div>

            <div class="uk-width-medium-1-2">

                @if($app('admin')->data['menu.modules']->count())
                <ul class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-grid-gutter uk-text-center" data-uk-grid-margin>

                    @foreach($app('admin')->data['menu.modules'] as $item)
                    <li class="uk-width-1-2 uk-width-medium-1-4" data-route="{{ $item['route'] }}">
                        <a class="uk-display-block uk-panel-box" href="@route($item['route'])">
                            <div class="uk-svg-adjust">
                                @if(preg_match('/\.svg$/i', $item['icon']))
                                <img src="@url($item['icon'])" alt="@lang($item['label'])" data-uk-svg width="30" height="30" />
                                @else
                                <img src="@url('assets:app/media/icons/module.svg')" alt="@lang($item['label'])" data-uk-svg width="30" height="30" />
                                @endif
                            </div>
                            <div class="uk-text-truncate uk-text-small uk-margin-small-top">@lang($item['label'])</div>
                        </a>
                    </li>
                    @endforeach

                </ul>
                @endif

                @trigger('admin.dashboard.header')

            </div>

        </div>

    </div>
</div>


<div id="dashboard">

    <div class="uk-margin">
        @trigger('admin.dashboard.top')
    </div>

    <div class="uk-grid uk-margin" data-uk-grid-margin>
        <div class="uk-width-medium-1-2" data-area="main">
            <div class="uk-sortable uk-grid uk-grid-gutter uk-grid-width-1-1" data-uk-sortable="{group:'dashboard',animation:false}">
                @foreach($areas['main'] as $widget)
                <div data-widget="{{ $widget['name'] }}">
                    {{ $widget['content'] }}
                </div>
                @endforeach
            </div>
        </div>
        <div class="uk-width-medium-1-4" data-area="aside-left">
            <div class="uk-sortable uk-grid uk-grid-gutter uk-grid-width-medium-1-1" data-uk-sortable="{group:'dashboard',animation:false}">
                @foreach($areas['aside-left'] as $widget)
                <div data-widget="{{ $widget['name'] }}">
                    {{ $widget['content'] }}
                </div>
                @endforeach
            </div>
        </div>
        <div class="uk-width-medium-1-4" data-area="aside-right">
            <div class="uk-sortable uk-grid uk-grid-gutter uk-grid-width-medium-1-1" data-uk-sortable="{group:'dashboard',animation:false}">
                @foreach($areas['aside-right'] as $widget)
                <div data-widget="{{ $widget['name'] }}">
                    {{ $widget['content'] }}
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="uk-margin">
        @trigger('admin.dashboard.bottom')
    </div>

</div>

<style>

    #dashboard .uk-grid.uk-sortable {
        min-height: 30vh;
    }

</style>

<script>

    App.$(function($){

        var data, dashboard = App.$('#dashboard').on('stop.uk.sortable', function(){

            data = {};

            dashboard.find('[data-area]').each(function(){
                var $a      = $(this),
                    area    = $a.data('area'),
                    widgets = $a.find('[data-widget]');

                widgets.each(function(prio){
                    data[this.getAttribute('data-widget')] = {
                        area: area,
                        prio: prio + 1
                    };
                });
            });

            App.request('/cockpit/savedashboard',{widgets:data}).then(function(){

            });
        });
    });

</script>
