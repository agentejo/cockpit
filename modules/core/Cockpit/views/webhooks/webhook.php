<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li><a href="@route('/webhooks')">@lang('Webhooks')</a></li>
        <li class="uk-active"><span>@lang('Webhook')</span></li>
    </ul>
</div>


<div class="" riot-view>

    <form class="uk-form" onsubmit="{ submit }">

        <div class="uk-grid" data-uk-grid-margin>

            <div class="uk-width-medium-2-3">

                <div class="uk-form-row">
                    <label class="uk-text-small">@lang('Name')</label>
                    <input class="uk-width-1-1 uk-form-large" type="text" bind="webhook.name" required>
                </div>

                <div class="uk-form-row">
                    <label class="uk-text-small">@lang('Url')</label>
                    <input class="uk-width-1-1 uk-form-large" type="url" bind="webhook.url" required>
                </div>

                <div class="uk-form-row">
                    <button class="uk-button uk-button-large uk-button-primary uk-margin-small-right">@lang('Save')</button>
                    <a href="@route('/webhooks')">@lang('Cancel')</a>
                </div>


            </div>

            <div class="uk-width-medium-1-3">

            </div>
        </div>



    </form>


    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.webhook = {{ json_encode($webhook) }};


        submit() {

            App.request('/webhooks/save', {webhook: this.webhook}).then(function(data) {

                if (data) {

                    App.ui.notify("Saving successful", "success");

                    $this.webhook = data;

                    $this.update();

                } else {
                    App.ui.notify("Saving failed.", "danger");
                }
            });
        }


    </script>

</div>
