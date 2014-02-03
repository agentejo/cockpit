{{ $app->assets(['addons:assets/addons.js','addons:assets/js/index.js'], $app['cockpit/version']) }}


<h1><a href="@route('/settingspage')">@lang('Settings')</a> / @lang('Addons')</h1>

<div data-ng-controller="addons">


    @if(count($addons))

        <table class="uk-table">
            <tbody>
                @foreach($addons as $addon)
                <tr>
                    <td>{{ $addon }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @else

        <div class="uk-alert">
            @lang('No additional addons installed.')
        </div>

    @endif


</div>
