<style>

    .uk-nestable-list.filtered {
        padding-left: 0;
    }

    .uk-nestable-list.filtered .uk-nestable-toggle {
        display: none;
    }

</style>

<script type="riot/tag" src="@base('collections:assets/entries-tree.tag')"></script>

<div class="uk-margin-top" riot-view>

    <div class="uk-margin uk-text-center uk-text-muted" show="{ (Array.isArray(entries) && entries.length) || filter}">

        <img class="uk-svg-adjust" src="@url($collection['icon'] ? 'assets:app/media/icons/'.$collection['icon']:'collections:icon.svg')" width="50" alt="icon" data-uk-svg>
        @if($collection['description'])
        <div class="uk-container-center uk-margin-top uk-width-medium-1-2">
            {{ htmlspecialchars($collection['description'], ENT_QUOTES, 'UTF-8') }}
        </div>
        @endif
    </div>

    <div show="{ ready }">

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{ !loading && !entries.length && !filter}">

            <div class="uk-animation-scale">

                <img class="uk-svg-adjust" src="@url($collection['icon'] ? 'assets:app/media/icons/'.$collection['icon']:'collections:icon.svg')" width="50" alt="icon" data-uk-svg>
                @if($collection['description'])
                <div class="uk-margin-top uk-text-small uk-text-muted">
                    {{ htmlspecialchars($collection['description'], ENT_QUOTES, 'UTF-8') }}
                </div>
                @endif
                <hr>
                <span class="uk-text-large"><strong>@lang('No entries').</strong> <a href="@route('/collections/entry/'.$collection['name'])">@lang('Create an entry').</a></span>

            </div>

        </div>

        <div class="uk-clearfix uk-margin-top" show="{ !loading && (entries.length || filter) }">

            <div class="uk-float-left uk-form-select uk-margin-right" if="{ languages.length }">
                <span class="uk-padding-horizontal-remove uk-button uk-button-large uk-button-link {lang ? 'uk-text-primary' : 'uk-text-muted'}">
                    <i class="uk-icon-globe"></i>
                    { lang ? _.find(languages,{'code':lang}).label : App.$data.languageDefaultLabel }
                </span>
                <select onchange="{changelanguage}">
                    <option value="" selected="{lang === ''}">{App.$data.languageDefaultLabel}</option>
                    <option each="{language,idx in languages}" value="{language.code}" selected="{lang === language.code}">{language.label}</option>
                </select>
            </div>

            <div class="uk-float-left uk-width-1-2">
                <div class="uk-form-icon uk-form uk-width-1-1 uk-text-muted">

                    <i class="uk-icon-search"></i>
                    <input class="uk-width-1-1 uk-form-large uk-form-blank {filter && filter.match(/\{(.*)\}/) && 'uk-text-monospace'}" type="text" ref="txtfilter" placeholder="@lang('Filter items...')" onchange="{ updatefilter }">

                </div>
            </div>

            <div class="uk-float-right">

                <div class="uk-display-inline-block uk-margin-small-right" data-uk-dropdown="mode:'click'" if="{ selected.length }">
                    <button class="uk-button uk-button-large uk-animation-fade">@lang('Batch Action') <span class="uk-badge uk-badge-contrast uk-margin-small-left">{ selected.length }</span></button>
                    <div class="uk-dropdown">
                        <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                            <li class="uk-nav-header">@lang('Actions')</li>
                            <li><a onclick="{ batchedit }">@lang('Edit')</a></li>
                            @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
                            <li class="uk-nav-item-danger"><a onclick="{ removeselected }">@lang('Delete')</a></li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if($app->module('collections')->hasaccess($collection['name'], 'entries_create'))
                <a class="uk-button uk-button-large uk-button-primary" href="@route('/collections/entry/'.$collection['name'])">@lang('Add Entry')</a>
                @endif
            </div>
        </div>

        <div class="uk-text-xlarge uk-text-muted uk-viewport-height-1-3 uk-flex uk-flex-center uk-flex-middle" if="{ !entries.length && filter && !loading }">
            <div>@lang('No entries found')</div>
        </div>

        <div class="uk-margin-top" if="{ (Array.isArray(entries) && entries.length) && !filter}">
            <entries-tree entries="{entries}" collection="{collection}" fields="{fields}"></entries-tree>
        </div>

        <div class="uk-margin-top" if="{(Array.isArray(entries) && entries.length) && filter}">

            <span class="uk-badge uk-badge-outline uk-text-warning uk-text-uppercase">@lang('Filtered Items')</span>

            <ul class="uk-nestable-list filtered uk-margin-top" data-is="entries-tree-list" entries="{entries}" collection="{collection}" fields="{fields}"></ul>
        </div>

        <cp-preloader-fullscreen if="{ loading }"></cp-preloader-fullscreen>

    </div>

    <entries-batchedit collection="{collection}" fields={fieldsidx}></entries-batchedit>

    <script>

        var $this = this, $root = App.$(this.root);

        this.ready      = false;
        this.filter     = null;
        this.collection = {{ json_encode($collection) }};
        this.entries    = [];
        this.selected   = [];
        this.fieldsidx  = {};
        this.languages  = App.$data.languages;

        if (this.languages.length) {
            this.lang = App.session.get('collections.entry.'+this.collection._id+'.lang', '');
        }

        this.fields = this.collection.fields.filter(function(field){

            if (!CollectionHasFieldAccess(field)) {
                return false;
            }

            $this.fieldsidx[field.name] = field;

            return field.lst;
        });

        this.on('mount', function(){

            // update on sort
            $root.on('sort-update', function(e, entries) {

                App.request('/collections/update_order/'+$this.collection.name, {entries:entries}).then(function(data) {
                    // anything?
                });
            });

            $root.on('remove-entry', function(e, entry) {
                $this.remove(entry);
            });

            $root.on('click', '[data-check]', function() {
                $this.checkselected();
                $this.update();
            });

            $root.on('duplicate-entry', function(e, entry, parent) {

                var _entry = App.$.extend({}, entry);

                delete _entry._id;
                delete _entry.children;

                _entry._o = parent ? parent.children.length : $this.entries.length;

                App.request('/collections/save_entry/'+$this.collection.name, {'entry': _entry}).then(function(dupentry) {

                    if (dupentry) {
                        dupentry.children = [];
                        (parent ? parent.children : $this.entries).push(dupentry);
                        App.ui.notify("Entry duplicated", "success");
                        $this.update();
                    }
                });
            });

            this.initState();
        });

        this.initState = function() {

            var searchParams = new URLSearchParams(location.search);

            if (searchParams.has('q')) {

                try {

                    var q = JSON.parse(searchParams.get('q'));

                    if (q.filter) {
                        this.filter = q.filter;
                        this.refs.txtfilter.value = q.filter;
                        this.load(true);
                    } else {
                        this.loadTree();
                    }
                } catch(e){
                    this.loadTree();
                }
            } else {
                this.loadTree();
            }

            this.update();
        }

        this._getListObject = function(element) {

            var list = element.parentNode.closest('[entry-id]');

            return list ? list.__entry.children : this.entries;
        }

        this._remove = function(entries, selected) {

            if (!entries.length) {
                return;
            }

            App.ui.confirm("Are you sure?", function() {

                var promises = [], toDelete = [];

                entries.forEach(function(id) {

                    toDelete.push(id);

                    App.$('[entry-id="'+id+'"]').find('[entry-id]').each(function() {
                        toDelete.push(this.getAttribute('entry-id'));
                    });
                });

                toDelete = _.uniq(toDelete);

                toDelete.forEach(function(id) {
                    promises.push(App.request('/collections/delete_entries/'+$this.collection.name, {filter: {'_id':id}}));
                });

                Promise.all(promises).then(function(){

                    App.ui.notify(promises.length > 1 ? (promises.length + " entries removed") : "Entry removed", "success");
                    $this.loading = false;

                    var ele, list;

                    toDelete.forEach(function(id) {

                        var ele = document.querySelector('[entry-id="'+id+'"]');

                        if (ele) {

                            list  = $this._getListObject(ele);
                            list.splice(list.indexOf(ele.__entry), 1);
                        }
                    });

                    if (selected) $this.selected = [];
                    $this.update();
                });

                this.loading = true;
                this.update();

            }.bind(this));
        }

        this.remove = function(entry) {
            this._remove([entry._id]);
        }

        this.removeselected = function() {
            this._remove(this.selected, true);
        }

        this.load = function(initial) {

            var options = {};

            if (this.lang) {
                options.lang = this.lang;
            }

            if (this.filter) {
                options.filter = this.filter;
            }

            this.loading = true;
            this.entries = [];
            this.selected = [];

            if (!initial) {

                window.history.pushState(
                    null, null,
                    App.route(['/collections/entries/', this.collection.name, '?q=', JSON.stringify({
                        filter: this.filter || null
                    })].join(''))
                );
            }

            App.request('/collections/find', {collection:this.collection.name, options:options}).then(function(data){

                window.scrollTo(0, 0);

                this.entries = data.entries;

                this.ready   = true;
                this.loading = false;
                this.update();

            }.bind(this))
        }

        this.loadTree = function() {

            this.loading = true;
            this.entries = [];
            this.selected = [];

            App.request('/collections/tree', {collection:this.collection.name}).then(function(tree){

                this.entries = tree;

                this.ready   = true;
                this.loading = false;
                this.update();

            }.bind(this))
        }

        this.updatefilter = function() {

            this.filter = this.refs.txtfilter.value || null;
            this[this.filter ? 'load':'loadTree']();
        }

        this.checkselected = function(update) {

            var checkboxes = $root.find('[data-check]'),
                selected   = checkboxes.filter(':checked');

            this.selected = [];

            if (selected.length) {

                selected.each(function(){
                    $this.selected.push(this.getAttribute('data-check'));
                });
            }

            if (update) {
                this.update();
            }
        }

        this.batchedit = function() {
            this.tags['entries-batchedit'].open(this.entries, this.selected)
        }

        this.changelanguage = function(e) {
            var lang = e.target.value;
            App.session.set('collections.entry.'+this.collection._id+'.lang', lang);
            this.lang = lang;
            this.load(false);
            this.update();
        }

    </script>

</div>
