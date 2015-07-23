<div>
    <ul class="uk-breadcrumb">
        <li><span>@lang('Dashboard')</span></li>
    </ul>
</div>

<div class="uk-grid uk-margin" data-uk-grid-margin>
    <div class="uk-width-medium-1-2">
        <div class="uk-sortable uk-grid uk-grid-gutter uk-grid-width-1-1 uk-viewport-height-1-3" data-uk-sortable="{group:'dashboard'}">
        @trigger('admin.dashboard.main')
        </div>
    </div>
    <div class="uk-width-medium-1-2">
        <div class="uk-sortable uk-grid uk-grid-gutter uk-grid-width-medium-1-2 uk-viewport-height-1-3" data-uk-sortable="{group:'dashboard'}">
            @trigger('admin.dashboard.aside')
        </div>
    </div>
</div>

<div class="uk-margin">
    @trigger('admin.dashboard.bottom')
</div>
