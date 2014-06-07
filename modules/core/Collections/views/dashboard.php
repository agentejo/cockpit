@if(count($collections))

    <div class="uk-margin-bottom">
        <span class="uk-button-group">
            @hasaccess?("Collections", 'manage.collections')
            <a class="uk-button uk-button-success uk-button-small" href="@route('/collections/collection')" title="@lang('Add collection')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
            @end
            <a class="uk-button app-button-secondary uk-button-small" href="@route('/collections')" title="@lang('Show all collections')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-ellipsis-h"></i></a>
        </span>
    </div>


    <span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Latest')</span>
    <ul class="uk-list uk-list-space">
        @foreach($collections as $collection)
        <li><a href="@route('/collections/entries/'.$collection['_id'])"><i class="uk-icon-map-marker"></i> {{ $collection["name"] }}</a></li>
        @endforeach
    </ul>

@else

    <div class="uk-text-center">
        <h2><i class="uk-icon-list"></i></h2>
        <p class="uk-text-muted">
            @lang('You don\'t have any collections created.')
        </p>
        @hasaccess?("Collections", 'manage.collections')
        <a href="@route('/collections/collection')" class="uk-button uk-button-success" title="@lang('Create a collection')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
        @end
    </div>

@endif