{{ $app->assets(['assets:lib/uikit/js/components/form-password.min.js']) }}

<div>
    <ul class="uk-breadcrumb">
        @hasaccess?('cockpit', 'accounts')
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li><a href="@route('/accounts')">@lang('Accounts')</a></li>
        @endif
        <li class="uk-active"><span>@lang('Account')</span></li>
    </ul>
</div>

<div class="uk-grid uk-margin-top uk-invisible" data-uk-grid-margin riot-view>

    <div class="uk-width-medium-2-3">

        <h3>@lang('General')</h3>

        <div class="uk-panel uk-panel-space uk-panel-box uk-panel-card uk-text-center">

            <cp-gravatar email="{ account.email }" size="100" alt="{ account.name || account.user }"></cp-gravatar>

        </div>

        <div class="uk-panel">

            <div class="uk-grid" data-uk-grid-margin>

                <div class="uk-width-medium-1-1">

                    <ul class="uk-tab uk-margin uk-flex uk-flex-center" if="{ tabs && tabs.length }">
                        <li class="{ tab == 'general' ? 'uk-active':'' }"><a onclick="{ selectTab }" select="general">@lang('General')</a></li>
                        <li class="{ t == parent.tab ? 'uk-active':'' }" each="{t in tabs}">
                            <a onclick="{ parent.selectTab }" select="{t}">{t}</a>
                        </li>
                    </ul>

                    <form id="account-form" class="uk-form" onsubmit="{ submit }">

                        <div class="uk-grid-margin" show="{tab == 'general'}">

                            <div class="uk-form-row">
                                <label class="uk-text-small">@lang('Name')</label>
                                <input class="uk-width-1-1 uk-form-large" type="text" bind="account.name" autocomplete="off" required>
                            </div>

                            <div class="uk-form-row">
                                <label class="uk-text-small">@lang('Username')</label>
                                <input class="uk-width-1-1 uk-form-large" type="text" bind="account.user" autocomplete="off" required>
                            </div>

                            <div class="uk-form-row">
                                <label class="uk-text-small">@lang('Email')</label>
                                <input class="uk-width-1-1 uk-form-large" type="email" bind="account.email" autocomplete="off" required>
                            </div>

                            <div class="uk-form-row">
                                <label class="uk-text-small">@lang('New Password')</label>
                                <div class="uk-form-password uk-width-1-1">
                                    <input class="uk-form-large uk-width-1-1" type="password" placeholder="@lang('Password')" bind="account.password" autocomplete="off">
                                    <a href="" class="uk-form-password-toggle" data-uk-form-password>@lang('Show')</a>
                                </div>
                                <div class="uk-alert">
                                    @lang('Leave the password field empty to keep your current password.')
                                </div>
                            </div>

                            <div class="uk-form-row">
                                <label class="uk-text-small">@lang('API Key')</label>

                                <div class="uk-flex uk-flex-middle">
                                    <div class="uk-form-icon uk-display-block uk-flex-item-1">
                                        <i class="uk-icon-key"></i>
                                        <input class="uk-form-large uk-width-1-1" type="text" bind="account.api_key" placeholder="@lang('No token generated yet')" bind="account.apikey" disabled>
                                    </div>
                                    <a class="uk-icon-refresh uk-margin-left" onclick="{ generateApiToken }" style="pointer-events:auto;"></a>
                                    <a class="uk-margin-left" type="button" onclick="{ copyApiKey }" title="@lang('Copy Token')" data-uk-tooltip="pos:'top'"><i class="uk-icon-copy"></i></a>
                                </div>
                            </div>

                        </div>

                        <div if="{ App.Utils.count(fields) }">

                            <div show="{tab == name}" each="{group, name in meta}">

                                <div class="uk-grid">

                                    <div class="uk-width-medium-{field.width || '1-1'} uk-grid-margin" each="{field, fieldname in group}" no-reorder>

                                        <label class="uk-text-small">
                                            { field.label || fieldname }
                                        </label>

                                        <div class="uk-margin uk-text-small uk-text-muted">
                                            { field.info || '' }
                                        </div>

                                        <div class="uk-margin">
                                            <cp-field type="{field.type || 'text'}" bind="account.{fieldname}" opts="{ field.options || {} }"></cp-field>
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>

                        @trigger('cockpit.account.editview')

                        <div class="uk-margin-large-top">
                            <button class="uk-button uk-button-large uk-button-primary uk-width-1-3 uk-margin-right">@lang('Save')</button>
                            <a href="@route('/accounts')">@lang('Cancel')</a>
                        </div>

                    </form>

                </div>

            </div>
        </div>

    </div>

    <div class="uk-width-medium-1-4 uk-form">

        <h3>@lang('Settings')</h3>

        @if($app["user"]["group"]=="admin" AND @$account["_id"]!=$app["user"]["_id"])
        <div class="uk-form-row">
            <label class="uk-text-small">@lang('Status')</label>

            <div class="uk-form-controls uk-margin-small-top">
                <a class="uk-button { !account.active ? 'uk-button-danger':'uk-button-success' }" onclick="{ toggleactive }">
                    { account.active ? 'Active' : 'Inactive' }
                </a>
            </div>

        </div>
        @endif

        <div class="uk-form-row">
            <label class="uk-text-small">@lang('Language')</label>

            <div class="uk-form-controls uk-margin-small-top">
                <div class="uk-form-select">
                    <a>{ _.result(_.find(languages, { 'i18n': account.i18n }), 'language') || account.i18n }</a>
                    <select class="uk-width-1-1 uk-form-large" ref="i18n" bind="account.i18n">
                        @foreach($languages as $lang)
                        <option value="{{ $lang['i18n'] }}">{{ $lang['language'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($app->module('cockpit')->isSuperAdmin() AND @$account["_id"] != $app["user"]["_id"])
        <div class="uk-form-row">
            <label class="uk-text-small">@lang('Group')</label>

            <div class="uk-form-controls uk-margin-small-top">
                <div class="uk-form-select">
                    <a>{ account.group }</a>
                    <select class="uk-width-1-1 uk-form-large" ref="group" bind="account.group">
                        @foreach($groups as $group)
                        <option value="{{ $group }}">{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
        @endif

    </div>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.account   = {{ json_encode($account) }};
        this.languages = {{ json_encode($languages) }};

        this.tabs      = [];
        this.tab       = 'general';
        this.fields    = {{ (isset($fields)) ? json_encode($fields) : "null" }} || {};
        this.meta      = {};

        Object.keys(this.fields || {}).forEach(function(key, group){

            group = $this.fields[key].group || 'Additional';

            if (!$this.meta[group]) {
                $this.meta[group] = {};
            }

            if ($this.tabs.indexOf(group) < 0) {
                $this.tabs.push(group);
            }

            $this.meta[group][key] = $this.fields[key];

            if ($this.account[key] === undefined) {
                $this.account[key] = $this.fields[key].options && $this.fields[key].options.default || null;
            }
        });

        selectTab(e) {

            this.tab = e.target.getAttribute('select');

            setTimeout(function(){
                UIkit.Utils.checkDisplay();
            }, 50);
        }


        this.on('mount', function(){

            this.root.classList.remove('uk-invisible');

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                e.preventDefault();
                $this.submit();
                return false;
            });

            if (!this.account.api_key) {
                this.generateApiToken();
            }

            $this.update();
        });

        generateApiToken() {
            this.account.api_key = 'account-'+App.Utils.generateToken(120);
        }

        copyApiKey() {

            var token = this.account.api_key;

            App.Utils.copyText(token, function() {
                App.ui.notify("Copied!", "success");
            });
        }

        toggleactive() {
            this.account.active = !(this.account.active);
        }

        submit(e) {

            if(e) e.preventDefault();

            App.request("/accounts/save", {"account": this.account}).then(function(data){
                $this.account = data;
                App.ui.notify("Account saved", "success");
            });

            return false;
        }

    </script>

</div>
