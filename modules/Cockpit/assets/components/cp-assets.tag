<cp-assets>

    <style>
        .uk-breadcrumb { margin-bottom: 0; }
    </style>

    <div ref="list" show="{ mode=='list' }">

        <div ref="uploadprogress" class="uk-margin uk-hidden">
            <div class="uk-progress">
                <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div>
            </div>
        </div>

        <div class="uk-form" if="{ mode=='list' }">

            <div class="uk-grid">
                <div>
                    <div class="uk-grid uk-grid-small uk-flex-middle">
                        <div>
                            <span class="uk-button-group uk-margin-right">
                                <button class="uk-button uk-button-large {listmode=='list' && 'uk-button-primary'}" type="button" onclick="{ toggleListMode }" aria-label="{ App.i18n.get('Switch to list-mode') }"><i class="uk-icon-list"></i></button>
                                <button class="uk-button uk-button-large {listmode=='grid' && 'uk-button-primary'}" type="button" onclick="{ toggleListMode }" aria-label="{ App.i18n.get('Switch to tile-mode') }"><i class="uk-icon-th"></i></button>
                            </span>
                        </div>
                        <div show="{!opts.typefilter}">
                            <div class="uk-form-select">

                                <span class="uk-button uk-button-large { getRefValue('filtertype') && 'uk-button-primary'} uk-text-capitalize"><i class="uk-icon-eye uk-margin-small-right"></i> { getRefValue('filtertype') || App.i18n.get('All') }</span>

                                <select ref="filtertype" onchange="{ updateFilter }" aria-label="{App.i18n.get('Mime Type')}">
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
                                <input class="uk-width-1-1 uk-form-large" type="text" aria-label="{ App.i18n.get('Search assets') }" ref="filtertitle" onchange="{ updateFilter }">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="uk-flex-item-1"></div>
                <div class="uk-flex uk-flex-middle">

                    <button class="uk-button uk-button-large uk-button-danger" type="button" onclick="{ removeSelected }" show="{ selected.length }">
                        { App.i18n.get('Delete') } <span class="uk-badge uk-badge-contrast uk-margin-small-left">{ selected.length }</span>
                    </button>

                    <button class="uk-button uk-button-large uk-button-link" onclick="{addFolder}">{ App.i18n.get('Add folder') }</button>

                    <div data-uk-dropdown="mode:'click'">

                        <a class="uk-button uk-button-large uk-button-primary"><i class="uk-icon-upload"></i></a>

                        <div class="uk-dropdown uk-margin-top uk-text-left">

                            <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                                <li class="uk-nav-header uk-flex uk-flex-middle">
                                    <span class="uk-flex-item-1">{ App.i18n.get('Upload') }</span>
                                    <span class="uk-badge uk-badge-outline uk-text-warning"> max. { App.Utils.formatSize(App.$data.maxUploadSize) }</span>
                                </li>
                                <li>
                                    <a class="uk-form-file">
                                        <i class="uk-icon-file-o uk-icon-justify"></i> { App.i18n.get('File') }
                                        <input class="js-upload-select" aria-label="{ App.i18n.get('Select file') }" type="file" multiple="true">
                                    </a>
                                    <a class="uk-form-file">
                                        <i class="uk-icon-folder-o uk-icon-justify"></i> { App.i18n.get('Folder') }
                                        <input class="js-upload-folder" type="file" title="" multiple multiple directory webkitdirectory allowdirs>
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>

            <div class="uk-margin">
                <ul class="uk-breadcrumb">
                    <li onclick="{ changeDir }"><a title="{ App.i18n.get('Change dir to root') }"><i class="uk-icon-home"></i></a></li>
                    <li each="{folder, idx in foldersPath}"><a onclick="{ parent.changeDir }" title="Change dir to { folder.name }">{ folder.name }</a></li>
                </ul>
            </div>

            <div class="uk-text-center uk-margin-large-top" show="{ loading }">
                <cp-preloader class="uk-container-center"></cp-preloader>
            </div>

            <div class="{modal && 'uk-overflow-container'}" style="padding: 1px 1px;">

                <div class="uk-margin" if="{ !loading && folders.length }">

                    <strong class="uk-text-small uk-text-muted"><i class="uk-icon-folder-o uk-margin-small-right"></i> {folders.length} {App.i18n.get('Folders')}</strong>

                    <div class="uk-grid uk-grid-small uk-grid-match uk-grid-width-medium-1-4 uk-grid-width-xlarge-1-5">
                        <div class="uk-grid-margin" each="{ folder,idx in folders }">
                            <div class="uk-panel uk-panel-box uk-panel-card">
                                <div class="uk-flex">
                                    <div class="uk-margin-small-right"><i class="uk-icon-folder-o"></i></div>
                                    <div class="uk-flex-item-1 uk-text-bold uk-text-truncate"><a class="uk-link-muted" onclick="{parent.changeDir}">{ folder.name }</a></div>
                                    <div>
                                        <span data-uk-dropdown="mode:'click', pos:'bottom-right'">
                                            <a><i class="uk-icon-ellipsis-v js-no-item-select"></i></a>
                                            <div class="uk-dropdown">
                                                <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                                                    <li class="uk-nav-header uk-text-truncate">{ folder.name }</li>
                                                    <li><a class="uk-dropdown-close" onclick="{ parent.renameFolder }">{ App.i18n.get('Rename') }</a></li>
                                                    <li class="uk-nav-divider"></li>
                                                    <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ parent.removeFolder }">{ App.i18n.get('Delete') }</a></li>
                                                </ul>
                                            </div>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="uk-margin-large-top uk-panel-space uk-text-center" show="{ !loading && !assets.length }">
                    <span class="uk-text-muted uk-h2">{ App.i18n.get('No Assets found') }</span>
                </div>

                <div class="uk-margin" if="{ !loading && assets.length }">

                    <strong class="uk-text-small uk-text-muted"><i class="uk-icon-file-o uk-margin-small-right"></i> {count} {App.i18n.get('Assets')}</strong>

                    <div class="uk-grid uk-grid-match uk-grid-small uk-grid-width-medium-1-5" if="{ listmode=='grid' }">
                        <div class="uk-grid-margin" each="{ asset,idx in assets }" onclick="{ select }">
                            <div class="uk-panel uk-panel-box uk-panel-card uk-padding-remove { selected.length && selected.indexOf(asset) != -1 ? 'uk-selected':''}">
                                <div class="uk-overlay uk-display-block uk-position-relative { asset.mime.match(/^image\//) && 'uk-bg-transparent-pattern' }">
                                    <canvas class="uk-responsive-width" width="200" height="150"></canvas>
                                    <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle">
                                        <div class="uk-width-1-1 uk-text-center">
                                            <span if="{ asset.mime.match(/^image\//) == null }"><i class="uk-h1 uk-text-muted uk-icon-{ parent.getIconCls(asset.path) }"></i></span>
                                            <cp-thumbnail src="{asset._id}" height="150" if="{ asset.mime.match(/^image\//) }" title="{ asset.width && [asset.width, asset.height].join('x') }"></cp-thumbnail>
                                        </div>
                                    </div>
                                </div>
                                <div class="uk-panel-body uk-text-small">
                                    <div class="uk-text-truncate">
                                        <a onclick="{ parent.edit }">{ asset.title }</a>
                                    </div>
                                    <div class="uk-text-muted uk-margin-small-top uk-flex">
                                        <strong>{ asset.mime }</strong>
                                        <span class="uk-flex-item-1 uk-margin-small-left uk-margin-small-right">{ App.Utils.formatSize(asset.size) }</span>
                                        <a href="{ASSETS_URL+asset.path}" if="{ asset.mime.match(/^image\//) }" data-uk-lightbox="type:'image'" title="{ asset.width && [asset.width, asset.height].join('x') }" aria-label="{ asset.width && [asset.width, asset.height].join('x') }">
                                            <i class="uk-icon-search"></i>
                                        </a>
                                    </div>
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

                                    <a href="{ASSETS_URL+asset.path}" if="{ asset.mime.match(/^image\//) }" data-uk-lightbox="type:'image'" title="{ asset.width && [asset.width, asset.height].join('x') }" aria-label="{ asset.width && [asset.width, asset.height].join('x') }">
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

                </div>

            </div>

            <div class="uk-margin uk-flex uk-flex-middle uk-noselect" if="{!loading && pages > 1 }">

                <ul class="uk-breadcrumb uk-margin-remove">
                    <li class="uk-active"><span>{ page }</span></li>
                    <li data-uk-dropdown="mode:'click'">

                        <a><i class="uk-icon-bars"></i> { pages }</a>

                        <div class="uk-dropdown">

                            <strong class="uk-text-small"> { App.i18n.get('Pages') }</strong>

                            <div class="uk-margin-small-top { pages > 5 && 'uk-scrollable-box' }">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-text-small" each="{k,v in new Array(pages)}"><a class="uk-dropdown-close" onclick="{ parent.loadPage }" data-page="{ (v + 1) }"> { App.i18n.get('Page') } {v + 1}</a></li>
                                </ul>
                            </div>
                        </div>

                    </li>
                </ul>

                <div class="uk-button-group uk-margin-small-left">
                    <a class="uk-button uk-button-link uk-button-small" onclick="{ loadPage }" data-page="{ (page - 1) }" if="{page-1 > 0}"> { App.i18n.get('Previous') }</a>
                    <a class="uk-button uk-button-link uk-button-small" onclick="{ loadPage }" data-page="{ (page + 1) }" if="{page+1 <= pages}"> { App.i18n.get('Next') }</a>
                </div>

            </div>
        </div>
    </div>

    <div class="uk-form" if="{asset && mode=='edit'}">

        <h3 class="uk-text-bold">{ App.i18n.get('Edit Asset') }</h3>
        
        <cp-asset asset="{asset._id}"></cp-asset>
        
        <div class="uk-margin-top" show="{modal}">
            <button type="button" class="uk-button uk-button-large uk-button-primary" onclick="{ saveAsset }">{ App.i18n.get('Save') }</button>
            <a class="uk-button uk-button-large uk-button-link" onclick="{ cancelAssetEdit }">{ App.i18n.get('Cancel') }</a>
        </div>

        <cp-actionbar show="{!modal}">
            <div class="uk-container uk-container-center">
                <button type="button" class="uk-button uk-button-large uk-button-primary" onclick="{ saveAsset }">{ App.i18n.get('Save') }</button>
                <a class="uk-button uk-button-large uk-button-link" onclick="{ cancelAssetEdit }">{ App.i18n.get('Cancel') }</a>
            </div>
        </cp-actionbar>
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
        this.loading  = true;
        this.assets   = [];
        this.selected = [];

        this.folders  = [];
        this.folder   = '';
        this.foldersPath = [];

        this.modal    = opts.modal;

        // pagination
        this.count    = 0;
        this.page     = 1;
        this.pages    = 1;
        this.limit    = opts.limit || 15;

        this.on('mount', function() {

            if (opts.typefilter) {
                this.refs.filtertype.value = opts.typefilter;
            }

            this.listAssets(1);

            // handle uploads
            App.assets.require([
                '/assets/lib/uikit/js/components/upload.js',
                '/assets/lib/uppie.js'
            ], function() {

                var uploadSettings = {

                    action: App.route('/assetsmanager/upload'),
                    type: 'json',
                    before: function(options) {
                        options.params.folder = $this.folder
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
                            App.ui.notify("File(s) failed to upload.", "danger");
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

                // upload folder
                
                var uppie = new Uppie();

                uppie($this.root.querySelector('.js-upload-folder'), async (e, formData, files) => {
                    
                    if (!files) return;

                    files.forEach(function(path) {
                        formData.append("paths[]", path);
                    });

                    formData.append("folder", $this.folder);

                    var xhr = new XMLHttpRequest();

                    xhr.open('POST', App.route('/assetsmanager/uploadfolder'), true);

                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.upload.addEventListener('progress', function(e){
                        uploadSettings.progress((e.loaded / e.total)*100, e);
                    }, false);

                    xhr.addEventListener('loadstart', function(e){ uploadSettings.loadstart(e); }, false);
                    
                    xhr.onreadystatechange = function() {

                        if (xhr.readyState==4){

                            var response = xhr.responseText;

                            try {
                                response = App.$.parseJSON(response);
                            } catch(e) {
                                response = false;
                            }

                            uploadSettings.allcomplete(response, xhr);
                        }
                    };
                    
                    xhr.send(formData);
                });

                UIkit.init(this.root);
            });
        });

        toggleListMode() {
            this.listmode = this.listmode=='list' ? 'grid':'list';
            App.session.set('app.assets.listmode', this.listmode);
        }

        listAssets(page) {

            this.page    = page || 1;
            this.loading = true;

            this.filter = null;

            if (this.refs.filtertitle.value || this.refs.filtertype.value) {
                this.filter = {};
            }

            if (this.refs.filtertitle.value) {

                this.filter.$or = [];
                this.filter.$or.push({title: {'$regex':this.refs.filtertitle.value, '$options': 'i'}});
                this.filter.$or.push({description: {'$regex':this.refs.filtertitle.value, '$options': 'i'}});
                this.filter.$or.push({tags: this.refs.filtertitle.value});
            }

            if (this.refs.filtertype.value) {
                this.filter[this.refs.filtertype.value] = true;
            }

            var options = {
                filter : this.filter || null,
                limit  : this.limit,
                skip   : (this.page-1) * this.limit,
                sort   : {created:-1},
                folder : this.folder
            };

            if (this.folder) {

                if (!options.filter) {
                    options.filter = {};
                }

                options.filter.folder = this.folder;
            }

            App.request('/assetsmanager/listAssets', options).then(function(response) {

                $this.folders  = Array.isArray(response.folders) ? response.folders:[];
                $this.assets   = Array.isArray(response.assets) ? response.assets:[];
                $this.count    = response.total || 0;
                $this.pages    = Math.ceil($this.count/$this.limit);
                $this.loading  = false;
                $this.selected = [];
                $this.update();
            }, function(res) {
                App.ui.notify(res && (res.message || res.error) ? (res.message || res.error) : 'Loading failed.', 'danger');
            });

        }

        updateFilter() {

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
        }
        
        saveAsset() {
          
          App.$('cp-asset', this.root)[0]._tag.updateAsset(function(asset) {
              $this.asset = _.extend($this.asset, asset);
          });
        }

        cancelAssetEdit() {
            this.asset = null;
            this.mode  = 'list';
            this.update();
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

        addFolder() {

            App.ui.prompt(App.i18n.get('Folder Name:'), '', function(name) {

                if (!name.trim()) return;

                App.request('/assetsmanager/addFolder', {name:name, parent:$this.folder}).then(function(folder) {

                    if (!folder._id) return;

                    $this.folders.push(folder);
                    $this.update();
                });
            });
        }

        renameFolder(e) {

            var folder = e.item.folder;

            App.ui.prompt(App.i18n.get('Folder Name:'), folder.name, function(name) {

                if (!name.trim()) return;

                App.request('/assetsmanager/renameFolder', {name:name, folder:folder}).then(function() {

                    folder.name = name;
                    $this.update();
                });
            });
        }

        removeFolder(e) {

            var folder = e.item.folder, idx = e.item.idx;

            App.ui.confirm(App.i18n.get('Are you sure?'), function() {

                App.request('/assetsmanager/removeFolder', {folder:folder}).then(function() {

                    $this.folders.splice(idx, 1);
                    $this.update();
                });
            });

        }

        changeDir(e) {

            var folder = e.item ? e.item.folder : {_id:''};

            if (this.folder == folder._id) {
                return;
            }

            this.folder = folder._id;

            if (this.folder) {

                var skip = false;

                this.foldersPath = this.foldersPath.filter(function(f) {
                    if (f._id == folder._id) skip = true;
                    return !skip;
                });

                this.foldersPath.push(folder);
            } else {
                this.foldersPath = [];
            }

            $this.listAssets(1);
        }

    </script>

</cp-assets>

<cp-asset>

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
  
  <div class="uk-text-center uk-margin-large-top" show="{ !asset }">
      <cp-preloader class="uk-container-center"></cp-preloader>
  </div>

  <div class="uk-form" if="{asset}">

      <ul class="uk-tab uk-flex-center uk-margin" show="{ App.Utils.count(panels) }">
          <li class="{!panel && 'uk-active'}"><a onclick="{selectPanel}">Main</a></li>
          <li class="uk-text-capitalize {p.name == panel && 'uk-active'}" each="{p in panels}"><a onclick="{parent.selectPanel}">{p.name}</a></li>
      </ul>

      <div class="uk-grid" show="{!panel}">
          <div class="uk-width-2-3">

              <div class="uk-panel uk-panel-box uk-panel-card uk-panel-space">
                  <div class="uk-form-row">
                      <label class="uk-text-small uk-text-bold">{ App.i18n.get('Title') }</label>
                      <input class="uk-width-1-1" type="text" bind="asset.title" required>
                  </div>

                  <div class="uk-form-row">
                      <label class="uk-text-small uk-text-bold">{ App.i18n.get('Description') }</label>
                      <textarea class="uk-width-1-1" bind="asset.description" bind-event="input"></textarea>
                  </div>

                  <div class="uk-margin-large-top uk-text-center" if="{asset}">
                      <span class="uk-h1" if="{asset.mime.match(/^image\//) == null }"><i class="uk-icon-{ getIconCls(asset.path) }"></i></span>
                      <div class="uk-display-inline-block uk-position-relative asset-fp-image" if="{asset.mime.match(/^image\//) }">
                          <cp-thumbnail src="{ASSETS_URL+asset.path}" width="800"></cp-thumbnail>
                          <div class="cp-assets-fp" title="Focal Point" data-uk-tooltip></div>
                      </div>
                      <div class="uk-margin-top uk-text-truncate uk-text-small uk-text-muted">
                          <a href="{ASSETS_URL+asset.path}" target="_blank"  title="{ App.i18n.get('Direct link to asset') }" data-uk-tooltip><i class="uk-icon-button uk-icon-button-outline uk-text-primary uk-icon-link"></i></a>
                      </div>
                  </div>
              </div>
          </div>
          <div class="uk-width-1-3">

              <div class="uk-margin">
                  <label class="uk-text-small uk-text-bold">{ App.i18n.get('Id') }</label>
                  <div class="uk-margin-small-top uk-text-muted">{ asset._id }</div>
              </div>
              <div class="uk-margin">
                  <label class="uk-text-small uk-text-bold">{ App.i18n.get('Folder') }</label>
                  <div class="uk-margin-small-top"><cp-assets-folderselect asset="{asset}"></cp-assets-folderselect></div>
              </div>
              <div class="uk-margin">
                  <label class="uk-text-small uk-text-bold">{ App.i18n.get('Type') }</label>
                  <div class="uk-margin-small-top uk-text-muted"><span class="uk-badge uk-badge-outline">{ asset.mime }</span></div>
              </div>
              <div class="uk-margin" if="{asset.colors && Array.isArray(asset.colors) && asset.colors.length}">
                  <label class="uk-text-small uk-text-bold">{ App.i18n.get('Colors') }</label>
                  <div class="uk-margin-small-top uk-text-muted">
                      <span class="uk-icon-circle uk-text-large uk-margin-small-right" each="{color in asset.colors}" riot-style="color: #{String(color).replace('#', '')}"></span>
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

      <div data-is="{'assetspanel-'+p.name}" asset="{asset}" each="{p in panels}" show="{panel == p.name}"></div>

  </div>
  
  <script>
    
    this.mixin(RiotBindMixin);
    
    var $this = this, $root = App.$(this.root);
    
    this.panel  = null;
    this.panels = [];

    for (var tag in riot.tags) {

        if (tag.indexOf('assetspanel-')==0) {

            var f = tag.replace('assetspanel-', '');

            this.panels.push({name:f, value:f});
        }
    }
    
    this.on('mount', function() {
      
      App.request('/assetsmanager/asset/'+opts.asset, {}).then(function(asset) {
          
          $this.asset = asset;
          $this.update();
          
          if ($this.asset.mime.match(/^image\//)) {
              
              setTimeout(function() {
                  
                  $this.placeFocalPoint($this.asset.fp);
                  
                  $root.on('click', '.asset-fp-image canvas', function(e) {

                      var x = e.offsetX, y = e.offsetY,
                          px = (x / this.offsetWidth),
                          py = (y / this.offsetHeight);

                      $this.asset.fp = {x: px, y: py};
                      $this.placeFocalPoint($this.asset.fp);
                  });
                  
              }, 500)
          }
          
      }, function(res) {
          App.ui.notify(res && (res.message || res.error) ? (res.message || res.error) : 'Loading failed.', 'danger');
      });
      
    });
    
    selectPanel(e) {
        this.panel = e.item ? e.item.p.name : null;
    }

    updateAsset(clb) {
  
        if (!this.asset) {
          return;
        }
        
        return App.request('/assetsmanager/updateAsset', {asset:$this.asset}).then(function(asset) {

            if (Array.isArray(asset)) {
                asset = asset[0];
            }

            App.$.extend($this.asset, asset);
            App.ui.notify("Asset updated", "success");
            $this.update();
            
            if (clb) clb(asset);
            
            return asset;
        });
    }
    
    placeFocalPoint(point) {
        
        point = point || {x:0.5, y:0.5};

        var canvas = $root.find('.asset-fp-image canvas')[0];
        var x = (point.x * 100)+'%';
        var y = (point.y * 100)+'%';

        $root.find('.cp-assets-fp').css({
            left: x,
            top: y,
            visibility: 'visible'
        });
    }
  
  </script>

</cp-asset>

<cp-assets-folderselect>

    <div data-uk-dropdown="mode:'click'">

        <a class="uk-text-muted">
            <i class="uk-icon-folder-o"></i> { asset.folder && folders[asset.folder] ? folders[asset.folder].name : App.i18n.get('Select folder') }
        </a>

        <div class="uk-dropdown uk-dropdown-close uk-width-1-1">

            <strong>{ App.i18n.get('Folders') }</strong>

            <div class="uk-margin-small-top { App.Utils.count(folders) > 10 && 'uk-scrollable-box' }">
                <ul class="uk-list">
                    <li each="{folder, idx in folders}" riot-style="margin-left: {(folder._lvl * 10)}px">
                        <a class="uk-link-muted" onclick="{selectFolder}"><i class="uk-icon-folder-o"></i> {folder.name}</a>
                    </li>
                </ul>
            </div>
        </div>

    </div>

    <script>

        var $this = this;

        this.asset   = opts.asset;
        this.folders = {};
        this.loading = true;

        this.on('mount', function() {

            this.load();
        });

        selectFolder(e) {
            this.asset.folder = e.item.folder._id;
        }

        load() {

            this.loading = true;

            App.request('/assetsmanager/_folders', {}).then(function(folders) {

                $this.loading = false;
                $this.folders = {};

                folders.forEach( function(f) {
                    $this.folders[f._id] = f
                });

                $this.update();
            });
        }

    </script>

</cp-assets-folderselect>
