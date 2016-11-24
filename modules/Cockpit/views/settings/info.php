
<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('System Information')</span></li>
    </ul>
</div>

<div riot-view>

    <div class="uk-grid" data-uk-grid-margin>

        <div class="uk-width-medium-3-4">

            <div id="settings-info" class="uk-switcher">

                <div>

                    <p><strong><span class="uk-badge app-badge">System</span></strong></p>

                    <strong>General</strong>
                    <table class="uk-table uk-table-striped">
                        <tbody>
                            <tr>
                                <td width="30%">Version</td>
                                <td>{{ $info['app']['version'] }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <strong>Cache</strong>

                    <div class="uk-margin">

                        <div if="{ cacheSize }">
                            { cacheSize } <a title="@lang('Clean up')" data-uk-tooltip="pos:'right'" onclick="{cleanUpCache}"><i class="uk-icon-trash-o"></i></a>
                        </div>

                        <div class="uk-alert" if="{ cleaning }">
                            <i class="uk-icon-spinner uk-icon-spin"></i> @lang('Cleaning up...')
                        </div>

                        <div class="uk-alert uk-alert-success" if="{ !cacheSize }">
                            @lang('Cache is clean')
                        </div>
                    </div>

                </div>

                <div>
                    <p>
                        <strong><span class="uk-badge app-badge">PHP</span></strong>
                    </p>
                    <table class="uk-table uk-table-striped">
                        <tbody>
                            <tr>
                                <td width="30%">Version</td>
                                <td>{{ $info['phpversion'] }}</td>
                            </tr>
                            <tr>
                                <td width="30%">PHP SAPI</td>
                                <td>{{ $info['sapi_name'] }}</td>
                            </tr>
                            <tr>
                                <td width="30%">System</td>
                                <td>{{ $info['system'] }}</td>
                            </tr>
                            <tr>
                                <td width="30%">Loaded Extensions</td>
                                <td>{{ implode(", ", $info['extensions']) }}</td>
                            </tr>
                            <tr>
                                <td width="30%">Memory limit</td>
                                <td>{{ ini_get("memory_limit") }}</td>
                            </tr>
                            <tr>
                                <td width="30%">Upload file size limit</td>
                                <td>{{ ini_get("upload_max_filesize") }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            @trigger("cockpit.settings.infopage.main")

        </div>

        <div class="uk-width-medium-1-4">
            <ul class="uk-nav uk-nav-side" data-uk-switcher="connect:'#settings-info'">
                <li><a href="#SYSTEM">System</a></li>
                <li><a href="#PHP">PHP</a></li>
            </ul>

            @trigger("cockpit.settings.infopage.aside")
        </div>

        <script type="view/script">

            var $this = this;

            this.cacheSize = {{ $info['cacheSize'] ? '"'.$info['cacheSize'].'"':0 }};

            cleanUpCache() {

                this.cleaning = true;

                App.callmodule('cockpit:clearCache').then(function(){
                    setTimeout(function(){
                        $this.cleaning = false;
                        $this.cacheSize = 0;
                        $this.update();
                    }, 1000);
                });
            }

        </script>

    </div>

</div>
