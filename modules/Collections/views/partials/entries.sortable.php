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
            {{ $collection['description'] }}
        </div>
        @endif
    </div>

    <div show="{ ready }">

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{ loading }">

            <div class="uk-animation-fade uk-text-center">

                <cp-preloader class="uk-container-center"></cp-preloader>

            </div>

        </div>

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{ !loading && !entries.length && !filter}">

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

            <div class="uk-float-left uk-width-1-2">
                <div class="uk-form-icon uk-form uk-width-1-1 uk-text-muted">

                    <i class="uk-icon-search"></i>
                    <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter items...')" onchange="{ updatefilter }">

                </div>
            </div>

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

    </div>

    <script>

        var $this = this, $root = App.$(this.root);

        this.ready      = false;
        this.filter     = null;
        this.collection = {{ json_encode($collection) }};
        this.entries    = [];
        this.selected   = [];

        this.fields = this.collection.fields.filter(function(field){

            if (!CollectionHasFieldAccess(field)) {
                return false;
            }

            return field.lst;
        });

        this.on('mount', function(){

            this.loadTree();

            // handle dragging

            var listSrc;

            $root.on('start.uk.nestable', function(e, nestable) {
                e.stopPropagation();
                listSrc  = $this._getListObject(nestable.placeEl[0]);
            });

            $root.on('change.uk.nestable', function(e, sortable, $item, action) {

                if (!sortable) return;

                var entries = [], _pid = $item.parent().closest('[entry-id]').attr('entry-id') || null, item;

                $item.parent().children().each(function() {

                    item = App.$(this);

                    entries.push({
                        _id  : item.attr('entry-id'),
                        _pid : _pid,
                        _o   : item.index()
                    })
                });

                // update data structure

                var listTarget = $this._getListObject($item[0]), sameList = (listSrc == listTarget);

                listSrc.splice(listSrc.indexOf($item[0].__entry), 1);
                listTarget.splice($item.index(), 0, $item[0].__entry);

                App.request('/collections/update_order/'+$this.collection.name, {entries:entries}).then(function(data) {

                    if (listSrc != listTarget) {
                        $item.remove();
                    }

                    $this.update();
                });
            });

            $root.on('click', '[data-check]', function() {

                $this.checkselected();
                $this.update();
            });

        });

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

        this.load = function() {

            var options = { };

            if (this.filter) {
                options.filter = this.filter;
            }

            this.loading = true;
            this.entries = [];
            this.selected = [];

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

    </script>

</div>
