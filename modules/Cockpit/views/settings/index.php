<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Settings')</span></li>
    </ul>
</div>

<div class="uk-grid uk-grid-gutter uk-grid-match uk-grid-width-medium-1-4 uk-text-center">

    @if($app['user']['group']=='admin')
    <div>
        <div class="uk-panel uk-panel-space uk-panel-box uk-panel-card">
            <img src="@url('assets:app/media/icons/settings.svg')" width="50" height="50" alt="@lang('Settings')" />

            <div class="uk-text-truncate uk-margin">
                @lang('Settings')
            </div>
            <a class="uk-position-cover" href="@route('/settings/edit')"></a>
        </div>
    </div>
    @endif

    @hasaccess?('cockpit', 'accounts')
    <div>
        <div class="uk-panel uk-panel-space uk-panel-box uk-panel-card">

            <img src="@url('assets:app/media/icons/accounts.svg')" width="50" height="50" alt="@lang('Accounts')" />

            <div class="uk-text-truncate uk-margin">
                @lang('Accounts')
            </div>
            <a class="uk-position-cover" href="@route('/accounts/index')"></a>
        </div>
    </div>
    @endif

    @hasaccess?('cockpit', 'rest')
    <div>
        <div class="uk-panel uk-panel-space uk-panel-box uk-panel-card">

            <img src="@url('assets:app/media/icons/api.svg')" width="50" height="50" alt="@lang('API Access')" />

            <div class="uk-text-truncate uk-margin">
                @lang('API Access')
            </div>
            <a class="uk-position-cover" href="@route('/restadmin/index')"></a>
        </div>
    </div>
    @endif

    @hasaccess?('cockpit', 'webhooks')
    <div>
        <div class="uk-panel uk-panel-space uk-panel-box uk-panel-card">

            <img src="@url('assets:app/media/icons/webhooks.svg')" width="50" height="50" alt="@lang('Webhooks')" />

            <div class="uk-text-truncate uk-margin">
                @lang('Webhooks')
            </div>
            <a class="uk-position-cover" href="@route('/webhooks/index')"></a>
        </div>
    </div>
    @endif

    @hasaccess?('cockpit', 'info')
    <div>
        <div class="uk-panel uk-panel-space uk-panel-box uk-panel-card">

            <img src="@url('assets:app/media/icons/info.svg')" width="50" height="50" alt="@lang('System')" />

            <div class="uk-text-truncate uk-margin">
                @lang('System')
            </div>
            <a class="uk-position-cover" href="@route('/settings/info')"></a>
        </div>
    </div>
    @endif

    @trigger('cockpit.view.settings.item')

</div>

@trigger('cockpit.view.settings')
