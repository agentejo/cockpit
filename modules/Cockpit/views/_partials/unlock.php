@if($app->module('cockpit')->hasaccess('cockpit', 'unlockresources'))
<div>

  <div class="uk-margin-top">
    <button class="uk-button uk-button-danger" onclick="{unlock}"><i class="uk-icon-unlock"></i> @lang('Unlock')</button>
  </div>

  <script type="view/script">
      var $this = this;
      this.resource = {{ json_encode($resource) }};

      unlock() {
          App.request('/cockpit/utils/unlockResourceId/'+$this.resource._id, {}).then(function(data) {
              if (data && data.success) {
                  location.reload();
              } else {
                  App.ui.notify("Error during unlock operation", "danger");
              }
          });
      }
  </script>

</div>
@endif
