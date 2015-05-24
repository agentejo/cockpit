<div class="uk-width-1-1">

    <div class="uk-panel uk-panel-box">

        @if(count($collections))
        <div class="uk-margin">

            <strong>@lang('Collections')</strong> <span class="uk-badge">{{ count($collections) }}</span>

            <ul class="uk-list uk-margin-top">
                @foreach($collections as $col)
                <li><a href="@route('/collections/entries/'.$col['name'])"><i class="uk-icon-justify uk-icon-list"></i> {{ @$col['label'] ? $col['label'] : $col['name'] }}</a></li>
                @endforeach
            </ul>

        </div>
        @else
        <div class="uk-margin">


        </div>
        @endif

    </div>

</div>
