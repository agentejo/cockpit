
<style>

.uk-scrollable-box {
    border: none;
    padding-top: 0;
    padding-left: 0;
}

.collection-grid-avatar-container {
    border-top: 1px rgba(0,0,0,0.1) solid;
}

.collection-grid-avatar {
    transform: translateY(-50%);
    max-width: 40px;
    max-height: 40px;
    border: 1px #fff solid;
    box-shadow: 0 0 40px rgba(0,0,0,0.3);
    border-radius: 50%;
    margin: 0 auto;
}

.collection-grid-avatar .uk-icon-spinner {
    display: none;
}

@if($collection['color'])
.app-header { border-top: 8px {{ $collection['color'] }} solid; }
@endif
</style>


<div>

    <ul class="uk-breadcrumb">
        <li><a href="@route('/collections')">@lang('Collections')</a></li>
        <li class="uk-active" data-uk-dropdown="mode:'hover', delay:300">

            <a><i class="uk-icon-bars"></i> {{ @$collection['label'] ? $collection['label']:$collection['name'] }}</a>

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

<div class="uk-margin-top" riot-view>

    <div class="uk-margin uk-text-center uk-text-muted" show="{ (Array.isArray(entries) && entries.length) || filter}">

        <img class="uk-svg-adjust" src="@url($collection['icon'] ? 'assets:app/media/icons/'.$collection['icon']:'collections:icon.svg')" width="50" alt="icon" data-uk-svg>
        @if($collection['description'])
        <div class="uk-container-center uk-margin-top uk-width-medium-1-2">
            {{ $collection['description'] }}
        </div>
        @endif
    </div>


    <div show="{ ready }">

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{ loading }">

            <div class="uk-animation-fade uk-text-center">

                <p class="uk-text-xlarge">
                    <i class="uk-text-primary uk-icon-spin uk-icon-spinner"></i>
                </p>

            </div>

        </div>

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{ !loading && !entries.length && !filter }">

            <div class="uk-animation-scale">

                <img class="uk-svg-adjust" src="@url($collection['icon'] ? 'assets:app/media/icons/'.$collection['icon']:'collections:icon.svg')" width="50" alt="icon" data-uk-svg>
                @if($collection['description'])
                <div class="uk-margin-top uk-text-small uk-text-muted">
                    {{ $collection['description'] }}
                </div>
                @endif
                <hr>
                <span class="uk-text-large"><strong>@lang('No entries').</strong> <a href="@route('/collections/entry/'.$collection['name'])">@lang('Create an entry').</a></span>

            </div>

        </div>

        <div class="uk-clearfix uk-margin-top" show="{ !loading && (entries.length || filter) }">

        <div class="uk-float-left uk-margin-right">

            <div class="uk-button-group">
                <button class="uk-button uk-button-large {listmode=='list' && 'uk-button-primary'}" onclick="{ toggleListMode }"><i class="uk-icon-list"></i></button>
                <button class="uk-button uk-button-large {listmode=='grid' && 'uk-button-primary'}" onclick="{ toggleListMode }"><i class="uk-icon-th"></i></button>
            </div>

        </div>

            @if(!$collection['sortable'])
            <div class="uk-float-left uk-width-1-2">
                <div class="uk-form-icon uk-form uk-width-1-1 uk-text-muted">

                    <i class="uk-icon-search"></i>
                    <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter items...')" onchange="{ updatefilter }">

                </div>
            </div>
            @endif



            <div class="uk-float-right">

                @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
                <a class="uk-button uk-button-large uk-button-danger uk-animation-fade uk-margin-small-right" onclick="{ removeselected }" if="{ selected.length }">
                    @lang('Delete') <span class="uk-badge uk-badge-contrast uk-margin-small-left">{ selected.length }</span>
                </a>
                @endif

                @if($app->module('collections')->hasaccess($collection['name'], 'entries_create'))
                <a class="uk-button uk-button-large uk-button-primary" href="@route('/collections/entry/'.$collection['name'])"><i class="uk-icon-plus-circle uk-icon-justify"></i> @lang('Entry')</a>
                @endif
            </div>
        </div>


        <div class="uk-margin-top" show="{ !loading && (entries.length || filter) }">

            @render('collections:views/partials/entries'.($collection['sortable'] ? '.sortable':'').'.php', compact('collection'))

        </div>

    </div>


    <script type="view/script">

        App.Utils.renderer.collectionlink = function(v) {

            if (Array.isArray(v)) {

                var vals = [];

                v.forEach(function(val) {
                    vals.push(val && val.display ? val.display: App.Utils.renderer.default(val));
                });

                if (vals.length > 1) {
                    return '<span class="uk-badge" title="'+vals.join(', ')+'" data-uk-tooltip>'+vals.length+'</span>';
                }

                return vals[0];
            }

            return v && v.display ? v.display : App.Utils.renderer.default(v);
        };

        var $this = this, $root = App.$(this.root), limit = 20;

        this.ready      = false;
        this.collection = {{ json_encode($collection) }};
        this.loadmore   = false;
        this.count      = 0;
        this.page       = 1;
        this.entries    = [];
        this.fieldsidx  = {};
        this.imageField = null;
        this.fields     = this.collection.fields.filter(function(field){

            $this.fieldsidx[field.name] = field;

            if (!$this.imageField && (field.type=='image' || field.type=='asset')) {
                $this.imageField = field;
            }

            return field.lst;
        });

        this.fieldsidx['_created'] = {name:'_created', 'label':'@lang('Created')', type: 'text'};
        this.fieldsidx['_modified'] = {name:'_modified', 'label':'@lang('Modified')', type: 'text'};

        this.fields.push(this.fieldsidx['_created']);
        this.fields.push(this.fieldsidx['_modified']);

        this.sort     = {'_created': -1};
        this.selected = [];
        this.listmode = App.session.get('collections.entries.'+this.collection.name+'.listmode', 'list');

        this.on('mount', function(){

            $root.on('click', '[data-check]', function() {

                if (this.getAttribute('data-check') == 'all') {
                    $root.find('[data-check][data-id]').prop('checked', this.checked);
                }

                $this.checkselected();

                $this.update();
            });

            if (this.collection.sortable) {
                this.initSortable();
            }

            this.load();

        });

        initSortable() {

            this.sort = {'_order': 1};

            App.$(this.root).on('change.uk.sortable', '[data-uk-sortable]', function(e, sortable, ele){

                if (App.$(e.target).is(':input')) return;

                var updates = [];

                App.$(sortable.element).children().each(function(idx) {
                    updates.push({'_id':this.getAttribute('data-id'),'_order':idx});
                });

                if (updates.length) {

                    App.callmodule('collections:save',[$this.collection.name, updates]).then(function(){
                        App.ui.notify("Entries reordered", "success");
                    });
                }

            });
        }

        remove(e, entry, idx) {

            entry = e.item.entry
            idx   = e.item.idx;

            App.ui.confirm("Are you sure?", function() {

                App.request('/collections/delete_entries/'+$this.collection.name, {filter: {'_id':entry._id}}).then(function(data) {

                    App.ui.notify("Entry removed", "success");

                    $this.entries.splice(idx, 1);

                    if ($this.pages > 1 && !$this.entries.length) {
                        $this.page = $this.page == 1 ? 1 : $this.page - 1;
                        $this.load();
                        return;
                    }

                    $this.update();

                    $this.checkselected();
                });

            }.bind(this));
        }

        removeselected() {

            if (this.selected.length) {

                App.ui.confirm("Are you sure?", function() {

                    var promises = [];

                    this.entries = this.entries.filter(function(entry, yepp){

                        yepp = ($this.selected.indexOf(entry._id) === -1);

                        if (!yepp) {
                            promises.push(App.request('/collections/delete_entries/'+$this.collection.name, {filter: {'_id':entry._id}}));
                        }

                        return yepp;
                    });

                    Promise.all(promises).then(function(){

                        App.ui.notify("Entries removed", "success");

                        $this.loading = false;

                        if ($this.pages > 1 && !$this.entries.length) {
                            $this.page = $this.page == 1 ? 1 : $this.page - 1;
                            $this.load();
                        } else {
                            $this.update();
                        }

                    });

                    this.loading = true;
                    this.update();
                    this.checkselected(true);

                }.bind(this));
            }
        }

        load() {

            var options = { sort:this.sort };

            if (this.filter) {
                options.filter = this.filter;
            }

            if (!this.collection.sortable) {
                options.limit = limit;
                options.skip  = (this.page - 1) * limit;
            }

            this.loading = true;

            return App.request('/collections/find', {collection:this.collection.name, options:options}).then(function(data){

                window.scrollTo(0, 0);

                this.entries = data.entries;
                this.pages   = data.pages;
                this.page    = data.page;
                this.count   = data.count;

                this.ready    = true;
                this.loadmore = data.entries.length && data.entries.length == limit;

                this.checkselected();
                this.loading = false;
                this.update();

            }.bind(this))
        }

        loadpage(page) {
            this.page = page > this.pages ? this.pages:page;
            this.load();
        }

        updatesort(e, field) {

            field = e.target.getAttribute('data-sort');

            if (!field) {
                return;
            }

            var col = field;

            switch (this.fieldsidx[field].type) {
                case 'collectionlink':
                    col = field+'.display';
                    break;
                case 'location':
                    col = field+'.address';
                    break;
                default:
                    col = field;
            }

            if (!this.sort[col]) {
                this.sort      = {};
                this.sort[col] = 1;
            } else {
                this.sort[col] = this.sort[col] == 1 ? -1 : 1;
            }

            this.sortedBy = field;
            this.sortedOrder = this.sort[col];

            this.entries = [];

            this.load();
        }

        checkselected(update) {

            var checkboxes = $root.find('[data-check][data-id]'),
                selected   = checkboxes.filter(':checked');

            this.selected = [];

            if (selected.length) {

                selected.each(function(){
                    $this.selected.push(App.$(this).attr('data-id'));
                });
            }

            $root.find('[data-check="all"]').prop('checked', checkboxes.length && checkboxes.length === selected.length);

            if (update) {
                this.update();
            }
        }

        updatefilter() {

            var load = this.filter ? true:false;

            this.filter = this.refs.txtfilter.value || null;

            if (this.filter || load) {
                this.entries = [];
                this.loading = true;
                this.page = 1;
                this.load();
            }
        }

        duplicateEntry(e, collection, entry, idx) {

            collection = this.collection.name;
            entry      = App.$.extend({}, e.item.entry);
            idx        = e.item.idx;

            delete entry._id;

            App.request('/collections/save_entry/'+this.collection.name, {"entry": entry}).then(function(entry) {

                if (entry) {

                    $this.entries.unshift(entry);
                    App.ui.notify("Entry duplicated", "success");
                    $this.update();
                }
            });
        }

        toggleListMode() {
            this.listmode = this.listmode=='list' ? 'grid':'list';
            App.session.set('collections.entries.'+this.collection.name+'.listmode', this.listmode);
        }

        isImageField(entry) {

            if (!this.imageField) {
                return false;
            }

            var data = entry[this.imageField.name];

            if (!data) {
                return false;
            }

            switch(this.imageField.type) {
                case 'asset':
                    if (data.mime && data.mime.match(/^image\//)) {
                        return ASSETS_URL+data.path;
                    }
                    break;
                case 'image':

                    if (data.path) {
                        return data.path.match(/^(http\:|https\:|\/\/)/) ? data.path : SITE_URL+'/'+data.path;
                    }
                    break;
            }

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
