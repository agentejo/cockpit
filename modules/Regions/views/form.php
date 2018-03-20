
@if($region['color'])
<style>
    .app-header { border-top: 8px {{ $region['color'] }} solid; }
</style>
@endif

<div>

    <ul class="uk-breadcrumb">
        <li><a href="@route('/regions')">@lang('Regions')</a></li>
        <li class="uk-active" data-uk-dropdown>

            <a><i class="uk-icon-bars"></i> {{ @$region['label'] ? $region['label']:$region['name'] }}</a>

            @if($app->module('regions')->hasaccess($region['name'], 'edit'))
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Actions')</li>
                    <li><a href="@route('/regions/region/'.$region['name'])">@lang('Edit')</a></li>
                </ul>
            </div>
            @endif

        </li>
    </ul>

    <div class="uk-margin-top" riot-view>

        <div class="uk-alert" if="{ !fields.length }">
            @lang('No fields defined'). <a href="@route('/regions/region')/{ region.name }">@lang('Define region fields').</a>
        </div>

        <h3 class="uk-flex uk-flex-middle uk-text-bold">
            <img class="uk-margin-small-right" src="@url($region['icon'] ? 'assets:app/media/icons/'.$region['icon']:'regions:icon.svg')" width="25" alt="icon">
            { region.label || region.name }
        </h3>

        @if($region['description'])
        <div class="uk-margin uk-text-muted">
            {{ $region['description'] }}
        </div>
        @endif

        <div class="uk-grid">

            <div class="uk-width-medium-3-4 uk-grid-margin">

                <ul class="uk-tab uk-margin-large-bottom uk-flex uk-flex-center" show="{ App.Utils.count(groups) > 1 }">
                    <li class="{ !group && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get('All') }</a></li>
                    <li class="{ group==parent.group && 'uk-active'}" each="{items,group in groups}" show="{ items.length }"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get(group) }</a></li>
                </ul>

                <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

                    <div class="uk-grid uk-grid-match uk-grid-gutter">

                        <div class="uk-width-medium-{field.width}" each="{field,idx in fields}" show="{!group || (group == field.group) }" if="{ hasFieldAccess(field.name) }" no-reorder>

                            <div class="uk-panel">

                                <label class="uk-text-bold">
                                    { field.label || field.name }
                                    <span if="{ field.localize }" class="uk-icon-globe" title="@lang('Localized field')" data-uk-tooltip="pos:'right'"></span>
                                </label>

                                 <div class="uk-margin uk-text-small uk-text-muted">
                                    { field.info || ' ' }
                                </div>

                                <div class="uk-margin">
                                    <cp-field type="{field.type || 'text'}" bind="{ parent.getBindValue(field) }" opts="{ field.options || {} }"></cp-field>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="uk-margin-large-top">
                        <button class="uk-button uk-button-large uk-button-primary uk-margin-right">@lang('Save')</button>
                        <a href="@route('/regions')">@lang('Close')</a>
                    </div>

                </form>
            </div>

            <div class="uk-grid-margin uk-width-medium-1-4 uk-flex-order-first uk-flex-order-last-medium">

                <div class="uk-panel">

                    <div class="uk-margin uk-form" if="{ languages.length }">

                        <div class="uk-width-1-1 uk-form-select">

                            <label class="uk-text-small">@lang('Language')</label>
                            <div class="uk-margin-small-top">{ lang ? _.find(languages,{code:lang}).label:'Default' }</div>

                            <select bind="lang">
                                <option value="">@lang('Default')</option>
                                <option each="{language in languages}" value="{language.code}">{language.label}</option>
                            </select>
                        </div>

                    </div>

                    <div class="uk-margin">
                        <label class="uk-text-small">@lang('Last Modified')</label>
                        <div class="uk-margin-small-top uk-text-muted"><i class="uk-icon-calendar uk-margin-small-right"></i> {  App.Utils.dateformat( new Date( 1000 * region._modified )) }</div>
                    </div>

                </div>

            </div>

        </div>


        <script type="view/script">

            var $this = this;

            this.mixin(RiotBindMixin);

            this.region    = {{ json_encode($region) }};
            this.fields    = this.region.fields;
            this.fieldsidx = {};

            this.data      = this.region.data || {};

            this.languages = App.$data.languages;
            this.groups       = {main:[]};
            this.group        = 'main';

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

            if (!this.groups[this.group].length) {
                this.group = Object.keys(this.groups)[1];
            }

            this.on('mount', function(){

                // bind clobal command + save
                Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {
                    $this.submit(e);
                    return false;
                });

                // wysiwyg cmd + save hack
                App.$(this.root).on('submit', function(e, component) {
                    if (component) $this.submit(e);
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

                var required = [];

                this.fields.forEach(function(field){

                    if (field.required && !$this.data[field.name]) {

                        if (!($this.data[field.name]===false || $this.data[field.name]===0)) {
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

                App.request('/regions/update_region/'+this.region.name, {data:this.data}).then(function(region) {

                    if (region) {

                        App.ui.notify("Saving successful", "success");

                        $this.data = region.data;

                        $this.fields.forEach(function(field){

                            if (field.type == 'password') {
                                $this.data[field.name] = '';
                            }
                        });

                        $this.update();

                    } else {
                        App.ui.notify("Saving failed.", "danger");
                    }
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

        </script>

    </div>

</div>
