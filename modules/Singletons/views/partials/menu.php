<ul class="uk-nav uk-nav-side uk-nav-dropdown uk-margin-top">

    <li class="uk-nav-header">@lang('Singletons')</li>

    @foreach($singletons as $singleton)
    <li>
        <a class="uk-flex uk-flex-middle" href="@route('/singletons/form/'.$singleton['name'])">
            <i class="uk-icon-justify"><img class="uk-svg-adjust" src="@base('singletons:icon.svg')" width="20" height="20" data-uk-svg></i> {{ htmlspecialchars($singleton['label'] ? $singleton['label'] : $singleton['name']) }}
        </a>
    </li>
    @endforeach
</ul>
