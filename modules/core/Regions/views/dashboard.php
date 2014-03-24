@if(count($regions))

    <div class="uk-margin-bottom">
        <span class="uk-button-group">
            @hasaccess?("Regions", 'create.regions')
            <a class="uk-button uk-button-success uk-button-small" href="@route('/regions/region')" title="@lang('Add region')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
            @end
            <a class="uk-button app-button-secondary uk-button-small" href="@route('/regions')" title="@lang('Show all regions')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-ellipsis-h"></i></a>
        </span>
    </div>

    <span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Latest')</span>
    <ul class="uk-list uk-list-line">
        @foreach($regions as $region)
        <li><a href="@route('/regions/region/'.$region['_id'])"><i class="uk-icon-map-marker"></i> {{ $region["name"] }}</a></li>
        @endforeach
    </ul>

@else

    <div class="uk-text-center">
        <h2><i class="uk-icon-th-large"></i></h2>
        <p class="uk-text-muted">
            @lang('You don\'t have any regions created.')
        </p>

        @hasaccess?("Regions", 'create.regions')
        <a href="@route('/regions/region')" class="uk-button uk-button-success" title="@lang('Create a region')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
        @end
    </div>

@endif