<ul class="uk-nav uk-nav-side uk-nav-dropdown uk-margin-top">

    <li class="uk-nav-header">@lang('Singletons')</li>

    @foreach($singletons as $singleton)
    <li>
        <a href="@route('/singletons/form/'.$singleton['name'])">
        <i class="uk-icon-justify uk-icon-list"></i> {{ htmlspecialchars($singleton['label'] ? $singleton['label'] : $singleton['name']) }}
        </a>
    </li>
    @endforeach
</ul>
