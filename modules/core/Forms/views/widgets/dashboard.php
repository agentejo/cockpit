<div class="uk-grid-margin uk-width-1-1">

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header">
            <strong>@lang('Forms')</strong> <span class="uk-badge">{{ count($forms) }}</span>
        </div>

        @if(count($forms))

            <div class="uk-margin">

                <ul class="uk-list uk-margin-top">
                    @foreach(array_slice($forms, 0, count($forms) > 5 ? 5: count($forms)) as $form)
                    <li><a href="@route('/forms/entries/'.$form['name'])"><i class="uk-icon-justify uk-icon-list"></i> {{ @$form['label'] ? $form['label'] : $form['name'] }}</a></li>
                    @endforeach
                </ul>

            </div>

            <div class="uk-panel-box-footer uk-bg-light">
                <a href="@route('/forms')">@lang('See all')</a>
            </div>

        @else

            <div class="uk-margin uk-text-center uk-text-muted">

                <p class="uk-text-large">
                    <i class="uk-icon-list"></i>
                </p>

                @lang('No forms'). <a href="@route('/forms/form')">@lang('Create a form')</a>.

            </div>

        @endif

    </div>

</div>
