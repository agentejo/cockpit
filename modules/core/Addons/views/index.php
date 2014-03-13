{{ $app->assets(['addons:assets/addons.js','addons:assets/js/index.js'], $app['cockpit/version']) }}


<h1><a href="@route('/settingspage')">@lang('Settings')</a> / @lang('Addons')</h1>

<div data-ng-controller="addons">


    <table class="uk-table" ng-show="addons.length">
        <tbody>
            @foreach($addons as $addon)
            <tr>
                <td>{{ $addon["name"] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <div class="uk-alert" ng-show="!addons.length">
        @lang('No additional addons installed.')
    </div>



</div>


<script>

    window.ADDONS = {{ json_encode($addons) }};

</script>
