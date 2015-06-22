<ul class="uk-nav uk-nav-side uk-nav-dropdown uk-margin-top">

    <li class="uk-nav-header"><i class="uk-icon-justify uk-icon-inbox"></i> @lang('Forms')</li>

    @foreach($forms as $form)
    <li>
        <a href="@route('/forms/entries/'.$form['name'])">
        {{ $form['label'] ? $form['label'] : $form['name'] }}
        </a>
    </li>
    @endforeach
</ul>