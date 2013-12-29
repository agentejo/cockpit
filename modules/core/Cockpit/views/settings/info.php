
<h1><a href="@route('/settingspage')">Settings</a> / System Information</h1>

<div class="app-panel uk-width-medium-3-4">

    <h3>PHP</h3>
    
    <table class="uk-table uk-table-striped">
        <tbody>
            <tr>
                <td>Version</td>
                <td>{{ $info['phpversion'] }}</td>
            </tr>
            <tr>
                <td>PHP to Webserver interface</td>
                <td>{{ $info['sapi_name'] }}</td>
            </tr>
            <tr>
                <td>System</td>
                <td>{{ $info['system'] }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Mailer</h3>
    
    @if($info["mailer"])

    <table class="uk-table uk-table-striped">
        <tbody>
            @foreach($info['mailer'] as $key => $value)
            <tr>
                <td>{{ $key }}</td>
                <td>{{ ($key=="password") ? str_pad("", strlen($value), '*') : $value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <button id="btnTestEmail" class="uk-button uk-button-primary"><i class="uk-icon-envelope-o"></i> Send test email</button>

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


    <h3>Directories</h3>
    
    <table class="uk-table uk-table-striped">
        <tbody>
            <tr>
                <th>Path</th>
                <th>Status</th>
            </tr>
        </tbody>
        <tbody>
            @foreach($info['folders'] as $folder=>$permission)
            <tr>
                <td>{{ $app->pathToUrl($folder) }}</td>
                <td><div class="uk-badge uk-badge-{{ $permission ? 'success':'danger' }}">writable</div></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @trigger("cockpit.settings.infopage")

</div>