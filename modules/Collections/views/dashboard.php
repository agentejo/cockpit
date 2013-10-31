@if(count($collections))

<span class="uk-text-small uk-text-muted">LATEST</span>
<ul class="uk-list uk-list-line">
    @foreach($collections as $collection)
    <li><a href="@route('/collections/entries/'.$collection['_id'])">{{ $collection["name"] }}</a></li>
    @endforeach
</ul>

@endif

<a class="uk-button uk-button-success uk-button-small" href="@route('/collections/collection')" title="Add collection" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-sign"></i></a>