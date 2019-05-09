@if($app->module('cockpit')->hasaccess('cockpit', 'unlockresources'))
<div class="uk-margin-large-top uk-text-center">

    <hr>

    <div>
        <button class="uk-button uk-button-large uk-button-danger" onclick="btnUnlockResource()"><i class="uk-icon-unlock"></i> @lang('Unlock')</button>
    </div>

    <script>

        window.btnUnlockResource = function() {

            var resourceId = '{{ $resourceId }}';

            App.request('/cockpit/utils/unlockResourceId/'+resourceId, {}).then(function(data) {
                
                if (data && data.success) {
                    location.reload();
                } else {
                    App.ui.notify('Error during unlock operation', 'danger');
                }

            }.bind(this));
        }
    </script>

</div>
@endif
