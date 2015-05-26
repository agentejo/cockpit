<div>
    <ul class="uk-breadcrumb">
        <li><span>@lang('Dashboard')</span></li>
    </ul>
</div>

<div class="uk-margin">
    @trigger('admin.dashboard.top')
</div>

<div class="uk-grid uk-margin" data-uk-grid-margin>
    <div class="uk-width-medium-1-2">
        @trigger('admin.dashboard.main')
    </div>
    <div class="uk-width-medium-1-2">
        <div class="uk-grid uk-grid-gutter uk-grid-width-medium-1-2">
            @trigger('admin.dashboard.aside')
        </div>
    </div>
</div>

<div class="uk-margin">
    @trigger('admin.dashboard.bottom')
</div>
