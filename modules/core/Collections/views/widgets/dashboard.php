<div class="uk-width-1-1">

    <div class="uk-panel-box">

        <div class="uk-panel-box-header">
            <strong>@lang('Collections')</strong> <span class="uk-badge">{{ count($collections) }}</span>
        </div>

        @if(count($collections))

            <div class="uk-margin">

                <ul class="uk-list uk-margin-top">
                    @foreach(array_slice($collections, 0, count($collections) > 5 ? 5: count($collections)) as $col)
                    <li><a href="@route('/collections/entries/'.$col['name'])"><i class="uk-icon-justify uk-icon-list"></i> {{ @$col['label'] ? $col['label'] : $col['name'] }}</a></li>
                    @endforeach
                </ul>

            </div>

            <div class="uk-panel-box-footer uk-bg-light">
                <a href="@route('/collections')">@lang('See all')</a>
            </div>

        @else

            <div class="uk-margin">


            </div>

        @endif

    </div>

</div>
