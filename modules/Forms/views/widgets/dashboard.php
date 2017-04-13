<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex">
            <strong class="uk-panel-box-header-title uk-flex-item-1">
                @lang('Forms')

                @hasaccess?('forms', 'create')
                <a href="@route('/forms/form')" class="uk-icon-plus uk-margin-small-left" title="@lang('Create Form')" data-uk-tooltip></a>
                @end
            </strong>

            @if(count($forms))
            <span class="uk-badge uk-flex uk-flex-middle"><span>{{ count($forms) }}</span></span>
            @endif
        </div>

        @if(count($forms))

            <div class="uk-margin">

                <ul class="uk-list uk-list-space uk-margin-top">
                    @foreach(array_slice($forms, 0, count($forms) > 5 ? 5: count($forms)) as $form)
                    <li>
                        <a href="@route('/forms/entries/'.$form['name'])">

                            <img class="uk-margin-small-right uk-svg-adjust" src="@url(isset($form['icon']) && $form['icon'] ? 'assets:app/media/icons/'.$form['icon']:'forms:icon.svg')" width="18px" alt="icon" data-uk-svg>

                            {{ @$form['label'] ? $form['label'] : $form['name'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>

            </div>

            <div class="uk-panel-box-footer">
                <a href="@route('/forms')">@lang('See all')</a>
            </div>

        @else

            <div class="uk-margin uk-text-center uk-text-muted">

                <p>
                    <img src="@url('forms:icon.svg')" width="30" height="30" alt="Forms" data-uk-svg />
                </p>

                @lang('No forms'). 

                @hasaccess?('forms', 'create')
                <a href="@route('/forms/form')">@lang('Create a form')</a>.
                @end

            </div>

        @endif

    </div>

</div>
