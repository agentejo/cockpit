@if(count($forms))

<span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Latest')</span>
<ul class="uk-list uk-list-line">
    @foreach($forms as $form)
    <li><a href="@route('/forms/entries/'.$form['_id'])">{{ $form["name"] }}</a></li>
    @endforeach
</ul>

@endif

<a class="uk-button uk-button-success uk-button-small" href="@route('/forms/form')" title="@lang('Add form')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>