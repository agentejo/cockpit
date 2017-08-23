<cp-account>

    <span class="uk-icon-spinner uk-icon-spin" show="{!account}"></span>

    <span class="uk-flex-inline uk-flex-middle" if="{account}">
        <cp-gravatar email="{account.email}" alt="{account.name || 'Unknown'}" size="{ opts.size || 25 }"></cp-gravatar>
        <span class="uk-margin-small-left">{ account.name || 'Unknown' }</span>
    </span>

    <script>

        var $this = this;

        this.account = null;

        this.on('mount', function() {
            this.trigger('update');
        })

        this.on('update', function(){
            
            if (this.account && this.account._id == opts.account) {
                return;
            }

            Cockpit.account(opts.account).then(function(account) {
                $this.account = account;
                $this.update();
            });
        })

    </script>

</cp-account>