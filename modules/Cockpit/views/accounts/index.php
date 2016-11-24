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
                <th class="uk-text-small">@lang('Name')</th>
                <th class="uk-text-small" width="30%">@lang('Email')</th>
                <th class="uk-text-small" width="150">@lang('Group')</th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
            <tr each="{account, $index in accounts}"  if="{ infilter(account) }">
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
