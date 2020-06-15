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
                    <span class="uk-flex-item-1">
                        { getDisplay(link) }
                    </span>
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

            <h3 class="uk-flex uk-flex-middle">
                <div class="uk-flex-item-1">{ collection && (collection.label || opts.link) }</div>
                <div class="uk-form-select uk-margin-left" if="{ languages.length }">
                    <span class="uk-button uk-button-large uk-button-link {lang ? 'uk-text-primary' : 'uk-text-muted'}" style="padding-right:0;">
                        <i class="uk-icon-globe"></i>
                        { lang ? _.find(languages,{'code':lang}).label : App.$data.languageDefaultLabel }
                    </span>
                    <select onchange="{changelanguage}">
                        <option value="" selected="{lang === ''}">{App.$data.languageDefaultLabel}</option>
                        <option each="{language,idx in languages}" value="{language.code}" selected="{lang === language.code}">{language.label}</option>
                    </select>
                </div>
                <div>
                <a class="uk-modal-close uk-link-muted uk-margin-left"><i class="uk-icon-close"></i></a></div>
            </h3>

            <div class="uk-margin uk-flex uk-flex-middle" if="{collection}">

                <div class="uk-form-icon uk-form uk-flex-item-1 uk-text-muted" show="{!opts.filter}">

                    <i class="uk-icon-search"></i>
                    <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="{ App.i18n.get('Filter items...') }" onchange="{ updatefilter }">

                </div>

            </div>

            <div class="uk-overflow-container" if="{collection}">

                <div class="uk-text-xlarge uk-text-center uk-text-muted uk-margin-large-bottom" if="{ !entries.length && (filter || opts.filter) && !loading }">
                    { App.i18n.get('No entries found') }.
                </div>

                <table class="uk-table uk-table-tabbed uk-table-striped" if="{ modalOpen && entries.length }">
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
                            <td show="{!parent.opts.multiple}">
                                <a onclick="{ parent.linkItem }"><i class="uk-icon-link"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="uk-margin-large-bottom" if="{ loading }">
                    <cp-preloader class="uk-container-center"></cp-preloader>
                </div>

                <div class="uk margin" if="{ loadmore && !loading }">
                    <a class="uk-button uk-width-1-1" onclick="{ load.bind(this, false) }">
                        { App.i18n.get('Load more...') }
                    </a>
                </div>

            </div>

            <div class="uk-modal-footer uk-text-right" if="{opts.multiple}">
                <button type="button" class="uk-button uk-button-large uk-button-primary" onclick="{linkItems}" show="{selected.length}">
                    <i class="uk-icon-link"></i> {selected.length} {App.i18n.get('Entries')}
                </button>
                <a class="uk-modal-close uk-button uk-button-large uk-margin-small-left">{App.i18n.get('Cancel')}</a>
            </div>
        </div>
    </div>


    <script>

    this.mixin(RiotBindMixin);

    window.__collectionLinkCache = window.__collectionLinkCache || {};

    var $this = this, modal, cache = window.__collectionLinkCache, collections, _init = function(){

        this.error = this.collection ? false:true;

        this.loadmore   = false;
        this.entries    = [];
        this.fieldsidx  = {};
        this.fields     = this.collection.fields.filter(function(field){
            $this.fieldsidx[field.name] = field;
            return field.lst;
        });

		if (this.collection && this.languages.length) {
			this.lang = App.session.get('collections.entry.'+this.collection._id+'.lang', '');
		}

        this.fields.push({name:'_modified', 'label':App.i18n.get('Modified')});

        this.update();

    }.bind(this);

    this.modalOpen = false;
    this.link = null;
    this.sort = {_created: -1};
    this.languages  = App.$data.languages;
    this.lang = null;

    this.selected = [];

    this.$updateValue = function(value, field) {

        if (opts.multiple && !Array.isArray(value)) {
            value = [].concat(value ? [value]:[]);
        }

        if (JSON.stringify(this.link) !== JSON.stringify(value)) {
            this.link = value;
            this.update();
        }

    }.bind(this);

    this.on('mount', function(){

        this.sort = opts.sort || {_created: -1};

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

        modal.element.on('show.uk.modal', function() {
            if (!$this.modalOpen) {
                $this.modalOpen = true;
                $this.update();
            }
        })

        modal.element.on('hide.uk.modal', function() {

            if ($this.modalOpen) {
                $this.modalOpen = false;
                $this.update();
            }
        })
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

        $this.modalOpen = true;
        modal.show();
        modal.find(':checked').prop('checked', false);

        if (!this.entries.length) this.load();
    }

    linkItem(e) {

        var defaultField = this.collection.fields[0].name;
        var _entry = e.item.entry;
        var entry = {
            _id: _entry._id,
            link: this.collection.name,
            display: _.get(_entry, opts.display) || typeof _entry[defaultField] === 'string' && _entry[defaultField] || 'n/a'
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
        
        $this.cacheDisplay(_entry);
        $this.modalOpen = false;

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
            
            var defaultField = $this.collection.fields[0].name;
            $this.cacheDisplay(_entry);
            entry = {
                _id: _entry._id,
                link: $this.collection.name,
                display: _.get(_entry, opts.display) || typeof _entry[defaultField] === 'string' && _entry[defaultField] || 'n/a'
            };

            $this.link.push(entry);
        });

        setTimeout(function(){
            modal.hide();
        }, 50);

        $this.modalOpen = false;
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

    load(replace) {

        var limit = 50;
        var options = { sort:this.sort, lang:this.lang };

        if (this.filter) {
            options.filter = this.filter;
        } else {
            if (opts.filter) {
                options.filter = opts.filter;
            }
        }

        if (!this.collection.sortable) {
            options.limit = limit;
            options.skip  = replace ? 0 : this.entries.length;
        }

        this.loading = true;

        return App.request('/collections/find', {collection:this.collection.name, options:options}).then(function(data){

            this.entries = replace ? data.entries : this.entries.concat(data.entries);

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
            this.load(true);
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
        var cacheKey = link._id + ':' + this.lang;

        if (!cache[cacheKey]) {

            App.request('/collections/find', {collection:this.collection.name, options:{lang:this.lang, filter:{_id:link._id}, limit:1}}).then(function(data){

                if (!data.entries.length) {
                    cache[cacheKey] = 'n/a';
                    link.display = 'n/a';
                    this.update();
                    return;
                }

                link.display = $this.cacheDisplay(data.entries[0]);
                
                this.update();

            }.bind(this))

            cache[cacheKey] = '...';
            
        } else {
            display = cache[cacheKey];
        }
        
        return display;
    }

    changelanguage(e) {
		var lang = e.target.value;
		App.session.set('collections.entry.'+this.collection._id+'.lang', lang);
		this.lang = lang;
		this.load(true);
		this.update();
	}

    cacheDisplay(item) {

        var cacheKey = item._id + ':' + this.lang;
        var display = opts.display, val;

        if (!display) {
            display = item.name ? 'name':'title';
            val = item[display] || 'n/a';
        } else if (Array.isArray(display)) {
            val = display.map(function(field){
                return item[field] || "";
            }).join(', ');
        } else {
            val = item[display] || App.Utils.interpolate(display, item);
        }

        cache[cacheKey] = val;

        return val;
    }

    </script>

</field-collectionlink>
