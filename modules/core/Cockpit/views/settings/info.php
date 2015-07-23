
<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('System Information')</span></li>
    </ul>
</div>

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
        <ul class="uk-nav uk-nav-side" data-uk-switcher="{connect:'#settings-info'}">
            <li><a href="#SYSTEM">System</a></li>
            <li><a href="#PHP">PHP</a></li>
        </ul>

        @trigger("cockpit.settings.infopage.aside")
    </div>

</div>
