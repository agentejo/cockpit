<style>

    .app-dashboard-widget {
        margin-bottom: 15px;
    }

    .dashboard-aside {
        -webkit-column-count: 2;
        -webkit-column-gap: 15px;
        -moz-column-count: 2;
        -moz-column-gap: 15px;
        column-count: 2;
        column-gap: 15px;
    }

    .dashboard-aside > div {
        -webkit-column-break-inside: avoid;
        -moz-column-break-inside: avoid;
        column-break-inside: avoid;
        margin: 0 2px 15px;
    }

    @media all and (max-width: 960px) {
        .dashboard-aside {
            -webkit-column-count: 1;
            -moz-column-count: 1;
            column-count: 1;
        }
    }
</style>

<div class="uk-grid">
    <div class="uk-width-medium-1-2">
        @trigger('admin.dashboard.main')
    </div>
    <div class="uk-width-medium-1-2">
        <div class="dashboard-aside">
            @trigger('admin.dashboard.aside')
        </div>
    </div>
</div>