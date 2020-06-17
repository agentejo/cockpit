<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex uk-flex-middle">
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
                    <li class="uk-text-truncate">
                        <a class="uk-link-muted" href="@route('/forms/entries/'.$form['name'])">

                            <img class="uk-margin-small-right uk-svg-adjust" src="@url(isset($form['icon']) && $form['icon'] ? 'assets:app/media/icons/'.$form['icon']:'forms:icon.svg')" width="18px" alt="icon" data-uk-svg>

                            {{ htmlspecialchars(@$form['label'] ? $form['label'] : $form['name'], ENT_QUOTES, 'UTF-8') }}
                        </a>
                    </li>
                    @endforeach
                </ul>

            </div>

            @if(count($forms) > 5)
            <div class="uk-panel-box-footer uk-text-center">
                <a class="uk-button uk-button-small uk-button-link" href="@route('/forms')">@lang('Show all')</a>
            </div>
            @endif

        @else

            <div class="uk-margin uk-text-center uk-text-muted">

                <p>
                    <img src="@url('forms:icon.svg')" width="30" height="30" alt="Forms" data-uk-svg />
                </p>

                @lang('No forms')

            </div>

        @endif

    </div>

</div>
