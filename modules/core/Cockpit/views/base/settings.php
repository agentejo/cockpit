
<h1>Settings</h1>

<div class="app-panel">

    <div class="uk-grid" uk-grid-margin uk-grid-match>
        <div class="uk-width-medium-1-4">
            <div>
                <i class="uk-icon-group"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/accounts/index')">Accounts</a>
            </div>
        </div>
        <div class="uk-width-medium-1-4">
            <div>
                <i class="uk-icon-code-fork"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/settings/addons')">Addons</a>
            </div>
        </div>
        <div class="uk-width-medium-1-4">
            <div>
                <i class="uk-icon-info-circle"></i>
            </div>
            <div class="uk-text-truncate">
                <a href="@route('/settings/info')">Info</a>
            </div>
        </div>
    </div>
</div>

<style>

    .app-panel > div {
        text-align: center;
    }

    .app-panel > div  *[class*=uk-icon] {
        font-size: 40px;
        line-height: 60px;
    }

</style>