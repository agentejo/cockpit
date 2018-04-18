<entries-tree>

    <style media="screen">

        .uk-nestable-item.uk-parent > div > div {
            font-weight: bold;
        }

        .uk-nestable-placeholder {
            opacity: .5;
        }

    </style>

    <ul data-uk-nestable class="uk-nestable" data-is="entries-tree-list" show="{ready}"
        collection="{collection}"
        entries="{entries}"
        fields="{fields}"
        display="{display}"
        imagefield="{imageField}"
    ></ul>

    <script>

        var $this = this;

        this.entries = opts.entries || [];
        this.collection = opts.collection || {};
        this.ready = false;

        this.imageField = null;
        this.fieldsidx = {};
        this.display = null;
        this.fields = this.collection.fields.filter(function(field){

            if (!CollectionHasFieldAccess(field)) {
                return false;
            }

            $this.fieldsidx[field.name] = field;

            if (!$this.imageField && (field.type=='image' || field.type=='asset')) {
                $this.imageField = field;
            }

            if (!this.display) {
                this.display = field;
            }

            return field.lst;
        });

        this.on('mount', function() {

            App.assets.require(['/assets/lib/uikit/js/components/nestable.js']).then(function() {
                $this.ready = true;
                $this.update();
            });
        })

    </script>

</entries-tree>

<entries-tree-list>

    <li class="entry-item uk-nestable-item" each="{entry in entries}" entry-id="{entry._id}">
        <entries-tree-item collection="{parent.collection}" entry="{entry}" collection="{ collection }" imagefield="{imagefield}" fields="{fields}"></entries-tree-item>
        <ul class="uk-nestable-list" data-is="entries-tree-list" entries="{entry.children}" collection="{collection}" fields="{fields}" imagefield="{imagefield}" if="{entry.children && entry.children.length}"></ul>
    </li>

    <script>

        this.entries = opts.entries;
        this.collection = opts.collection || {};
        this.imagefield = opts.imagefield;
        this.fields = opts.fields;

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

            <div class="uk-text-truncate uk-margin-small-right" each="{field,idy in fields}" if="{ field.name != '_modified' && field.name != '_created' }">
                <a class="uk-link-muted" href="{ App.route('/collections/entry/'+parent.collection.name+'/'+parent.entry._id) }">
                    <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name]) }" if="{parent.entry[field.name] !== undefined}"></raw>
                    <span class="uk-icon-eye-slash uk-text-muted" if="{parent.entry[field.name] === undefined}"></span>
                </a>
            </div>

            <div data-uk-dropdown="mode:'click'" if="{ extrafields.length }">

                <a class="extrafields-indicator uk-text-muted" title="{App.i18n.get('More fields')}" data-uk-tooltip="pos:'right'"><i class="uk-icon-ellipsis-h"></i></a>

                <div class="uk-dropdown uk-dropdown-scrollable">
                    <div class="uk-margin-small-bottom" each="{field,idy in extrafields}" if="{ field.name != '_modified' && field.name != '_created' }">
                        <span class="uk-text-small uk-text-uppercase uk-text-muted">{ field.label || field.name }</span>
                        <a class="uk-link-muted uk-text-small uk-display-block uk-text-truncate" href="{App.route('/collections/entry/'+parent.collection.name+'/'+parent.entry._id) }">
                            <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name]) }" if="{parent.entry[field.name] !== undefined}"></raw>
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
                    <li class="uk-nav-divider"></li>
                    <li class="uk-nav-item-danger"><a onclick="{ parent.remove }">{ App.i18n.get('Delete') }</a></li>
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

    </script>

</entries-tree-item>
