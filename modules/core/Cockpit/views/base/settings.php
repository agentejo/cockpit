
<h1>@lang('Settings')</h1>

<div class="app-panel">
    <h4 class="uk-text-center">@lang('System')</h4>
    <hr>
    <div class="uk-grid" uk-grid-margin uk-grid-match>
        <div class="uk-width-medium-1-4">
            <div>
                <i class="uk-icon-group"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/accounts/index')">@lang('Accounts')</a>
            </div>
        </div>
        <div class="uk-width-medium-1-4">
            <div>
                <i class="uk-icon-code-fork"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/settings/addons')">@lang('Addons')</a>
            </div>
        </div>
        <div class="uk-width-medium-1-4">
            <div>
                <i class="uk-icon-archive"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/backups')">@lang('Backups')</a>
            </div>
        </div>
        <div class="uk-width-medium-1-4">
            <div>
                <i class="uk-icon-info-circle"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/settings/info')">@lang('Info')</a>
            </div>
        </div>
    </div>
</div>

@trigger('cockpit.settings.index')

<style>

    .app-panel > div {
        text-align: center;
    }

    .app-panel > div  *[class*=uk-icon] {
        font-size: 40px;
        line-height: 60px;
    }

</style>