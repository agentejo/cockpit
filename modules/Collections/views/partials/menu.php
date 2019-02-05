<ul class="uk-nav uk-nav-side uk-nav-dropdown uk-margin-top">

    <li class="uk-nav-header">@lang('Collections')</li>

    @foreach($collections as $collection)
    <li>
        <a class="uk-flex uk-flex-middle" href="@route('/collections/entries/'.$collection['name'])">
            <i class="uk-icon-justify"><img class="uk-svg-adjust" src="@base('collections:icon.svg')" width="20" height="20" data-uk-svg></i> {{ htmlspecialchars($collection['label'] ? $collection['label'] : $collection['name']) }}
        </a>
    </li>
    @endforeach
</ul>
