{{ $app->assets(['regions:assets/regions.js','regions:assets/js/index.js'], $app['cockpit/version']) }}

<div data-ng-controller="regions">

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">@lang('Regions')</span>
        <div class="uk-navbar-content">
            <form class="uk-form uk-margin-remove uk-display-inline-block">
                <div class="uk-form-icon">
                    <i class="uk-icon-filter"></i>
                    <input type="text" placeholder="@lang('Filter by name...')" data-ng-model="filter">
                </div>
            </form>
        </div>
        @hasaccess?("Regions", 'create.regions')
        <ul class="uk-navbar-nav">
            <li><a href="@route('/regions/region')" title="@lang('Add region')" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-plus-circle"></i></a></li>
        </ul>
        @end
        <div class="uk-navbar-flip" data-ng-if="regions && regions.length">
            <div class="uk-navbar-content">
                <div class="uk-button-group">
                    <button class="uk-button" data-ng-class="mode=='list' ? 'uk-button-primary':''" data-ng-click="setListMode('list')" title="@lang('List mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th"></i></button>
                    <button class="uk-button" data-ng-class="mode=='table' ? 'uk-button-primary':''" data-ng-click="setListMode('table')" title="@lang('Table mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-list-alt"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <div class="uk-grid uk-grid-small" data-uk-grid-margin data-uk-grid-match data-ng-if="regions && regions.length && mode=='list'">
        <div class="uk-width-1-1 uk-width-medium-1-3 uk-width-large-1-4" data-ng-repeat="region in regions" data-ng-show="matchName(region.name)">

            <div class="app-panel app-panel-box">

                <strong>@@ region.name @@</strong>

                <div class="uk-margin">
                    <span class="uk-badge app-badge" title="Last update">@@ region.modified |fmtdate:'d M, Y H:i' @@</span>
                </div>


                <span class="uk-button-group">
                    <a class="uk-button uk-button-small" href="@route('/regions/region')/@@ region._id @@" title="@lang('Edit region')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-pencil"></i></a>
                    @hasaccess?("Regions", 'create.regions')
                    <a class="uk-button uk-button-danger uk-button-small" data-ng-click="remove($index, region)" href="#" title="@lang('Delete region')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-minus-circle"></i></a>
                    @end
                </span>
            </div>
        </div>
    </div>

    <div class="app-panel" data-ng-if="regions && regions.length && mode=='table'">
        <table class="uk-table uk-table-striped">
            <thead>
                <tr>
                    <th>@lang('Region')</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr data-ng-repeat="region in regions" data-ng-show="matchName(region.name)">
                    <td>
                        <a href="@route('/regions/region')/@@ region._id @@">@@ region.name @@</a>
                    </td>
                    <td align="right">
                        @hasaccess?("Regions", 'create.regions')
                        <a class="uk-text-danger" data-ng-click="remove($index, region)" href="#" title="@lang('Delete region')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-minus-circle"></i></a>
                        @end
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <div class="uk-text-center app-panel" data-ng-show="regions && !regions.length">
        <h2><i class="uk-icon-th-large"></i></h2>
        <p class="uk-text-large">
            @lang('You don\'t have any regions created.')
        </p>

        @hasaccess?("Regions", 'create.regions')
        <a href="@route('/regions/region')" class="uk-button uk-button-success uk-button-large">@lang('Create a region')</a>
        @end
    </div>

</div>
