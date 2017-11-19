<field-access-list>

    <style>

        .badge-label {
            display: inline-block;
            padding: .35em .6em;
            font-size: .8em;
            border: 1px currentColor solid;
            border-radius: 3px;
            color: #4FC1E9;
        }

        .badge-label a { color: currentColor; }

    </style>


    <div class="uk-clearfix {!_entries.length && 'uk-text-center'}">

        <div class="uk-margin uk-text-muted" if="{!_entries.length}">
            <img class="uk-svg-adjust" riot-src="{ App.base('/assets/app/media/icons/acl.svg') }" width="50" data-uk-svg>
            <p>{ App.i18n.get('Nothing selected') }</p>
        </div>

        <span class="badge-label uk-margin-small-right uk-margin-small-top {(entry in App.$data.groups) ? '':'uk-text-danger'}" each="{entry,idx in _entries}">
            <i class="uk-icon-users uk-margin-small-right" show="{ (entry in App.$data.groups) }"></i>
            <span data-entry="{entry}">{ parent.getEntryDisplay(entry) }</span> <a class="uk-margin-small-left" onclick="{ parent.remove }"><i class="uk-icon-minus"></i></a>
        </span>

        <span class="uk-position-relative uk-margin-small-top" data-uk-dropdown="mode:'click', pos:'right-bottom'">
            <a><i class="uk-icon-plus-circle uk-text-large"></i></a>

            <div class="uk-dropdown uk-dropdown-width-2 uk-text-left">

                <div class="uk-margin">
                    <strong>{ App.i18n.get('Groups') }</strong>
                    <div class="uk-margin-small-top">
                        <span class="badge-label uk-margin-small-right uk-margin-small-top" each="{ admin,group in App.$data.groups}" if="{_entries.indexOf(group)<0}">
                            <i class="uk-icon-users uk-margin-small-right"></i>
                            {group} <a class="uk-margin-small-left" onclick="{parent.add}"><i class="uk-icon-plus"></i></a>
                        </span>
                    </div>
                </div>

                <div class="uk-margin uk-form">
                    <strong>{ App.i18n.get('Users') }</strong>
                    <div class="uk-margin-small-top">
                        <div class="uk-form-icon uk-form uk-text-muted uk-display-block">
                            <i class="uk-icon-search"></i> <input class="uk-width-1-1" type="text" ref="txtfilter" placeholder="Filter users...">
                        </div>
                    </div>

                    <div class="uk-margin-small-top">
                        <span class="badge-label uk-text-danger uk-margin-small-right uk-margin-small-top uk-flex-inline uk-flex-middle" each="{ user in _users}" if="{_entries.indexOf(user._id)<0}">
                            <cp-account account="{user._id}" size="15"></cp-account> <a class="uk-margin-small-left" onclick="{parent.add}"><i class="uk-icon-plus"></i></a>
                        </span>
                    </div>
                </div>

            </div>
        </span>

    </div>

    <script>

        var $this = this, cache = {};

        this._entries = [];
        this._users = [];

        this.on('mount', function() {

            App.$(this.refs.txtfilter).on('keyup', _.debounce(function() {

                var value = $this.refs.txtfilter.value.trim();

                $this._users = [];

                if (value && value.length > 2) {

                    App.request('/accounts/find', {options: {filter: value}}).then(function(response) {
                        $this._users = response && Array.isArray(response.accounts) ? response.accounts : [];
                        $this.update();
                    });
                }

                $this.update();

            }, 500));

            App.$(this.refs.txtfilter).on('keydown', function(e) {

                if (e.keyCode == 13) {
                    return false;
                }
            });
        });

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this._entries !== value) {
                this._entries = value;
                this.update();
            }

        }.bind(this);

        add(e) {
            this._entries.push(e.item.group || e.item.user._id);
            this.$setValue(_.uniq(this._entries));
            this.refs.txtfilter.value = '';
        }

        remove(e) {
            this._entries.splice(e.item.idx, 1);
            this.$setValue(this._entries);
        }

        getEntryDisplay(entry) {

            if (entry in App.$data.groups) {
                return entry;
            }

            if (!cache[entry]) {

                cache[entry] = new Promise(function(resolve, reject){
                    App.request('/accounts/find', {options: {filter: {_id:entry}}}).then(function(response) {

                        if (response && Array.isArray(response.accounts) && response.accounts[0]) {
                            resolve(response.accounts[0].name);
                        } else {
                            reject(entry);
                        }
                    });
                });
            }

            cache[entry].then(function(txt) {
                App.$($this.root).find('[data-entry="'+entry+'"]').text(txt);
            }).catch(function() {
                App.$($this.root).find('[data-entry="'+entry+'"]').text(entry);
            });

            return '...';
        }

    </script>

</field-access-list>
