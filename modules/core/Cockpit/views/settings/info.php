
<h1><a href="@route('/settingspage')">@lang('Settings')</a> / System Information</h1>

<div class="uk-grid" data-uk-grid-margin>

    <div class="uk-width-medium-3-4">
        <div class="app-panel">
            <p id="SYSTEM">
                <strong><span class="uk-badge app-badge">System</span></strong>
            </p>

            <strong>General</strong>
            <table class="uk-table uk-table-striped">
                <tbody>
                    <tr>
                        <td width="30%">Version</td>
                        <td>{{ $info['app']['version'] }}</td>
                    </tr>
                    <tr>
                        <td width="30%">Cache size</td>
                        <td>{{ $info['sizeCache'] }}</td>
                    </tr>
                    <tr>
                        <td width="30%">Data size</td>
                        <td>{{ $info['sizeData'] }}</td>
                    </tr>
            </table>


            <strong>Mailer</strong>

            @if($info["mailer"])

            <table class="uk-table uk-table-striped">
                <tbody>
                    @foreach($info['mailer'] as $key => $value)
                    <tr>
                        <td width="30%">{{ $key }}</td>
                        <td>{{ ($key=="password") ? str_pad("", strlen($value), '*') : $value }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p>
                <button id="btnTestEmail" class="uk-button uk-button-primary"><i class="uk-icon-envelope-o"></i> Send test email</button>
            </p>

            <script>

                $("#btnTestEmail").on("click", function(){

                    var email = prompt("Send test email to:", '{{ @$info['mailer']['from'] }}');

                    if(email && email.match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/)) {

                        App.request('/settings/test/email', {"email":email}, function(data){
                            App.notify(data.status ? 'Email was sent. Please check your mailbox.': 'Sending email failed.', data.status ? 'info':'danger');
                        }, "json");

                    } else {
                        App.notify("Please provide a valid email adress", "danger");
                    }

                });
            </script>

            @else

            <div class="uk-alert">
                No mailer settings found.
            </div>

            @endif


            <strong>Directories</strong>

            <table class="uk-table uk-table-striped">
                <thead class="uk-text-small">
                    <tr>
                        <th>Path</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($info['folders'] as $folder=>$permission)
                    <tr>
                        <td>{{ $app->pathToUrl($folder) }}</td>
                        <td><div class="uk-badge uk-badge-{{ $permission ? 'success':'danger' }}">writable</div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <hr>

            <p id="PHP">
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

            @trigger("cockpit.settings.infopage.main")
        </div>
    </div>

    <div class="uk-width-medium-1-4">
        <ul class="uk-nav uk-nav-side">
            <li class="uk-nav-header">Index</li>
            <li><a href="#SYSTEM">System</a></li>
            <li><a href="#PHP">PHP</a></li>
        </ul>

        @trigger("cockpit.settings.infopage.aside")
    </div>

</div>