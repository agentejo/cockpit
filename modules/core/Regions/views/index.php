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
    </nav>

    <div class="uk-grid" data-uk-grid-margin data-uk-grid-match>
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
