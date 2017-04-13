<ul class="uk-nav uk-nav-side uk-nav-dropdown uk-margin-top">

    <li class="uk-nav-header">@lang('Collections')</li>

    @foreach($collections as $collection)
    <li>
        <a href="@route('/collections/entries/'.$collection['name'])">
        <i class="uk-icon-justify uk-icon-list"></i> {{ $collection['label'] ? $collection['label'] : $collection['name'] }}
        </a>
    </li>
    @endforeach
</ul>