{{ $app->assets(['collections:assets/collections.js','collections:assets/js/index.js'], $app['cockpit/version']) }}


<div data-ng-controller="collections">

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">@lang('Collections')</span>
        <div class="uk-navbar-content" data-ng-show="collections && collections.length">
            <form class="uk-form uk-margin-remove uk-display-inline-block">
                <div class="uk-form-icon">
                    <i class="uk-icon-filter"></i>
                    <input type="text" placeholder="@lang('Filter by name...')" data-ng-model="filter">
                </div>
            </form>
        </div>
        @hasaccess?("Collections", 'manage.collections')
        <ul class="uk-navbar-nav">
            <li><a href="@route('/collections/collection')" title="@lang('Add collection')" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-plus-circle"></i></a></li>
        </ul>
        @end
        <div class="uk-navbar-flip" data-ng-if="collections && collections.length">
            <div class="uk-navbar-content">
                <div class="uk-button-group">
                    <button class="uk-button" data-ng-class="mode=='list' ? 'uk-button-primary':''" data-ng-click="setListMode('list')" title="@lang('List mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th"></i></button>
                    <button class="uk-button" data-ng-class="mode=='table' ? 'uk-button-primary':''" data-ng-click="setListMode('table')" title="@lang('Table mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th-list"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <div class="uk-grid uk-grid-small" data-uk-grid-margin data-uk-grid-match data-ng-if="collections && collections.length && mode=='list'">
        <div class="uk-width-1-1 uk-width-medium-1-3 uk-width-large-1-4" data-ng-repeat="collection in collections" data-ng-show="matchName(collection.name)">

            <div class="app-panel">

                <a class="uk-link-muted" href="@route('/collections/entries')/@@ collection._id @@"><strong>@@ collection.name @@</strong></a>

                <div class="uk-margin">
                    <span class="uk-badge app-badge">@@ collection.count @@ @lang('Entries')</span>
                </div>

                <div class="app-panel-box docked-bottom">
                    <span class="uk-button-group">
                        <a class="uk-button uk-button-primary uk-button-small" href="@route('/collections/entries')/@@ collection._id @@" title="@lang('Show entries')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-bars"></i></a>
                        <a class="uk-button uk-button-small" href="@route('/collections/entry')/@@ collection._id @@" title="@lang('Create new entry')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
                        @hasaccess?("Collections", 'manage.collections')
                        <a class="uk-button uk-button-small" href="@route('/collections/collection')/@@ collection._id @@" title="@lang('Edit collection')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-pencil"></i></a>
                        <a class="uk-button uk-button-danger uk-button-small" data-ng-click="remove($index, collection)" href="#" title="@lang('Delete collection')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-minus-circle"></i></a>
                        @end
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="app-panel" data-ng-if="collections && collections.length && mode=='table'">
        <table class="uk-table uk-table-striped">
            <thead>
                <tr>
                    <th>@lang('Collection')</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr data-ng-repeat="collection in collections" data-ng-show="matchName(collection.name)">
                    <td>
                        <a href="@route('/collections/collection')/@@ collection._id @@">@@ collection.name @@</a>
                    </td>
                    <td align="right">
                        <ul class="uk-subnav uk-subnav-line">
                            <li><a href="@route('/collections/entries')/@@ collection._id @@" title="@lang('Show entries')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-bars"></i></a></li>
                            <li><a href="@route('/collections/entry')/@@ collection._id @@" title="@lang('Create new entry')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a></li>
                            @hasaccess?("Collections", 'manage.collections')
                            <li><a class="uk-text-danger" data-ng-click="remove($index, collection)" href="#" title="@lang('Delete collection')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-minus-circle"></i></a></li>
                            @end
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="uk-text-center app-panel" data-ng-show="collections && !collections.length">
        <h2><i class="uk-icon-list"></i></h2>
        <p class="uk-text-large">
            @lang('You don\'t have any collections created.')
        </p>

        @hasaccess?("Collections", 'manage.collections')
        <a href="@route('/collections/collection')" class="uk-button uk-button-success uk-button-large">@lang('Create a collection')</a>
        @end
    </div>


</div>