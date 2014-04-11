{{ $app->assets(['addons:assets/addons.js','addons:assets/js/index.js'], $app['cockpit/version']) }}


<h1><a href="@route('/settingspage')">@lang('Settings')</a> / @lang('Addons')</h1>

<div data-ng-controller="addons" ng-cloak>

    <div class="uk-grid">

        <div class="uk-width-3-4">

            <div class="app-panel">

                <table class="uk-table" ng-show="addons.length">
                    <tbody>
                        <tr ng-repeat="addon in addons">
                            <td>@@ addon.name @@</td>
                            <td style="width:10%;text-align:right;">@@ addon.version @@</td>
                        </tr>
                    </tbody>
                </table>


                <div class="uk-text-center" ng-show="!addons.length">
                    <h2><i class="uk-icon-code-fork"></i></h2>
                    <p class="uk-text-large">
                        @lang('No additional addons installed.')
                    </p>
                </div>

            </div>

        </div>

        <div class="uk-width-1-4">



        </div>

    </div>

</div>


<script>

    window.ADDONS = {{ json_encode($addons) }};

</script>
