<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li><a href="@route('/webhooks')">@lang('Webhooks')</a></li>
        <li class="uk-active"><span>@lang('Webhook')</span></li>
    </ul>
</div>


<div riot-view>

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
                    <label class="uk-text-small">@lang('Events')</label>

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{!webhook.events.length}">
                        @lang('You have not assign any event yet.')
                    </div>

                    <table class="uk-table uk-table-border" show="{webhook.events.length}">
                        <thead>
                            <tr>
                                <th>@lang('Event')</th>
                                <th width="20"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr each="{event,idx in webhook.events}">
                                <td><i class="uk-icon-bolt uk-margin-small-right uk-text-primary"></i> {event}</td>
                                <td><a class="uk-text-danger" onclick="{ removeEvent }"><i class="uk-icon-trash"></i></a></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="uk-margin uk-form">
                        <div class="uk-form-icon uk-width-1-1 uk-display-block">
                            <i class="uk-icon-bolt"></i>
                            <input class="uk-width-1-1 uk-form-large" type="text" name="event" placeholder="@lang('Add event...')">
                        </div>
                    </div>

                </div>

                <div class="uk-form-row">
                    <button class="uk-button uk-button-large uk-button-primary uk-margin-small-right">@lang('Save')</button>
                    <a href="@route('/webhooks')">@lang('Cancel')</a>
                </div>

            </div>

            <div class="uk-width-medium-1-3">
                <div class="uk-margin" if="{webhook._id}">
                    <label class="uk-text-small">@lang('Last Modified')</label>
                    <div class="uk-margin-small-top uk-text-muted"><i class="uk-icon-calendar uk-margin-small-right"></i> {  App.Utils.dateformat( new Date( 1000 * webhook._modified )) }</div>
                </div>
            </div>
        </div>

    </form>


    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.webhook = {{ json_encode($webhook) }};

        this.on('mount', function(){

            App.$(this.event).on('keydown', function(e) {

                if (e.keyCode == 13) {
                    e.preventDefault();

                    if ($this.webhook.events.indexOf($this.event.value.trim()) != -1) {
                        App.ui.notify("Event already exists");
                    } else {
                        $this.webhook.events.push($this.event.value.trim());
                    }

                    $this.event.value = '';
                    $this.update();

                    return false;
                }

            });

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {
                e.preventDefault();
                $this.submit();
                return false;
            });
        });

        removeEvent(evt) {
            this.webhook.events.splice(evt.item.idx, 1);
        }

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
