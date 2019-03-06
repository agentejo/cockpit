<field-collectionlink>

    <div class="uk-alert" if="{!opts.link}">
        { App.i18n.get('Collection to link not defined in the field settings') }
    </div>

    <div class="uk-alert uk-alert-danger" if="{opts.link && error}">
        { App.i18n.get('Failed loading collection') } {opts.link}
    </div>

    <div class="uk-margin" if="{opts.link && !collection && !error}">
        <cp-preloader class="uk-container-center"></cp-preloader>
    </div>

    <div if="{opts.link && collection}">

        <div class="uk-alert" if="{!link || (link && opts.multiple && !link.length)}">
            { App.i18n.get('Nothing linked yet') }. <a onclick="{ showDialog }">{ App.i18n.get('Create link to') } { collection.label || opts.link }</a>
        </div>

        <div if="{!opts.multiple && link}">

            <div class="uk-panel uk-panel-card uk-panel-box">

                <div class="uk-flex">
                    <span class="uk-flex-item-1">{ getDisplay(link) }</span>
                    <a class="uk-margin-small-left" href="{ App.route('/collections/entry/'+opts.link+'/'+link._id) }"><i class="uk-icon-link"></i></a>
                </div>

                <div class="uk-panel-box-footer uk-text-small uk-padding-bottom-remove">
                    <a class="uk-margin-small-right" onclick="{ showDialog }"><i class="uk-icon-link"></i> { App.i18n.get('Link item') }</a>
                    <a class="uk-text-danger" onclick="{ removeItem }"><i class="uk-icon-trash-o"></i> { App.i18n.get('Remove') }</a>
                </div>
            </div>

        </div>

        <div if="{link && opts.multiple && link.length}">

            <div class="uk-panel uk-panel-card uk-panel-box">

                <ul class="uk-list uk-list-space uk-sortable" data-uk-sortable>
                    <li each="{l,index in link}" data-idx="{ index }">
                        <div class="uk-grid uk-grid-small uk-text-small">
                            <div><a onclick="{ removeListItem }"><i class="uk-icon-trash-o"></i></a></div>
                            <div class="uk-flex uk-flex-item-1">
                                <span class="uk-flex-item-1">{ parent.getDisplay(l) }</span>
                                <a class="uk-margin-small-left" href="{ App.route('/collections/entry/'+parent.opts.link+'/'+l._id) }"><i class="uk-icon-link"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="uk-panel-box-footer uk-text-small uk-padding-bottom-remove">
                    <a class="uk-margin-small-right" onclick="{ showDialog }"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Item') }</a>
                    <a class="uk-text-danger" onclick="{ removeItem }"><i class="uk-icon-trash-o"></i> { App.i18n.get('Reset') }</a>
                </div>
            </div>

        </div>

    </div>

    <div class="uk-modal" ref="modal">

        <div class="uk-modal-dialog uk-modal-dialog-large">
            <a href="" class="uk-modal-close uk-close"></a>

            <h3>{ collection && (collection.label || opts.link) }</h3>

            <div class="uk-margin uk-flex uk-flex-middle" if="{collection}">

                <div class="uk-form-icon uk-form uk-flex-item-1 uk-text-muted">

                    <i class="uk-icon-search"></i>
                    <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="{ App.i18n.get('Filter items...') }" onchange="{ updatefilter }">

                </div>

                <div show="{selected.length}">
                    <button type="button" class="uk-button uk-button-large uk-button-link" onclick="{linkItems}">
                        <i class="uk-icon-link"></i> {selected.length} {App.i18n.get('Entries')}
                    </button>
                </div>

            </div>

            <div class="uk-overflow-container" if="{collection}">

                <div class="uk-text-xlarge uk-text-center uk-text-muted uk-margin-large-bottom" if="{ !entries.length && filter && !loading }">
                    { App.i18n.get('No entries found') }.
                </div>

                <table class="uk-table uk-table-tabbed uk-table-striped" if="{ entries.length }">
                    <thead>
                        <tr>
                            <th show="{opts.multiple}"></th>
                            <th class="uk-text-small" each="{field,idx in fields}">
                                <a class="uk-link-muted { parent.sort[field.name] ? 'uk-text-primary':'' }" onclick="{ parent.updatesort }" data-sort="{ field.name }">

                                    { field.label || field.name }

                                    <span if="{parent.sort[field.name]}" class="uk-icon-long-arrow-{ parent.sort[field.name] == 1 ? 'up':'down'}"></span>
                                </a>
                            </th>
                            <th width="20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr each="{entry,idx in entries}">
                            <td show="{parent.opts.multiple}"><input class="uk-checkbox" type="checkbox" onclick="{parent.toggleSelected}"></td>
                            <td class="uk-text-truncate" each="{field,idy in parent.fields}" if="{ field.name != '_modified' }">
                                <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name], field) }"></raw>
                            </td>
                            <td>{ App.Utils.dateformat( new Date( 1000 * entry._modified )) }</td>
                            <td>
                                <a onclick="{ parent.linkItem }"><i class="uk-icon-link"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="uk-margin-large-bottom" if="{ loading }">
                    <cp-preloader class="uk-container-center"></cp-preloader>
                </div>

                <div class="uk margin" if="{ loadmore && !loading }">
                    <a class="uk-button uk-width-1-1" onclick="{ load }">
                        { App.i18n.get('Load more...') }
                    </a>
                </div>

            </div>
        </div>
    </div>


    <script>

    this.mixin(RiotBindMixin);

    var $this = this, modal, collections, cache = {}, _init = function(){

        this.error = this.collection ? false:true;

        this.loadmore   = false;
        this.entries    = [];
        this.fieldsidx  = {};
        this.fields     = this.collection.fields.filter(function(field){
            $this.fieldsidx[field.name] = field;
            return field.lst;
        });

        this.fields.push({name:'_modified', 'label':App.i18n.get('Modified')});

        this.update();

    }.bind(this);

    this.link = null;
    this.sort = {'_created': -1};

    this.selected = [];

    this.$updateValue = function(value, field) {

        if (opts.multiple && !Array.isArray(value)) {
            value = [].concat(value ? [value]:[]);
        }

        if (JSON.stringify(this.reference) !== JSON.stringify(value)) {
            this.link = value;
            this.update();
        }

    }.bind(this);

    this.on('mount', function(){

        if (!opts.link) return;

        modal = UIkit.modal(this.refs.modal, {modal:false});

        modal.element.appendTo(document.body)

        App.request('/collections/_collections').then(function(data){
            collections = data;
            $this.collection  = collections[opts.link] || null;
            _init();
        });

        App.$(this.root).on('keydown', 'input',function(e){

            if (e.keyCode == 13) {
                e.preventDefault();
                e.stopPropagation();

                $this.updatefilter(e);
                $this.update();
            }
        });

        if (opts.multiple) {
            App.$(this.root).on('stop.uk.sortable', function(){
                $this.updateorder();
            });
        }
    });
    
    this.on('before-unmount', function() {
        modal.element.appendTo(this.root);
    });

    showDialog(){

        this.selected = [];

        if (opts.multiple && opts.limit && this.link && this.link.length >= Number(opts.limit)) {
            App.ui.notify('Maximum amount of items reached');
            return;
        }

        modal.show();
        modal.find(':checked').prop('checked', false);

        if (!this.entries.length) this.load();
    }

    linkItem(e) {

        var _entry = e.item.entry;
        var entry = {
            _id: _entry._id,
            link: this.collection.name,
            display: _entry[opts.display] || _entry[this.collection.fields[0].name] || 'n/a'
        };

        if (opts.multiple) {

            if (!this.link || !Array.isArray(this.link)) {
                this.link = [];
            }

            this.link.push(entry);
            this.link = _.uniqBy(this.link, '_id');

        } else {
            this.link = entry;
        }
        
        cache[entry._id] = entry;

        setTimeout(function(){
            modal.hide();
        }, 50);

        this.$setValue(this.link);
    }

    linkItems(e) {

        e.preventDefault();

        if (!opts.multiple || !this.selected.length) {
            return;
        }

        if (!this.link || !Array.isArray(this.link)) {
            this.link = [];
        }

        var entry;

        this.selected.forEach(function(_entry) {
            
            cache[_entry._id] = _entry;
            entry = {
                _id: _entry._id,
                link: $this.collection.name,
                display: _entry[opts.display] || _entry[$this.collection.fields[0].name] || 'n/a'
            };

            $this.link.push(entry);
        });

        setTimeout(function(){
            modal.hide();
        }, 50);

        this.link = _.uniqBy(this.link, '_id');
        this.$setValue(this.link);
    }

    removeItem() {
        this.link = opts.multiple ? [] : null;
        this.$setValue(this.link);
    }

    removeListItem(e) {
        this.link.splice(e.item.index, 1);
        this.$setValue(this.link);
    }

    load() {

        var limit = 50;

        var options = { sort:this.sort };

        if (this.filter) {
            options.filter = this.filter;
        } else {
            if (opts.filter) {
                options.filter = opts.filter;
            }
        }

        if (!this.collection.sortable) {
            options.limit = limit;
            options.skip  = this.entries.length || 0;
        }

        this.loading = true;

        return App.request('/collections/find', {collection:this.collection.name, options:options}).then(function(data){

            this.entries = this.entries.concat(data.entries);

            this.ready    = true;
            this.loadmore = data.entries.length && data.entries.length == limit;

            this.loading = false;

            this.update();

        }.bind(this))
    }

    updatefilter(e) {

        var load = this.filter ? true:false;

        if (this.refs.txtfilter.value == this.filter) {
            return;
        }

        this.filter = this.refs.txtfilter.value || null;

        if (this.filter || load) {

            this.entries = [];
            this.loading = true;
            this.load();
        }

        return false;
    }

    updatesort(e, field) {

        field = e.target.getAttribute('data-sort');

        if (!field) {
            return;
        }

        if (!this.sort[field]) {
            this.sort        = {};
            this.sort[field] = 1;
        } else {
            this.sort[field] = this.sort[field] == 1 ? -1:1;
        }

        this.entries = [];

        this.load();
    }

    updateorder() {

        var items = [];

        App.$($this.root).css('height', App.$($this.root).height());

        App.$('.uk-sortable', $this.root).children().each(function(){
            items.push($this.link[Number(this.getAttribute('data-idx'))]);
        });

        $this.link = [];
        $this.update();

        setTimeout(function() {
            $this.link = items;
            $this.$setValue($this.link);
            $this.update();

            setTimeout(function(){
                $this.root.style.height = '';
            }, 30)
        }, 10);
    }

    toggleSelected(e) {

        var _entry = e.item.entry;

        if (e.target.checked) {
            this.selected.push(_entry);
        } else {

            var idx = this.selected.indexOf(_entry);

            if (idx > -1) {
                this.selected.splice(idx, 1);
            }
        }
    }
    
    getDisplay(link) {
        
        var display = '...';
        
        if (!cache[link._id]) {
            
            cache[link._id] = App.request('/collections/find', {collection:this.collection.name, options:{filter:{_id:link._id}}}).then(function(data){

                if (!data.entries.length) {
                    link.display = 'n/a';
                    this.update();
                    return;
                }
                
                var _entry = data.entries[0];
                
                link.display = _entry[opts.display] || _entry[$this.collection.fields[0].name] || 'n/a';
                
                this.update();

            }.bind(this))
            
        } else {
            display = link.display
        }
        
        return display;
    }


    </script>

</field-collectionlink>
