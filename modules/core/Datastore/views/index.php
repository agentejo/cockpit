@start('header')

    {{ $app->assets(['datastore:assets/datastore.js','datastore:assets/js/index.js'], $app['cockpit/version']) }}

@end('header')

<div data-ng-controller="datastore" ng-cloak>

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-hidden-small uk-navbar-brand">@lang('Datastore')</span>
        <div class="uk-hidden-small uk-navbar-content" data-ng-show="tables && tables.length">
            <form class="uk-form uk-margin-remove uk-display-inline-block">
                <div class="uk-form-icon">
                    <i class="uk-icon-filter"></i>
                    <input type="text" placeholder="@lang('Filter by name...')" data-ng-model="filter">
                </div>
            </form>
        </div>
        @hasaccess?("Datastore", 'manage.datastore')
        <ul class="uk-navbar-nav">
            <li><a href="@route('/datastore/table')" title="@lang('Add table')" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-plus-circle"></i></a></li>
        </ul>
        @end
        <div class="uk-navbar-flip" data-ng-if="tables && tables.length">
            <div class="uk-navbar-content">
                <div class="uk-button-group">
                    <button class="uk-button" data-ng-class="mode=='list' ? 'uk-button-primary':''" data-ng-click="setListMode('list')" title="@lang('List mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th"></i></button>
                    <button class="uk-button" data-ng-class="mode=='table' ? 'uk-button-primary':''" data-ng-click="setListMode('table')" title="@lang('Table mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th-list"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <div class="uk-grid uk-grid-small" data-uk-grid-match data-ng-if="tables && tables.length && mode=='list'">
        <div class="uk-width-1-1 uk-width-medium-1-3 uk-width-large-1-4 uk-grid-margin" data-ng-repeat="table in tables track by table._id" data-ng-show="matchName(table.name)">

            <div class="app-panel">

                <a class="uk-link-muted" href="@route('/datastore/table')/@@ table._id @@"><strong>@@ table.name @@</strong></a>

                <div class="uk-margin">
                    <span class="uk-badge app-badge">@@ table.count @@ @lang('Entries')</span>
                </div>

                <div class="app-panel-box docked-bottom">

                    <div class="uk-link" data-uk-dropdown="{mode:'click'}">
                        <i class="uk-icon-bars"></i>
                        <div class="uk-dropdown">
                            <ul class="uk-nav uk-nav-dropdown uk-nav-parent-icon">
                                <li><a href="@route('/datastore/table')/@@ table._id @@"><i class="uk-icon-pencil"></i> @lang('Manage table')</a></li>
                                <li class="uk-danger"><a data-ng-click="remove($index, table)" href="#"><i class="uk-icon-minus-circle"></i> @lang('Delete table')</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-panel" data-ng-if="tables && tables.length && mode=='table'">

        <table class="uk-table uk-table-striped" multiple-select="{model:tables}">
            <thead>
                <tr>
                    <th width="10"><input class="js-select-all" type="checkbox"></th>
                    <th width="60%">@lang('Table')</th>
                    <th width="10%">@lang('Entries')</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr class="js-multiple-select" data-ng-repeat="table in tables track by table._id" data-ng-show="matchName(table.name)">
                    <td><input class="js-select" type="checkbox"></td>
                    <td>
                        <a href="@route('/datastore/table')/@@ table._id @@">@@ table.name @@</a>
                    </td>
                    <td>@@ table.count @@</td>
                    <td>
                        <div class="uk-link uk-float-right" data-uk-dropdown>
                            <i class="uk-icon-bars"></i>
                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown uk-nav-parent-icon">
                                    <li><a href="@route('/datastore/table')/@@ table._id @@"><i class="uk-icon-pencil"></i> @lang('Manage table')</a></li>
                                    <li class="uk-danger"><a data-ng-click="remove($index, table)" href="#"><i class="uk-icon-minus-circle"></i> @lang('Delete table')</a></li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="uk-margin-top">
            <button class="uk-button uk-button-danger" data-ng-click="removeSelected()" data-ng-show="selected"><i class="uk-icon-trash-o"></i> @lang('Delete')</button>
        </div>

    </div>

    <div class="uk-text-center app-panel" data-ng-show="tables && !tables.length">
        <h2><i class="uk-icon-database"></i></h2>
        <p class="uk-text-large">
            @lang('No tables yet.')
        </p>

        <a href="@route('/datastore/table')" class="uk-button uk-button-success uk-button-large">@lang('Create a table')</a>
    </div>

</div>