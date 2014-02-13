{{ $app->assets(['collections:assets/collections.js','collections:assets/js/index.js'], $app['cockpit/version']) }}


<div data-ng-controller="collections">

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">@lang('Collections')</span>
        <div class="uk-navbar-content">
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
    </nav>

    <div class="uk-grid" data-uk-grid-margin data-uk-grid-match>
        <div class="uk-width-1-1 uk-width-medium-1-3 uk-width-large-1-4" data-ng-repeat="collection in collections" data-ng-show="matchName(collection.name)">

            <div class="app-panel app-panel-box">

                <strong>@@ collection.name @@</strong>

                <div class="uk-margin">
                    <span class="uk-badge app-badge">@@ collection.count @@ @lang('Entries')</span>
                </div>

                <span class="uk-button-group">
                    <a class="uk-button uk-button-small" href="@route('/collections/entries')/@@ collection._id @@" title="@lang('Show entries')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-bars"></i></a>
                    <a class="uk-button uk-button-small" href="@route('/collections/entry')/@@ collection._id @@" title="@lang('Create new entry')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
                    @hasaccess?("Collections", 'manage.collections')
                    <a class="uk-button uk-button-small" href="@route('/collections/collection')/@@ collection._id @@" title="@lang('Edit collection')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-pencil"></i></a>
                    <a class="uk-button uk-button-danger uk-button-small" data-ng-click="remove($index, collection)" href="#" title="@lang('Delete collection')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-minus-circle"></i></a>
                    @end
                </span>
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