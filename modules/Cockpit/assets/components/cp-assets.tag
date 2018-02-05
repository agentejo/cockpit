<cp-assets>

    <style>
        .cp-assets-fp {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: red;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            border: 2px #fff solid;
            top: 50%;
            left: 50%;
            transform: translateX(-50%) translateY(-50%);
            visibility: hidden;
        }
    </style>

    <div class="uk-form" ref="list" if="{ mode=='list' }">

        <div class="uk-grid uk-grid-width-1-2">
            <div>
                <div class="uk-grid uk-grid-small uk-flex-middle">
                    <div>
                        <div class="uk-form-select">

                            <span class="uk-button uk-button-large { getRefValue('filtertype') && 'uk-button-primary'} uk-text-capitalize"><i class="uk-icon-eye uk-margin-small-right"></i> { getRefValue('filtertype') || App.i18n.get('All') }</span>

                            <select ref="filtertype" onchange="{ updateFilter }">
                                <option value="">All</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="audio">Audio</option>
                                <option value="document">Document</option>
                                <option value="archive">Archive</option>
                                <option value="code">Code</option>
                            </select>

                        </div>
                    </div>
                    <div class="uk-flex-item-1">
                        <div class="uk-form-icon uk-display-block uk-width-1-1">
                            <i class="uk-icon-search"></i>
                            <input class="uk-width-1-1 uk-form-large" type="text" ref="filtertitle" onchange="{ updateFilter }">
                        </div>
                    </div>
                </div>
            </div>
            <div class="uk-text-right">

                <button class="uk-button uk-button-large uk-button-danger" type="button" onclick="{ removeSelected }" show="{ selected.length }">
                    { App.i18n.get('Delete') } <span class="uk-badge uk-badge-contrast uk-margin-small-left">{ selected.length }</span>
                </button>

                <span class="uk-button-group uk-button-large">
                    <button class="uk-button uk-button-large {listmode=='list' && 'uk-button-primary'}" type="button" onclick="{ toggleListMode }"><i class="uk-icon-list"></i></button>
                    <button class="uk-button uk-button-large {listmode=='grid' && 'uk-button-primary'}" type="button" onclick="{ toggleListMode }"><i class="uk-icon-th"></i></button>
                </span>

                <span class="uk-button uk-button-large uk-button-primary uk-margin-small-right uk-form-file">
                    <input class="js-upload-select" type="file" multiple="true">
                    <i class="uk-icon-upload"></i>
                </span>
            </div>
        </div>

        <div ref="uploadprogress" class="uk-margin uk-hidden">
            <div class="uk-progress">
                <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div>
            </div>
        </div>

        <div class="uk-margin-large-top uk-panel-space uk-text-center" show="{ !loading && !assets.length }">
            <span class="uk-text-muted uk-h2">{ App.i18n.get('No Assets found') }</span>
        </div>

        <div class="uk-text-center uk-text-muted uk-h2 uk-margin-large-top" show="{ loading }">
            <i class="uk-icon-spinner uk-icon-spin"></i>
        </div>

        <div class="uk-margin-large-top {modal && 'uk-overflow-container'}" if="{ !loading && assets.length }">

            <div class="uk-grid uk-grid-small uk-grid-width-medium-1-5" if="{ listmode=='grid' }">
                <div class="uk-grid-margin" each="{ asset,idx in assets }" onclick="{ select }">
                    <div class="uk-panel uk-panel-box { selected.length && selected.indexOf(asset) != -1 ? 'uk-selected':''}">
                        <div class="uk-overlay uk-display-block uk-position-relative">
                            <canvas class="uk-responsive-width" width="200" height="150"></canvas>
                            <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle">
                                <div class="uk-width-1-1 uk-text-center">
                                    <span if="{ asset.mime.match(/^image\//) == null }"><i class="uk-h1 uk-text-muted uk-icon-{ parent.getIconCls(asset.path) }"></i></span>
                                    <cp-thumbnail src="{ASSETS_URL+asset.path}" height="150" if="{ asset.mime.match(/^image\//) }" title="{ asset.width && [asset.width, asset.height].join('x') }"></cp-thumbnail>
                                </div>
                            </div>
                        </div>
                        <div class="uk-text-small uk-margin-small-top uk-text-truncate">
                            <a onclick="{ parent.edit }"><i class="uk-icon-pencil uk-small-margin-right"></i> { asset.title }</a>
                        </div>
                        <div class="uk-text-small uk-text-muted uk-margin-small-top uk-flex">
                            <strong>{ asset.mime }</strong>
                            <span class="uk-flex-item-1 uk-margin-small-left uk-margin-small-right">{ App.Utils.formatSize(asset.size) }</span>
                            <a href="{ASSETS_URL+asset.path}" if="{ asset.mime.match(/^image\//) }" data-uk-lightbox="type:'image'" title="{ asset.width && [asset.width, asset.height].join('x') }">
                                <i class="uk-icon-search"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <table class="uk-table uk-table-tabbed" if="{ listmode=='list' }">
                <thead>
                    <tr>
                        <td width="30"></td>
                        <th class="uk-text-small uk-noselect">{ App.i18n.get('Title') }</th>
                        <th class="uk-text-small uk-noselect" width="20%">{ App.i18n.get('Type') }</th>
                        <th class="uk-text-small uk-noselect" width="10%">{ App.i18n.get('Size') }</th>
                        <th class="uk-text-small uk-noselect" width="10%">{ App.i18n.get('Updated') }</th>
                        <th class="uk-text-small uk-noselect" width="30"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="{ selected.length && selected.indexOf(asset) != -1 ? 'uk-selected':''}" each="{ asset,idx in assets }" onclick="{ select }">
                        <td class="uk-text-center">

                            <span if="{ asset.mime.match(/^image\//) == null }"><i class="uk-text-muted uk-icon-{ parent.getIconCls(asset.path) }"></i></span>

                            <a href="{ASSETS_URL+asset.path}" if="{ asset.mime.match(/^image\//) }" data-uk-lightbox="type:'image'" title="{ asset.width && [asset.width, asset.height].join('x') }">
                                <cp-thumbnail src="{ASSETS_URL+asset.path}" width="20" height="20"></cp-thumbnail>
                            </a>
                        </td>
                        <td>
                            <a if="{!parent.modal}" onclick="{ parent.edit }">{ asset.title }</a>
                            <span if="{parent.modal}">{ asset.title }</span>
                        </td>
                        <td class="uk-text-small">{ asset.mime }</td>
                        <td class="uk-text-small">{ App.Utils.formatSize(asset.size) }</td>
                        <td class="uk-text-small">{ App.Utils.dateformat( new Date( 1000 * asset.modified )) }</td>
                        <td>
                            <span class="uk-float-right" data-uk-dropdown="mode:'click'">

                                <a class="uk-icon-bars"></a>

                                <div class="uk-dropdown uk-dropdown-flip">
                                    <ul class="uk-nav uk-nav-dropdown">
                                        <li class="uk-nav-header">{ App.i18n.get('Actions') }</li>
                                        <li><a class="uk-dropdown-close" onclick="{ parent.edit }">{ App.i18n.get('Edit') }</a></li>
                                        <li><a class="uk-dropdown-close" onclick="{ parent.remove }">{ App.i18n.get('Delete') }</a></li>
                                    </ul>
                                </div>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="uk-margin uk-flex uk-flex-middle uk-noselect" if="{ pages > 1 }">

                <ul class="uk-breadcrumb uk-margin-remove">
                    <li class="uk-active"><span>{ page }</span></li>
                    <li data-uk-dropdown="mode:'click'">

                        <a><i class="uk-icon-bars"></i> { pages }</a>

                        <div class="uk-dropdown">

                            <strong class="uk-text-small"> { App.i18n.get('Pages') }</strong>

                            <div class="uk-margin-small-top { pages > 5 ? 'uk-scrollable-box':'' }">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-text-small" each="{k,v in new Array(pages)}"><a class="uk-dropdown-close" onclick="{ parent.loadPage }" data-page="{ (v + 1) }"> { App.i18n.get('Page') } {v + 1}</a></li>
                                </ul>
                            </div>
                        </div>

                    </li>
                </ul>

                <div class="uk-button-group uk-margin-small-left">
                    <a class="uk-button uk-button-small" onclick="{ loadPage }" data-page="{ (page - 1) }" if="{page-1 > 0}"> { App.i18n.get('Previous') }</a>
                    <a class="uk-button uk-button-small" onclick="{ loadPage }" data-page="{ (page + 1) }" if="{page+1 <= pages}"> { App.i18n.get('Next') }</a>
                </div>

            </div>

        </div>

    </div>

    <div class="uk-form" if="{asset && mode=='edit'}">
        <form onsubmit="{ updateAsset }">
            <div class="uk-grid">
                <div class="uk-width-2-3">

                    <div class="uk-panel uk-panel-box uk-panel-card uk-panel-space">
                        <div class="uk-form-row">
                            <label class="uk-text-small uk-text-bold">{ App.i18n.get('Title') }</label>
                            <input class="uk-width-1-1" type="text" bind="asset.title" required>
                        </div>

                        <div class="uk-form-row">
                            <label class="uk-text-small uk-text-bold">{ App.i18n.get('Description') }</label>
                            <textarea class="uk-width-1-1" bind="asset.description"></textarea>
                        </div>

                        <div class="uk-margin-large-top uk-text-center" if="{asset}">
                            <span class="uk-h1" if="{asset.mime.match(/^image\//) == null }"><i class="uk-icon-{ getIconCls(asset.path) }"></i></span>
                            <div class="uk-display-inline-block uk-position-relative asset-fp-image" if="{asset.mime.match(/^image\//) }">
                                <cp-thumbnail src="{ASSETS_URL+asset.path}" width="800"></cp-thumbnail>
                                <div class="cp-assets-fp" title="Focal Point" data-uk-tooltip></div>
                            </div>
                            <div class="uk-margin-top uk-text-truncate uk-text-small uk-text-muted">
                                <a class="uk-button uk-button-outline uk-text-primary" href="{ASSETS_URL+asset.path}" target="_blank"><i class="uk-icon-link"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="uk-width-1-3" if="{ asset }">

                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Id') }</label>
                        <div class="uk-margin-small-top uk-text-muted">{ asset._id }</div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Type') }</label>
                        <div class="uk-margin-small-top uk-text-muted"><span class="uk-badge uk-badge-outline">{ asset.mime }</span></div>
                    </div>
                    <div class="uk-margin" if="{asset.colors && Array.isArray(asset.colors) && asset.colors.length}">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Colors') }</label>
                        <div class="uk-margin-small-top uk-text-muted">
                            <span class="uk-icon-circle uk-text-large uk-margin-small-right" each="{color in asset.colors}" riot-style="color: #{color}"></span>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Size') }</label>
                        <div class="uk-margin-small-top uk-text-muted">{ App.Utils.formatSize(asset.size) }</div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Modified') }</label>
                        <div class="uk-margin-small-top uk-text-primary"><span class="uk-badge uk-badge-outline">{ App.Utils.dateformat( new Date( 1000 * asset.modified )) }</span></div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Tags') }</label>
                        <div class="uk-margin-small-top">
                            <field-tags bind="asset.tags"></field-tags>
                        </div>
                    </div>
                    <div class="uk-margin" if="{ asset._by }">
                        <label class="uk-text-small">{ App.i18n.get('Last update by') }</label>
                        <div class="uk-margin-small-top">
                            <cp-account account="{asset._by}"></cp-account>
                        </div>
                    </div>

                </div>
            </div>

            <div class="uk-margin-large-top">
                <button type="submit" class="uk-button uk-button-large uk-button-primary uk-margin-right">{ App.i18n.get('Save') }</button>
                <a onclick="{ cancelEdit }">{ App.i18n.get('Cancel') }</a>
            </div>

        </form>

    </div>


    <script>

        this.mixin(RiotBindMixin);

        var $this = this, typefilters = {
            'image'    : /\.(jpg|jpeg|png|gif|svg)$/i,
            'video'    : /\.(mp4|mov|ogv|webv|wmv|flv|avi)$/i,
            'audio'    : /\.(mp3|weba|ogg|wav|flac)$/i,
            'archive'  : /\.(zip|rar|7zip|gz)$/i,
            'document' : /\.(txt|pdf|md)$/i,
            'code'     : /\.(htm|html|php|css|less|js|json|yaml|xml|htaccess)$/i
        };

        this.mode     = 'list';
        this.listmode = App.session.get('app.assets.listmode', 'list');
        this.loading  = false;
        this.assets   = [];
        this.selected = [];

        this.modal    = opts.modal;

        // pagination
        this.count    = 0;
        this.page     = 1;
        this.pages    = 1;
        this.limit    = opts.limit || 15;

        this.on('mount', function() {

            this.listAssets(1);

            // handle uploads
            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                var uploadSettings = {

                    action: App.route('/assetsmanager/upload'),
                    type: 'json',
                    before: function(options) {

                    },
                    loadstart: function() {
                        $this.refs.uploadprogress.classList.remove('uk-hidden');
                    },
                    progress: function(percent) {

                        percent = Math.ceil(percent) + '%';

                        $this.refs.progressbar.innerHTML   = '<span>'+percent+'</span>';
                        $this.refs.progressbar.style.width = percent;
                    },
                    allcomplete: function(response) {

                        $this.refs.uploadprogress.classList.add('uk-hidden');

                        if (response && response.failed && response.failed.length) {
                            App.ui.notify("File(s) failed to uploaded.", "danger");
                        }

                        if (response && Array.isArray(response.assets) && response.assets.length) {

                            if (!Array.isArray($this.assets)) {
                                $this.assets = [];
                            }

                            App.ui.notify("File(s) uploaded.", "success");

                            response.assets.forEach(function(asset){
                                $this.assets.unshift(asset);
                            });

                            $this.listAssets(1);
                        }

                        if (!response) {
                            App.ui.notify("Something went wrong.", "danger");
                        }

                    }
                },

                uploadselect = UIkit.uploadSelect(App.$('.js-upload-select', $this.root)[0], uploadSettings),
                uploaddrop   = UIkit.uploadDrop($this.refs.list, uploadSettings);

                UIkit.init(this.root);
            });

            App.$(this.root).on('click', '.asset-fp-image canvas', function(e) {

                var x = e.offsetX, y = e.offsetY,
                    px = (x / this.offsetWidth),
                    py = (y / this.offsetHeight);

                $this.asset.fp = {x: px, y: py};
                $this.placeFocalPoint($this.asset.fp);
            });
        });

        toggleListMode() {
            this.listmode = this.listmode=='list' ? 'grid':'list';
            App.session.set('app.assets.listmode', this.listmode);
        }

        listAssets(page) {

            this.page    = page || 1;
            this.loading = true;

            var options = {
                filter : this.filter || null,
                limit  : this.limit,
                skip   : (this.page-1) * this.limit,
                sort   : {created:-1}
            };

            App.request('/assetsmanager/listAssets', options).then(function(response){

                $this.assets   = Array.isArray(response.assets) ? response.assets:[];
                $this.count    = response.count || 0;
                $this.pages    = Math.ceil($this.count/$this.limit);
                $this.loading  = false;
                $this.selected = [];
                $this.update();
            });

        }

        updateFilter() {

            this.filter = null;

            if (this.refs.filtertitle.value || this.refs.filtertype.value) {
                this.filter = {};
            }

            if (this.refs.filtertitle.value) {
                this.filter.title = {'$regex':this.refs.filtertitle.value};
            }

            if (this.refs.filtertype.value) {
                this.filter[this.refs.filtertype.value] = true;
            }

            this.listAssets(1);
        }

        loadPage(e) {

            var page = parseInt(e.target.getAttribute('data-page'), 10);

            this.listAssets(page || 1);
        }

        remove(e) {
            var asset = e.item.asset,
                idx   = e.item.idx;

            App.ui.confirm("Are you sure?", function() {

                App.request('/assetsmanager/removeAssets', {assets:[asset]}).then(function(data) {

                    App.ui.notify("Asset removed", "success");

                    $this.assets.splice(idx, 1);

                    if (!$this.assets.length) {
                        $this.page = $this.page > 1 ? $this.page -1 : 1;
                    }

                    $this.listAssets($this.page);

                    $this.selected = [];
                    $this.update();
                });
            });

        }

        removeSelected() {

            App.ui.confirm("Are you sure?", function() {

                App.request('/assetsmanager/removeAssets', {assets:$this.selected}).then(function(data) {

                    $this.selected.forEach(function(asset){
                        $this.assets.splice($this.assets.indexOf(asset), 1);
                    });

                    if (!$this.assets.length) {
                        $this.page = $this.page > 1 ? $this.page -1 : 1;
                    }

                    $this.listAssets($this.page);

                    App.ui.notify("Assets removed", "success");
                    $this.selected = [];
                    $this.update();
                });
            });

        }

        edit(e) {

            this.asset = e.item.asset;
            this.mode  = 'edit';

            if (this.asset.mime.match(/^image\//)) {
                setTimeout(function() {
                    $this.placeFocalPoint($this.asset.fp);
                }, 500)
            }
        }

        cancelEdit() {
            this.asset = null;
            this.mode  = 'list';
        }

        updateAsset(e) {

            e.preventDefault();

            App.request('/assetsmanager/updateAsset', {asset:$this.asset}).then(function(asset) {

                App.$.extend($this.asset, asset);
                App.ui.notify("Asset updated", "success");
                $this.update();
            });

            return false;
        }

        select(e) {

            if (App.$(e.target).is('a') || App.$(e.target).parents('a').length) return;

            var idx = this.selected.indexOf(e.item.asset);

            if (idx == -1) {
                this.selected.push(e.item.asset);
            } else {
                this.selected.splice(idx, 1);
            }

            App.$(this.root).trigger('selectionchange', [this.selected]);
        }

        getIconCls(path) {

            var name = path.toLowerCase();

            if (name.match(typefilters.image)) {

                return 'image';

            } else if(name.match(typefilters.video)) {

                return 'video-camera';

            } else if(name.match(typefilters.audio)) {

                return 'music';

            } else if(name.match(typefilters.document)) {

                return 'file-text-o';

            } else if(name.match(typefilters.code)) {

                return 'code';

            } else if(name.match(typefilters.archive)) {

                return 'archive';

            } else {
                return 'paperclip';
            }
        }

        getRefValue(name) {
            return this.refs[name] && this.refs[name].value;
        }

        placeFocalPoint(point) {

            point = point || {x:0.5, y:0.5};

            var canvas = App.$(this.root).find('.asset-fp-image canvas')[0];
            var x = (point.x * 100)+'%';
            var y = (point.y * 100)+'%';

            App.$(this.root).find('.cp-assets-fp').css({
                left: x,
                top: y,
                visibility: 'visible'
            });
        }


    </script>

</cp-assets>
