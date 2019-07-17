<entries-tree>

    <style media="screen">

        .uk-nestable-placeholder {
            opacity: .5;
        }

    </style>

    <ul data-uk-nestable class="uk-nestable" data-is="entries-tree-list" show="{ready}"
        collection="{collection}"
        entries="{entries}"
        fields="{fields}"
        imagefield="{imageField}"
    ></ul>

    <script>

        var $this = this, $root = App.$(this.root);

        this.entries = opts.entries || [];
        this.collection = opts.collection || {};
        this.fields = opts.fields;

        this.ready = false;
        this.imageField = null;

        this.on('mount', function() {

            App.assets.require(['/assets/lib/uikit/js/components/nestable.js']).then(function() {

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

                    var listTarget = $this._getListObject($item[0]);

                    listSrc.splice(listSrc.indexOf($item[0].__entry), 1);
                    listTarget.splice($item.index(), 0, $item[0].__entry);

                    $root.trigger('sort-update', [entries]);
                });

                $root.on('click', '[data-nestable-action="toggle"]', function() {

                    var li =  this.closest('li'),
                        collapsed = li.classList.contains('uk-collapsed');

                    localStorage[collapsed ? 'setItem':'removeItem']($this.collection._id+'_'+li.getAttribute('entry-id'), true);
                });

                $this.ready = true;
                $this.update();
            });
        })

        this._getListObject = function(element) {

            var list = element.parentNode.closest('[entry-id]');

            return list ? list.__entry.children : this.entries;
        }

    </script>

</entries-tree>

<entries-tree-list>

    <li class="entry-item uk-nestable-item { isCollapsed(entry) && 'uk-collapsed'}" each="{entry in entries}" entry-id="{entry._id}">
        <entries-tree-item collection="{parent.collection}" entry="{entry}" collection="{ collection }" imagefield="{imagefield}" parent="{_parent}" fields="{fields}"></entries-tree-item>
        <ul class="uk-nestable-list" data-is="entries-tree-list" entries="{entry.children}" collection="{collection}" fields="{fields}" imagefield="{imagefield}" parent="{entry}" if="{entry.children && entry.children.length}"></ul>
    </li>

    <script>

        this.entries = opts.entries;
        this.collection = opts.collection || {};
        this.imagefield = opts.imagefield;
        this.fields = opts.fields;

        this._parent = opts.parent || null;

        this.on('mount', function() {
            this.root.__entries = this.entries;
        });

        this.isCollapsed = function(entry) {
            return (localStorage[this.collection._id+'_'+entry._id] && entry.children.length) || false;
        }

    </script>

</entries-tree-list>

<entries-tree-item>

    <style media="screen">

        .entry-item-container {
            padding: 10px;
        }

        .extrafields-indicator {
            display: inline-block;
            padding: 2px 4px;
            font-size: 12px;
            border: 1px currentColor solid;
            line-height: 1;
            border-radius: 3px;
            opacity: .5;
        }

        .entry-date {
            min-width: 70px;
        }

    </style>


    <div class="entry-item-container uk-panel-box uk-panel-card uk-flex uk-flex-middle">

        <span class="uk-nestable-toggle uk-margin-small-right uk-text-muted" data-nestable-action="toggle"></span>

        <div class="uk-flex-item-1 uk-flex uk-flex-middle">

            <input data-check="{entry._id}" type="checkbox" class="uk-margin-small-right uk-checkbox">

            <div class="uk-text-truncate uk-margin-small-left" each="{field,idy in fields}" if="{ entry[field.name] !== null && entry[field.name] !== undefined && field.name != '_modified' && field.name != '_created' }">
                <a class="uk-link-muted" href="{ App.route('/collections/entry/'+parent.collection.name+'/'+parent.entry._id) }">
                    <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name], field) }" if="{parent.entry[field.name] !== undefined}"></raw>
                    <span class="uk-icon-eye-slash uk-text-muted" if="{parent.entry[field.name] === undefined}"></span>
                </a>
            </div>

            <div class="uk-margin-small-left" data-uk-dropdown="mode:'click'" if="{ extrafields.length }">

                <a class="extrafields-indicator uk-text-muted" title="{App.i18n.get('More fields')}" data-uk-tooltip="pos:'right'"><i class="uk-icon-ellipsis-h"></i></a>

                <div class="uk-dropdown uk-dropdown-scrollable">
                    <div class="uk-margin-small-bottom" each="{field,idy in extrafields}" if="{ field.name != '_modified' && field.name != '_created' }">
                        <span class="uk-text-small uk-text-uppercase uk-text-muted">{ field.label || field.name }</span>
                        <a class="uk-link-muted uk-text-small uk-display-block uk-text-truncate" href="{App.route('/collections/entry/'+parent.collection.name+'/'+parent.entry._id) }">
                            <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name], field) }" if="{parent.entry[field.name] !== undefined}"></raw>
                            <span class="uk-icon-eye-slash uk-text-muted" if="{parent.entry[field.name] === undefined}"></span>
                        </a>
                    </div>
                </div>

            </div>

        </div>

        <span class="uk-badge uk-badge-outline uk-text-primary uk-margin-left entry-date">{ App.Utils.dateformat( new Date( 1000 * entry._modified )) }</span>

        <span class="uk-margin-left" data-uk-dropdown="mode:'click'">

            <a><i class="uk-icon-bars"></i></a>

            <div class="uk-dropdown uk-dropdown-close">

                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">{ App.i18n.get('Actions') }</li>
                    <li><a href="{ App.route('/collections/entry/'+collection.name+'/'+entry._id) }">{ App.i18n.get('Edit') }</a></li>
                    <li><a class="uk-dropdown-close" onclick="{ duplicate}">{ App.i18n.get('Duplicate') }</a></li>
                    <li class="uk-nav-divider"></li>
                    <li class="uk-nav-item-danger"><a onclick="{ remove }">{ App.i18n.get('Delete') }</a></li>
                </ul>

            </div>
        </span>
    </div>

    <script>

        this._fields = (opts.fields || []);

        this.entry = opts.entry;
        this.collection = opts.collection || {};
        this.fields = this._fields.slice(0, 2);
        this.extrafields = this._fields.length > 2 ? this._fields.slice(2) : [];
        this.imagefield = opts.imagefield;

        this.on('mount', function() {
            this.root.parentNode.__entry = this.entry;
        });

        this.remove = function(e) {
            App.$(this.root).trigger('remove-entry', [this.entry]);
        }

        this.duplicate = function(e) {
            App.$(this.root).trigger('duplicate-entry', [this.entry, opts.parent]);
        }

    </script>

</entries-tree-item>
