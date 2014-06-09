@if(count($collections))

    <div class="uk-margin-bottom">
        <span class="uk-button-group">
            @hasaccess?("Addons", 'manage.logger')
            <a class="uk-button uk-button-success uk-button-small" href="@route('/logger/logger')" title="@lang('Add LogEntry')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
            @end
            <a class="uk-button app-button-secondary uk-button-small" href="@route('/logger')" title="@lang('Show all Entries')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-ellipsis-h"></i></a>
        </span>
    </div>


    <span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Latest')</span>
    <ul class="uk-list uk-list-line">
        @foreach($collections as $collection)
        <li><a href="@route('/logger/'.$collection['_id'])"><i class="uk-icon-map-marker"></i> {{ $collection["message"] }}</a></li>
        @endforeach
    </ul>

@else

    <div class="uk-text-center">
        <h2><i class="uk-icon-list"></i></h2>
        <p class="uk-text-muted">
            @lang('You don\'t have any Log Entries.')
        </p>
        @hasaccess?("Addons", 'manage.logger')
        <a href="@route('/logger')" class="uk-button uk-button-success" title="@lang('Create a LogEntry')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
        @end
    </div>

@endif