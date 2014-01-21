@if(count($forms))

<span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Latest')</span>
<ul class="uk-list uk-list-line">
    @foreach($forms as $form)
    <li><a href="@route('/forms/entries/'.$form['_id'])">{{ $form["name"] }}</a></li>
    @endforeach
</ul>

<a class="uk-button uk-button-success uk-button-small" href="@route('/forms/form')" title="@lang('Add form')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>

@else

<div class="uk-text-center">
    <h2><i class="uk-icon-inbox"></i></h2>
    <p class="uk-text-muted">
        @lang('You don\'t have any forms created.')
    </p>

    <a href="@route('/forms/form')" class="uk-button uk-button-success">@lang('Create a form')</a>
</div>

@endif
