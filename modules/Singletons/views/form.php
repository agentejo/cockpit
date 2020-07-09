
<style>
    @if($singleton['color'])
    .app-header { border-top: 8px {{ $singleton['color'] }} solid; }
    @endif
</style>

<script>
    window.__singletonData = {{ json_encode($data) }} || {};
    window.__singleton = {{ json_encode($singleton) }} || {};
</script>

<div riot-view>

    <div class="header-sub-panel">

        <div class="uk-container uk-container-center">
            <ul class="uk-breadcrumb">
                <li><a href="@route('/singletons')">@lang('Singletons')</a></li>
                <li class="uk-active" data-uk-dropdown>

                    <a><i class="uk-icon-bars"></i> {{ htmlspecialchars(@$singleton['label'] ? $singleton['label']:$singleton['name'], ENT_QUOTES, 'UTF-8') }}</a>

                    @if($app->module('singletons')->hasaccess($singleton['name'], 'edit'))
                    <div class="uk-dropdown">
                        <ul class="uk-nav uk-nav-dropdown">
                            <li class="uk-nav-header">@lang('Actions')</li>
                            <li><a href="@route('/singletons/singleton/'.$singleton['name'])">@lang('Edit')</a></li>
                        </ul>
                    </div>
                    @endif

                </li>
            </ul>

            <div class="uk-h3 uk-flex uk-flex-middle uk-text-bold">
                <div class="uk-margin-small-right">
                    <img src="@url($singleton['icon'] ? 'assets:app/media/icons/'.$singleton['icon']:'singletons:icon.svg')" width="40" alt="icon">
                </div>
                <div class="uk-flex-item-1">{ singleton.label || singleton.name }</div>
            </div>
        </div>

        <ul class="uk-tab header-sub-panel-tab uk-flex uk-flex-center" divider="true" if="{ App.Utils.count(_groups) > 1 && App.Utils.count(_groups) < 6 }">
            <li class="{ !group && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get('All') }</a></li>
            <li class="{ group==parent.group && 'uk-active'}" each="{group, idx in _groups}" show="{ parent.groups[group].length }"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get(group) }</a></li>
        </ul>

        <ul class="uk-tab header-sub-panel-tab uk-flex uk-flex-center" divider="true" if="{ App.Utils.count(_groups) > 5 }">
            <li class="uk-active" data-uk-dropdown="mode:'click', pos:'bottom-center'">
                <a>{ App.i18n.get(group || 'All') } <i class="uk-margin-small-left uk-icon-angle-down"></i></a>
                <div class="uk-dropdown uk-dropdown-scrollable uk-dropdown-close">
                    <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Groups')</li>
                    <li class="{ !group && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get('All') }</a></li>
                    <li class="uk-nav-divider"></li>
                    <li class="{ group==parent.group && 'uk-active'}" each="{group in _groups}" show="{ parent.groups[group].length }"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get(group) }</a></li>
                    </ul>
                </div>
            </li>
        </ul>

    </div>

    <div class="uk-margin-top">

        <div class="uk-alert" if="{ !fields.length }">
            @lang('No fields defined'). <a href="@route('/singletons/singleton')/{ singleton.name }">@lang('Define singleton fields').</a>
        </div>

        @if($singleton['description'])
        <div class="uk-margin uk-text-muted">
            {{ htmlspecialchars($singleton['description'], ENT_QUOTES, 'UTF-8') }}
        </div>
        @endif

        <div class="uk-grid">

            <div class="uk-width-medium-3-4 uk-grid-margin">

                <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

                    <div class="uk-grid uk-grid-match uk-grid-gutter">

                        <div class="uk-width-medium-{field.width}" each="{field,idx in fields}" show="{checkVisibilityRule(field) && (!group || (group == field.group)) }" if="{ hasFieldAccess(field.name) }" no-reorder>

                            <cp-fieldcontainer>

                                <label title="{ field.name }">

                                    <span class="uk-text-bold"><i class="uk-icon-pencil-square uk-margin-small-right"></i> { field.label || App.Utils.ucfirst(field.name) }</span>
                                    <span class="uk-text-muted" show="{field.required}">&mdash; @lang('required')</span>
                                    <span if="{ field.localize }" data-uk-dropdown="mode:'click'">
                                        <a class="uk-icon-globe" title="@lang('Localized field')" data-uk-tooltip="pos:'right'"></a>
                                        <div class="uk-dropdown uk-dropdown-close">
                                            <ul class="uk-nav uk-nav-dropdown">
                                                <li class="uk-nav-header">@lang('Copy content from:')</li>
                                                <li show="{parent.lang}"><a onclick="{parent.copyLocalizedValue}" lang="" field="{field.name}">@lang('Default')</a></li>
                                                <li show="{parent.lang != language.code}" each="{language,idx in languages}" value="{language.code}"><a onclick="{parent.parent.copyLocalizedValue}" lang="{language.code}" field="{field.name}">{language.label}</a></li>
                                            </ul>
                                        </div>
                                    </span>

                                </label>

                                <div class="uk-margin-top">
                                    <cp-field type="{field.type || 'text'}" bind="{ parent.getBindValue(field) }" opts="{ field.options || {} }"></cp-field>
                                </div>

                                <div class="uk-margin-top uk-text-small uk-text-muted" if="{field.info}">
                                    { field.info || ' ' }
                                </div>

                            </cp-fieldcontainer>

                        </div>

                    </div>

                    <cp-actionbar>
                        <div class="uk-container uk-container-center">
                            <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                            <a class="uk-button uk-button-link" href="@route('/singletons')">@lang('Close')</a>
                        </div>
                    </cp-actionbar>

                </form>
            </div>

            <div class="uk-grid-margin uk-width-medium-1-4 uk-flex-order-first uk-flex-order-last-medium">

                @if($app->module('cockpit')->isSuperAdmin())
                <div class="uk-button-group uk-flex uk-margin">
                    <a class="uk-button" onclick="{showDataObject}">@lang('Show json')</a>
                </div>
                @endif


                <div class="uk-panel uk-panel-box uk-panel-framed uk-width-1-1 uk-form-select uk-form" if="{ languages.length }">
                    
                    <div class="uk-text-bold {lang ? 'uk-text-primary' : 'uk-text-muted'}">
                        <i class="uk-icon-globe"></i>
                        <span class="uk-margin-small-left">{ lang ? _.find(languages,{code:lang}).label:App.$data.languageDefaultLabel }</span>
                    </div>

                    <select bind="lang" onchange="{persistLanguage}">
                        <option value="">{App.$data.languageDefaultLabel}</option>
                        <option each="{language,idx in languages}" value="{language.code}">{language.label}</option>
                    </select>
                </div>

                <div class="uk-margin">
                    <label class="uk-text-small">@lang('Last Modified')</label>
                    <div class="uk-margin-small-top uk-text-muted"><i class="uk-icon-calendar uk-margin-small-right"></i> {  App.Utils.dateformat( new Date( 1000 * singleton._modified )) }</div>
                </div>

                <div class="uk-margin">
                    <label class="uk-text-small">@lang('Revisions')</label>
                    <div class="uk-margin-small-top">
                        <span class="uk-position-relative">
                            <cp-revisions-info class="uk-badge uk-text-large" rid="{singleton._id}"></cp-revisions-info>
                            <a class="uk-position-cover" href="@route('/singletons/revisions/'.$singleton['name'])/{singleton._id}"></a>
                        </span>
                    </div>
                </div>

                <div class="uk-margin" if="{data._mby}">
                    <label class="uk-text-small">@lang('Last update by')</label>
                    <div class="uk-margin-small-top">
                        <cp-account account="{data._mby}"></cp-account>
                    </div>
                </div>

                @trigger('singletons.form.aside', [$singleton['name']])

            </div>


        </div>

        <cp-inspectobject ref="inspect"></cp-inspectobject>


        <script type="view/script">

            var $this = this;

            this.mixin(RiotBindMixin);

            this.singleton = window.__singleton;
            this.fields    = this.singleton.fields;
            this.fieldsidx = {};

            this.data      = window.__singletonData;

            this.languages = App.$data.languages;
            this.groups    = {main:[]};
            this._groups   = [];
            this.group     = 'main';

            // fill with default values
            this.fields.forEach(function(field){

                $this.fieldsidx[field.name] = field;

                if ($this.data[field.name] === undefined) {
                    $this.data[field.name] = field.options && field.options.default || null;
                }

                if (field.localize && $this.languages.length) {

                    $this.languages.forEach(function(lang) {

                        var key = field.name+'_'+lang.code;

                        if ($this.data[key] === undefined) {
                            $this.data[key] = field.options && field.options.default || null;
                            $this.data[key] = field.options && field.options['default_'+lang.code] || $this.data[key];
                        }
                    });
                }

                if (field.type == 'password') {
                    $this.data[field.name] = '';
                }

                if (field.group && !$this.groups[field.group]) {
                    $this.groups[field.group] = [];
                } else if (!field.group) {
                    field.group = 'main';
                }

                $this.groups[field.group || 'main'].push(field);
            });

            this._groups = Object.keys(this.groups).sort(function (a, b) {
                return a.toLowerCase().localeCompare(b.toLowerCase());
            });

            if (!this.groups[this.group].length) {
                this.group = $this._groups[1];
            }

            if (this.languages.length) {
                this.lang = App.session.get('singletons.form.'+this.singleton._id+'.lang', '');
            }

            this.on('mount', function(){

                // bind global command + save
                Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                    if (App.$('.uk-modal.uk-open').length) {
                        return;
                    }

                    $this.submit(e);
                    return false;
                });

                // wysiwyg cmd + save hack
                App.$(this.root).on('submit', function(e, component) {
                    if (component) $this.submit(e);
                });

                // lock resource
                Cockpit.lockResource('singleton_'+$this.singleton.name, function(e){
                    window.location.reload();
                });
            });

            toggleGroup(e) {
                e.preventDefault();
                this.group = e.item && e.item.group || false;
            }

            getBindValue(field) {
                return 'data.'+(field.localize && this.lang ? (field.name+'_'+this.lang):field.name);
            }

            submit(e) {

                if(e) e.preventDefault();

                var required = [], val;

                this.fields.forEach(function(field) {

                    val = $this.data[field.name];

                    if (field.required && (!val || (Array.isArray(val) && !val.length))) {

                        if (!(val===false || val===0)) {
                            required.push(field.label || field.name);
                        }
                    }
                });

                if (required.length) {
                    App.ui.notify([
                        App.i18n.get('Fill in these required fields before saving:'),
                        '<div class="uk-margin-small-top">'+required.join(',')+'</div>'
                    ].join(''), 'danger');
                    return;
                }

                App.request('/singletons/update_data/'+this.singleton.name, {data:this.data}).then(function(res) {

                    if (res) {

                        App.ui.notify("Saving successful", "success");

                        _.extend($this.data, res.data);

                        $this.fields.forEach(function(field){

                            if (field.type == 'password') {
                                $this.data[field.name] = '';
                            }
                        });

                        if ($this.tags['cp-revisions-info']) {
                            $this.tags['cp-revisions-info'].sync();
                        }

                        $this.update();

                    } else {
                        App.ui.notify("Saving failed.", "danger");
                    }

                }, function(res) {
                    App.ui.notify(res && (res.message || res.error) ? (res.message || res.error) : 'Saving failed.', 'danger');
                });
            }

            hasFieldAccess(field) {

                var acl = this.fieldsidx[field] && this.fieldsidx[field].acl || [];

                if (field == '_modified' ||
                    App.$data.user.group == 'admin' ||
                    !acl ||
                    (Array.isArray(acl) && !acl.length) ||
                    acl.indexOf(App.$data.user.group) > -1 ||
                    acl.indexOf(App.$data.user._id) > -1

                ) { return true; }

                return false;
            }

            copyLocalizedValue(e) {

                var field = e.target.getAttribute('field'),
                    lang = e.target.getAttribute('lang'),
                    val  = JSON.stringify(this.data[field+(lang ? '_':'')+lang]);

                this.data[field+(this.lang ? '_':'')+this.lang] = JSON.parse(val);
            },

            checkVisibilityRule(field) {

                if (field.options && field.options['@visibility']) {

                    try {
                        return (new Function('$', 'v','return ('+field.options['@visibility']+')'))(this.data, function(key) {
                            var f = this.fieldsidx[key] || {};
                            return this.data[(f.localize && this.lang ? (f.name+'_'+this.lang):f.name)];
                        }.bind(this));
                    } catch(e) {
                        return false;
                    }

                    return this.data.check;
                }

                return true;
            }

            persistLanguage(e) {
                App.session.set('singletons.form.'+this.singleton._id+'.lang', e.target.value);
            }

            showDataObject() {
                $this.refs.inspect.show($this.data);
                $this.update();
            }
        </script>

    </div>

</div>
