<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('Accounts')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>

    @if($app->module('cockpit')->isSuperAdmin())
    <div class="uk-form uk-clearfix">

        <span class="uk-form-icon">
            <i class="uk-icon-filter"></i>
            <input type="text" class="uk-form-large uk-form-blank" placeholder="@lang('Filter by name...')" onkeyup="{ updatefilter }">
        </span>

        <div class="uk-float-right">
            <a class="uk-button uk-button-primary uk-button-large" href="@route('/accounts/create')">
                <i class="uk-icon-plus-circle uk-icon-justify"></i> @lang('Account')
            </a>
        </div>

    </div>
    @endif


    <table class="uk-table uk-table-border uk-table-striped uk-margin-top">
        <thead>
            <tr>
                <th width="30"></th>
                <th class="uk-text-small" data-sort="name"><a class="uk-link-muted">@lang('Name') <span if="{sort.by == 'name'}" class="uk-icon-long-arrow-{ sort.order == -1 ? 'up':'down'}"></span></a></th>
                <th class="uk-text-small" width="30%" data-sort="email"><a class="uk-link-muted">@lang('Email') <span if="{sort.by == 'email'}" class="uk-icon-long-arrow-{ sort.order == -1 ? 'up':'down'}"></span></a></th>
                <th class="uk-text-small" width="150" data-sort="group"><a class="uk-link-muted">@lang('Group') <span if="{sort.by == 'group'}" class="uk-icon-long-arrow-{ sort.order == -1 ? 'up':'down'}"></span></a></th>
                <th class="uk-text-small" width="80" data-sort="_created"><a class="uk-link-muted">@lang('Created') <span if="{sort.by == '_created'}" class="uk-icon-long-arrow-{ sort.order == -1 ? 'up':'down'}"></span></a></th>
                <th class="uk-text-small" width="80" data-sort="_modified"><a class="uk-link-muted">@lang('Modified')  <span if="{sort.by == '_modified'}" class="uk-icon-long-arrow-{ sort.order == -1 ? 'up':'down'}"></span></a></th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
            <tr each="{account, $index in accounts}" if="{ infilter(account) }">
                <td class="uk-text-center">
                    <a class="uk-link-muted" href="@route('/accounts/account')/{ account._id }" title="@lang('Edit account')">
                        <cp-gravatar email="{ account.email }" size="25" alt="{ account.name || account.user }"></cp-gravatar>
                    </a>
                </td>
                <td>
                    <a class="uk-link-muted" href="@route('/accounts/account')/{ account._id }" title="@lang('Edit account')">
                        { account.name || account.user }
                    </a>
                </td>
                <td class="uk-text-truncate"><a class="uk-link-muted" href="mailto:{ account.email }">{ account.email }</a></td>
                <td><span class="{ account.group=='admin' && 'uk-badge' }">{ account.group }</span></td>
                <td><span class="uk-badge uk-badge-outline uk-text-muted">{ App.Utils.dateformat( new Date( 1000 * account._created )) }</span></td>
                <td><span class="uk-badge uk-badge-outline uk-text-primary">{ App.Utils.dateformat( new Date( 1000 * account._modified )) }</span></td>
                <td>
                    <span data-uk-dropdown="pos:'bottom-right'">

                        <a class="uk-icon-bars"></a>

                        <div class="uk-dropdown">
                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-nav-header">@lang('Actions')</li>
                                <li><a href="@route('/accounts/account')/{ account._id }">@lang('Edit')</a></li>
                                <li><a onclick="{ this.parent.remove }" href="#">@lang('Delete')</a></li>
                            </ul>
                        </div>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>

    <script type="view/script">

        var $this = this;

        this.accounts = {{ json_encode($accounts) }};
        this.current  = {{ json_encode($current) }};
        this.filter   = '';
        this.sort     = {by: '', order: 1};

        this.on('mount', function() {
            
            App.$(this.root).on('click', '[data-sort]', function() {
                
                var col = this.getAttribute('data-sort');

                if ($this.sort.by != col) {
                    $this.sort = {by: col, order: 1};
                } else {
                    $this.sort.order = $this.sort.order == 1 ? -1 : 1;
                }

                $this.accounts = _.sortBy($this.accounts, $this.sort.by);

                if ($this.sort.order == -1) {
                    $this.accounts = $this.accounts.reverse();
                }

                $this.update();
            });
        });

        remove(evt) {

            var account = evt.item.account;

            if (account._id == this.current) {
                App.ui.notify("You can't delete yourself", "danger");
                return;
            }

            App.ui.confirm("Are you sure?", function() {

                App.request('/accounts/remove', { "account": account }).then(function(data){

                    App.ui.notify("Account removed", "success");
                    $this.accounts.splice(evt.item.$index, 1);
                    $this.update();
                });
            });
        }

        updatefilter(evt) {
            this.filter = evt.target.value.toLowerCase();
        }

        infilter(account) {
            var name = account.name.toLowerCase();
            return (!this.filter || (name && name.indexOf(this.filter) !== -1));
        }

    </script>

</div>
