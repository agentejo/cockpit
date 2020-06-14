(function () {

    var linkCache = {};

    App.Utils.renderer.collectionlink = function (v, field) {

        if (!v) {
            return '<i class="uk-icon-eye-slash uk-text-muted"></i>';
        }

        if (Array.isArray(v)) {
            if (v.length > 1) {
                return `<span class="uk-badge ${!v.length && 'uk-badge-outline uk-text-muted'}">${v.length}</span>`;
            }
            v = v[0];
        }

        if (!linkCache[v._id]) {

            linkCache[v._id] = new Promise(function (resolve) {

                App.request('/collections/find', { collection: field.options.link, options: { filter: { _id: v._id } } }).then(function (data) {

                    if (!data.entries || !data.entries.length) {
                        v.display = 'n/a';
                    } else {

                        var _entry = data.entries[0], display = field.options.display;

                        if (!display) {
                            display = _entry.name ? 'name' : 'title';
                            v.display = _entry[display] || 'n/a';
                        } else {
                            v.display = _entry[display] || App.Utils.interpolate(display, _entry);
                        }
                    }

                    resolve(v.display)

                });
            });
        }

        setTimeout(() => {
            linkCache[v._id].then(display => {

                let spans = document.querySelectorAll(`[data-collection-display-id='${v._id}']`);

                [...spans].forEach(span => {
                    span.innerText = display;
                    span.removeAttribute('data-collection-display-id');
                });
            })
        });

        return `<span data-collection-display-id="${v._id}"><i class="uk-icon-spin uk-icon-spinner uk-text-muted"></i></span>`;
    };

    function selectCollectionItem(fn, options) {

        var options = _.extend({
            release: fn || function () { }
        }, options || {});

        var dialog = UIkit.modal.dialog(
            '<div riot-view><link-collectionitem></link-collectionitem></div>',
            { modal: false }
        );

        options.dialog = dialog;
        riot.util.initViews(dialog.element[0], options);
        dialog.show();
    }

    Cockpit.selectCollectionItem = selectCollectionItem;

    // register picker
    App.$(document).on('init-html-editor', function (e, editor) {

        editor.addButtons({
            cpcollectionlink: {
                title: 'Collection Link',
                label: `<img src="${App.base('/modules/Collections/icon.svg')}" width="13" height="13" style="transform: translateY(-2px)">`
            }
        });


        editor.on('action.cpcollectionlink', function () {

            selectCollectionItem(function (data) {

                if (editor.getCursorMode() == 'markdown') {
                    editor['replaceSelection']('[' + data.title + '](' + data.url + ')');
                } else {
                    editor['replaceSelection']('<a href="' + data.url + '">' + data.title + '</a>');
                }

            }, { url: '', title: '' });

        });

        editor.options.toolbar = editor.options.toolbar.concat(['cpcollectionlink']);
    });

    App.$(document).on('init-wysiwyg-editor', function (e, editor) {

        tinymce.PluginManager.add('cpcollectionlink', function (ed) {

            ed.addMenuItem('pageurl', {
                icon: 'link',
                text: App.i18n.get('Link Collection Item'),
                onclick: function () {

                    selectCollectionItem(function (data) {
                        ed.insertContent('<a href="' + data.url + '" alt="">' + data.title + '</a>');
                    }, { url: '', title: '' });
                },
                context: 'insert',
                prependToContext: true
            });

        });

    });

    App.Utils.renderer.collectionlinkselect = App.Utils.renderer.collectionlink;


    riot.tag2('link-collectionitem',
        `
                <div class="uk-modal-header uk-text-large">
                    { App.i18n.get('Link Collection Item') }
                </div>

                <form ref="form" class="uk-form">

                    <div class="uk-form-row">
                        <label class="uk-text-small">{ App.i18n.get('Collection') }</label>

                        <div class="uk-margin-top uk-text-large" show="{ !collections }">
                            <i class="uk-icon-spinner uk-icon-spin"></i>
                        </div>

                        <div class="uk-margin-small-top" show="{collections}" data-uk-dropdown="mode:'click'">

                            <a>{collection ? collections[collection].label || collection : App.i18n.get('Select collection...') }</a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                                    <li class="uk-nav-header">{ App.i18n.get('Collections') }</li>
                                    <li each="{meta, name in collections}">
                                        <a onclick="{parent.selectCollection}" name="{name}">{meta.label || name}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="frmSelectCollectionLink" class="uk-form-row" if="{collection}">

                        <label class="uk-text-small">{ App.i18n.get('Items') }</label>

                        <div class="uk-margin-top uk-text-large" show="{ !items }">
                            <i class="uk-icon-spinner uk-icon-spin"></i>
                        </div>

                        <div class="uk-margin-small-top" show="{ Array.isArray(items) }">

                            <div class="uk-form-icon uk-form uk-width-1-1 uk-text-muted">
                                <i class="uk-icon-search"></i>
                                <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="{ App.i18n.get('Filter items...') }" onchange="{ updatefilter }">
                            </div>

                            <div class="uk-scrollable-box" show="{items && items.length}">

                                <ul class="uk-list">
                                    <li class="uk-margin-small-top uk-text-truncate" each="{item in items}">
                                        <a onclick="{ parent.apply }"><i class="uk-icon-file-text-o"></i> { item.title || item.name || 'n/a' }</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="uk-button-group uk-margin-small-top uk-flex uk-flex-middle" show="{items && items.length}">

                                <ul class="uk-breadcrumb uk-margin-remove">
                                    <li class="uk-active"><span>{ page }</span></li>
                                    <li data-uk-dropdown="mode:'click'">

                                        <a><i class="uk-icon-bars"></i> { pages }</a>

                                        <div class="uk-dropdown">

                                            <strong class="uk-text-small">{ App.i18n.get('Pages') }</strong>

                                            <div class="uk-margin-small-top { pages > 5 ? 'uk-scrollable-box':'' }">
                                                <ul class="uk-nav uk-nav-dropdown">
                                                    <li class="uk-text-small" each="{k,v in new Array(pages)}"><a class="uk-dropdown-close" onclick="{ parent.loadpage.bind(parent, v+1) }">{App.i18n.get('Page')} {v + 1}</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                    </li>
                                </ul>

                                <a class="uk-button uk-button-link uk-button-small" onclick="{ loadpage.bind(this, page-1) }" if="{page-1 > 0}">{ App.i18n.get('Previous') }</a>
                                <a class="uk-button uk-button-link uk-button-small" onclick="{ loadpage.bind(this, page+1) }" if="{page+1 <= pages}">{ App.i18n.get('Next') }</a>
                            </div>

                            <div class="uk-alert" show="{ items && !items.length }">No items found</div>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">Title</label>
                        <input ref="title" type="text" class="uk-width-1-1 uk-form-large" required>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">Url</label>
                        <input ref="url" type="text" class="uk-width-1-1 uk-form-large" onkeyup="{ update }" required>
                    </div>
                </form>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-primary uk-margin-right uk-button-large js-create-button" onclick="App.$('#frmSelectCollectionLink').submit()" show="{val('url')}">${App.i18n.get('Select')}</button>
                    <button class="uk-button uk-button-link uk-button-large uk-modal-close">${App.i18n.get('Cancel')}</button>
                </div>

              `, '', '', function (opts) {

        var $this = this;

        this.count = 0;
        this.page = 1;
        this.pages = 1;

        App.request('/collections/_collections').then(function (data) {
            $this.collections = data;
            $this.update();
        });

        this.on('mount', function () {

            App.$(this.refs.form).on('submit', function (e) {

                e.preventDefault();
                $this.parent.opts.release({ url: $this.refs.url.value, title: $this.refs.title.value });
                $this.parent.opts.dialog.hide();
            });
        })

        this.selectCollection = function (e) {
            this.collection = e.item.name;
            this.filter = '';
            this.page = 1;
            this.load();
        }.bind(this)

        this.apply = function (e) {
            this.refs.url.value = 'collection://' + this.collection + '/' + e.item.item._id;
            this.refs.title.value = e.item.item.title || e.item.item.name || '';
        }.bind(this)

        this.val = function (ref) {
            return this.refs[ref] && this.refs[ref].value;
        }.bind(this)

        this.updatefilter = function (e) {
            this.filter = e.target.value;
            this.page = 1;
            this.load();
        }.bind(this)

        this.loadpage = function (page) {
            this.page = page > this.pages ? this.pages : page;
            this.load();
        }.bind(this)

        this.load = function () {

            this.items = null;

            var options = {
                limit: 20
            };

            if (this.filter) {
                options.filter = this.filter;
            }

            options.skip = (this.page - 1) * options.limit;

            App.request('/collections/find', { collection: this.collection, options: options }).then(function (data) {

                this.items = data.entries;
                this.page = data.page;
                this.pages = data.pages;
                this.count = data.count;
                this.update();

            }.bind(this))
        }.bind(this)
    });

})();
