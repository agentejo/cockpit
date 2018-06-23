<field-account-link>

    <div class="uk-text-center uk-panel uk-panel-framed" show="{!_value || (Array.isArray(_value) && !_value.length)}">
        <img if="{opts.multiple}" class="uk-svg-adjust uk-text-muted" riot-src="{ App.base('/assets/app/media/icons/accounts.svg') }" width="50" data-uk-svg>
        <img if="{!opts.multiple}" class="uk-svg-adjust uk-text-muted" riot-src="{ App.base('/assets/app/media/icons/login.svg') }" width="50" data-uk-svg>

        <div>
            <a class="uk-button uk-button-link" onclick="{selectAccount}">{ App.i18n.get('Select Account') }</a>
        </div>
    </div>

    <div class="uk-panel uk-panel-box uk-panel-card uk-flex uk-flex-middle" if="{ready && _value && !opts.multiple}">
        <div class="uk-flex-item-1 uk-margin-right">
            <cp-account account="{_value}"></cp-account>
        </div>
        <div>
            <a onclick="{removeAccount}"><i class="uk-icon-trash-o uk-text-danger"></i></a>
        </div>
    </div>


    <div if="{ready && opts.multiple && Array.isArray(_value) && _value.length}">
        <div class="uk-sortable" data-uk-sortable>

            <div class="uk-panel uk-panel-box uk-panel-card uk-flex uk-flex-middle uk-margin-small-bottom" each="{account in _value}" data-account="{account}">
                <div class="uk-flex-item-1 uk-margin-right">
                    <cp-account account="{account}"></cp-account>
                </div>
                <div>
                    <a onclick="{parent.removeAccount}"><i class="uk-icon-trash-o uk-text-danger"></i></a>
                </div>
            </div>
        </div>

        <p class="uk-text-center">
            <a onclick="{selectAccount}" title="{ App.i18n.get('Add Account') }" data-uk-tooltip><i class="uk-icon-plus-circle"></i></a>
        </p>

    </div>

    <div ref="modalSelectAccounts" class="uk-modal">

        <div class="uk-modal-dialog uk-modal-dialog-large">

            <a href="" class="uk-modal-close uk-close"></a>

            <h3>{ App.i18n.get('Accounts') }</h3>

            <div class="uk-margin uk-flex uk-flex-middle">
                <div class="uk-form-icon uk-form uk-flex-item-1 uk-text-muted">

                    <i class="uk-icon-search"></i>
                    <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="{ App.i18n.get('Filter accounts...') }" onchange="{ updatefilter }">

                </div>

                <div show="{selected.length}">
                    <button type="button" class="uk-button uk-button-large uk-button-link" onclick="{linkSelected}">
                        <i class="uk-icon-link"></i> {selected.length} {App.i18n.get('Accounts')}
                    </button>
                </div>
            </div>

            <div class="uk-margin-large-bottom" if="{ loading }">
                <cp-preloader class="uk-container-center"></cp-preloader>
            </div>

            <div class="uk-text-xlarge uk-text-center uk-text-muted uk-margin-large" if="{ !_accounts.length && filter && !loading }">
                { App.i18n.get('No accounts found') }.
            </div>

            <table class="uk-table uk-table-tabbed uk-table-striped" show="{!loading && _accounts.length}">
                <thead>
                    <tr>
                        <th show="{opts.multiple}"></th>
                        <th></th>
                        <th class="uk-text-small">{ App.i18n.get('Name') }</th>
                        <th class="uk-text-small">{ App.i18n.get('Email') }</th>
                        <th class="uk-text-small">{ App.i18n.get('Group') }</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr each="{account in _accounts}">
                        <td show="{parent.opts.multiple}"><input class="uk-checkbox" type="checkbox" onclick="{parent.toggleSelected}"></td>
                        <td><cp-gravatar email="{ account.email }" size="25" alt="{ account.name || account.user }"></cp-gravatar></td>
                        <td>{account.name}</td>
                        <td>{account.email}</td>
                        <td><span class="{ account.group=='admin' && 'uk-badge' }">{ account.group }</span></td>
                        <td width="20"><a onclick="{ parent.linkAccount }"><i class="uk-icon-link"></i></a></td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

    <script>

        var $this = this, cache = {};

        this._value = null;
        this._accounts = [];
        this.selected  = [];
        this.loading   = false;
        this.ready     = false;

        this.on('mount', function() {

            this.modal = UIkit.modal(this.refs.modalSelectAccounts, {modal:false});
            this.modal.on('keydown', 'input',function(e){

                if (e.keyCode == 13) {
                    e.preventDefault();
                    e.stopPropagation();

                    $this.updatefilter(e);
                    $this.update();
                }
            });

            if (opts.multiple) {

                App.$(this.root).on('stop.uk.sortable', function(){

                    var accounts = [];

                    App.$('.uk-sortable', $this.root).children().each(function(){
                        accounts.push(this.getAttribute('data-account'));
                    });

                    $this._value = [];
                    $this.update();

                    $this._value = accounts;
                    $this.$setValue($this._value);
                });
            }

            this.ready = true;
            this.update();
        });

        this.$updateValue = function(value) {

            if (opts.multiple && !Array.isArray(value)) {
                value = [];
            }

            if (this._value !== value) {
                this._value = value;
                this.update();
            }

        }.bind(this);


        this.selectAccount = function() {

            this.selected = [];
            this.modal.find(':checked').prop('checked', false);
            this.load();
            this.modal.show();
        }

        this.load = function() {

            var value = this.refs.txtfilter.value, options = {};

            if (this.filter) {
                options.filter = this.filter;
            }

            options.limt = 10;

            this.loading = true;

            App.request('/accounts/find', {options: options}).then(function(response) {
                $this._accounts = response && Array.isArray(response.accounts) ? response.accounts : [];
                $this.loading = false;
                $this.update();
            });
        }

        this.linkAccount = function(e) {

            var account = e.item.account;

            if (opts.multiple) {

                if (!this._value || !Array.isArray(this._value)) {
                    this._value = [];
                }

                if (opts.limit && this._value.length >= opts.limit ) {
                    // Notice?
                } else {
                    this._value.push(account._id);
                    this._value = _.uniq(this._value);
                }

            } else {
                this._value = account._id;
            }

            setTimeout(function(){
                $this.modal.hide();
            }, 50);

            this.$setValue(this._value);
        }

        this.removeAccount = function(e) {

            if (opts.multiple) {
                this._value.splice(this._value.indexOf(e.item.account), 1);
            } else {
                this._value = null;
            }

            this.$setValue(this._value);
        }

        this.toggleSelected = function(e) {

            var account = e.item.account;

            if (e.target.checked) {
                this.selected.push(account._id);
            } else {

                var idx = this.selected.indexOf(account._id);

                if (idx > -1) {
                    this.selected.splice(idx, 1);
                }
            }
        }

        this.linkSelected = function() {

            if (!Array.isArray(this._value)) {
                this._value = [];
            }

            this.selected.forEach(function(account) {
                $this._value.push(account);
            })

            this._value = _.uniq(this._value);
            this.$setValue(this._value);
            this.modal.hide();
        }

        this.updatefilter = function(e) {

            var load = this.filter ? true:false;

            if (this.refs.txtfilter.value == this.filter) {
                return;
            }

            this.filter = this.refs.txtfilter.value || null;

            if (this.filter || load) {

                this._accounts = [];
                this.load();
            }

            return false;
        }

    </script>

</field-account-link>
