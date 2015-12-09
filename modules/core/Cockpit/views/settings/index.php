<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Settings')</span></li>
    </ul>
</div>

<div class="uk-grid uk-grid-small uk-grid-match uk-grid-width-medium-1-4" data-uk-grid-margin>

    @if($app['user']['group']=='admin')
    <div>
        <div class="uk-panel uk-panel-box uk-panel-card">
            <div class="uk-text-truncate">
                <a href="@route('/settings/edit')"><i class="uk-icon-cogs uk-icon-justify"></i> @lang('Settings')</a>
            </div>
        </div>
    </div>
    @endif

    @hasaccess?('cockpit', 'manage.accounts')
    <div>
        <div class="uk-panel uk-panel-box uk-panel-card">
            <div class="uk-text-truncate">
                <a href="@route('/accounts/index')"><i class="uk-icon-users uk-icon-justify"></i> @lang('Accounts')</a>
            </div>
        </div>
    </div>
    @endif

    @if($app['user']['group']=='admin')
    <div>
        <div class="uk-panel uk-panel-box uk-panel-card">
            <div class="uk-text-truncate">
                <a href="@route('/settings/info')"><i class="uk-icon-info-circle uk-icon-justify"></i> @lang('System')</a>
            </div>
        </div>
    </div>
    @endif

</div>

@trigger('cockpit.view.settings')
