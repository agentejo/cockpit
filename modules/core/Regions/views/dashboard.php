@if(count($regions))

<span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Latest')</span>
<ul class="uk-list uk-list-line">
    @foreach($regions as $region)
    <li><a href="@route('/regions/region/'.$region['_id'])">{{ $region["name"] }}</a></li>
    @endforeach
</ul>

@endif

<a class="uk-button uk-button-success uk-button-small" href="@route('/regions/region')" title="@lang('Add region')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>