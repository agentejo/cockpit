{{ $app->assets(['forms:assets/forms.js','forms:assets/js/index.js'], $app['cockpit/version']) }}

<div data-ng-controller="forms" ng-cloak>

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">@lang('Forms')</span>
        <div class="uk-navbar-content" data-ng-show="forms && forms.length">
            <form class="uk-form uk-margin-remove uk-display-inline-block">
                <div class="uk-form-icon">
                    <i class="uk-icon-filter"></i>
                    <input type="text" placeholder="@lang('Filter by name...')" data-ng-model="filter">
                </div>
            </form>
        </div>
        @hasaccess?("Forms", 'manage.forms')
        <ul class="uk-navbar-nav">
            <li><a href="@route('/forms/form')" title="@lang('Add form')" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-plus-circle"></i></a></li>
        </ul>
        @end
        <div class="uk-navbar-flip" data-ng-if="forms && forms.length">
            <div class="uk-navbar-content">
                <div class="uk-button-group">
                    <button class="uk-button" data-ng-class="mode=='list' ? 'uk-button-primary':''" data-ng-click="setListMode('list')" title="@lang('List mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th"></i></button>
                    <button class="uk-button" data-ng-class="mode=='table' ? 'uk-button-primary':''" data-ng-click="setListMode('table')" title="@lang('Table mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th-list"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <div class="uk-grid uk-grid-small" data-uk-grid-margin data-uk-grid-match data-ng-if="forms && forms.length && mode=='list'">
        <div class="uk-width-1-1 uk-width-medium-1-3 uk-width-large-1-4" data-ng-repeat="form in forms track by form._id" data-ng-show="matchName(form.name)">

            <div class="app-panel">

                <a class="uk-link-muted" href="@route('/forms/entries')/@@ form._id @@"><strong>@@ form.name @@</strong></a>

                <div class="uk-margin">
                    <span class="uk-badge app-badge" title="Last update">@@ form.modified |fmtdate:'d M, Y H:i' @@</span>
                </div>

                <div class="app-panel-box docked-bottom">
                    <span class="uk-button-group">
                        <a class="uk-button uk-button-primary uk-button-small" href="@route('/forms/entries')/@@ form._id @@" title="@lang('Show entries')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-bars"></i></a>
                        @hasaccess?("Forms", 'manage.forms')
                        <a class="uk-button uk-button-small" href="@route('/forms/form')/@@ form._id @@" title="@lang('Edit form')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-pencil"></i></a>
                        <a class="uk-button uk-button-danger uk-button-small" data-ng-click="remove($index, form)" href="#" title="@lang('Delete form')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-minus-circle"></i></a>
                        @end
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="app-panel" data-ng-if="forms && forms.length && mode=='table'">
        <table class="uk-table uk-table-striped" multiple-select="{model:forms}">
            <thead>
                <tr>
                    <th width="10"><input class="js-select-all" type="checkbox"></th>
                    <th>@lang('Form')</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr class="js-multiple-select" data-ng-repeat="form in forms track by form._id" data-ng-show="matchName(form.name)">
                    <td><input class="js-select" type="checkbox"></td>
                    <td>
                        <a href="@route('/forms/form')/@@ form._id @@">@@ form.name @@</a>
                    </td>
                    <td align="right">
                        @hasaccess?("Forms", 'manage.forms')
                        <a class="uk-text-danger" data-ng-click="remove($index, form)" href="#" title="@lang('Delete form')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-minus-circle js-ignore-select"></i></a>
                        @end
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="uk-margin-top">
            <button class="uk-button uk-button-danger" data-ng-click="removeSelected()" data-ng-show="selected"><i class="uk-icon-trash-o"></i> @lang('Delete')</button>
        </div>
    </div>

    <div class="uk-text-center app-panel" data-ng-show="forms && !forms.length">
        <h2><i class="uk-icon-inbox"></i></h2>
        <p class="uk-text-large">
            @lang('You don\'t have any forms created.')
        </p>

        @hasaccess?("Forms", 'manage.forms')
        <a href="@route('/forms/form')" class="uk-button uk-button-success uk-button-large">@lang('Create a form')</a>
        @end
    </div>

</div>