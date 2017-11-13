<cp-account>

    <span class="uk-icon-spinner uk-icon-spin" show="{!account}"></span>

    <span class="uk-flex-inline uk-flex-middle" if="{account}">
        <cp-gravatar email="{account.email}" alt="{account.name || 'Unknown'}" size="{ opts.size || 25 }" title="{ opts.label === false && (account.name || 'Unknown') }" data-uk-tooltip></cp-gravatar>
        <span class="uk-margin-small-left" if="{ opts.label !== false}" >{ account.name || 'Unknown' }</span>
    </span>

    <script>

        var $this = this;

        this.account = null;

        this.on('mount', function() {
            this.update();
        })

        this.on('update', function(){

            if (this.account && this.account._id == opts.account) {
                return;
            }

            Cockpit.account(opts.account).then(function(account) {

                if (!account) {
                    account = {
                        _id: opts.account
                    };
                }

                $this.account = account;
                $this.update();
            });
        })

    </script>

</cp-account>
