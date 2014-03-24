@if(count($galleries))

    <div class="uk-margin-bottom">
        <span class="uk-button-group">
            @hasaccess?("Galleries", 'create.gallery')
            <a class="uk-button uk-button-success uk-button-small" href="@route('/galleries/gallery')" title="@lang('Add gallery')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
            @end
            <a class="uk-button app-button-secondary uk-button-small" href="@route('/galleries')" title="@lang('Show all galleries')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-ellipsis-h"></i></a>
        </span>
    </div>

    <span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Latest')</span>
    <ul class="uk-list uk-list-space">
        @foreach($galleries as $gallery)
        <li>
            <a href="@route('/galleries/gallery/'.$gallery['_id'])">
                <i class="uk-icon-map-marker"></i> {{ $gallery["name"] }}
                @if(count($gallery["images"]))
                <div class="uk-margin-small-top">
                    @foreach(array_slice($gallery["images"], 0, 6) as $image)
                    <div class="uk-thumbnail uk-rounded uk-thumb-small">
                        <img src="@thumbnail_url($image['path'], 25, 25)" width="25" height="25" title="{{ $image['path'] }}">
                    </div>
                    @endforeach
                </div>
                @endif
            </a>
        </li>
        @endforeach
    </ul>
@else

    <div class="uk-text-center">
        <h2><i class="uk-icon-picture-o"></i></h2>
        <p class="uk-text-muted">
            @lang('You don\'t have any galleries created.')
        </p>

        @hasaccess?("Galleries", 'create.gallery')
        <a href="@route('/galleries/gallery')" class="uk-button uk-button-success" title="@lang('Create a gallery')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
        @end
    </div>

@endif