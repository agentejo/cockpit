
@if(isset($collection['color']) && $collection['color'])
<style>
    .app-header { border-top: 8px {{ $collection['color'] }} solid; }
</style>
@endif

<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/collections')">@lang('Collections')</a></li>
        <li data-uk-dropdown="mode:'hover', delay:300">
            <a href="@route('/collections/entries/'.$collection['name'])"><i class="uk-icon-bars"></i> {{ @$collection['label'] ? $collection['label']:$collection['name'] }}</a>

            @if($app->module('collections')->hasaccess($collection['name'], 'collection_edit'))
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Actions')</li>
                    <li><a href="@route('/collections/collection/'.$collection['name'])">@lang('Edit')</a></li>
                    <li class="uk-nav-divider"></li>
                    <li class="uk-text-truncate"><a href="@route('/collections/export/'.$collection['name'])" download="{{ $collection['name'] }}.collection.json">@lang('Export entries')</a></li>
                    <li class="uk-text-truncate"><a href="@route('/collections/import/collection/'.$collection['name'])">@lang('Import entries')</a></li>
                </ul>
            </div>
            @endif
        </li>
    </ul>
</div>

<div class="uk-margin-top-large" riot-view>

    <div class="uk-alert" if="{ !fields.length }">
        @lang('No fields defined'). <a href="@route('/collections/collection')/{ collection.name }">@lang('Define collection fields').</a>
    </div>

    <h3 class="uk-flex uk-flex-middle uk-text-bold">
        <img class="uk-margin-small-right" src="@url($collection['icon'] ? 'assets:app/media/icons/'.$collection['icon']:'collections:icon.svg')" width="25" alt="icon">
        { entry._id ? 'Edit':'Add' } @lang('Entry')
    </h3>

    <div class="uk-grid">

        <div class="uk-grid-margin uk-width-medium-3-4">

            <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

                <ul class="uk-tab uk-margin-large-bottom uk-flex uk-flex-center" show="{ App.Utils.count(groups) > 1 }">
                    <li class="{ !group && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get('All') }</a></li>
                    <li class="{ group==parent.group && 'uk-active'}" each="{items,group in groups}" show="{ items.length }"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get(group) }</a></li>
                </ul>

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
                                <cp-field type="{field.type || 'text'}" bind="entry.{ field.localize && parent.lang ? (field.name+'_'+parent.lang):field.name }" opts="{ field.options || {} }"></cp-field>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="uk-margin-top">
                    <button class="uk-button uk-button-large uk-button-primary uk-margin-right">@lang('Save')</button>
                    <a href="@route('/collections/entries/'.$collection['name'])">
                        <span show="{ !entry._id }">@lang('Cancel')</span>
                        <span show="{ entry._id }">@lang('Close')</span>
                    </a>
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
                            <option each="{language,idx in languages}" value="{language.code}">{language.label}</option>
                        </select>
                    </div>

                </div>

                <div class="uk-margin">
                    <label class="uk-text-small">@lang('Last Modified')</label>
                    <div class="uk-margin-small-top uk-text-muted" if="{entry._id}">
                        <i class="uk-icon-calendar uk-margin-small-right"></i> {  App.Utils.dateformat( new Date( 1000 * entry._modified )) }
                    </div>
                    <div class="uk-margin-small-top uk-text-muted" if="{!entry._id}">@lang('Not saved yet')</div>
                </div>

                <div class="uk-margin" if="{entry._id}">
                    <label class="uk-text-small">@lang('Revisions')</label>
                    <div class="uk-margin-small-top">
                        <span class="uk-position-relative">
                            <cp-revisions-info class="uk-badge uk-text-large" rid="{entry._id}" parent="true"></cp-revisions-info>
                            <a class="uk-position-cover" href="@route('/collections/revisions/'.$collection['name'])/{entry._id}"></a>
                        </span>
                    </div>
                </div>

                <div class="uk-margin" if="{entry._id && entry._by}">
                    <label class="uk-text-small">@lang('Last update by')</label>
                    <div class="uk-margin-small-top">
                        <cp-account account="{entry._by}"></cp-account>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.collection   = {{ json_encode($collection) }};
        this.fields       = this.collection.fields;
        this.fieldsidx    = {};

        this.entry        = {{ json_encode($entry) }};

        this.languages    = App.$data.languages;
        this.groups       = {main:[]};
        this.group        = 'main';

        // fill with default values
        this.fields.forEach(function(field){

            $this.fieldsidx[field.name] = field;

            if ($this.entry[field.name] === undefined) {
                $this.entry[field.name] = field.options && field.options.default || null;
            }

            if (field.type == 'password') {
                $this.entry[field.name] = '';
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
            this.group = e.item && e.item.group || false;
        }

        submit(e) {

            if(e) e.preventDefault();

            App.request('/collections/save_entry/'+this.collection.name,{entry:this.entry}).then(function(entry) {

                if (entry) {

                    App.ui.notify("Saving successful", "success");

                    $this.entry = entry;

                    $this.fields.forEach(function(field){

                        if (field.type == 'password') {
                            $this.entry[field.name] = '';
                        }
                    });

                    $this.update();

                } else {
                    App.ui.notify("Saving failed.", "danger");
                }
            }, function(res) {
                App.ui.notify(res && res.message ? res.message : "Saving failed.", "danger");
            });

            return false;
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
