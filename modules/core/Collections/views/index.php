@start('header')

    {{ $app->assets(['assets:vendor/uikit/js/components/sortable.min.js'], $app['cockpit/version']) }}
    {{ $app->assets(['collections:assets/collections.js','collections:assets/js/index.js'], $app['cockpit/version']) }}

    <style>

        #groups-list li {
            position: relative;
            overflow: hidden;
        }
        .group-actions {
            position: absolute;
            display:none;
            min-width: 60px;
            text-align: right;
            top: 5px;
            right: 10px;
        }

        .group-actions a { font-size: 11px; }

        #groups-list li.uk-active .group-actions,
        #groups-list li:hover .group-actions { display:block; }
        #groups-list li:hover .group-actions a { color: #666; }
        #groups-list li.uk-active a,
        #groups-list li.uk-active .group-actions a { color: #fff; }


    </style>

@end('header')

<div data-ng-controller="collections" ng-cloak>

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-hidden-small uk-navbar-brand">@lang('Collections')</span>
        <div class="uk-hidden-small uk-navbar-content" data-ng-show="collections && collections.length">
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

    <div class="uk-grid uk-grid-divider" data-uk-grid-match data-ng-show="collections && collections.length" data-uk-grid-margin>

        <div class="uk-width-medium-1-4">
            <div class="uk-panel">
                <ul class="uk-nav uk-nav-side uk-nav-plain" ng-show="groups.length">
                    <li class="uk-nav-header">@lang("Groups")</li>
                    <li ng-class="activegroup=='-all' ? 'uk-active':''" ng-click="(activegroup='-all')"><a>@lang("All collections")</a></li>
                </ul>

                <ul id="groups-list" class="uk-nav uk-nav-side uk-animation-fade uk-sortable" ng-show="groups.length" data-uk-sortable>
                    <li ng-repeat="group in groups" ng-class="$parent.activegroup==group ? 'uk-active':''" ng-click="($parent.activegroup=group)" draggable="true">
                        <a><i class="uk-icon-bars" style="cursor:move;"></i> @@ group @@</a>
                        @hasaccess?("Collections", 'manage.collections')
                        <ul class="uk-subnav group-actions uk-animation-slide-right">
                            <li><a href="#" ng-click="editGroup(group, $index)"><i class="uk-icon-pencil"></i></a></li>
                            <li><a href="#" ng-click="removeGroup($index)"><i class="uk-icon-trash-o"></i></a></li>
                        </ul>
                        @end
                    </li>
                </ul>

                <div class="uk-text-muted" ng-show="!groups.length">
                    @lang('Create groups to organize your collections.')
                </div>

                @hasaccess?("Collections", 'manage.collections')
                <hr>
                <div class="uk-margin-top">
                    <button class="uk-button uk-button-success" title="@lang('Create new group')" data-uk-tooltip="{pos:'right'}" ng-click="addGroup()"><i class="uk-icon-plus-circle"></i></button>
                </div>
                @end
            </div>
        </div>
        <div class="uk-width-medium-3-4">

            <div class="uk-margin-bottom">
                <span class="uk-badge app-badge">@@ (activegroup=='-all' ? '@lang("All collections")' : activegroup) @@</span>
            </div>

            <div class="uk-grid uk-grid-small" data-uk-grid-match data-ng-if="collections && collections.length && mode=='list'">
                <div class="uk-width-1-1 uk-width-medium-1-3 uk-grid-margin" data-ng-repeat="collection in collections track by collection._id" data-ng-show="matchName(collection.name) && inGroup(collection.group)">

                    <div class="app-panel">

                        <a class="uk-link-muted" href="@route('/collections/entries')/@@ collection._id @@"><strong>@@ collection.name @@</strong></a>

                        <div class="uk-margin">
                            <span class="uk-badge app-badge">@@ collection.count @@ @lang('Entries')</span>
                        </div>

                        <div class="app-panel-box docked-bottom">

                            <div class="uk-link" data-uk-dropdown="{mode:'click'}">
                                <i class="uk-icon-bars"></i>
                                <div class="uk-dropdown">
                                    <ul class="uk-nav uk-nav-dropdown uk-nav-parent-icon">

                                        <li><a href="@route('/collections/entries')/@@ collection._id @@"><i class="uk-icon-list"></i> @lang('Show entries')</a></li>
                                        <li><a href="@route('/collections/entry')/@@ collection._id @@"><i class="uk-icon-plus-circle"></i> @lang('Create new entry')</a></li>
                                        @hasaccess?("Collections", 'manage.collections')
                                        <li class="uk-nav-divider"></li>
                                        <li><a href="@route('/collections/collection')/@@ collection._id @@"><i class="uk-icon-pencil"></i> @lang('Edit collection')</a></li>
                                        <li><a ng-click="duplicate(collection._id)"><i class="uk-icon-copy"></i> @lang('Duplicate collection')</a></li>
                                        <li class="uk-nav-divider"></li>
                                        <li class="uk-danger"><a data-ng-click="remove($index, collection)" href="#"><i class="uk-icon-minus-circle"></i> @lang('Delete collection')</a></li>
                                        @end
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-panel" data-ng-if="collections && collections.length && mode=='table'">
                <table class="uk-table uk-table-striped" multiple-select="{model:collections}">
                    <thead>
                        <tr>
                            <th width="10"><input class="js-select-all" type="checkbox"></th>
                            <th width="60%">@lang('Collection')</th>
                            <th width="10%">@lang('Entries')</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="js-multiple-select" data-ng-repeat="collection in collections track by collection._id" data-ng-show="matchName(collection.name) && inGroup(collection.group)">
                            <td><input class="js-select" type="checkbox"></td>
                            <td>
                                <a href="@route('/collections/entries')/@@ collection._id @@">@@ collection.name @@</a>
                            </td>
                            <td>@@ collection.count @@</td>
                            <td>
                                <div class="uk-float-right uk-link" data-uk-dropdown>
                                    <i class="uk-icon-bars"></i>
                                    <div class="uk-dropdown">
                                        <ul class="uk-nav uk-nav-dropdown uk-nav-parent-icon">

                                            <li><a href="@route('/collections/entries')/@@ collection._id @@"><i class="uk-icon-list"></i> @lang('Show entries')</a></li>
                                            <li><a href="@route('/collections/entry')/@@ collection._id @@"><i class="uk-icon-plus-circle"></i> @lang('Create new entry')</a></li>
                                            @hasaccess?("Collections", 'manage.collections')
                                            <li class="uk-nav-divider"></li>
                                            <li><a href="@route('/collections/collection')/@@ collection._id @@"><i class="uk-icon-pencil"></i> @lang('Edit collection')</a></li>
                                            <li><a ng-click="duplicate(collection._id)"><i class="uk-icon-copy"></i> @lang('Duplicate collection')</a></li>
                                            <li class="uk-nav-divider"></li>
                                            <li class="uk-danger"><a data-ng-click="remove($index, collection)" href="#"><i class="uk-icon-minus-circle"></i> @lang('Delete collection')</a></li>
                                            @end
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

        </div>
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