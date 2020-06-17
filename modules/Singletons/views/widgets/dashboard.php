<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex uk-flex-middle">
            <strong class="uk-panel-box-header-title uk-flex-item-1">
                @lang('Singletons')

                @hasaccess?('singletons', 'create')
                <a href="@route('/singletons/singleton')" class="uk-icon-plus uk-margin-small-left" title="@lang('Create Singleton')" data-uk-tooltip></a>
                @end
            </strong>

            @if(count($singletons))
            <span class="uk-badge uk-flex uk-flex-middle"><span>{{ count($singletons) }}</span></span>
            @endif
        </div>

        @if(count($singletons))

            <div class="uk-margin">

                <ul class="uk-list uk-list-space uk-margin-top">
                    @foreach(array_slice($singletons, 0, count($singletons) > 5 ? 5: count($singletons)) as $singleton)
                    <li class="uk-text-truncate">
                        <a class="uk-link-muted" href="@route('/singletons/form/'.$singleton['name'])">

                            <img class="uk-margin-small-right uk-svg-adjust" src="@url(isset($singleton['icon']) && $singleton['icon'] ? 'assets:app/media/icons/'.$singleton['icon']:'singletons:icon.svg')" width="18px" alt="icon" data-uk-svg>

                            {{ htmlspecialchars(@$singleton['label'] ? $singleton['label'] : $singleton['name'], ENT_QUOTES, 'UTF-8') }}
                        </a>
                    </li>
                    @endforeach
                </ul>

            </div>

            @if(count($singletons) > 5)
            <div class="uk-panel-box-footer uk-text-center">
                <a class="uk-button uk-button-small uk-button-link" href="@route('/singletons')">@lang('Show all')</a>
            </div>
            @endif

        @else

            <div class="uk-margin uk-text-center uk-text-muted">

                <p>
                    <img src="@url('singletons:icon.svg')" width="30" height="30" alt="Singletons" data-uk-svg />
                </p>

                @lang('No singletons')

            </div>

        @endif

    </div>

</div>
