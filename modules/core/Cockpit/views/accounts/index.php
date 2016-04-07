<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('Accounts')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>

    @if($app["user"]["group"]=="admin")
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

    <div class="uk-grid uk-grid-match uk-grid-width-1-1 uk-grid-width-medium-1-3">

        <div class="uk-grid-margin" each="{account, $index in accounts}" if="{ parent.infilter(account) }">

            <div class="uk-panel uk-panel-box uk-panel-card">

                <div class="uk-grid uk-grid-small uk-flex-middle">

                    <div>
                        <a class="uk-link-muted" href="@route('/accounts/account')/{ account._id }" title="@lang('Edit account')">
                            <cp-gravatar email="{ account.md5email }" size="30" alt="{ account.name || account.user }"></cp-gravatar>
                        </a>
                    </div>

                    <div class="uk-flex-item-1 { account.active ? '':'uk-text-danger' }">

                        <div class="uk-text-truncate">
                            <a class="uk-link-muted" href="@route('/accounts/account')/{ account._id }" title="@lang('Edit account')">
                                <strong>{ account.name || account.user }</strong>
                            </a>
                        </div>

                    </div>

                    <div>
                        <span class="uk-badge">{ account.group }</span>
                    </div>

                    <div>
                        <div data-uk-dropdown="pos:'bottom-right'">

                            <a class="uk-icon-cog"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/accounts/account')/{ account._id }">@lang('Edit')</a></li>
                                    <li><a onclick="{ parent.remove }" href="#">@lang('Delete')</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

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
