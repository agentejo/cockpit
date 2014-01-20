@if(count($galleries))

<span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Latest')</span>
<ul class="uk-list uk-list-line">
    @foreach($galleries as $gallery)
    <li><a href="@route('/galleries/gallery/'.$gallery['_id'])">{{ $gallery["name"] }}</a></li>
    @endforeach
</ul>

@endif

<a class="uk-button uk-button-success uk-button-small" href="@route('/galleries/gallery')" title="@lang('Add gallery')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>