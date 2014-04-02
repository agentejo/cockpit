
<h1>@lang('Settings')</h1>

<div class="app-panel">

    <div class="uk-text-left">
        <span class="uk-badge app-badge">@lang('System')</span>
    </div>

    <hr>

    <div class="uk-grid uk-grid-width-medium-1-6" uk-grid-margin uk-grid-match>

        @if($app["user"]["group"]=="admin")
        <div class="uk-margin-bottom">
            <div>
                <i class="uk-icon-cogs"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/settings/general')">@lang('General')</a>
            </div>
        </div>
        @endif

        <div class="uk-margin-bottom">
            <div>
                <i class="uk-icon-group"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/accounts/index')">@lang('Accounts')</a>
            </div>
        </div>

        @if($app["user"]["group"]=="admin")
        <div class="uk-margin-bottom">
            <div>
                <i class="uk-icon-code-fork"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/settings/addons')">@lang('Addons')</a>
            </div>
        </div>
        @endif

        @if($app->module("auth")->hasaccess("Cockpit", "manage.backups"))
        <div class="uk-margin-bottom">
            <div>
                <i class="uk-icon-archive"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/backups')">@lang('Backups')</a>
            </div>
        </div>
        @endif

        <!--
        @if($app["user"]["group"]=="admin")
        <div class="uk-margin-bottom">
            <div>
                <i class="uk-icon-fire"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/updater/index')">@lang('Update')</a>
            </div>
        </div>
        @endif
        -->

        @if($app["user"]["group"]=="admin")
        <div class="uk-margin-bottom">
            <div>
                <i class="uk-icon-info-circle"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/settings/info')">@lang('Info')</a>
            </div>
        </div>
        @endif
    </div>
</div>

@trigger('cockpit.settings.index')

<style>

    .app-panel > div {
        text-align: center;
    }

    .app-panel > div  *[class*=uk-icon], .app-panel > div img {
        font-size: 40px;
        line-height: 60px;
    }

    .app-panel > div img {
        width: 40px;
        height: 40px;
    }

</style>