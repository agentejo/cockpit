<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('Webhooks')</span></li>
    </ul>
</div>

@if(!function_exists('curl_init'))
<div class="uk-alert uk-alert-large uk-alert-danger">
    <p>Please make sure that the <strong>cURL</strong> extension is loaded.</p>
</div>
@end

<div class="uk-form" riot-view>

    <div class="uk-form uk-clearfix" if="{ App.Utils.count(webhooks) }">

        <span class="uk-form-icon">
            <i class="uk-icon-filter"></i>
            <input type="text" class="uk-form-large uk-form-blank" placeholder="@lang('Filter by name...')" onkeyup="{ updatefilter }">
        </span>

        <div class="uk-float-right">

            <a class="uk-button uk-button-large uk-button-danger uk-animation-fade" onclick="{ removeselected }" if="{ selected.length }">
                @lang('Delete') <span class="uk-badge uk-badge-contrast uk-margin-small-left">{ selected.length }</span>
            </a>

            <a class="uk-button uk-button-primary uk-button-large" href="@route('/webhooks/webhook')">
                <i class="uk-icon-plus-circle uk-icon-justify"></i> @lang('Webhook')
            </a>
        </div>

    </div>

    <div class="uk-margin-large-top uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(webhooks) }">

        <div class="uk-width-medium-1-3 uk-animation-scale">

            <p>
                <img src="@url('assets:app/media/icons/webhooks.svg')" width="80" height="80" alt="Webhooks" data-uk-svg />
            </p>
            <hr>
            <span class="uk-text-large uk-text-muted">@lang('No Webhooks'). <a href="@route('/webhooks/webhook')">@lang('Create a webhook').</a></span>

        </div>

    </div>

    <table class="uk-table uk-table-tabbed uk-table-striped uk-margin-top" if="{ webhooks.length }">
        <thead>
            <tr>
                <th width="20"><input type="checkbox" data-check="all"></th>
                <th width="20"></th>
                <th>@lang('Name')</th>
                <th>@lang('Url')</th>
                <th>@lang('Events')</th>
                <th>@lang('Modified')</th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
            <tr each="{webhook,idx in webhooks}" show="{infilter(webhook)}">
                <td><input type="checkbox" data-check data-id="{ webhook._id }"></td>
                <td class="uk-text-center">
                    <a onclick="{ toggleStatus }" title="@lang('Toggle status')" data-uk-tooltip="pos:'left'"><i class="uk-icon-circle{webhook.active ? '':'-thin'} uk-text-{webhook.active ? 'success':'danger'}"></i></a>
                </td>
                <td><a href="@route('/webhooks/webhook')/{ webhook._id }">{ webhook.name }</a></td>
                <td><a class="uk-text-muted uk-text-truncate" href="@route('/webhooks/webhook')/{ webhook._id }">{ webhook.url }</a></td>
                <td><span class="uk-badge {!webhook.events.length && 'uk-badge-danger'}">{webhook.events.length}</span></td>
                <td>{App.Utils.dateformat( new Date( 1000 * webhook._modified ))}</td>
                <td>
                    <span data-uk-dropdown="mode:'click'">

                        <a class="uk-icon-bars"></a>

                        <div class="uk-dropdown uk-dropdown-flip">
                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-nav-header">@lang('Actions')</li>
                                <li><a href="@route('/webhooks/webhook')/{ webhook._id }">@lang('Edit')</a></li>
                                <li><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                            </ul>
                        </div>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>


    <script type="view/script">

        var $this = this, $root = App.$(this.root);

        this.webhooks = {{ json_encode($webhooks) }};
        this.filter   = '';
        this.selected = [];

        this.on('mount', function(){

            $root.on('click', '[data-check]', function() {

                if (this.getAttribute('data-check') == 'all') {
                    $root.find('[data-check][data-id]').prop('checked', this.checked);
                }

                $this.checkselected();

                $this.update();
            });
        });

        remove(evt) {

            var webhook = evt.item.webhook;

            App.ui.confirm("Are you sure?", function() {

                App.request('/webhooks/remove', { "webhook": webhook }).then(function(data){

                    App.ui.notify("Webhook removed", "success");
                    $this.webhooks.splice(evt.item.idx, 1);
                    $this.update();
                });
            });
        }

        removeselected() {

            if (this.selected.length) {

                App.ui.confirm("Are you sure?", function() {

                    var promises = [];

                    this.webhooks = this.webhooks.filter(function(webhook, yepp){

                        yepp = ($this.selected.indexOf(webhook._id) === -1);

                        if (!yepp) {
                            promises.push(App.request('/webhooks/remove', { "webhook": webhook }));
                        }

                        return yepp;
                    });

                    Promise.all(promises).then(function(){
                        App.ui.notify("Webhooks removed", "success");
                    });

                    this.selected = [];

                    this.update();

                }.bind(this));
            }
        }

        toggleStatus(evt) {

            var webhook = evt.item.webhook;

            webhook.active = !webhook.active;

            App.request('/webhooks/save', {webhook: webhook}).then(function(data) {

                if (data) {
                    App.ui.notify("Status updated", "success");
                    $this.update();
                }
            });
        }

        updatefilter(evt) {
            this.filter = evt.target.value.toLowerCase();
        }

        infilter(webhook) {
            var name = webhook.name.toLowerCase();
            return (!this.filter || (name && name.indexOf(this.filter) !== -1));
        }

        checkselected(update) {

            var checkboxes = $root.find('[data-check][data-id]'),
                selected   = checkboxes.filter(':checked');

            this.selected = [];

            if (selected.length) {

                selected.each(function(){
                    $this.selected.push(App.$(this).attr('data-id'));
                });
            }

            $root.find('[data-check="all"]').prop('checked', checkboxes.length === selected.length);

            if (update) {
                this.update();
            }
        }

    </script>
</div>
