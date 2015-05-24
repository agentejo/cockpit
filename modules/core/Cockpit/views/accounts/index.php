<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('Accounts')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>

    @if($app["user"]["group"]=="admin")
    <div class="uk-form">

        <span class="uk-form-icon">
            <i class="uk-icon-filter"></i>
            <input type="text" placeholder="@lang('Filter by name...')" onkeyup="{ updatefilter }">
        </span>

        <a class="uk-button uk-button-primary uk-margin-small-right" href="@route('/accounts/create')" title="@lang('Create account')">
            <i class="uk-icon-plus"></i>
        </a>

    </div>
    @endif

    <div class="uk-grid uk-grid-match uk-grid-width-1-1 uk-grid-width-medium-1-4">

        <div class="uk-grid-margin" each="{account, $index in accounts}" if="{ parent.infilter(account) }">

            <div class="uk-panel uk-panel-box">

                <div class="uk-grid">

                    <div>
                        <a class="uk-link-muted" href="@route('/accounts/account')/{ account._id }" title="@lang('Edit account')">
                            <img class="uk-border-circle" riot-src="//www.gravatar.com/avatar/{ account.md5email }?d=mm&s=50" width="50" height="50" alt="gravatar">
                        </a>
                    </div>

                    <div class="uk-flex-item-1">

                        <div class="uk-text-truncate">
                            <span class="{ account.active ? 'uk-icon-circle uk-text-success':'uk-icon-circle-o uk-text-danger' }"></span>
                            <strong>{ account.name || account.user }</strong>
                        </div>
                        <div class="uk-badge uk-margin-small-top">{ account.group }</div>

                        <ul class="uk-subnav uk-subnav-line uk-text-small uk-margin-small-top">
                            <li><a href="@route('/accounts/account')/{ account._id }">@lang('Edit')</a></li>
                            <li><a class="uk-text-danger" onclick="{ parent.remove }" href="#">@lang('Delete')</a></li>
                        </ul>

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
                App.UI.notify("You can't delete yourself", "danger");
                return;
            }

            App.UI.confirm("Are you sure?", function() {

                App.request('/accounts/remove', { "account": account }).then(function(data){

                    App.UI.notify("Account removed", "success");
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