{{ $app->assets(['forms:assets/forms.js','forms:assets/js/index.js'], $app['cockpit/version']) }}

<div data-ng-controller="forms">

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">@lang('Forms')</span>
        <div class="uk-navbar-content">
            <form class="uk-form uk-margin-remove uk-display-inline-block">
                <div class="uk-form-icon">
                    <i class="uk-icon-filter"></i>
                    <input type="text" placeholder="@lang('Filter by name...')" data-ng-model="filter">
                </div>
            </form>
        </div>
        <ul class="uk-navbar-nav">
            <li><a href="@route('/forms/form')" title="@lang('Add form')" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-plus-circle"></i></a></li>
        </ul>
    </nav>

    <div class="uk-grid" data-uk-grid-margin data-uk-grid-match>
        <div class="uk-width-1-1 uk-width-medium-1-3 uk-width-large-1-4" data-ng-repeat="form in forms" data-ng-show="matchName(form.name)">

            <div class="app-panel app-panel-box">

                <strong>@@ form.name @@</strong>

                <div class="uk-margin">
                    <span class="uk-badge app-badge" title="Last update">@@ form.modified |fmtdate:'d M, Y H:i' @@</span>
                </div>


                <span class="uk-button-group">
                    <a class="uk-button uk-button-small" href="@route('/forms/entries')/@@ form._id @@" title="@lang('Show entries')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-bars"></i></a>
                    <a class="uk-button uk-button-small" href="@route('/forms/form')/@@ form._id @@" title="@lang('Edit form')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-pencil"></i></a>
                    <a class="uk-button uk-button-danger uk-button-small" data-ng-click="remove($index, form)" href="#" title="@lang('Delete form')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-minus-circle"></i></a>
                </span>
            </div>
        </div>
    </div>


    <div class="uk-text-center app-panel" data-ng-show="forms && !forms.length">
        <h2><i class="uk-icon-inbox"></i></h2>
        <p class="uk-text-large">
            @lang('You don\'t have any forms created.')
        </p>

        <a href="@route('/forms/form')" class="uk-button uk-button-success uk-button-large">@lang('Create a form')</a>
    </div>

</div>