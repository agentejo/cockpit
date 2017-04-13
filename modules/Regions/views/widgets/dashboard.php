<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex">
            <strong class="uk-panel-box-header-title uk-flex-item-1">
                @lang('Regions')

                @hasaccess?('regions', 'create')
                <a href="@route('/regions/region')" class="uk-icon-plus uk-margin-small-left" title="@lang('Create Region')" data-uk-tooltip></a>
                @end
            </strong>

            @if(count($regions))
            <span class="uk-badge uk-flex uk-flex-middle"><span>{{ count($regions) }}</span></span>
            @endif
        </div>

        @if(count($regions))

            <div class="uk-margin">

                <ul class="uk-list uk-list-space uk-margin-top">
                    @foreach(array_slice($regions, 0, count($regions) > 5 ? 5: count($regions)) as $region)
                    <li>
                        <a href="@route('/regions/form/'.$region['name'])">

                            <img class="uk-margin-small-right uk-svg-adjust" src="@url(isset($region['icon']) && $region['icon'] ? 'assets:app/media/icons/'.$region['icon']:'regions:icon.svg')" width="18px" alt="icon" data-uk-svg>

                            {{ @$region['label'] ? $region['label'] : $region['name'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>

            </div>

            <div class="uk-panel-box-footer">
                <a href="@route('/regions')">@lang('See all')</a>
            </div>

        @else

            <div class="uk-margin uk-text-center uk-text-muted">

                <p>
                    <img src="@url('regions:icon.svg')" width="30" height="30" alt="Regions" data-uk-svg />
                </p>

                @lang('No regions').

                @hasaccess?('regions', 'create')
                <a href="@route('/regions/region')">@lang('Create a region')</a>.
                @end

            </div>

        @endif

    </div>

</div>
