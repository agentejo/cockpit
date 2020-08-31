riot.tag2('codemirror', '', '', '', function(opts) {

        var $this = this,
            root  = this.root,
            $root = App.$(root),
            $textarea, editor, options;

        this.on('mount', function(){

            codemirror().then(function() {

                $textarea = App.$('<textarea style="visibility:hidden;"></textarea>');

                $root.append($textarea);

                editor = CodeMirror.fromTextArea($textarea[0], App.$.extend({
                    lineNumbers: true,
                    indentUnit: 2,
                    indentWithTabs: false,
                    smartIndent: false,
                    tabSize: 2,
                    readOnly: opts.readonly || false,
                    autoCloseBrackets: true,
                    extraKeys: {
                        Tab: function(cm) {
                            var spaces = Array(cm.getOption("indentUnit") + 1).join(" ");
                            cm.replaceSelection(spaces);
                        }
                    }
                }, opts || {}));

                root.editor = editor;
                this.editor = editor;

                if (opts.syntax) {
                    var mode = CodeMirror.findModeByName(opts.syntax) || {mode:'text'};
                    editor.setOption("mode", mode.mode);
                }

                if (opts.theme) {
                    App.assets.require(['/assets/lib/codemirror/theme/'+opts.theme+'.css'], function() {
                        editor.setOption("theme", opts.theme);
                    });
                }

                if (opts.height) {

                    if (opts.height == "auto") {
                        editor.setOption("height", "auto");
                        editor.setOption("viewportMargin", Infinity);
                    } else {
                        editor.setSize(opts.width || '100%', opts.height);
                    }
                }

                editor.on('focus', function() {
                    editor.refresh();
                });

                this.trigger('ready');

            }.bind(this));

        });

});

riot.tag2('cp-account', '<span class="uk-icon-spinner uk-icon-spin" show="{!account}"></span> <span class="uk-flex-inline uk-flex-middle" if="{account}"> <cp-gravatar email="{account.email}" alt="{account.name || \'Unknown\'}" size="{opts.size || 25}" title="{opts.label === false && (account.name || \'Unknown\')}" data-uk-tooltip></cp-gravatar> <span class="uk-margin-small-left" if="{opts.label !== false}">{account.name || \'Unknown\'}</span> </span>', '', '', function(opts) {

        var $this = this;

        this.account = null;

        this.on('mount', function() {
            this.update();
        })

        this.on('update', function(){

            if (this.account && this.account._id == opts.account) {
                return;
            }

            Cockpit.account(opts.account).then(function(account) {

                if (!account) {
                    account = {
                        _id: opts.account
                    };
                }

                $this.account = account;
                $this.update();
            });
        })

});

riot.tag2('cp-assets', '<div ref="list" show="{mode==\'list\'}"> <div ref="uploadprogress" class="uk-margin uk-hidden"> <div class="uk-progress"> <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div> </div> </div> <div class="uk-form" if="{mode==\'list\'}"> <div class="uk-grid"> <div> <div class="uk-grid uk-grid-small uk-flex-middle"> <div> <span class="uk-button-group uk-margin-right"> <button class="uk-button uk-button-large {listmode==\'list\' && \'uk-button-primary\'}" type="button" onclick="{toggleListMode}" aria-label="{App.i18n.get(\'Switch to list-mode\')}"><i class="uk-icon-list"></i></button> <button class="uk-button uk-button-large {listmode==\'grid\' && \'uk-button-primary\'}" type="button" onclick="{toggleListMode}" aria-label="{App.i18n.get(\'Switch to tile-mode\')}"><i class="uk-icon-th"></i></button> </span> </div> <div show="{!opts.typefilter}"> <div class="uk-form-select"> <span class="uk-button uk-button-large {getRefValue(\'filtertype\') && \'uk-button-primary\'} uk-text-capitalize"><i class="uk-icon-eye uk-margin-small-right"></i> {getRefValue(\'filtertype\') || App.i18n.get(\'All\')}</span> <select ref="filtertype" onchange="{updateFilter}" aria-label="{App.i18n.get(\'Mime Type\')}"> <option value="">All</option> <option value="image">Image</option> <option value="video">Video</option> <option value="audio">Audio</option> <option value="document">Document</option> <option value="archive">Archive</option> <option value="code">Code</option> </select> </div> </div> <div class="uk-flex-item-1"> <div class="uk-form-icon uk-display-block uk-width-1-1"> <i class="uk-icon-search"></i> <input class="uk-width-1-1 uk-form-large" type="text" aria-label="{App.i18n.get(\'Search assets\')}" ref="filtertitle" onchange="{updateFilter}"> </div> </div> </div> </div> <div class="uk-flex-item-1"></div> <div class="uk-flex uk-flex-middle"> <button class="uk-button uk-button-large uk-button-danger" type="button" onclick="{removeSelected}" show="{selected.length}"> {App.i18n.get(\'Delete\')} <span class="uk-badge uk-badge-contrast uk-margin-small-left">{selected.length}</span> </button> <button class="uk-button uk-button-large uk-button-link" onclick="{addFolder}">{App.i18n.get(\'Add folder\')}</button> <div data-uk-dropdown="mode:\'click\'"> <a class="uk-button uk-button-large uk-button-primary"><i class="uk-icon-upload"></i></a> <div class="uk-dropdown uk-margin-top uk-text-left"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header uk-flex uk-flex-middle"> <span class="uk-flex-item-1">{App.i18n.get(\'Upload\')}</span> <span class="uk-badge uk-badge-outline uk-text-warning"> max. {App.Utils.formatSize(App.$data.maxUploadSize)}</span> </li> <li> <a class="uk-form-file"> <i class="uk-icon-file-o uk-icon-justify"></i> {App.i18n.get(\'File\')} <input class="js-upload-select" aria-label="{App.i18n.get(\'Select file\')}" type="file" multiple="true"> </a> <a class="uk-form-file"> <i class="uk-icon-folder-o uk-icon-justify"></i> {App.i18n.get(\'Folder\')} <input class="js-upload-folder" type="file" title="" multiple multiple directory webkitdirectory allowdirs> </a> </li> </ul> </div> </div> </div> </div> <div class="uk-margin"> <ul class="uk-breadcrumb"> <li onclick="{changeDir}"><a title="{App.i18n.get(\'Change dir to root\')}"><i class="uk-icon-home"></i></a></li> <li each="{folder, idx in foldersPath}"><a onclick="{parent.changeDir}" title="Change dir to {folder.name}">{folder.name}</a></li> </ul> </div> <div class="uk-text-center uk-margin-large-top" show="{loading}"> <cp-preloader class="uk-container-center"></cp-preloader> </div> <div class="{modal && \'uk-overflow-container\'}" style="padding: 1px 1px;"> <div class="uk-margin" if="{!loading && folders.length}"> <strong class="uk-text-small uk-text-muted"><i class="uk-icon-folder-o uk-margin-small-right"></i> {folders.length} {App.i18n.get(\'Folders\')}</strong> <div class="uk-grid uk-grid-small uk-grid-match uk-grid-width-medium-1-4 uk-grid-width-xlarge-1-5"> <div class="uk-grid-margin" each="{folder,idx in folders}"> <div class="uk-panel uk-panel-box uk-panel-card"> <div class="uk-flex"> <div class="uk-margin-small-right"><i class="uk-icon-folder-o"></i></div> <div class="uk-flex-item-1 uk-text-bold uk-text-truncate"><a class="uk-link-muted" onclick="{parent.changeDir}">{folder.name}</a></div> <div> <span data-uk-dropdown="mode:\'click\', pos:\'bottom-right\'"> <a><i class="uk-icon-ellipsis-v js-no-item-select"></i></a> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header uk-text-truncate">{folder.name}</li> <li><a class="uk-dropdown-close" onclick="{parent.renameFolder}">{App.i18n.get(\'Rename\')}</a></li> <li class="uk-nav-divider"></li> <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{parent.removeFolder}">{App.i18n.get(\'Delete\')}</a></li> </ul> </div> </span> </div> </div> </div> </div> </div> </div> <div class="uk-margin-large-top uk-panel-space uk-text-center" show="{!loading && !assets.length}"> <span class="uk-text-muted uk-h2">{App.i18n.get(\'No Assets found\')}</span> </div> <div class="uk-margin" if="{!loading && assets.length}"> <strong class="uk-text-small uk-text-muted"><i class="uk-icon-file-o uk-margin-small-right"></i> {count} {App.i18n.get(\'Assets\')}</strong> <div class="uk-grid uk-grid-match uk-grid-small uk-grid-width-medium-1-5" if="{listmode==\'grid\'}"> <div class="uk-grid-margin" each="{asset,idx in assets}" onclick="{select}"> <div class="uk-panel uk-panel-box uk-panel-card uk-padding-remove {selected.length && selected.indexOf(asset) != -1 ? \'uk-selected\':\'\'}"> <div class="uk-overlay uk-display-block uk-position-relative {asset.mime.match(/^image\\//) && \'uk-bg-transparent-pattern\'}"> <canvas class="uk-responsive-width" width="200" height="150"></canvas> <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle"> <div class="uk-width-1-1 uk-text-center"> <span if="{asset.mime.match(/^image\\//) == null}"><i class="uk-h1 uk-text-muted uk-icon-{parent.getIconCls(asset.path)}"></i></span> <cp-thumbnail riot-src="{asset._id}" height="150" if="{asset.mime.match(/^image\\//)}" title="{asset.width && [asset.width, asset.height].join(\'x\')}"></cp-thumbnail> </div> </div> </div> <div class="uk-panel-body uk-text-small"> <div class="uk-text-truncate"> <a onclick="{parent.edit}">{asset.title}</a> </div> <div class="uk-text-muted uk-margin-small-top uk-flex"> <strong>{asset.mime}</strong> <span class="uk-flex-item-1 uk-margin-small-left uk-margin-small-right">{App.Utils.formatSize(asset.size)}</span> <a href="{ASSETS_URL+asset.path}" if="{asset.mime.match(/^image\\//)}" data-uk-lightbox="type:\'image\'" title="{asset.width && [asset.width, asset.height].join(\'x\')}" aria-label="{asset.width && [asset.width, asset.height].join(\'x\')}"> <i class="uk-icon-search"></i> </a> </div> </div> </div> </div> </div> <table class="uk-table uk-table-tabbed" if="{listmode==\'list\'}"> <thead> <tr> <td width="30"></td> <th class="uk-text-small uk-noselect">{App.i18n.get(\'Title\')}</th> <th class="uk-text-small uk-noselect" width="20%">{App.i18n.get(\'Type\')}</th> <th class="uk-text-small uk-noselect" width="10%">{App.i18n.get(\'Size\')}</th> <th class="uk-text-small uk-noselect" width="10%">{App.i18n.get(\'Updated\')}</th> <th class="uk-text-small uk-noselect" width="30"></th> </tr> </thead> <tbody> <tr class="{selected.length && selected.indexOf(asset) != -1 ? \'uk-selected\':\'\'}" each="{asset,idx in assets}" onclick="{select}"> <td class="uk-text-center"> <span if="{asset.mime.match(/^image\\//) == null}"><i class="uk-text-muted uk-icon-{parent.getIconCls(asset.path)}"></i></span> <a href="{ASSETS_URL+asset.path}" if="{asset.mime.match(/^image\\//)}" data-uk-lightbox="type:\'image\'" title="{asset.width && [asset.width, asset.height].join(\'x\')}" aria-label="{asset.width && [asset.width, asset.height].join(\'x\')}"> <cp-thumbnail riot-src="{ASSETS_URL+asset.path}" width="20" height="20"></cp-thumbnail> </a> </td> <td> <a if="{!parent.modal}" onclick="{parent.edit}">{asset.title}</a> <span if="{parent.modal}">{asset.title}</span> </td> <td class="uk-text-small">{asset.mime}</td> <td class="uk-text-small">{App.Utils.formatSize(asset.size)}</td> <td class="uk-text-small">{App.Utils.dateformat( new Date( 1000 * asset.modified ))}</td> <td> <span class="uk-float-right" data-uk-dropdown="mode:\'click\'"> <a class="uk-icon-bars"></a> <div class="uk-dropdown uk-dropdown-flip"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header">{App.i18n.get(\'Actions\')}</li> <li><a class="uk-dropdown-close" onclick="{parent.edit}">{App.i18n.get(\'Edit\')}</a></li> <li><a class="uk-dropdown-close" onclick="{parent.remove}">{App.i18n.get(\'Delete\')}</a></li> </ul> </div> </span> </td> </tr> </tbody> </table> </div> </div> <div class="uk-margin uk-flex uk-flex-middle uk-noselect" if="{!loading && pages > 1}"> <ul class="uk-breadcrumb uk-margin-remove"> <li class="uk-active"><span>{page}</span></li> <li data-uk-dropdown="mode:\'click\'"> <a><i class="uk-icon-bars"></i> {pages}</a> <div class="uk-dropdown"> <strong class="uk-text-small"> {App.i18n.get(\'Pages\')}</strong> <div class="uk-margin-small-top {pages > 5 && \'uk-scrollable-box\'}"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-text-small" each="{k,v in new Array(pages)}"><a class="uk-dropdown-close" onclick="{parent.loadPage}" data-page="{(v + 1)}"> {App.i18n.get(\'Page\')} {v + 1}</a></li> </ul> </div> </div> </li> </ul> <div class="uk-button-group uk-margin-small-left"> <a class="uk-button uk-button-link uk-button-small" onclick="{loadPage}" data-page="{(page - 1)}" if="{page-1 > 0}"> {App.i18n.get(\'Previous\')}</a> <a class="uk-button uk-button-link uk-button-small" onclick="{loadPage}" data-page="{(page + 1)}" if="{page+1 <= pages}"> {App.i18n.get(\'Next\')}</a> </div> </div> </div> </div> <div class="uk-form" if="{asset && mode==\'edit\'}"> <h3 class="uk-text-bold">{App.i18n.get(\'Edit Asset\')}</h3> <cp-asset asset="{asset._id}"></cp-asset> <div class="uk-margin-top" show="{modal}"> <button type="button" class="uk-button uk-button-large uk-button-primary" onclick="{saveAsset}">{App.i18n.get(\'Save\')}</button> <a class="uk-button uk-button-large uk-button-link" onclick="{cancelAssetEdit}">{App.i18n.get(\'Cancel\')}</a> </div> <cp-actionbar show="{!modal}"> <div class="uk-container uk-container-center"> <button type="button" class="uk-button uk-button-large uk-button-primary" onclick="{saveAsset}">{App.i18n.get(\'Save\')}</button> <a class="uk-button uk-button-large uk-button-link" onclick="{cancelAssetEdit}">{App.i18n.get(\'Cancel\')}</a> </div> </cp-actionbar> </div>', 'cp-assets .uk-breadcrumb,[data-is="cp-assets"] .uk-breadcrumb{ margin-bottom: 0; }', '', function(opts) {

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

        this.count    = 0;
        this.page     = 1;
        this.pages    = 1;
        this.limit    = opts.limit || 15;

        this.on('mount', function() {

            if (opts.typefilter) {
                this.refs.filtertype.value = opts.typefilter;
            }

            this.listAssets(1);

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

        this.toggleListMode = function() {
            this.listmode = this.listmode=='list' ? 'grid':'list';
            App.session.set('app.assets.listmode', this.listmode);
        }.bind(this)

        this.listAssets = function(page) {

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

        }.bind(this)

        this.updateFilter = function() {

            this.listAssets(1);
        }.bind(this)

        this.loadPage = function(e) {

            var page = parseInt(e.target.getAttribute('data-page'), 10);

            this.listAssets(page || 1);
        }.bind(this)

        this.remove = function(e) {
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

        }.bind(this)

        this.removeSelected = function() {

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

        }.bind(this)

        this.edit = function(e) {

            this.asset = e.item.asset;
            this.mode  = 'edit';
        }.bind(this)

        this.saveAsset = function() {

          App.$('cp-asset', this.root)[0]._tag.updateAsset(function(asset) {
              $this.asset = _.extend($this.asset, asset);
          });
        }.bind(this)

        this.cancelAssetEdit = function() {
            this.asset = null;
            this.mode  = 'list';
            this.update();
        }.bind(this)

        this.select = function(e) {

            if (App.$(e.target).is('a') || App.$(e.target).parents('a').length) return;

            var idx = this.selected.indexOf(e.item.asset);

            if (idx == -1) {
                this.selected.push(e.item.asset);
            } else {
                this.selected.splice(idx, 1);
            }

            App.$(this.root).trigger('selectionchange', [this.selected]);
        }.bind(this)

        this.getIconCls = function(path) {

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
        }.bind(this)

        this.getRefValue = function(name) {
            return this.refs[name] && this.refs[name].value;
        }.bind(this)

        this.addFolder = function() {

            App.ui.prompt(App.i18n.get('Folder Name:'), '', function(name) {

                if (!name.trim()) return;

                App.request('/assetsmanager/addFolder', {name:name, parent:$this.folder}).then(function(folder) {

                    if (!folder._id) return;

                    $this.folders.push(folder);
                    $this.update();
                });
            });
        }.bind(this)

        this.renameFolder = function(e) {

            var folder = e.item.folder;

            App.ui.prompt(App.i18n.get('Folder Name:'), folder.name, function(name) {

                if (!name.trim()) return;

                App.request('/assetsmanager/renameFolder', {name:name, folder:folder}).then(function() {

                    folder.name = name;
                    $this.update();
                });
            });
        }.bind(this)

        this.removeFolder = function(e) {

            var folder = e.item.folder, idx = e.item.idx;

            App.ui.confirm(App.i18n.get('Are you sure?'), function() {

                App.request('/assetsmanager/removeFolder', {folder:folder}).then(function() {

                    $this.folders.splice(idx, 1);
                    $this.update();
                });
            });

        }.bind(this)

        this.changeDir = function(e) {

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
        }.bind(this)

});

riot.tag2('cp-asset', '<div class="uk-text-center uk-margin-large-top" show="{!asset}"> <cp-preloader class="uk-container-center"></cp-preloader> </div> <div class="uk-form" if="{asset}"> <ul class="uk-tab uk-flex-center uk-margin" show="{App.Utils.count(panels)}"> <li class="{!panel && \'uk-active\'}"><a onclick="{selectPanel}">Main</a></li> <li class="uk-text-capitalize {p.name == panel && \'uk-active\'}" each="{p in panels}"><a onclick="{parent.selectPanel}">{p.name}</a></li> </ul> <div class="uk-grid" show="{!panel}"> <div class="uk-width-2-3"> <div class="uk-panel uk-panel-box uk-panel-card uk-panel-space"> <div class="uk-form-row"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Title\')}</label> <input class="uk-width-1-1" type="text" bind="asset.title" required> </div> <div class="uk-form-row"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Description\')}</label> <textarea class="uk-width-1-1" bind="asset.description" bind-event="input"></textarea> </div> <div class="uk-margin-large-top uk-text-center" if="{asset}"> <span class="uk-h1" if="{asset.mime.match(/^image\\//) == null}"><i class="uk-icon-{getIconCls(asset.path)}"></i></span> <div class="uk-display-inline-block uk-position-relative asset-fp-image" if="{asset.mime.match(/^image\\//)}"> <cp-thumbnail riot-src="{ASSETS_URL+asset.path}" width="800"></cp-thumbnail> <div class="cp-assets-fp" title="Focal Point" data-uk-tooltip></div> </div> <div class="uk-margin-top uk-text-truncate uk-text-small uk-text-muted"> <a href="{ASSETS_URL+asset.path}" target="_blank" title="{App.i18n.get(\'Direct link to asset\')}" data-uk-tooltip><i class="uk-icon-button uk-icon-button-outline uk-text-primary uk-icon-link"></i></a> </div> </div> </div> </div> <div class="uk-width-1-3"> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Id\')}</label> <div class="uk-margin-small-top uk-text-muted">{asset._id}</div> </div> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Folder\')}</label> <div class="uk-margin-small-top"><cp-assets-folderselect asset="{asset}"></cp-assets-folderselect></div> </div> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Type\')}</label> <div class="uk-margin-small-top uk-text-muted"><span class="uk-badge uk-badge-outline">{asset.mime}</span></div> </div> <div class="uk-margin" if="{asset.colors && Array.isArray(asset.colors) && asset.colors.length}"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Colors\')}</label> <div class="uk-margin-small-top uk-text-muted"> <span class="uk-icon-circle uk-text-large uk-margin-small-right" each="{color in asset.colors}" riot-style="color: #{String(color).replace(\'#\', \'\')}"></span> </div> </div> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Size\')}</label> <div class="uk-margin-small-top uk-text-muted">{App.Utils.formatSize(asset.size)}</div> </div> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Modified\')}</label> <div class="uk-margin-small-top uk-text-primary"><span class="uk-badge uk-badge-outline">{App.Utils.dateformat( new Date( 1000 * asset.modified ))}</span></div> </div> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Tags\')}</label> <div class="uk-margin-small-top"> <field-tags bind="asset.tags"></field-tags> </div> </div> <div class="uk-margin" if="{asset._by}"> <label class="uk-text-small">{App.i18n.get(\'Last update by\')}</label> <div class="uk-margin-small-top"> <cp-account account="{asset._by}"></cp-account> </div> </div> </div> </div> <div data-is="{\'assetspanel-\'+p.name}" asset="{asset}" each="{p in panels}" show="{panel == p.name}"></div> </div>', 'cp-asset .cp-assets-fp,[data-is="cp-asset"] .cp-assets-fp{ position: absolute; width: 10px; height: 10px; border-radius: 50%; background: red; box-shadow: 0 0 10px rgba(0,0,0,.1); border: 2px #fff solid; top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%); visibility: hidden; }', '', function(opts) {

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

    this.selectPanel = function(e) {
        this.panel = e.item ? e.item.p.name : null;
    }.bind(this)

    this.updateAsset = function(clb) {

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
    }.bind(this)

    this.placeFocalPoint = function(point) {

        point = point || {x:0.5, y:0.5};

        var canvas = $root.find('.asset-fp-image canvas')[0];
        var x = (point.x * 100)+'%';
        var y = (point.y * 100)+'%';

        $root.find('.cp-assets-fp').css({
            left: x,
            top: y,
            visibility: 'visible'
        });
    }.bind(this)

});

riot.tag2('cp-assets-folderselect', '<div data-uk-dropdown="mode:\'click\'"> <a class="uk-text-muted"> <i class="uk-icon-folder-o"></i> {asset.folder && folders[asset.folder] ? folders[asset.folder].name : App.i18n.get(\'Select folder\')} </a> <div class="uk-dropdown uk-dropdown-close uk-width-1-1"> <strong>{App.i18n.get(\'Folders\')}</strong> <div class="uk-margin-small-top {App.Utils.count(folders) > 10 && \'uk-scrollable-box\'}"> <ul class="uk-list"> <li each="{folder, idx in folders}" riot-style="margin-left: {(folder._lvl * 10)}px"> <a class="uk-link-muted" onclick="{selectFolder}"><i class="uk-icon-folder-o"></i> {folder.name}</a> </li> </ul> </div> </div> </div>', '', '', function(opts) {

        var $this = this;

        this.asset   = opts.asset;
        this.folders = {};
        this.loading = true;

        this.on('mount', function() {

            this.load();
        });

        this.selectFolder = function(e) {
            this.asset.folder = e.item.folder._id;
        }.bind(this)

        this.load = function() {

            this.loading = true;

            App.request('/assetsmanager/_folders', {}).then(function(folders) {

                $this.loading = false;
                $this.folders = {};

                folders.forEach( function(f) {
                    $this.folders[f._id] = f
                });

                $this.update();
            });
        }.bind(this)

});

riot.tag2('cp-field', '<div ref="field" data-is="{\'field-\'+opts.type}" bind="{opts.bind}"></div>', '', '', function(opts) {

        this.on('mount', function() {

            var o = opts.opts || {};

            if (this.root.$value == undefined && o.default !== undefined) {
                this.$setValue(o.default);
            }

            if (this.root.$value == undefined) {
                this.$setValue(null);
            }

            if (o.disabled) {
                this.root.classList.add('uk-disabled');
            }

            this.parent.update();
        });

        this.on('update', function() {

            this.refs.field.opts.bind = opts.bind;

            if (opts.required) this.refs.field.opts.required = opts.required;

            if (opts.opts) {
                App.$.extend(this.refs.field.opts, opts.opts);
            }

            this.refs.field.update();
        });

});

riot.tag2('cp-preloader', '<div> <div></div> <div></div> <div></div> <div></div> </div>', 'cp-preloader { display: block; position: relative; width: 40px; height: 40px; } cp-preloader[size="large"] { width: 80px; height: 80px; } cp-preloader[size="small"] { width: 20px; height: 20px; } cp-preloader > div { position: absolute; width: 100%; height: 100%; animation: preloader-rotate-elements 8000ms infinite linear; } cp-preloader div div { border-radius: 50%; transform: scale(0.1); opacity: 0.1; } cp-preloader div div:nth-child(1) { position: absolute; top: 0; left: 0; width: 50%; height: 50%; background: #03A9F4; animation: preloader-pulse-elements 1000ms infinite ease alternate; animation-delay: 0; } cp-preloader div div:nth-child(2) { position: absolute; top: 0; left: 50%; width: 50%; height: 50%; background: #F44336; animation: preloader-pulse-elements 1000ms infinite ease alternate; animation-delay: 250ms; } cp-preloader div div:nth-child(3) { position: absolute; top: 50%; left: 0; width: 50%; height: 50%; background: #8BC34A; animation: preloader-pulse-elements 1000ms infinite ease alternate; animation-delay: 500ms; } cp-preloader div div:nth-child(4) { position: absolute; top: 50%; left: 50%; width: 50%; height: 50%; background: #FFC107; animation: preloader-pulse-elements 1000ms infinite ease alternate; animation-delay: 750ms; } @keyframes preloader-rotate-elements { from { transform: rotate(-180deg); } to { transform: rotate(180deg); } } @keyframes preloader-pulse-elements { from { top: -50%; left: -50%; transform: scale(1.0); opacity: 0; } to { transform: scale(0.2); opacity: 0.8; } }', '', function(opts) {
});

riot.tag2('cp-preloader-fullscreen', '<div class="uk-text-center"> <cp-preloader></cp-preloader> <div class="uk-margin-top uk-text-large uk-text-bold" if="{opts.message}"> {opts.message} </div> </div>', 'cp-preloader-fullscreen { position: fixed; display: flex; top: 0; bottom: 0; left: 0; right: 0; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.5); z-index: 1000000000000000; } cp-preloader-fullscreen cp-preloader,[data-is="cp-preloader-fullscreen"] cp-preloader{ display: inline-block; }', '', function(opts) {
});

riot.tag2('cp-inspectobject', '<div class="uk-offcanvas" ref="offcanvas"> <div class="uk-offcanvas-bar uk-offcanvas-bar-flip uk-width-3-4 uk-flex uk-flex-column"> <div class="uk-flex uk-flex-middle header"> <span class="uk-badge">{opts.title || \'JSON\'}</span> <a class="uk-margin-left" onclick="{copyJSON}"><i class="uk-icon-clone"></i></a> <div class="uk-flex-item-1 uk-text-right"> <a class="uk-offcanvas-close uk-link-muted uk-icon-close"></a> </div> </div> <pre class="uk-text-small uk-flex-item-1" ref="code"></pre> </div> </div>', 'cp-inspectobject .header,[data-is="cp-inspectobject"] .header{ padding: 20px; } cp-inspectobject pre,[data-is="cp-inspectobject"] pre{ background: #1C1D21; color: #eee; border-radius: 0; padding: 15px; max-width: 100%; margin: 0; overflow: auto; } cp-inspectobject .string,[data-is="cp-inspectobject"] .string{ color: #4FB4D7; } cp-inspectobject .number,[data-is="cp-inspectobject"] .number{ color: #fff; } cp-inspectobject .boolean,[data-is="cp-inspectobject"] .boolean{ color: #E7CE56;} cp-inspectobject .null,[data-is="cp-inspectobject"] .null{color: #808080;} cp-inspectobject .key,[data-is="cp-inspectobject"] .key{color: #888;}', '', function(opts) {

        this.data = null;

        this.on('mount', function() {

        });

        this.show = function(data) {
            this.data = null;
            this.refs.code.innerHTML = '';

            if (data) {
                this.data = data;
                this.refs.code.innerHTML = this.syntaxHighlight(data);
            } else {
                this.refs.code.innerHTML = 'n/a';
            }

            UIkit.offcanvas.show(this.refs.offcanvas);

            setTimeout(this.update, 100);
        }

        this.syntaxHighlight = function(json) {

            if (typeof json != 'string') {
                json = JSON.stringify(json, undefined, 2);
            }

            var cls;

            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {

                cls = 'number';

                if (/^"/.test(match)) {
                    cls = /:$/.test(match) ? 'key' : 'string';
                } else if (/true|false/.test(match)) {
                    cls = 'boolean';
                } else if (/null/.test(match)) {
                    cls = 'null';
                }

                return '<span class="'+cls+'">'+match+'</span>';
            });
        }

        this.copyJSON = function() {

            App.Utils.copyText(this.refs.code.innerText, function() {
                App.ui.notify("Copied!", "success");
            });
        }

});

riot.tag2('cp-diff', '<div class="uk-overflow-container"> <div><pre ref="canvas" style="background:none;margin:0;"></pre></div> </div>', 'cp-diff pre,[data-is="cp-diff"] pre{ background:none; margin:0; width:100%; overflow: auto; word-wrap: normal; white-space: pre; } cp-diff del,[data-is="cp-diff"] del{ text-decoration: none; background: #A52A2A; color: #fff; } cp-diff ins,[data-is="cp-diff"] ins{ text-decoration: none; background: #008000; color: #fff; }', '', function(opts) {

        var $this = this;

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function() {
            this.refs.canvas.innerHTML = '';
            this.diff(opts.oldtxt, opts.newtxt)
        });

        this.diff = function(oldtxt, newtxt) {

            App.assets.require(['/assets/lib/diff.js'], function() {

                if (typeof(oldtxt) !== 'string') oldtxt = JSON.stringify(oldtxt, null, 2);
                if (typeof(newtxt) !== 'string') newtxt = JSON.stringify(newtxt, null, 2);

                var diff = JsDiff.diffLines(oldtxt || '', newtxt || '');
                var html = '';

                for (var i=0; i < diff.length; i++) {

                    if (diff[i].added && diff[i + 1] && diff[i + 1].removed) {
                        var swap = diff[i];
                        diff[i] = diff[i + 1];
                        diff[i + 1] = swap;
                    }

                    if (diff[i].removed) {
                        html += '<del>'+diff[i].value+'</del>';
                    } else if (diff[i].added) {
                        html += '<ins>'+diff[i].value+'</ins>';
                    } else {
                        html += diff[i].value;
                    }
                }

                this.refs.canvas.textContent = '';
                this.refs.canvas.innerHTML = html;

            }.bind(this));
        }.bind(this)

});

riot.tag2('cp-fieldsmanager', '<div ref="fieldscontainer" class="uk-sortable uk-grid uk-grid-small uk-grid-gutter uk-form"> <div class="uk-width-{field.width}" data-idx="{idx}" each="{field,idx in fields}"> <div class="uk-panel uk-panel-box uk-panel-card"> <div class="uk-grid uk-grid-small"> <div class="uk-flex-item-1 uk-flex"> <input class="uk-flex-item-1 uk-form-small uk-form-blank" type="text" bind="fields[{idx}].name" placeholder="name" pattern="[a-zA-Z0-9_]+" required> </div> <div class="uk-width-1-4"> <div class="uk-form-select" data-uk-form-select> <div class="uk-form-icon"> <i class="uk-icon-arrows-h"></i> <input class="uk-width-1-1 uk-form-small uk-form-blank" riot-value="{field.width}"> </div> <select bind="fields[{idx}].width"> <option value="1-1">1-1</option> <option value="1-2">1-2</option> <option value="1-3">1-3</option> <option value="2-3">2-3</option> <option value="1-4">1-4</option> <option value="3-4">3-4</option> </select> </div> </div> <div class="uk-text-right"> <ul class="uk-subnav"> <li if="{parent.opts.listoption}"> <a class="uk-text-{field.lst ? \'success\':\'muted\'}" onclick="{parent.togglelist}" title="{App.i18n.get(\'Show field on list view\')}"> <i class="uk-icon-list"></i> </a> </li> <li> <a onclick="{parent.fieldSettings}"><i class="uk-icon-cog uk-text-primary"></i></a> </li> <li> <a class="uk-text-danger" onclick="{parent.removefield}"> <i class="uk-icon-trash"></i> </a> </li> </ul> </div> </div> </div> </div> </div> <div class="uk-modal uk-sortable-nodrag" ref="modalField"> <div class="uk-modal-dialog uk-modal-dialog-large" if="{field}"> <div class="uk-form-row uk-text-large uk-text-bold"> {field.name || \'Field\'} </div> <div class="uk-tab uk-flex uk-flex-center uk-margin" data-uk-tab> <li class="uk-active"><a>{App.i18n.get(\'General\')}</a></li> <li><a>{App.i18n.get(\'Access\')}</a></li> </div> <div class="uk-margin-top ref-tab"> <div> <div class="uk-form-row"> <label class="uk-text-muted uk-text-small">{App.i18n.get(\'Field Type\')}:</label> <div class="uk-form-select uk-width-1-1 uk-margin-small-top"> <a class="uk-text-capitalize">{field.type}</a> <select class="uk-width-1-1 uk-text-capitalize" bind="field.type"> <option each="{type,typeidx in fieldtypes}" riot-value="{type.value}">{type.name}</option> </select> </div> </div> <div class="uk-form-row"> <label class="uk-text-muted uk-text-small">{App.i18n.get(\'Field Label\')}:</label> <input class="uk-width-1-1 uk-margin-small-top" type="text" bind="field.label" placeholder="{App.i18n.get(\'Label\')}"> </div> <div class="uk-form-row"> <label class="uk-text-muted uk-text-small">{App.i18n.get(\'Field Info\')}:</label> <input class="uk-width-1-1 uk-margin-small-top" type="text" bind="field.info" placeholder="{App.i18n.get(\'Info\')}"> </div> <div class="uk-form-row"> <label class="uk-text-muted uk-text-small">{App.i18n.get(\'Field Group\')}:</label> <input class="uk-width-1-1 uk-margin-small-top" type="text" bind="field.group" placeholder="{App.i18n.get(\'Group name\')}"> </div> <div class="uk-form-row"> <label class="uk-text-small uk-text-bold uk-margin-small-bottom">{App.i18n.get(\'Options\')} <span class="uk-text-muted">JSON</span></label> <field-object cls="uk-width-1-1" bind="field.options" rows="6" allowtabs="2"></field-object> </div> <div class="uk-form-row"> <field-boolean bind="field.required" label="{App.i18n.get(\'Required\')}"></field-boolean> </div> <div class="uk-form-row" if="{opts.localize !== false}"> <field-boolean bind="field.localize" label="{App.i18n.get(\'Localize\')}"></field-boolean> </div> </div> <div class="uk-hidden"> <field-access-list class="uk-margin-large uk-margin-large-top uk-display-block" bind="field.acl"></field-access-list> </div> </div> <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{App.i18n.get(\'Close\')}</button></div> </div> </div> <div class="uk-margin-top" show="{fields.length}"> <a class="uk-button uk-button-outline uk-text-primary" onclick="{addfield}"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add field\')}</a> </div> <div class="uk-width-medium-1-3 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle" if="{!fields.length && !reorder}"> <div class="uk-animation-fade"> <p class="uk-text-xlarge"> <img riot-src="{App.base(\'/assets/app/media/icons/form-editor.svg\')}" width="100" height="100"> </p> <hr> {App.i18n.get(\'No fields added yet\')}. <span data-uk-dropdown="pos:\'bottom-center\'"> <a onclick="{addfield}">{App.i18n.get(\'Add field\')}.</a> <div class="uk-dropdown uk-dropdown-scrollable uk-text-left" if="{opts.templates && opts.templates.length}"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header">{App.i18n.get(\'Choose from template\')}</li> <li each="{template in opts.templates}"> <a onclick="{parent.fromTemplate.bind(parent, template)}"><i class="uk-icon-sliders uk-margin-small-right"></i> {template.label || template.name}</a> </li> </ul> </div> <span> </div> </div>', '', '', function(opts) {

        riot.util.bind(this);

        var $this = this;

        this.fields  = [];
        this.field = null;
        this.reorder = false;

        this.fieldtypes = [];

        for (var tag in riot.tags) {

            if (tag.indexOf('field-')==0) {

                f = tag.replace('field-', '');

                this.fieldtypes.push({name:f, value:f});
            }
        }

        this.fieldtypes = this.fieldtypes.sort(function(fieldA, fieldB) {

            return fieldA.name.localeCompare(fieldB.name);

        });

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this.fields !== value) {

                this.fields = value;

                this.fields.forEach(function(field) {
                    if (Array.isArray(field.options)) {
                        field.options = {};
                    }
                });

                this.update();
            }

        }.bind(this);

        this.on('bindingupdated', function(){
            $this.$setValue(this.fields);
        });

        this.one('mount', function(){

            UIkit.sortable(this.refs.fieldscontainer, {

                dragCustomClass:'uk-form'

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                if (App.$(e.target).is(':input')) {
                    return;
                }

                ele = App.$(ele);

                var fields = $this.fields,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                fields.splice(cidx, 0, fields.splice(oidx, 1)[0]);

                App.$($this.refs.fieldscontainer).css('height', App.$($this.refs.fieldscontainer).height());

                $this.fields = [];
                $this.reorder = true;
                $this.update();

                setTimeout(function() {
                    $this.reorder = false;
                    $this.fields = fields;
                    $this.update();
                    $this.$setValue(fields);

                    setTimeout(function(){
                        $this.refs.fieldscontainer.style.height = '';
                    }, 30)
                }, 0);

            });

            App.$(this.root).on('click', '.uk-modal [data-uk-tab] li', function(e) {
                var item = App.$(this),
                    idx = item.index();

                item.closest('.uk-tab')
                    .next('.ref-tab')
                    .children().addClass('uk-hidden').eq(idx).removeClass('uk-hidden')
            });
        });

        this.addfield = function() {

            this.fields.push({
                'name'    : '',
                'label'   : '',
                'type'    : 'text',
                'default' : '',
                'info'    : '',
                'group'   : '',
                'localize': false,
                'options' : {},
                'width'   : '1-1',
                'lst'     : true,
                'acl'     : []
            });

            $this.$setValue(this.fields);
        }.bind(this)

        this.removefield = function(e) {
            this.fields.splice(e.item.idx, 1);
            $this.$setValue(this.fields);
        }.bind(this)

        this.fieldSettings = function(e) {

            this.field = e.item.field;

            UIkit.modal(this.refs.modalField, {bgclose:false}).show()
        }.bind(this)

        this.togglelist = function(e) {
            e.item.field.lst = !e.item.field.lst;
        }.bind(this)

        this.fromTemplate = function(template) {

            if (template && Array.isArray(template.fields) && template.fields.length) {
                this.fields = template.fields;
                $this.$setValue(this.fields);
            }
        }.bind(this)

});

riot.tag2('cp-finder', '<div show="{App.Utils.count(data)}"> <div class="uk-clearfix" data-uk-margin> <div class="uk-float-left"> <span class="uk-button-group uk-margin-right"> <button class="uk-button uk-button-large {listmode==\'list\' && \'uk-button-primary\'}" type="button" onclick="{toggleListMode}"><i class="uk-icon-list"></i></button> <button class="uk-button uk-button-large {listmode==\'grid\' && \'uk-button-primary\'}" type="button" onclick="{toggleListMode}"><i class="uk-icon-th"></i></button> </span> <div class="uk-form uk-form-icon uk-display-inline-block"> <i class="uk-icon-filter"></i> <input ref="filter" type="text" class="uk-form-large" onkeyup="{updatefilter}"> </div> <span class="uk-margin-left" data-uk-dropdown="mode:\'click\'"> <a class="uk-text-{sortBy == \'name\' ? \'muted\':\'primary\'}" title="{App.i18n.get(\'Sort files\')}" data-uk-tooltip="pos:\'right\'"><i class="uk-icon-sort"></i> {App.Utils.ucfirst(sortBy)}</a> <div class="uk-dropdown uk-margin-top uk-text-left"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header">{App.i18n.get(\'Sort by\')}</li> <li><a class="uk-dropdown-close" onclick="{doSortBy.bind(this, \'name\')}">{App.i18n.get(\'Name\')}</a></li> <li><a class="uk-dropdown-close" onclick="{doSortBy.bind(this, \'filesize\')}">{App.i18n.get(\'Filesize\')}</a></li> <li><a class="uk-dropdown-close" onclick="{doSortBy.bind(this, \'mime\')}">{App.i18n.get(\'Type\')}</a></li> <li><a class="uk-dropdown-close" onclick="{doSortBy.bind(this, \'modified\')}">{App.i18n.get(\'Modified\')}</a></li> </ul> </div> </span> </div> <div class="uk-float-right uk-flex"> <span class="uk-margin-right uk-position-relative" data-uk-dropdown="mode:\'click\', pos:\'bottom-right\'"> <a class="uk-button uk-button-link uk-text-primary uk-button-large">{App.i18n.get(\'Create\')}</a> <div class="uk-dropdown uk-margin-top uk-text-left"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header">{App.i18n.get(\'Create\')}</li> <li><a onclick="{createfolder}"><i class="uk-icon-folder-o uk-icon-justify"></i> {App.i18n.get(\'Folder\')}</a></li> <li><a onclick="{createfile}"><i class="uk-icon-file-o uk-icon-justify"></i> {App.i18n.get(\'File\')}</a></li> </ul> </div> </span> <div data-uk-dropdown="mode:\'click\'"> <a class="uk-button uk-button-large uk-button-primary"><i class="uk-icon-upload"></i></a> <div class="uk-dropdown uk-margin-top uk-text-left"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header uk-flex uk-flex-middle"> <span class="uk-flex-item-1">{App.i18n.get(\'Upload\')}</span> <span class="uk-badge uk-badge-outline uk-text-warning"> max. {App.Utils.formatSize(App.$data.maxUploadSize)}</span> </li> <li> <a class="uk-form-file"> <i class="uk-icon-file-o uk-icon-justify"></i> {App.i18n.get(\'File\')} <input class="js-upload-select" type="file" multiple="true" title=""> </a> <a class="uk-form-file"> <i class="uk-icon-folder-o uk-icon-justify"></i> {App.i18n.get(\'Folder\')} <input class="js-upload-folder" type="file" title="" multiple multiple directory webkitdirectory allowdirs> </a> </li> </ul> </div> </div> <button class="uk-button uk-button-large" onclick="{refresh}"> <i class="uk-icon-refresh"></i> </button> <span class="uk-margin-left" if="{selected.count}" data-uk-dropdown="mode:\'click\', pos:\'bottom-right\'"> <span class="uk-button uk-button-large"><strong>{App.i18n.get(\'Batch\')}:</strong> {selected.count} &nbsp;<i class="uk-icon-caret-down"></i></span> <div class="uk-dropdown uk-margin-top uk-text-left"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header">{App.i18n.get(\'Batch action\')}</li> <li class="uk-nav-item-danger"><a onclick="{removeSelected}">{App.i18n.get(\'Delete\')}</a></li> </ul> </div> </span> </div> </div> <div class="uk-grid uk-grid-divider uk-margin-large-top" data-uk-grid-margin> <div class="uk-width-medium-1-4"> <div class="uk-panel"> <ul class="uk-nav uk-nav-side"> <li class="uk-nav-header">{App.i18n.get(\'Display\')}</li> <li class="{!typefilter ? \'uk-active\':\'\'}"><a data-type="" onclick="{settypefilter}"><i class="uk-icon-circle-o uk-icon-justify"></i> {App.i18n.get(\'All\')}</a></li> <li class="{typefilter==\'image\' ? \'uk-active\':\'\'}"><a data-type="image" onclick="{settypefilter}"><i class="uk-icon-image uk-icon-justify"></i> {App.i18n.get(\'Images\')}</a></li> <li class="{typefilter==\'video\' ? \'uk-active\':\'\'}"><a data-type="video" onclick="{settypefilter}"><i class="uk-icon-video-camera uk-icon-justify"></i> {App.i18n.get(\'Video\')}</a></li> <li class="{typefilter==\'audio\' ? \'uk-active\':\'\'}"><a data-type="audio" onclick="{settypefilter}"><i class="uk-icon-volume-up uk-icon-justify"></i> {App.i18n.get(\'Audio\')}</a></li> <li class="{typefilter==\'document\' ? \'uk-active\':\'\'}"><a data-type="document" onclick="{settypefilter}"><i class="uk-icon-paper-plane uk-icon-justify"></i> {App.i18n.get(\'Documents\')}</a></li> <li class="{typefilter==\'archive\' ? \'uk-active\':\'\'}"><a data-type="archive" onclick="{settypefilter}"><i class="uk-icon-archive uk-icon-justify"></i> {App.i18n.get(\'Archives\')}</a></li> </ul> </div> </div> <div class="uk-width-medium-3-4"> <div class="uk-panel"> <ul class="uk-breadcrumb"> <li onclick="{changedir}"><a title="{App.i18n.get(\'Change dir to root\')}"><i class="uk-icon-home"></i></a></li> <li each="{folder, idx in breadcrumbs}"><a onclick="{parent.changedir}" title="Change dir to {folder.name}">{folder.name}</a></li> </ul> </div> <div ref="uploadprogress" class="uk-margin uk-hidden"> <div class="uk-progress"> <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div> </div> </div> <div class="uk-alert uk-text-center uk-margin" if="{data && (this.typefilter || this.refs.filter.value) && (data.folders.length || data.files.length)}"> {App.i18n.get(\'Filter is active\')} </div> <div class="uk-alert uk-text-center uk-margin" if="{data && (!data.folders.length && !data.files.length)}"> {App.i18n.get(\'This is an empty folder\')} </div> <div class="{modal && \'uk-overflow-container\'}"> <div class="uk-margin-top" if="{data && data.folders.length}"> <strong class="uk-text-small uk-text-muted" if="{!(this.refs.filter.value)}"><i class="uk-icon-folder-o uk-margin-small-right"></i> {data.folders.length} {App.i18n.get(\'Folders\')}</strong> <ul class="uk-grid uk-grid-small uk-grid-match uk-grid-width-1-2 uk-grid-width-medium-1-4 uk-grid-width-xlarge-1-5"> <li class="uk-grid-margin" each="{folder, idx in data.folders}" onclick="{select}" if="{infilter(folder)}"> <div class="uk-panel uk-panel-box uk-panel-card finder-folder {folder.selected ? \'uk-selected\':\'\'}"> <div class="uk-flex"> <div class="uk-margin-small-right"> <i class="uk-icon-folder-o uk-text-muted js-no-item-select"></i> </div> <div class="uk-flex-item-1 uk-margin-small-right uk-text-truncate"> <a class="uk-link-muted uk-noselect" onclick="{parent.changedir}"><strong>{folder.name}</strong></a> </div> <div> <span data-uk-dropdown="mode:\'click\', pos:\'bottom-right\'"> <a><i class="uk-icon-ellipsis-v js-no-item-select"></i></a> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header uk-text-truncate">{folder.name}</li> <li><a class="uk-dropdown-close" onclick="{parent.download}">{App.i18n.get(\'Download\')}</a></li> <li><a class="uk-dropdown-close" onclick="{parent.rename}">{App.i18n.get(\'Rename\')}</a></li> <li class="uk-nav-divider"></li> <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{parent.remove}">{App.i18n.get(\'Delete\')}</a></li> </ul> </div> </span> </div> </div> </div> </li> </ul> </div> <div class="uk-margin-top" if="{data && data.files.length}"> <strong class="uk-text-small uk-text-muted" if="{!(this.typefilter || this.refs.filter.value)}"><i class="uk-icon-file-o uk-margin-small-right"></i> {data.files.length} {App.i18n.get(\'Files\')}</strong> <ul class="uk-grid uk-grid-small uk-grid-match uk-grid-width-1-2 uk-grid-width-medium-1-4 uk-grid-width-xlarge-1-5" if="{listmode==\'grid\'}"> <li class="uk-grid-margin" each="{file, idx in data.files}" onclick="{select}" if="{infilter(file)}"> <div class="uk-panel uk-panel-box finder-file {file.selected ? \'uk-selected\':\'\'}"> <div class="uk-panel-teaser uk-cover-background uk-position-relative"> <div if="{parent.getIconCls(file) != \'image\'}"> <canvas class="uk-responsive-width uk-display-block" width="400" height="300"></canvas> <div class="uk-position-center"><i class="uk-text-large uk-text-muted uk-icon-{parent.getIconCls(file)}"></i></div> </div> <cp-thumbnail riot-src="{file.url}" width="400" height="300" if="{parent.getIconCls(file) == \'image\'}"></cp-thumbnail> </div> <div class="uk-flex"> <a class="uk-link-muted uk-flex-item-1 js-no-item-select uk-text-truncate uk-margin-small-right" onclick="{parent.open}">{file.name}</a> <span class="uk-margin-small-right" data-uk-dropdown="mode:\'click\', pos:\'bottom-right\'"> <a><i class="uk-icon-ellipsis-v js-no-item-select"></i></a> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header uk-text-truncate">{file.name}</li> <li><a class="uk-link-muted js-no-item-select" onclick="{parent.open}">{App.i18n.get(\'Open\')}</a></li> <li><a onclick="{parent.rename}">{App.i18n.get(\'Rename\')}</a></li> <li><a onclick="{parent.download}">{App.i18n.get(\'Download\')}</a></li> <li if="{file.ext == \'zip\'}"><a onclick="{parent.unzip}">{App.i18n.get(\'Unzip\')}</a></li> <li class="uk-nav-divider"></li> <li class="uk-nav-item-danger"><a onclick="{parent.remove}">{App.i18n.get(\'Delete\')}</a></li> </ul> </div> </span> </div> <div class="uk-margin-small-top uk-text-small uk-text-muted"> {file.size} </div> </div> </li> </ul> <table class="uk-table uk-table-tabbed uk-table-striped" if="{listmode==\'list\' && data.files.length}"> <thead> <tr> <td width="30"></td> <th>{App.i18n.get(\'Name\')}</th> <th width="10%">{App.i18n.get(\'Size\')}</th> <th width="15%">{App.i18n.get(\'Updated\')}</th> <th width="30"></th> </tr> </thead> <tbody> <tr class="{file.selected ? \'uk-selected\':\'\'}" each="{file, idx in data.files}" onclick="{select}" if="{infilter(file)}"> <td class="uk-text-center"> <span if="{parent.getIconCls(file) != \'image\'}"><i class="uk-text-muted uk-icon-{parent.getIconCls(file)}"></i></span> <cp-thumbnail riot-src="{file.url}" width="400" height="300" if="{parent.getIconCls(file) == \'image\'}"></cp-thumbnail> </td> <td><a class="js-no-item-select" onclick="{parent.open}">{file.name}</a></td> <td class="uk-text-small">{file.size}</td> <td class="uk-text-small">{App.Utils.dateformat( new Date( 1000 * file.modified ))}</td> <td> <span class="uk-float-right" data-uk-dropdown="mode:\'click\'"> <a class="uk-icon-ellipsis-v"></a> <div class="uk-dropdown uk-dropdown-flip"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header">{App.i18n.get(\'Actions\')}</li> <li><a class="uk-link-muted js-no-item-select" onclick="{parent.open}">{App.i18n.get(\'Open\')}</a></li> <li><a onclick="{parent.rename}">{App.i18n.get(\'Rename\')}</a></li> <li><a onclick="{parent.download}">{App.i18n.get(\'Download\')}</a></li> <li if="{file.ext == \'zip\'}"><a onclick="{parent.unzip}">{App.i18n.get(\'Unzip\')}</a></li> <li class="uk-nav-divider"></li> <li class="uk-nav-item-danger"><a onclick="{parent.remove}">{App.i18n.get(\'Delete\')}</a></li> </ul> </div> </span> </td> </tr> </tbody> </table> </div> </div> </div> </div> <div ref="editor" class="uk-offcanvas"> <div class="uk-offcanvas-bar uk-width-3-4"> <picoedit height="auto"></picoedit> </div> </div> </div>', 'cp-finder .uk-offcanvas[ref=editor] .CodeMirror,[data-is="cp-finder"] .uk-offcanvas[ref=editor] .CodeMirror{ height: auto; } cp-finder .uk-offcanvas[ref=editor] .picoedit-toolbar,[data-is="cp-finder"] .uk-offcanvas[ref=editor] .picoedit-toolbar{ padding-left: 15px; padding-right: 15px; } cp-finder .uk-modal .uk-panel-box.finder-folder,[data-is="cp-finder"] .uk-modal .uk-panel-box.finder-folder,cp-finder .uk-modal .uk-panel-box.finder-file,[data-is="cp-finder"] .uk-modal .uk-panel-box.finder-file{ border: 1px rgba(0,0,0,0.1) solid; } cp-finder .picoedit-toolbar,[data-is="cp-finder"] .picoedit-toolbar{ -webkit-position: sticky; position: sticky; top: 0; padding-top: 10px !important; padding-bottom: 10px !important; background: #fff; z-index: 10; }', '', function(opts) {

        var $this = this,
            typefilters = {
                'image'    : /\.(jpg|jpeg|png|gif|svg)$/i,
                'video'    : /\.(mp4|mov|ogv|webv|flv|avi)$/i,
                'audio'    : /\.(mp3|weba|ogg|wav|flac)$/i,
                'archive'  : /\.(zip|rar|7zip|gz)$/i,
                'document' : /\.(htm|html|pdf|md)$/i,
                'text'     : /\.(csv|txt|htm|html|php|css|less|js|json|md|markdown|yaml|xml|htaccess)$/i
            };

        opts.root = opts.root || '/';

        this.currentpath = opts.path || App.session.get('app.finder.path', opts.root);

        this.data        = null;
        this.breadcrumbs = [];
        this.selected    = {count:0, paths:{}};
        this.bookmarks   = {"folders":[], "files":[]};

        this.typefilter = opts.typefilter || '';
        this.namefilter = '';

        this.mode       = 'table';
        this.dirlist    = false;
        this.selected   = {};

        this.sortBy     = 'name';
        this.listmode   = App.session.get('app.finder.listmode', 'list');

        this.modal = opts.modal;

        App.$(this.refs.editor).on('click', function(e){

            if (e.target.classList.contains('uk-offcanvas-bar')) {
                $this.tags.picoedit.codemirror.editor.focus();
            }
        });

        this.on('mount', function(){

            this.loadPath()

            App.assets.require([
                '/assets/lib/uikit/js/components/upload.js',
                '/assets/lib/uppie.js'
            ], function() {

                var uploadSettings = {

                        action: App.route('/media/api'),
                        params: {"cmd":"upload"},
                        type: 'json',
                        before: function(options) {
                            options.params.path = $this.currentpath;
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

                            if (!response) {
                                App.ui.notify("Something went wrong.", "danger");
                            }

                            if (response && response.uploaded && response.uploaded.length) {
                                App.ui.notify("File(s) uploaded.", "success");
                                $this.loadPath();
                            }

                        }
                },

                uploadselect = UIkit.uploadSelect(App.$('.js-upload-select', $this.root)[0], uploadSettings),
                uploaddrop   = UIkit.uploadDrop($this.root, uploadSettings);

                var uppie = new Uppie();

                uppie($this.root.querySelector('.js-upload-folder'), async (e, formData, files) => {

                    if (!files) return;

                    files.forEach(function(path) {
                        formData.append("paths[]", path);
                    });

                    formData.append("path", $this.currentpath);

                    var xhr = new XMLHttpRequest();

                    xhr.open('POST', App.route('/media/api?cmd=uploadfolder'), true);

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

        this.changedir = function(e, path) {

            if (e && e.item) {
                e.preventDefault();
                e.stopPropagation();
                path = e.item.folder.path;
            } else {
                path = opts.root;
            }

            this.loadPath(path);
        }.bind(this)

        this.open = function(e) {

            e.preventDefault();

            if (opts.previewfiles === false) {
                this.select(e, true);
                return;
            }

            var file = e.item.file,
                name = file.name.toLowerCase();

            if (name.match(typefilters.image)) {

                UIkit.lightbox.create([
                    {'source': file.url, 'type':'image'}
                ]).show();

            } else if(name.match(typefilters.video)) {

                UIkit.lightbox.create([
                    {'source': file.url, 'type':'video'}
                ]).show();

            } else if(name.match(typefilters.text) || name=='.env') {

                UIkit.offcanvas.show(this.refs.editor);
                this.tags.picoedit.open(file.path);

            } else {
                App.ui.notify("Filetype not supported");
            }
        }.bind(this)

        this.refresh = function() {
            this.loadPath().then(function(){
                App.ui.notify('Folder reloaded', 'success');
            });
        }.bind(this)

        this.select = function(e, force) {

            if (e && e.item && force || !e.target.classList.contains('js-no-item-select') && !App.$(e.target).parents('.js-no-item-select').length) {

                try {
                    window.getSelection().empty()
                } catch(err) {
                    try {
                        window.getSelection().removeAllRanges()
                    } catch(err){}
                }

                var item = e.item.folder || e.item.file, idx = e.item.idx;

                if (e.shiftKey) {

                    var prev, i, closest = idx, items = this.data[item.is_file ? 'files' : 'folders'];

                    for (i=idx;i>=0;i--) {
                        if (items[i].selected) {
                            closest = i;
                            break;
                        }
                    }

                    for (i=idx;i>=closest;i--) {
                        if (items[i].selected) break;

                        items[i].selected = true;
                        this.selected.paths[items[i].path] = items[i];
                    }

                    this.selected.count = Object.keys(this.selected.paths).length;

                    return App.$(this.root).trigger('selectionchange', [this.selected]);
                }

                if (!(e.metaKey || e.ctrlKey)) {

                    Object.keys(this.selected.paths).forEach(function(path) {
                        if (path != item.path) {
                            $this.selected.paths[path].selected = false;
                            delete $this.selected.paths[path];
                        }
                    });
                }

                item.selected = !item.selected;

                if (!item.selected && this.selected.paths[item.path]) {
                    delete this.selected.paths[item.path];
                }

                if (item.selected && !this.selected.paths[item.path]) {
                    this.selected.paths[item.path] = item;
                }

                this.selected.count = Object.keys(this.selected.paths).length;

                App.$(this.root).trigger('selectionchange', [this.selected]);
            }
        }.bind(this)

        this.rename = function(e, item) {

            e.stopPropagation();

            item = e.item.folder || e.item.file;

            App.ui.prompt("Please enter a name:", item.name, function(name){

                if (name!=item.name && name.trim()) {

                    requestapi({"cmd":"rename", "path": item.path, "name":name});
                    item.path = item.path.replace(item.name, name);
                    item.name = name;

                    $this.update();
                }

            });
        }.bind(this)

        this.download = function(e, item) {

            e.stopPropagation();

            item = e.item.file || e.item.folder;

            window.open(App.route('/media/api?cmd=download&path='+item.path));
        }.bind(this)

        this.unzip = function(e, item) {

            e.stopPropagation();

            item = e.item.file;

            requestapi({"cmd": "unzip", "path": $this.currentpath, "zip": item.path}, function(resp){

                if (resp) {

                    if (resp.success) {
                        App.ui.notify("Archive extracted!", "success");
                    } else {
                        App.ui.notify("Extracting archive failed!", "error");
                    }
                }

                $this.loadPath();

            });
        }.bind(this)

        this.remove = function(e, item, index) {

            e.stopPropagation();

            item = e.item.folder || e.item.file;

            App.ui.confirm("Are you sure?", function() {

                requestapi({"cmd":"removefiles", "paths": item.path}, function(){

                    index = $this.data[item.is_file ? "files":"folders"].indexOf(item);

                    $this.data[item.is_file ? "files":"folders"].splice(index, 1);

                    App.ui.notify("Item(s) deleted", "success");

                    $this.resetselected();

                    $this.update();
                });
            });
        }.bind(this)

        this.removeSelected = function() {

            var paths = Object.keys(this.selected.paths);

            if (paths.length) {

                App.ui.confirm("Are you sure?", function() {

                    requestapi({"cmd":"removefiles", "paths": paths}, function(){
                        $this.loadPath();
                        App.ui.notify("File(s) deleted", "success");
                    });
                });
            }
        }.bind(this)

        this.createfolder = function() {

            App.ui.prompt("Please enter a folder name:", "", function(name){

                if (name.trim()) {
                    requestapi({"cmd":"createfolder", "path": $this.currentpath, "name":name}, function(){
                        $this.loadPath();
                    });
                }
            });
        }.bind(this)

        this.createfile = function() {

            App.ui.prompt("Please enter a file name:", "", function(name){

                if (name.trim()) {
                    requestapi({"cmd":"createfile", "path": $this.currentpath, "name":name}, function(){
                        $this.loadPath();
                    });
                }
            });
        }.bind(this)

        this.loadPath = function(path, defer) {

            path  = path || $this.currentpath;
            defer = App.deferred();

            requestapi({cmd:"ls", path: path}, function(data){

                $this.currentpath = path;
                $this.breadcrumbs = [];
                $this.selected    = {};
                $this.selectAll   = false;

                if ($this.currentpath && $this.currentpath != opts.root && $this.currentpath != '.'){
                    var parts   = $this.currentpath.split('/'),
                        tmppath = [],
                        crumbs  = [];

                    for(var i=0;i<parts.length;i++){
                        if(!parts[i]) continue;
                        tmppath.push(parts[i]);
                        crumbs.push({'name':parts[i],'path':tmppath.join("/")});
                    }

                    $this.breadcrumbs = crumbs;
                }

                App.session.set('app.finder.path', path);

                defer.resolve(data);

                $this.data = data;

                $this.data.files = $this.data.files.sort(function(a,b) {
                    a = $this.sortBy == 'name' ? a[$this.sortBy].toLowerCase() : a[$this.sortBy];
                    b =  $this.sortBy == 'name' ? b[$this.sortBy].toLowerCase() : b[$this.sortBy];
                    if (a < b) return -1;
                    if (a> b) return 1;
                    return 0;
                });

                $this.resetselected();
                $this.update();

            });

            return defer;
        }.bind(this)

        this.settypefilter = function(e) {
            e.preventDefault();

            this.typefilter = e.target.dataset.type;
            this.resetselected();
        }.bind(this)

        this.updatefilter = function(e) {
            this.resetselected();
        }.bind(this)

        this.infilter = function(item) {

            var name = item.name.toLowerCase();

            if (this.typefilter && item.is_file && typefilters[this.typefilter]) {

                if (!name.match(typefilters[this.typefilter])) {
                    return false;
                }
            }

            return (!this.refs.filter.value || (name && name.indexOf(this.refs.filter.value.toLowerCase()) !== -1));
        }.bind(this)

        this.resetselected = function() {

            if (this.selected.paths) {
                Object.keys(this.selected.paths).forEach(function(path) {
                    $this.selected.paths[path].selected = false;
                });
            }

            this.selected  = {count:0, paths:{}};

            if (opts.onChangeSelect) {
                opts.onChangeSelect(this.selected);
            }
        }.bind(this)

        this.getIconCls = function(file) {

            var name = file.name.toLowerCase();

            if (name.match(typefilters.image)) {

                return 'image';

            } else if(name.match(typefilters.video)) {

                return 'video-camera';

            } else if(name.match(typefilters.text)) {

                return 'pencil';

            } else if(name.match(typefilters.archive)) {

                return 'archive';

            } else {
                return 'file-o';
            }
        }.bind(this)

        function requestapi(data, fn, type) {

            data = Object.assign({cmd:''}, data);

            App.request('/media/api', data).then(fn).catch(function() {
                App.ui.notify('Something went wrong.', 'danger');
            });
        }

        this.doSortBy = function(sortby) {
            this.sortBy = sortby;

            $this.data.files = $this.data.files.sort(function(a,b) {
                a = $this.sortBy == 'name' ? a[$this.sortBy].toLowerCase() : a[$this.sortBy];
                b =  $this.sortBy == 'name' ? b[$this.sortBy].toLowerCase() : b[$this.sortBy];
                if (a < b) return -1;
                if (a> b) return 1;
                return 0;
            });
        }.bind(this)

        this.toggleListMode = function() {
            this.listmode = this.listmode=='list' ? 'grid':'list';
            App.session.set('app.finder.listmode', this.listmode);
        }.bind(this)

});

riot.tag2('cp-gravatar', '<canvas ref="image" class="uk-responsive-width" width="{size}" height="{size}"></canvas>', '', '', function(opts) {

        var $this = this;

        this.size  = opts.size || 100;

        this.on('mount', function(){
            this.update();
        });

        this.on('update', function() {

            this.size = opts.size || 100;

            var img = new Image();

            img.onload = function() {
                $this.refs.image.getContext('2d').drawImage(img,0,0);
            };

            img.src = App.Utils.letterAvatar(opts.alt || '', this.size);
        });

});

riot.tag2('cp-revisions-info', '<span> <span class="uk-icon-spinner uk-icon-spin" if="{cnt === false || loading}"></span> <span if="{cnt !== false && !loading}">{cnt}</span> </span>', '', '', function(opts) {

        var $this = this;

        this.cnt = false;

        this.on('mount', function() {

            this.sync();

            if (opts.parent) {

                this.parent.on('update', function() {
                    $this.sync();
                });
            }
        });

        this.sync = function() {

            var rid = opts.rid || 0;

            this.loading = true;

            App.request('/cockpit/utils/revisionsCount', {id:opts.rid}, 'text').then(function(cnt){

                if (!App.Utils.isNumeric(cnt)) {
                    cnt = 'n/a';
                }

                $this.loading = false;
                $this.cnt = cnt;
                $this.update();

            }).catch(function(e){});

        }.bind(this)

});

riot.tag2('cp-search', '<div ref="autocomplete" class="uk-autocomplete uk-form uk-form-icon app-search"> <i class="uk-icon-search"></i> <input class="uk-width-1-1 uk-form-blank" type="text" aria-label="{App.i18n.get(\'Search for anything...\')}" placeholder="{App.i18n.get(\'Search for anything...\')}"> </div>', 'cp-search .uk-dropdown { min-width: 25vw; }', '', function(opts) {

        this.on('mount', function(){

            var txtSearch = App.$("input[type='text']", this.refs.autocomplete);

            UIkit.autocomplete(this.refs.autocomplete, {
                source: App.route('/cockpit/search'),
                template: '<ul class="uk-nav uk-nav-autocomplete uk-autocomplete-results">{{~items}}<li data-value="" data-url="{{$item.url}}"><a href="{{$item.url}}"><i class="uk-icon-{{ ($item.icon || "cube") }}"></i> {{$item.title}}</a></li>{{/items}}</ul>'
            });

            UIkit.$doc.on("keydown", function(e) {

                if (e.ctrlKey || e.altKey || e.metaKey) return;

                if (e.target.tagName && e.target.tagName.toLowerCase()=='body' && (e.keyCode>=65 && e.keyCode<=90)) {
                    txtSearch.focus();
                }
            });

            Mousetrap.bindGlobal(['alt+f'], function(e) {

                e.preventDefault();
                txtSearch.focus();
                return false;
            });

        });

        App.$(this.root).on("selectitem.uk.autocomplete", function(e, data) {

            if (data.url) {
                location.href = data.url;
            }
        });

});

riot.tag2('cp-thumbnail', '<div class="uk-position-relative"> <i ref="spinner" class="uk-icon-spinner uk-icon-spin uk-position-center"></i> <canvas ref="canvas" width="{this.width || \'\'}" height="{this.height || \'\'}" style="background-size:contain;background-position:50% 50%;background-repeat:no-repeat;visibility:hidden;"></canvas> </div>', '', '', function(opts) {

        var $this = this, src, cache = {};

        this.inView = false;
        this.width  = opts.width;
        this.height = opts.height;

        this.on('mount', function() {

            if (!('IntersectionObserver' in window)) {
                this.inView = true;
                this.load();
                return;
            }

            var observer = new IntersectionObserver(function(entries, observer) {

                if (!entries[0].intersectionRatio) return;

                if (opts.src || opts.riotSrc || opts['riot-src']) {
                    $this.inView = true;
                    $this.load();
                    observer.unobserve($this.refs.canvas);
                }

            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            setTimeout(function() {
                observer.observe($this.refs.canvas);
            }, 50);
        });

        this.on('update', function() {
            if (this.inView) this.load();
        })

        this.load = function() {

            var _src = opts.src || opts.riotSrc || opts['riot-src'], img, mode = opts.mode || 'bestFit';

            if (!_src || src === _src) {
                return;
            }

            this.refs.spinner.style.display = '';

            this.getUrl(_src, mode).then(function(url) {

                img = new Image();
                img.onload = function() {

                    setTimeout(function() {
                        $this.updateCanvasDim(img)
                    }, 0);
                }

                img.onerror = function() {}

                img.src = url;
            });
        };

        this.updateCanvasDim = function(img) {

            if (!App.$(this.root).closest('body').length) return;

            setTimeout(function() {

                this.width = img.width;
                this.height = img.height;

                this.refs.canvas.width = img.width;
                this.refs.canvas.height = img.height;

                App.$(this.refs.canvas).css({
                    backgroundImage: 'url('+img.src+')',
                    visibility: 'visible'
                });

                if (!$this.refs.spinner.style) {
                    return;
                }

                this.refs.spinner.style.display = 'none';

            }.bind(this), 0);

        }

        this.getUrl = function(url, mode) {

            var key = `${url}:${mode}`;

            if (!cache[key]) {

                cache[key] = new Promise(function(resolve) {

                    if (url.match(/^(http\:|https\:|\/\/)/) && !(url.includes(ASSETS_URL) || url.includes(SITE_URL))) {
                        resolve(url);
                        return;
                    }

                    if (!url.match(/\.(svg|ico)$/i)) {
                        url = App.route(`/cockpit/utils/thumb_url?src=${url}&w=${opts.width}&h=${opts.height}&m=${mode}&re=1`);
                    }

                    resolve(url);
                });
            }

            return cache[key];
        }.bind(this)

});

riot.tag2('field-access-list', '<div class="uk-clearfix {!_entries.length && \'uk-text-center\'}"> <div class="uk-margin uk-text-muted" if="{!_entries.length}"> <img class="uk-svg-adjust" riot-src="{App.base(\'/assets/app/media/icons/acl.svg\')}" width="50" data-uk-svg> <p>{App.i18n.get(\'Nothing selected\')}</p> </div> <span class="badge-label uk-margin-small-right uk-margin-small-top {(entry in App.$data.groups) ? \'\':\'uk-text-danger\'}" each="{entry,idx in _entries}"> <i class="uk-icon-users uk-margin-small-right" show="{(entry in App.$data.groups)}"></i> <span data-entry="{entry}">{parent.getEntryDisplay(entry)}</span> <a class="uk-margin-small-left" onclick="{parent.remove}"><i class="uk-icon-minus"></i></a> </span> <span class="uk-position-relative uk-margin-small-top" data-uk-dropdown="mode:\'click\', pos:\'right-bottom\'"> <a><i class="uk-icon-plus-circle uk-text-large"></i></a> <div class="uk-dropdown uk-dropdown-width-2 uk-text-left"> <div class="uk-margin"> <strong>{App.i18n.get(\'Groups\')}</strong> <div class="uk-margin-small-top"> <span class="badge-label uk-margin-small-right uk-margin-small-top" each="{admin,group in App.$data.groups}" if="{_entries.indexOf(group)<0}"> <i class="uk-icon-users uk-margin-small-right"></i> {group} <a class="uk-margin-small-left" onclick="{parent.add}"><i class="uk-icon-plus"></i></a> </span> </div> </div> <div class="uk-margin uk-form"> <strong>{App.i18n.get(\'Users\')}</strong> <div class="uk-margin-small-top"> <div class="uk-form-icon uk-form uk-text-muted uk-display-block"> <i class="uk-icon-search"></i> <input class="uk-width-1-1" type="text" ref="txtfilter" placeholder="Filter users..."> </div> </div> <div class="uk-margin-small-top"> <span class="badge-label uk-text-danger uk-margin-small-right uk-margin-small-top uk-flex-inline uk-flex-middle" each="{user in _users}" if="{_entries.indexOf(user._id)<0}"> <cp-account account="{user._id}" size="15"></cp-account> <a class="uk-margin-small-left" onclick="{parent.add}"><i class="uk-icon-plus"></i></a> </span> </div> </div> </div> </span> </div>', 'field-access-list .badge-label,[data-is="field-access-list"] .badge-label{ display: inline-block; padding: .35em .6em; font-size: .8em; border: 1px currentColor solid; border-radius: 3px; color: #4FC1E9; } field-access-list .badge-label a,[data-is="field-access-list"] .badge-label a{ color: currentColor; }', '', function(opts) {

        var $this = this, cache = {};

        this._entries = [];
        this._users = [];

        this.on('mount', function() {

            App.$(this.refs.txtfilter).on('keyup', _.debounce(function() {

                var value = $this.refs.txtfilter.value.trim();

                $this._users = [];

                if (value && value.length > 2) {

                    App.request('/accounts/find', {options: {filter: value}}).then(function(response) {
                        $this._users = response && Array.isArray(response.accounts) ? response.accounts : [];
                        $this.update();
                    });
                }

                $this.update();

            }, 500));

            App.$(this.refs.txtfilter).on('keydown', function(e) {

                if (e.keyCode == 13) {
                    return false;
                }
            });
        });

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this._entries !== value) {
                this._entries = value;
                this.update();
            }

        }.bind(this);

        this.add = function(e) {
            this._entries.push(e.item.group || e.item.user._id);
            this.$setValue(_.uniq(this._entries));
            this.refs.txtfilter.value = '';
        }.bind(this)

        this.remove = function(e) {
            this._entries.splice(e.item.idx, 1);
            this.$setValue(this._entries);
        }.bind(this)

        this.getEntryDisplay = function(entry) {

            if (entry in App.$data.groups) {
                return entry;
            }

            if (!cache[entry]) {

                cache[entry] = new Promise(function(resolve, reject){
                    App.request('/accounts/find', {options: {filter: {_id:entry}}}).then(function(response) {

                        if (response && Array.isArray(response.accounts) && response.accounts[0]) {
                            resolve(response.accounts[0].name);
                        } else {
                            reject(entry);
                        }
                    });
                });
            }

            cache[entry].then(function(txt) {
                App.$($this.root).find('[data-entry="'+entry+'"]').text(txt);
            }).catch(function() {
                App.$($this.root).find('[data-entry="'+entry+'"]').text(entry);
            });

            return '...';
        }.bind(this)

});

riot.tag2('field-account-link', '<div class="uk-text-center uk-panel uk-panel-framed" show="{!_value || (Array.isArray(_value) && !_value.length)}"> <img if="{opts.multiple}" class="uk-svg-adjust uk-text-muted" riot-src="{App.base(\'/assets/app/media/icons/accounts.svg\')}" width="50" data-uk-svg> <img if="{!opts.multiple}" class="uk-svg-adjust uk-text-muted" riot-src="{App.base(\'/assets/app/media/icons/login.svg\')}" width="50" data-uk-svg> <div> <a class="uk-button uk-button-link" onclick="{selectAccount}">{App.i18n.get(\'Select Account\')}</a> </div> </div> <div class="uk-panel uk-panel-box uk-panel-card uk-flex uk-flex-middle" if="{ready && _value && !opts.multiple}"> <div class="uk-flex-item-1 uk-margin-right"> <cp-account account="{_value}"></cp-account> </div> <div> <a onclick="{removeAccount}"><i class="uk-icon-trash-o uk-text-danger"></i></a> </div> </div> <div if="{ready && opts.multiple && Array.isArray(_value) && _value.length}"> <div class="uk-sortable" data-uk-sortable> <div class="uk-panel uk-panel-box uk-panel-card uk-flex uk-flex-middle uk-margin-small-bottom" each="{account in _value}" data-account="{account}"> <div class="uk-flex-item-1 uk-margin-right"> <cp-account account="{account}"></cp-account> </div> <div> <a onclick="{parent.removeAccount}"><i class="uk-icon-trash-o uk-text-danger"></i></a> </div> </div> </div> <p class="uk-text-center"> <a onclick="{selectAccount}" title="{App.i18n.get(\'Add Account\')}" data-uk-tooltip><i class="uk-icon-plus-circle"></i></a> </p> </div> <div ref="modalSelectAccounts" class="uk-modal"> <div class="uk-modal-dialog uk-modal-dialog-large"> <a href="" class="uk-modal-close uk-close"></a> <h3>{App.i18n.get(\'Accounts\')}</h3> <div class="uk-margin uk-flex uk-flex-middle"> <div class="uk-form-icon uk-form uk-flex-item-1 uk-text-muted"> <i class="uk-icon-search"></i> <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="{App.i18n.get(\'Filter accounts...\')}" onchange="{updatefilter}"> </div> <div show="{selected.length}"> <button type="button" class="uk-button uk-button-large uk-button-link" onclick="{linkSelected}"> <i class="uk-icon-link"></i> {selected.length} {App.i18n.get(\'Accounts\')} </button> </div> </div> <div class="uk-margin-large-bottom" if="{loading}"> <cp-preloader class="uk-container-center"></cp-preloader> </div> <div class="uk-text-xlarge uk-text-center uk-text-muted uk-margin-large" if="{!_accounts.length && filter && !loading}"> {App.i18n.get(\'No accounts found\')}. </div> <table class="uk-table uk-table-tabbed uk-table-striped" show="{!loading && _accounts.length}"> <thead> <tr> <th show="{opts.multiple}"></th> <th></th> <th class="uk-text-small">{App.i18n.get(\'Name\')}</th> <th class="uk-text-small">{App.i18n.get(\'Email\')}</th> <th class="uk-text-small">{App.i18n.get(\'Group\')}</th> <th></th> </tr> </thead> <tbody> <tr each="{account in _accounts}"> <td show="{parent.opts.multiple}"><input class="uk-checkbox" type="checkbox" onclick="{parent.toggleSelected}"></td> <td><cp-gravatar email="{account.email}" size="25" alt="{account.name || account.user}"></cp-gravatar></td> <td>{account.name}</td> <td>{account.email}</td> <td><span class="{account.group==\'admin\' && \'uk-badge\'}">{account.group}</span></td> <td width="20"><a onclick="{parent.linkAccount}"><i class="uk-icon-link"></i></a></td> </tr> </tbody> </table> </div> </div>', '', '', function(opts) {

        var $this = this, cache = {};

        this._value = null;
        this._accounts = [];
        this.selected  = [];
        this.loading   = false;
        this.ready     = false;

        this.on('mount', function() {

            this.modal = UIkit.modal(this.refs.modalSelectAccounts, {modal:false});
            this.modal.on('keydown', 'input',function(e){

                if (e.keyCode == 13) {
                    e.preventDefault();
                    e.stopPropagation();

                    $this.updatefilter(e);
                    $this.update();
                }
            });

            if (opts.multiple) {

                App.$(this.root).on('stop.uk.sortable', function(){

                    var accounts = [];

                    App.$('.uk-sortable', $this.root).children().each(function(){
                        accounts.push(this.getAttribute('data-account'));
                    });

                    $this._value = [];
                    $this.update();

                    $this._value = accounts;
                    $this.$setValue($this._value);
                });
            }

            this.ready = true;
            this.update();
        });

        this.$updateValue = function(value) {

            if (opts.multiple && !Array.isArray(value)) {
                value = [];
            }

            if (this._value !== value) {
                this._value = value;
                this.update();
            }

        }.bind(this);

        this.selectAccount = function() {

            this.selected = [];
            this.modal.find(':checked').prop('checked', false);
            this.load();
            this.modal.show();
        }

        this.load = function() {

            var value = this.refs.txtfilter.value, options = {};

            if (this.filter) {
                options.filter = this.filter;
            }

            options.limt = 10;

            this.loading = true;

            App.request('/accounts/find', {options: options}).then(function(response) {
                $this._accounts = response && Array.isArray(response.accounts) ? response.accounts : [];
                $this.loading = false;
                $this.update();
            });
        }

        this.linkAccount = function(e) {

            var account = e.item.account;

            if (opts.multiple) {

                if (!this._value || !Array.isArray(this._value)) {
                    this._value = [];
                }

                if (opts.limit && this._value.length >= opts.limit ) {

                } else {
                    this._value.push(account._id);
                    this._value = _.uniq(this._value);
                }

            } else {
                this._value = account._id;
            }

            setTimeout(function(){
                $this.modal.hide();
            }, 50);

            this.$setValue(this._value);
        }

        this.removeAccount = function(e) {

            if (opts.multiple) {
                this._value.splice(this._value.indexOf(e.item.account), 1);
            } else {
                this._value = null;
            }

            this.$setValue(this._value);
        }

        this.toggleSelected = function(e) {

            var account = e.item.account;

            if (e.target.checked) {
                this.selected.push(account._id);
            } else {

                var idx = this.selected.indexOf(account._id);

                if (idx > -1) {
                    this.selected.splice(idx, 1);
                }
            }
        }

        this.linkSelected = function() {

            if (!Array.isArray(this._value)) {
                this._value = [];
            }

            this.selected.forEach(function(account) {
                $this._value.push(account);
            })

            this._value = _.uniq(this._value);
            this.$setValue(this._value);
            this.modal.hide();
        }

        this.updatefilter = function(e) {

            var load = this.filter ? true:false;

            if (this.refs.txtfilter.value == this.filter) {
                return;
            }

            this.filter = this.refs.txtfilter.value || null;

            if (this.filter || load) {

                this._accounts = [];
                this.load();
            }

            return false;
        }

});

riot.tag2('field-asset', '<div ref="uploadprogress" class="uk-margin uk-hidden"> <div class="uk-progress"> <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div> </div> </div> <div class="uk-placeholder uk-text-center uk-text-muted" if="{!asset}"> <img class="uk-svg-adjust" riot-src="{App.base(\'/assets/app/media/icons/assets.svg\')}" width="100" data-uk-svg> <p>{App.i18n.get(\'No asset selected\')}. <a onclick="{selectAsset}">{App.i18n.get(\'Select one\')}</a></p> </div> <div class="uk-panel uk-panel-box uk-padding-remove uk-panel-card" if="{asset}"> <div class="uk-overlay uk-display-block uk-position-relative {asset.mime.match(/^image\\//) && \'uk-bg-transparent-pattern\'}"> <canvas class="uk-responsive-width" width="200" height="150"></canvas> <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle"> <div class="uk-width-1-1 uk-text-center"> <span if="{asset.mime.match(/^image\\//) == null}"><i class="uk-h1 uk-text-muted uk-icon-{getIconCls(asset.path)}"></i></span> <a href="{ASSETS_URL+asset.path}" if="{asset.mime.match(/^image\\//)}" data-uk-lightbox="type:\'image\'" title="{asset.width && [asset.width, asset.height].join(\'x\')}"> <cp-thumbnail riot-src="{asset && ASSETS_URL+asset.path}" height="160"></cp-thumbnail> </a> </div> </div> </div> <div class="uk-panel-body"> <div class="uk-margin-small-top uk-text-truncate"> <a href="{ASSETS_URL+asset.path}" target="_blank">{asset.title}</a> </div> <div class="uk-text-small uk-text-muted"> <strong>{asset.mime}</strong> {App.Utils.formatSize(asset.size)} </div> <div class="uk-margin-top"> <a class="uk-button uk-button-small uk-margin-small-right" onclick="{selectAsset}">{App.i18n.get(\'Replace\')}</a> <span class="uk-button-group"> <a class="uk-button uk-button-small" onclick="{edit}"><i class="uk-icon-pencil"></i></a> <a class="uk-button uk-button-small uk-text-danger" onclick="{reset}"><i class="uk-icon-trash-o"></i></a> </span> </div> </div> </div>', '', '', function(opts) {

        var $this = this, typefilters = {
            'image'    : /\.(jpg|jpeg|png|gif|svg)$/i,
            'video'    : /\.(mp4|mov|ogv|webv|wmv|flv|avi)$/i,
            'audio'    : /\.(mp3|weba|ogg|wav|flac)$/i,
            'archive'  : /\.(zip|rar|7zip|gz)$/i,
            'document' : /\.(txt|pdf|md)$/i,
            'code'     : /\.(htm|html|php|css|less|js|json|yaml|xml|htaccess)$/i
        };

        this.asset = opts.default || false;

        this.$updateValue = function(value, field, force) {

            if (force || (JSON.stringify(this.asset) != JSON.stringify(value))) {

                if (value && !value._id) {
                    value = false;
                }

                this.asset = value;
                this.update();
            }

        }.bind(this);

        this.on('mount', function() {

            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                UIkit.uploadDrop($this.root, {

                    action: App.route('/assetsmanager/upload'),
                    type: 'json',
                    filelimit: 1,
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
                            App.ui.notify("File(s) failed to upload.", "danger");
                        }

                        if (response && Array.isArray(response.assets) && response.assets.length) {
                            $this.$setValue(response.assets[0]);
                        }

                        if (!response) {
                            App.ui.notify("Something went wrong.", "danger");
                        }
                    }
                });
            });
        })

        this.selectAsset = function() {

            Cockpit.assets.select(function(assets){

                if (Array.isArray(assets)) {
                    $this.$setValue(assets[0]);
                }

            }, {typefilter: opts.typefilter});
        }.bind(this)

        this.reset = function() {
            $this.asset = null;
            $this.$setValue($this.asset);
        }.bind(this)

        this.edit = function() {

            var dialog = UIkit.modal.dialog([
                '<div>',
                    '<div class="uk-modal-header uk-text-large"><h3>'+App.i18n.get('Edit asset')+'</h3></div>',
                    '<cp-asset asset="'+this.asset._id+'"></cp-asset>',
                    '<div class="uk-modal-footer uk-text-right">',
                        '<button class="uk-button uk-button-primary uk-margin-right uk-button-large js-save-button">Save</button>',
                        '<a class="uk-button uk-button-large uk-button-link uk-modal-close">Close</a>',
                    '</div>',
                '</div>'
            ].join(''), {modal:false});

            dialog.dialog.addClass('uk-modal-dialog-large');

            riot.mount(dialog.element[0], '*', {});

            dialog.dialog.find('.js-save-button').on('click', function() {

                App.$('cp-asset', dialog.element)[0]._tag.updateAsset(function(asset) {
                    $this.$setValue(asset);
                });
            });

            dialog.show();
        }.bind(this)

        this.getIconCls = function(path) {

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
        }.bind(this)

});

riot.tag2('field-boolean', '<div ref="container" class="uk-display-inline-block" style="cursor:pointer;"> <div class="uk-form-switch"> <input ref="check" type="checkbox" id="{id}" onchange="{toggle}"> <label for="{id}"> <span show="{value && (opts.label !== \'false\' && opts.label !== false)}">{opts.label || \'On\'}</span> <span class="uk-text-muted" show="{!value && (opts.label !== \'false\' && opts.label !== false)}">{opts.label || \'Off\'}</span> </label> </div> </div>', '', '', function(opts) {

        this.id = 'switch'+Math.ceil(Math.random()*10000000);

        if (opts.cls) {
            App.$(this.refs.container).addClass(opts.cls);
        }

        this.value = undefined;

        this.$updateValue = function(value) {

            if (typeof(value) !== 'boolean') {
                return this.$setValue(!!value);
            }

            if (this.value !== value) {
                this.value = value;
                this.update();
            }
            this.refs.check.checked = Boolean(this.value);

        }.bind(this);

        this.toggle = function(e) {

            this.value = this.refs.check.checked;
            this.$setValue(this.value);
        }.bind(this)

});

riot.tag2('field-code', '<codemirror ref="codemirror" syntax="{opts.syntax || \'text\'}" height="{opts.height || 200}"></codemirror>', 'field-code .CodeMirror { height: auto; }', '', function(opts) {

        var $this = this, editor, idle;

        this.value  = null;
        this._field = null;

        this.$updateValue = function(value, field) {

            if (this.value != value) {

                this.value = value;

                if (editor && field != this._field) {
                    editor.setValue($this.value || '', true);
                }
            }

            this._field = field;

        }.bind(this);

        this.on('mount', function(){

            this.refs.codemirror.on('ready', function(){
                editor = $this.refs.codemirror.editor;

                editor.setValue($this.value || '');

                editor.on('change', function() {
                    $this.$setValue(editor.getValue(), true);
                });

                $this.isReady = true;
                $this.update();

                idle = setInterval(function() {

                    if (App.$($this.root).is(':visible')) {
                        if(!editor.hasFocus()) editor.refresh();
                    } else {
                        if (!App.$($this.root).closest('body').length) clearInterval(idle);
                    }
                }, 500)

            });
        });

});

riot.tag2('field-color', '<input ref="input" class="uk-width-1-1" type="text">', '', '', function(opts) {

        this.on('mount', function() { this.update(); });
        this.on('update', function() { if (opts.opts) App.$.extend(opts, opts.opts); });

        var $this = this;

        this.$updateValue = function(value, field) {

            if (value && this.refs.input.value !== value) {
                this.refs.input.value = value;
                this.update();
            }

            if (App.$.fn.spectrum) {
                App.$($this.refs.input).spectrum("set", $this.root.$value);
            }

        }.bind(this);

        this.on('mount', function(){

            App.assets.require([
                '/assets/lib/spectrum/spectrum.js',
                '/assets/lib/spectrum/spectrum.css'
            ], function(){

                $this.refs.input.value = $this.root.$value || '';

                App.$($this.refs.input).spectrum(App.$.extend({
                    preferredFormat: 'rgb',
                    allowEmpty:true,
                    showInitial: true,
                    showInput: true,
                    showButtons: false,
                    showAlpha: true,
                    showSelectionPalette: true,
                    palette: [ ],
                    change: function() {
                        $this.$setValue($this.refs.input.value);
                    }
                }, opts.spectrum));

            });
        });

});

riot.tag2('field-colortag', '<div class="uk-display-inline-block" data-uk-dropdown="pos:\'right-center\'"> <a riot-style="font-size:{size};color:{value || \'#ccc\'}"><i class="uk-icon-circle"></i></a> <div class="uk-dropdown uk-text-center"> <strong class="uk-text-small">{App.i18n.get(\'Choose\')}</strong> <div class="uk-grid uk-grid-small uk-margin-small-top uk-grid-width-1-4"> <div class="uk-grid-margin" each="{color in colors}"> <a onclick="{parent.select}" riot-style="color:{color};"><i class="uk-icon-circle"></i></a> </div> </div> <div class="uk-margin-top uk-text-small"> <a onclick="{reset}">{App.i18n.get(\'Reset\')}</a> </div> </div> </div>', '', '', function(opts) {

        var _defColors = ['#D8334A','#FFCE54','#A0D468','#48CFAD','#4FC1E9','#5D9CEC','#AC92EC','#EC87C0','#BAA286','#8E8271','#3C3B3D'];

        this.value  = '';

        this.on('mount',function(){
            this.update();
        });

        this.on('update', function(){
            this.size   = opts.size || 'inherit';
            this.colors = opts.colors || _defColors;
        });

        this.$updateValue = function(value, field) {

            if (this.value !== value) {
                this.value = value;
                this.update();
            }

        }.bind(this);

        this.select = function(e) {
            this.$setValue(e.item.color);
        }.bind(this)

        this.reset = function() {
            this.$setValue('');
        }.bind(this)

});

riot.tag2('field-date', '<input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="text" placeholder="{opts.placeholder}">', '', '', function(opts) {

        var $this = this;

        if (opts.cls) {
            App.$(this.refs.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.refs.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/datepicker.js', '/assets/lib/uikit/js/components/form-select.js'], function() {

                UIkit.datepicker(this.refs.input, opts).element.on('change', function() {
                    $this.refs.input.$setValue($this.refs.input.value);
                });

            }.bind(this));
        });

});

riot.tag2('field-file', '<div class="uk-panel uk-panel-box uk-panel-card "> <div ref="uploadprogress" class="uk-margin uk-hidden"> <div class="uk-progress"> <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div> </div> </div> <div class="uk-flex uk-flex-middle"> <input class="uk-form-blank uk-flex-item-1" type="text" ref="input" bind="{opts.bind}" placeholder="{opts.placeholder || App.i18n.get(\'No file selected...\')}"> <span class="uk-margin-small-left" data-uk-dropdown="pos:\'bottom-center\'"> <button type="button" class="uk-button" ref="picker" title="{App.i18n.get(\'Pick file\')}" onclick="{selectAsset}"><i class="uk-icon-paperclip"></i></button> <div class="uk-dropdown" if="{App.$data.acl.finder}"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header">{App.i18n.get(\'Source\')}</li> <li><a onclick="{selectAsset}">{App.i18n.get(\'Select Asset\')}</a></li> <li><a onclick="{selectFile}">{App.i18n.get(\'Select File\')}</a></li> </ul> </div> </span> </div> </div>', '', '', function(opts) {

        var $this = this, $input;

        this.on('mount', function() {

            $input = App.$(this.refs.input);

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
                App.$(this.refs.picker).addClass(opts.cls);
            }

            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                UIkit.uploadDrop($this.root, {

                    action: App.route('/assetsmanager/upload'),
                    type: 'json',
                    filelimit: 1,
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
                            App.ui.notify("File(s) failed to upload.", "danger");
                        }

                        if (response && Array.isArray(response.assets) && response.assets.length) {
                            $this.refs.input.$setValue(ASSETS_URL.replace(SITE_URL+'/', '')+response.assets[0].path);
                        }

                        if (!response) {
                            App.ui.notify("Something went wrong.", "danger");
                        }

                    }
                });
            });

        });

        this.selectFile = function() {

            App.media.select(function(selected) {
                $this.refs.input.$setValue(selected[0]);
            }, {});
        }.bind(this)

        this.selectAsset = function() {

            App.assets.select(function(assets){

                if (Array.isArray(assets) && assets[0]) {
                    $this.refs.input.$setValue(ASSETS_URL.replace(SITE_URL+'/', '')+assets[0].path);
                    $this.update();
                }
            });
        }.bind(this)

});

riot.tag2('field-gallery', '<div ref="uploadprogress" class="uk-margin uk-hidden"> <div class="uk-progress"> <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div> </div> </div> <div ref="panel"> <div ref="imagescontainer" class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-flex-center uk-grid-gutter uk-grid-width-medium-1-4" show="{images && images.length}"> <div data-idx="{idx}" each="{img,idx in images}"> <div class="uk-panel uk-panel-box uk-panel-thumbnail uk-panel-framed uk-visible-hover"> <div class="uk-bg-transparent-pattern uk-position-relative" style="min-height:120px;"> <canvas class="uk-responsive-width" width="200" height="150"></canvas> <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle"> <div class="uk-width-1-1 uk-text-center"> <cp-thumbnail riot-src="{img.path.match(/^(http\\:|https\\:|\\/\\/)/) ? img.path : (SITE_URL+\'/\'+img.path.replace(/^\\//, \'\'))}" height="120"></cp-thumbnail> </div> </div> </div> <div class="uk-invisible uk-margin-top"> <ul class="uk-grid uk-grid-small uk-flex-center uk-text-small"> <li data-uk-dropdown="pos:\'bottom-center\'"> <a class="uk-text-muted" onclick="{parent.selectAsset}" title="{App.i18n.get(\'Select image\')}" data-uk-tooltip><i class="uk-icon-image"></i></a> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header">{App.i18n.get(\'Source\')}</li> <li><a onclick="{parent.selectAsset}">{App.i18n.get(\'Select Asset\')}</a></li> <li show="{App.$data.acl.finder}"><a onclick="{parent.selectImage}">{App.i18n.get(\'Select Image\')}</a></li> </ul> </div> </li> <li><a class="uk-text-muted" onclick="{parent.showMeta}" title="{App.i18n.get(\'Edit meta data\')}" data-uk-tooltip><i class="uk-icon-cog"></i></a></li> <li><a class="uk-text-muted" href="{img.path.match(/^(http\\:|https\\:|\\/\\/)/) ? img.path : (SITE_URL+\'/\'+img.path.replace(/^\\//, \'\'))}" data-uk-lightbox="type:\'image\'" title="{App.i18n.get(\'Full size\')}" data-uk-tooltip><i class="uk-icon-eye"></i></a></li> <li><a class="uk-text-danger" onclick="{parent.remove}" title="{App.i18n.get(\'Remove image\')}" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li> </ul> </div> </div> </div> </div> <div class="uk-text-center {images && images.length ? \'uk-margin-top\':\'\'}"> <div class="uk-text-muted" if="{images && !images.length}"> <img class="uk-svg-adjust" riot-src="{App.base(\'/assets/app/media/icons/gallery.svg\')}" width="100" data-uk-svg> <p>{App.i18n.get(\'Gallery is empty\')}</p> </div> <div class="uk-display-inline-block uk-position-relative" data-uk-dropdown="pos:\'bottom-center\'"> <a class="uk-text-large" onclick="{selectAssetsImages}"> <i class="uk-icon-plus-circle" title="{App.i18n.get(\'Add images\')}" data-uk-tooltip></i> </a> <div class="uk-dropdown" if="{App.$data.acl.finder}"> <ul class="uk-nav uk-nav-dropdown uk-text-left uk-dropdown-close"> <li class="uk-nav-header">{App.i18n.get(\'Select\')}</li> <li><a onclick="{selectAssetsImages}">Asset</a></li> <li><a onclick="{selectimages}">{App.i18n.get(\'Finder\')}</a></li> </ul> </div> </div> </div> <div class="uk-modal uk-sortable-nodrag" ref="modalmeta"> <div class="uk-modal-dialog"> <div class="uk-modal-header"><h3>{App.i18n.get(\'Image Meta\')}</h3></div> <div class="uk-grid uk-grid-match uk-grid-gutter" if="{image}"> <div class="uk-grid-margin uk-width-medium-{field.width}" each="{field,name in meta}" no-reorder> <div class="uk-panel"> <label class="uk-text-small uk-text-bold"> <i class="uk-icon-pencil-square uk-margin-small-right"></i> {field.label || name} </label> <div class="uk-margin uk-text-small uk-text-muted"> {field.info || \' \'} </div> <div class="uk-margin"> <cp-field type="{field.type || \'text\'}" bind="image.meta[\'{name}\']" opts="{field.options || {}}"></cp-field> </div> </div> </div> </div> <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{App.i18n.get(\'Close\')}</button></div> </div> </div> </div>', '', '', function(opts) {

        riot.util.bind(this);

        var $this = this;

        this.images = [];
        this._field = null;
        this.meta = {
            title: {
                type: 'text',
                label: 'Title'
            }
        };

        this.on('mount', function() {

            this.meta = App.$.extend(this.meta, opts.meta || {});

            UIkit.sortable(this.refs.imagescontainer, {

                animation: false

            }).element.on('change.uk.sortable', function(e, sortable, ele) {

                ele = App.$(ele);

                var images = $this.images,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                images.splice(cidx, 0, images.splice(oidx, 1)[0]);

                App.$($this.refs.panel).css('height', App.$($this.refs.panel).height());

                $this.images = [];
                $this.update();

                setTimeout(function() {
                    $this.images = images;
                    $this.$setValue(images);
                    $this.update();

                    setTimeout(function(){
                        $this.refs.panel.style.height = '';
                        $this.update();
                    }, 30)
                }, 10);

            });

            var _uploads = [];

            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                UIkit.uploadDrop($this.root, {

                    action: App.route('/assetsmanager/upload'),
                    type: 'json',
                    allow : '*.(jpg|jpeg|gif|png)',
                    beforeAll: function() {
                        _uploads = [];
                    },
                    loadstart: function() {
                        $this.refs.uploadprogress.classList.remove('uk-hidden');
                    },
                    progress: function(percent) {

                        percent = Math.ceil(percent) + '%';

                        $this.refs.progressbar.innerHTML   = '<span>'+percent+'</span>';
                        $this.refs.progressbar.style.width = percent;
                    },

                    complete: function(response) {

                        if (response && response.failed && response.failed.length) {
                            App.ui.notify("File(s) failed to upload.", "danger");
                        }

                        if (response && Array.isArray(response.assets) && response.assets.length) {

                            response.assets.forEach(function(asset){

                                if (asset.mime.match(/^image\//)) {
                                    _uploads.push({
                                        meta:{title:'', asset: asset._id},
                                        path: ASSETS_URL.replace(SITE_URL, '')+asset.path
                                    });
                                }
                            });
                        }

                        if (!response) {
                            App.ui.notify("Something went wrong.", "danger");
                        }
                    },

                    allcomplete: function(response) {

                        $this.refs.uploadprogress.classList.add('uk-hidden');

                        if (Array.isArray(_uploads) && _uploads.length) {

                            $this.$setValue($this.images.concat(_uploads));
                        }
                    }
                });
            });

        });

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.images) !== JSON.stringify(value)) {
                this.images = value;
                this.update();
            }

        }.bind(this);

        this.$initBind = function() {
            this.root.$value = this.images;
        };

        this.on('bindingupdated', function() {
            $this.$setValue(this.images);
        });

        this.showMeta = function(e) {

            this.image = this.images[e.item.idx];

            setTimeout(function() {
                UIkit.modal($this.refs.modalmeta, {modal:false}).show().on('close.uk.modal', function(){
                    $this.image = null;
                });
            }, 50)
        }.bind(this)

        this.selectimages = function() {

            App.media.select(function(selected) {

                var images = [];

                selected.forEach(function(path){
                    images.push({meta:{title:''}, path:path});
                });

                $this.$setValue($this.images.concat(images));

            }, { typefilter:'image', pattern: '*.jpg|*.jpeg|*.png|*.gif|*.svg' });

        }.bind(this)

        this.selectAssetsImages = function() {

            App.assets.select(function(assets){

                if (Array.isArray(assets)) {

                    var images = [];

                    assets.forEach(function(asset){

                        if (asset.mime.match(/^image\//)) {
                            images.push({
                                meta:{title:'', asset: asset._id},
                                path: ASSETS_URL.replace(SITE_URL, '')+asset.path
                            });
                        }
                    });

                    $this.$setValue($this.images.concat(images));
                }

            }, {typefilter: 'image'});
        }.bind(this)

        this.selectImage = function(e) {

            var image = e.item.img;

            App.media.select(function(selected) {

                image.path = selected[0];
                $this.$setValue($this.images);
                $this.update();

            }, { typefilter:'image', pattern: '*.jpg|*.jpeg|*.png|*.gif|*.svg' });
        }.bind(this)

        this.selectAsset = function(e) {

            var image = e.item.img;

            App.assets.select(function(assets){

                if (Array.isArray(assets) && assets[0]) {

                    image.path = ASSETS_URL.replace(SITE_URL, '')+assets[0].path;
                    $this.$setValue($this.images);
                    $this.update();
                }
            });
        }.bind(this)

        this.remove = function(e) {
            this.images.splice(e.item.idx, 1);
            this.$setValue(this.images);
        }.bind(this)

});

riot.tag2('field-html', '<textarea ref="input" class="uk-visibility-hidden" hidden></textarea>', '', '', function(opts) {

        var $this = this, editor;

        this.value = '';
        this._field = null;
        this.evtSrc = false;

        this.$updateValue = function(value, field, force) {

            if (this.value != value) {

                if (typeof(value) != 'string') {
                    value = '';
                }

                this.value = value;

                if (editor && (!this.evtSrc || force)) {
                    editor.editor.setValue(value, true);
                }
            }

            this.evtSrc = false;

        }.bind(this);

        this.on('mount', function(){

            codemirror().then(function() {

                App.assets.require([
                    '/assets/lib/marked.js',
                    '/assets/lib/uikit/js/components/htmleditor.js'
                ], function() {

                    $this.refs.input.value = $this.value || '';

                    editor = UIkit.htmleditor(this.refs.input, opts);

                    editor.editor.on('change', function() {
                        $this.evtSrc = true;
                        $this.$setValue(editor.editor.getValue());
                    });

                    editor.editor.on('focus', function() {
                        editor.editor.refresh();
                    });

                    var buttons = {};

                    if (App.$data.acl.finder) {

                        buttons.cpfinder = {
                            title : 'Finder',
                            label : '<i class="uk-icon-folder-open"></i>'
                        };
                    }

                    buttons.cpasset = {
                        title : 'Asset',
                        label : '<i class="uk-icon-cloud"></i>'
                    };

                    editor.addButtons(buttons);

                    editor.on('action.cpfinder', function() {
                        App.media.select(function(selected) {

                            if (editor.getCursorMode() == 'markdown') {
                                editor['replaceSelection']('[title]('+SITE_URL+'/'+selected[0]+')');
                            } else {
                                editor['replaceSelection']('<a src="'+SITE_URL+'/'+selected[0]+'">'+selected[0]+'</a>');
                            }

                        }, { });
                    });

                    editor.on('action.cpasset', function() {

                        App.assets.select(function(assets){

                            if (Array.isArray(assets) && assets.length) {

                                var asset = assets[0], isImage = asset.mime.match(/^image\//);

                                if (editor.getCursorMode() == 'markdown') {
                                    editor['replaceSelection'](isImage ? '!['+asset.title+']('+ASSETS_URL+asset.path+')' : '['+asset.title+']('+ASSETS_URL+asset.path+')');
                                } else {
                                    editor['replaceSelection'](isImage ? '<img src="'+ASSETS_URL+asset.path+'" alt="'+asset.title+'">' : '<a href="'+ASSETS_URL+asset.path+'">'+asset.title+'</a>');
                                }
                            }
                        });
                    });

                    editor.options.toolbar = editor.options.toolbar.concat(['cpfinder', 'cpasset']);

                    App.$(document).trigger('init-html-editor', [editor]);

                }.bind($this));

            });

        });

});

riot.tag2('field-image', '<div ref="uploadprogress" class="uk-margin uk-hidden"> <div class="uk-progress"> <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div> </div> </div> <div class="uk-display-block uk-panel uk-panel-box uk-panel-card uk-padding-remove"> <div class="uk-flex uk-flex-middle uk-flex-center uk-text-muted"> <div class="uk-width-1-1 uk-text-center uk-bg-transparent-pattern" if="{image.path}"> <cp-thumbnail riot-src="{image.path.match(/^(http\\:|https\\:|\\/\\/)/) ? image.path : (SITE_URL+\'/\'+image.path.replace(/^\\//, \'\'))}" height="160"></cp-thumbnail> </div> <div class="uk-text-center uk-margin-top uk-margin-bottom" show="{!image.path}"> <img class="uk-svg-adjust uk-text-muted" riot-src="{App.base(\'/assets/app/media/icons/photo.svg\')}" width="60" height="60" data-uk-svg> <div class="uk-margin-top"> <a class="uk-button uk-button-link" onclick="{selectImage}" show="{App.$data.acl.finder}">{App.i18n.get(\'Select Image\')}</a> <a class="uk-button uk-button-link" onclick="{selectAsset}">{App.i18n.get(\'Select Asset\')}</a> <a class="uk-button uk-button-link" onclick="{editUrl}">{App.i18n.get(\'Enter Image Url\')}</a> </div> </div> </div> <div class="uk-panel-body" show="{image.path}"> <ul class="uk-grid uk-grid-small uk-flex-center "> <li data-uk-dropdown="pos:\'bottom-center\'"> <a class="uk-text-muted" onclick="{selectAsset}" title="{App.i18n.get(\'Select image\')}" data-uk-tooltip><i class="uk-icon-image"></i></a> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header">{App.i18n.get(\'Source\')}</li> <li><a onclick="{selectAsset}">{App.i18n.get(\'Select Asset\')}</a></li> <li><a onclick="{selectImage}" show="{App.$data.acl.finder}">{App.i18n.get(\'Select Image\')}</a></li> <li><a onclick="{editUrl}">{App.i18n.get(\'Enter Image Url\')}</a></li> </ul> </div> </li> <li><a class="uk-text-muted" onclick="{showMeta}" title="{App.i18n.get(\'Edit meta data\')}" data-uk-tooltip><i class="uk-icon-cog"></i></a></li> <li><a class="uk-text-danger" onclick="{remove}" title="{App.i18n.get(\'Reset\')}" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li> </ul> </div> </div> <div class="uk-modal uk-sortable-nodrag" ref="modalmeta"> <div class="uk-modal-dialog"> <div class="uk-modal-header"><h3>{App.i18n.get(\'Image Meta\')}</h3></div> <div class="uk-grid uk-grid-match uk-grid-gutter" if="{_meta}"> <div class="uk-grid-margin uk-width-medium-{field.width}" each="{field, name in meta}" no-reorder> <div class="uk-panel"> <label class="uk-text-small uk-text-bold"> <i class="uk-icon-pencil-square uk-margin-small-right"></i> {field.label || name} </label> <div class="uk-margin uk-text-small uk-text-muted"> {field.info || \' \'} </div> <div class="uk-margin"> <cp-field type="{field.type || \'text\'}" bind="image.meta[\'{name}\']" opts="{field.options || {}}"></cp-field> </div> </div> </div> </div> <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{App.i18n.get(\'Close\')}</button></div> </div> </div>', '', '', function(opts) {

        this.on('mount', function() { this.update(); });
        this.on('update', function() { if (opts.opts) App.$.extend(opts, opts.opts); });

        riot.util.bind(this);

        var $this = this, _default = {path:'', meta:{title:''}};

        this.image = Object.create(_default);

        this.on('mount', function() {

            this.meta  = App.$.extend(opts.meta || {}, {
                title: {
                    type: 'text',
                    label: 'Title'
                }
            });

            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                UIkit.uploadDrop($this.root, {

                    action: App.route('/assetsmanager/upload'),
                    type: 'json',
                    allow : '*.(jpg|jpeg|gif|png)',
                    filelimit: 1,
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
                            App.ui.notify("File(s) failed to upload.", "danger");
                        }

                        if (response && Array.isArray(response.assets) && response.assets.length) {
                            $this.image.path = ASSETS_URL.replace(SITE_URL, '')+response.assets[0].path;
                            $this.$setValue($this.image);
                        }

                        if (!response) {
                            App.ui.notify("Something went wrong.", "danger");
                        }

                    }
                });
            });

        });

        this.$updateValue = function(value, field, force) {

            value = value || Object.create(_default);

            if (!value.path) {
               value = Object.create(_default);
            }

            if ((JSON.stringify(this.image) !== JSON.stringify(value)) || force) {
                this.image = value;
                return this.update();
            }

        }.bind(this);

        this.selectImage = function() {

            App.media.select(function(selected) {

                $this.image.path = selected[0];
                $this.$setValue($this.image);
                $this.update();

            }, { typefilter:'image', pattern: '*.jpg|*.jpeg|*.png|*.gif|*.svg' });
        }.bind(this)

        this.selectAsset = function() {

            App.assets.select(function(assets){

                if (Array.isArray(assets) && assets[0]) {

                    $this.image.path = ASSETS_URL.replace(SITE_URL, '')+assets[0].path;
                    $this.$setValue($this.image);
                    $this.update();
                }

            }, {typefilter: 'image'});
        }.bind(this)

        this.remove = function() {
            this.image = Object.create(_default);
            this.$setValue(this.image);
        }.bind(this)

        this.showMeta = function() {

            this._meta = this.image.meta || {};

            setTimeout(function() {
                UIkit.modal($this.refs.modalmeta, {modal:false}).show().one('close.uk.modal', function(){
                    $this._meta = null;
                });
            }, 50)
        }.bind(this)

        this.editUrl = function() {
            App.ui.prompt('Image Url', this.image.path, function (url) {
                $this.image.path = url;
                $this.$setValue($this.image);
                $this.update();
            });
        }.bind(this)

});

riot.tag2('field-layout', '<div class="uk-sortable layout-components {!items.length && \'empty\'}" ref="components" data-uk-sortable="animation:false, group:\'field-layout-items\'"> <div class="uk-panel-box uk-panel-card" each="{item,idx in items}" data-idx="{idx}"> <div class="uk-flex uk-flex-middle uk-text-small uk-visible-hover"> <img class="uk-margin-small-right" riot-src="{parent.components[item.component].icon ? parent.components[item.component].icon : App.base(\'/assets/app/media/icons/component.svg\')}" width="16"> <div class="uk-text-bold uk-text-truncate uk-flex-item-1"> <a class="uk-link-muted" onclick="{parent.settings}">{item.name || parent.components[item.component].label || App.Utils.ucfirst(item.component)}</a> </div> <div class="uk-text-small uk-invisible"> <a onclick="{parent.cloneComponent}" title="{App.i18n.get(\'Clone Component\')}"><i class="uk-icon-clone"></i></a> <a class="uk-margin-small-left" onclick="{parent.addComponent}" title="{App.i18n.get(\'Add Component\')}"><i class="uk-icon-plus"></i></a> <a class="uk-margin-small-left uk-text-danger" onclick="{parent.remove}"><i class="uk-icon-trash-o"></i></a> </div> </div> <div class="uk-margin" if="{parent.components[item.component].children}"> <field-layout bind="items[{idx}].children" child="true" parent-component="{parent.components[item.component]}" components="{parent.components}" exclude="{opts.exclude}" restrict="{opts.restrict}" preview="{opts.preview}"></field-layout> </div> <div class="uk-margin" if="{item.component == \'grid\'}"> <field-layout-grid bind="items[{idx}].columns" components="{parent.components}" exclude="{opts.exclude}" restrict="{opts.restrict}" preview="{opts.preview}"></field-layout-grid> </div> <raw class="layout-field-preview uk-text-small uk-text-muted" content="{getPreview(item)}" if="{showPreview}"></raw> </div> </div> <div class="uk-margin uk-text-center"> <a class="uk-text-primary {!opts.child && \'uk-button uk-button-outline uk-button-large\'}" onclick="{addComponent.bind(this, true)}" title="{App.i18n.get(\'Add component\')}" data-uk-tooltip="pos:\'bottom\'"><i class="uk-icon-plus-circle"></i></a> </div> <div class="uk-modal uk-sortable-nodrag" ref="modalComponents"> <div class="uk-modal-dialog"> <h3 class="uk-flex uk-flex-middle uk-text-bold"> <img class="uk-margin-small-right" riot-src="{App.base(\'/assets/app/media/icons/component.svg\')}" width="30"> {App.i18n.get(\'Components\')} </h3> <ul class="uk-tab uk-tab-noborder uk-margin-bottom uk-flex uk-flex-center uk-noselect" show="{App.Utils.count(componentGroups) > 1}"> <li class="{!componentGroup && \'uk-active\'}"><a class="uk-text-capitalize" onclick="{toggleComponentGroup}">{App.i18n.get(\'All\')}</a></li> <li class="{group==parent.componentGroup && \'uk-active\'}" each="{items,group in componentGroups}" show="{items.length}"><a class="uk-text-capitalize" onclick="{toggleComponentGroup}">{App.i18n.get(group)}</a></li> </ul> <div class="uk-grid uk-grid-match uk-grid-small uk-grid-width-medium-1-4"> <div class="uk-grid-margin" each="{component,name in components}" show="{isComponentAvailable(name)}"> <div class="uk-panel uk-panel-framed uk-text-center"> <img riot-src="{component.icon || App.base(\'/assets/app/media/icons/component.svg\')}" width="30"> <p class="uk-text-small">{component.label || App.Utils.ucfirst(name)}</p> <a class="uk-position-cover" onclick="{add}"></a> </div> </div> </div> <div class="uk-modal-footer uk-text-right"> <a class="uk-button uk-button-link uk-button-large uk-modal-close">{App.i18n.get(\'Close\')}</a> </div> </div> </div> <div class="uk-modal uk-sortable-nodrag" ref="modalSettings"> <div class="uk-modal-dialog {components[settingsComponent.component].dialog==\'large\' && \'uk-modal-dialog-large\'}" if="{settingsComponent}"> <a class="uk-modal-close uk-close"></a> <div class="uk-margin-large-bottom"> <div class="uk-grid uk-grid-small"> <div> <img riot-src="{components[settingsComponent.component].icon ? components[settingsComponent.component].icon : App.base(\'/assets/app/media/icons/settings.svg\')}" width="30"> </div> <div class="uk-flex-item-1"> <h3 class="uk-margin-remove">{components[settingsComponent.component].label || App.Utils.ucfirst(settingsComponent.component)}</h3> <input type="text" class="uk-form-blank uk-width-1-1 uk-text-primary" bind="settingsComponent.name" placeholder="Name"> </div> </div> </div> <ul class="uk-tab uk-margin-bottom uk-flex uk-flex-center"> <li class="{!settingsGroup && \'uk-active\'}"><a class="uk-text-capitalize" onclick="{toggleGroup}">{App.i18n.get(\'All\')}</a></li> <li class="{group==parent.settingsGroup && \'uk-active\'}" each="{items,group in settingsGroups}" show="{items.length}"><a class="uk-text-capitalize" onclick="{toggleGroup}">{App.i18n.get(group)}</a></li> </ul> <div class="uk-grid uk-grid-small uk-grid-match"> <div class="uk-grid-margin uk-width-medium-{field.width}" each="{field,idx in settingsFields}" show="{!settingsGroup || (settingsGroup == field.group)}" no-reorder> <div class="uk-panel"> <label class="uk-text-small uk-text-bold"><i class="uk-icon-pencil-square uk-margin-small-right"></i> {field.label || field.name}</label> <div class="uk-margin-small-top uk-text-small uk-text-muted" show="{field.info}">{field.info}</div> <div class="uk-margin-small-top"> <cp-field type="{field.type || \'text\'}" bind="settingsComponent.settings.{field.name}" opts="{field.options || {}}"></cp-field> </div> </div> </div> </div> <div class="uk-modal-footer uk-text-right"> <a class="uk-button uk-button-link uk-button-large uk-modal-close">{App.i18n.get(\'Close\')}</a> </div> </div> </div>', 'field-layout .layout-components > div,[data-is="field-layout"] .layout-components > div{ margin-bottom: 5px; } field-layout .field-layout-column-label,[data-is="field-layout"] .field-layout-column-label{ font-size: .8em; font-weight: bold; } field-layout .uk-sortable-placeholder .uk-sortable,[data-is="field-layout"] .uk-sortable-placeholder .uk-sortable{ pointer-events: none; } field-layout .layout-components.empty,[data-is="field-layout"] .layout-components.empty{ min-height: 100px; background: rgba(0,0,0,.01); } field-layout .layout-components.empty:after,[data-is="field-layout"] .layout-components.empty:after{ font-family: FontAwesome; content: "\\f1b3"; position: absolute; top: 50%; left: 50%; font-size: 14px; transform: translate3d(-50%, -50%, 0); color: rgba(0,0,0,.3); } field-layout .layout-field-preview,[data-is="field-layout"] .layout-field-preview{ display: block; margin-top: 8px; padding-top: 6px; border-top: 1px rgba(0,0,0,.05) dotted; } field-layout .layout-field-preview canvas,[data-is="field-layout"] .layout-field-preview canvas{ background-size: contain; background-position: 50% 50%; background-repeat: no-repeat; } field-layout .layout-field-preview:empty,[data-is="field-layout"] .layout-field-preview:empty{ display:none }', '', function(opts) {

        var $this = this;

        riot.util.bind(this);

        this.mode = 'edit';
        this.items = [];
        this.settingsComponent = null;
        this.componentGroups = {'Core':[]};
        this.generalSettingsFields  = [
            {name: "id", type: "text", group: "General" },
            {name: "class", type: "text", group: "General" },
            {name: "style", type: "code", group: "General", options: {syntax: "css", height: "100px"}}
        ];

        this.components = {
            "section": {
                "group": "Core",
                "children":true
            },

            "grid": {
                "group": "Core"
            },

            "text": {
                "group": "Core",
                "icon": App.base('/assets/app/media/icons/text.svg'),
                "dialog": "large",
                "fields": [
                    {"name": "text", "type": "wysiwyg", "default": ""}
                ]
            },

            "html": {
                "group": "Core",
                "icon": App.base('/assets/app/media/icons/code.svg'),
                "dialog": "large",
                "fields": [
                    {"name": "html", "type": "html", "default": ""}
                ]
            },

            "heading": {
                "group": "Core",
                "icon": App.base('/assets/app/media/icons/heading.svg'),
                "fields": [
                    {"name": "text", "type": "text", "default": "Header"},
                    {"name": "tag", "type": "select", "options":{"options":['h1','h2','h3','h4','h5','h6']}, "default": "h1"}
                ]
            },

            "image": {
                "group": "Core",
                "icon": App.base('/assets/app/media/icons/photo.svg'),
                "fields": [
                    {"name": "image", "type": "image", "default": {}},
                    {"name": "width", "type": "text", "default": ""},
                    {"name": "height", "type": "text", "default": ""}
                ]
            },

            "gallery": {
                "group": "Core",
                "icon": App.base('/assets/app/media/icons/gallery.svg'),
                "fields": [
                    {"name": "gallery", "type": "gallery", "default": []}
                ]
            },

            "divider": {
                "group": "Core",
                "icon": App.base('/assets/app/media/icons/divider.svg'),
            },

            "button": {
                "group": "Core",
                "icon": App.base('/assets/app/media/icons/button.svg'),
                "fields": [
                    {"name": "text", "type": "text", "default": ""},
                    {"name": "url", "type": "text", "default": ""}
                ]
            }
        };

        if (window.CP_LAYOUT_COMPONENTS && App.Utils.isObject(window.CP_LAYOUT_COMPONENTS)) {
            this.components = App.$.extend(true, this.components, window.CP_LAYOUT_COMPONENTS);
        }

        if (opts.parentComponent && opts.parentComponent.options) {
            opts = App.$.extend(true, {}, opts.parentComponent.options, opts);
        }

        this.on('mount', function() {

            this.showPreview = opts.preview === undefined ? true : opts.preview;

            App.trigger('field.layout.components', {components:this.components, opts:opts});

            if (opts.components && App.Utils.isObject(opts.components)) {
                this.components = App.$.extend(true, this.components, opts.components);
            }

            Object.keys(this.components).forEach(function(k) {

                if (Array.isArray(opts.exclude) && opts.exclude.indexOf(k) > -1) return;
                if (Array.isArray(opts.restrict) && opts.restrict.indexOf(k) == -1) return;

                $this.components[k].group = $this.components[k].group || 'Misc';

                var g = $this.components[k].group;

                if (!$this.componentGroups[g]) {
                    $this.componentGroups[g] = [];
                }

                $this.componentGroups[g].push(k);
            });

            window.___moved_layout_item = null;

            App.$(this.refs.components).on('start.uk.sortable', function(e, sortable, el, placeholder) {

                if (!el) return;
                e.stopPropagation();
                window.___moved_layout_item = {idx: el._tag.idx, item: el._tag.item, src: $this};
            });

            App.$(this.refs.components).on('change.uk.sortable', function(e, sortable, el, mode) {

                if (!el) return;

                e.stopPropagation();

                var item = window.___moved_layout_item;

                if ($this.refs.components === sortable.element[0]) {

                    switch(mode) {

                        case 'moved':
                            var items = [];

                            App.$($this.refs.components).children().each(function() {
                                items.push(this._tag.item);
                            });

                            $this.$setValue(items);
                            $this.update();

                            break;

                        case 'removed':

                            $this.items.splice(item.idx, 1);
                            $this.$setValue($this.items);
                            break;

                        case 'added':

                            $this.items.splice(el.index(), 0, item.item);
                            $this.$setValue($this.items);
                            el.remove();

                            if (opts.child) {
                                $this.propagateUpdate();
                            }
                            break;
                    }
                }
            });

            UIkit.modal(this.refs.modalSettings, {modal:false}).on('hide.uk.modal', function(e) {

                if (e.target !== $this.refs.modalSettings) {
                    return;
                }

                $this.$setValue($this.items);

                setTimeout(function(){
                    $this.settingsComponent = null;
                    $this.update();

                    if (opts.child) {
                        $this.propagateUpdate();
                    }
                }, 50);
            });

            this.update();
        });

        this.$initBind = function() {
            this.root.$value = this.items;
        };

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.items) != JSON.stringify(value)) {
                this.items = value;
                this.update();
            }

        }.bind(this);

        this.propagateUpdate = function() {

            var n = this;

            while (n.parent) {
                if (n.parent.root.getAttribute('data-is') == 'field-layout') {
                    n.parent.$setValue(n.parent.items);
                }
                n = n.parent;
            }
        }

        this.isComponentAvailable = function(name) {

            if (Array.isArray(opts.exclude) && opts.exclude.indexOf(name) > -1) return false;
            if (Array.isArray(opts.restrict) && opts.restrict.indexOf(name) == -1) return false;

            return !this.componentGroup || (this.componentGroup == this.components[name].group);
        }.bind(this)

        this.addComponent = function(e, push) {
            this.componentGroup = null;
            this.refs.modalComponents.afterComponent = !push && e.item && e.item.item ? e.item.idx : false;
            UIkit.modal(this.refs.modalComponents, {modal:false}).show();
        }.bind(this)

        this.cloneComponent = function(e) {

            var item = JSON.parse(JSON.stringify(e.item.item)), idx = e.item.idx;

            this.items.splice(idx + 1, 0, item);
            this.$setValue(this.items);

            setTimeout(function() {
                if (opts.child) $this.propagateUpdate();
            }.bind(this));
        }.bind(this)

        this.add = function(e) {

            var item = {
                component: e.item.name,
                settings: { id: '', 'class': '', style: '' }
            };

            var settings = this.components[e.item.name];

            if (Array.isArray(settings.fields)) {

                settings.fields.forEach(function(field) {
                    item.settings[field.name] = field.options && field.options.default || null;
                })
            }

            if (this.components[e.item.name].children) {
                item.children = [];
            }

            if (e.item.name == 'grid') {
                item.columns = [];
            }

            if (App.Utils.isNumber(this.refs.modalComponents.afterComponent)) {
                this.items.splice(this.refs.modalComponents.afterComponent + 1, 0, item);
                this.refs.modalComponents.afterComponent = false;
            } else {
                this.items.push(item);
            }

            this.$setValue(this.items);

            setTimeout(function() {

                UIkit.modal(this.refs.modalComponents).hide();

                if (opts.child) {
                    $this.propagateUpdate();
                }

            }.bind(this));
        }.bind(this)

        this.remove = function(e) {
            this.items.splice(e.item.idx, 1);

            if (opts.child) {
                this.parent.update()
            }
        }.bind(this)

        this.settings = function(e) {

            var component = e.item.item;

            this.settingsComponent = e.item.item;

            this.settingsFields    = (this.components[component.component].fields || []).concat(this.generalSettingsFields);
            this.settingsFieldsIdx = {};
            this.settingsGroups    = {main:[]};
            this.settingsGroup     = 'main';

            this.settingsFields.forEach(function(field){

                $this.settingsFieldsIdx[field.name] = field;

                if (component.settings[field.name] === undefined) {
                    component.settings[field.name] = field.options && field.options.default || null;
                }

                if (field.group && !$this.settingsGroups[field.group]) {
                    $this.settingsGroups[field.group] = [];
                } else if (!field.group) {
                    field.group = 'main';
                }

                $this.settingsGroups[field.group || 'main'].push(field);
            });

            if (!this.settingsGroups[this.settingsGroup].length) {
                this.settingsGroup = Object.keys(this.settingsGroups)[1];
            }

            setTimeout(function() {
                UIkit.modal(this.refs.modalSettings, {modal:false}).show();
            }.bind(this));
        }.bind(this)

        this.toggleGroup = function(e) {
            e.preventDefault();
            this.settingsGroup = e.item && e.item.group || false;
        }.bind(this)

        this.toggleComponentGroup = function(e) {
            e.preventDefault();
            this.componentGroup = e.item && e.item.group || false;
        }.bind(this)

        this.getPreview = function(component) {

            var def = this.components[component.component];

            if (!def || def.children || component.component == 'grid') {
                return;
            }

            if (['heading', 'button'].indexOf(component.component) > -1) {
                return component.settings.text ? '<div class="uk-text-truncate">'+App.Utils.stripTags(component.settings.text)+'</div>':'';
            }

            if (['text', 'html'].indexOf(component.component) > -1) {
                var txt = App.Utils.stripTags(component.settings.text, '<b><strong>').trim();
                return txt ? '<div class="uk-text-truncate">'+txt.substr(0, 100)+'</div>':'';
            }

            if (component.component == 'image' && component.settings.image && component.settings.image.path) {

                var src = getPathUrl(component.settings.image.path),
                    url = component.settings.image.path.match(/^(http\:|https\:|\/\/)/) ? component.settings.image.path : encodeURI(SITE_URL+'/'+component.settings.image.path),
                    html;

                html = '<canvas class="uk-responsive-width" width="50" height="50" style="background-image:url('+src+')"></canvas>';

                return '<a href="'+url+'" data-uk-lightbox>'+html+'</a>';
            }

            if (component.component== 'gallery' && Array.isArray(component.settings.gallery) && component.settings.gallery.length) {

                var html = [], url, src;

                html.push('<div class="uk-flex">');
                component.settings.gallery.forEach(function(img) {
                    if (html.length > 6) return;
                    url = img.path.match(/^(http\:|https\:|\/\/)/) ? img.path : encodeURI(SITE_URL+'/'+img.path);
                    src = getPathUrl(img.path);

                    html.push('<div><a href="'+url+'" data-uk-lightbox><canvas class="uk-responsive-width" width="50" height="50" style="background-image:url('+src+')"></canvas></a></div>')
                });

                html.push('</div>');

                return html.join('');
            }

            return '';
        }.bind(this)

        function getPathUrl(path) {

            var p = path,
                url = p.match(/^(http\:|https\:|\/\/)/) ? p : encodeURI(SITE_URL+'/'+p),
                html, src;

            if (url.match(/^(http\:|https\:|\/\/)/) && !(url.includes(ASSETS_URL) || url.includes(SITE_URL))) {
                src = url;
            } else {
                src = App.route('/cockpit/utils/thumb_url?src='+url+'&w=50&h=50&m=bestFit&re=1');
            }

            if (src.match(/\.(svg|ico)$/i)) {
                src = url;
            }

            return src;
        }

});

riot.tag2('field-layout-grid', '<div class="uk-text-center uk-placeholder" if="{!columns.length}"> <a class="uk-button uk-button-link" onclick="{addColumn}">{App.i18n.get(\'Add Column\')}</a> </div> <div class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-grid-width-medium-1-{columns.length > 5 ? 1 : columns.length}" show="{columns.length}" ref="columns" data-uk-sortable="animation:false"> <div class="uk-grid-margin" each="{column,idx in columns}"> <div class="uk-panel"> <div class="uk-flex uk-flex-middle uk-text-small uk-visible-hover"> <div class="uk-flex-item-1 uk-margin-small-right"><a class="uk-text-muted uk-text-uppercase field-layout-column-label" onclick="{parent.settings}" title="{App.i18n.get(\'Settings\')}"><i class="uk-icon-columns" alt="Column {(idx+1)}"></i> {(idx+1)}</a></div> <a class="uk-invisible uk-margin-small-right" onclick="{parent.cloneColumn}" title="{App.i18n.get(\'Clone Column\')}"><i class="uk-icon-clone"></i></a> <a class="uk-invisible uk-margin-small-right" onclick="{parent.addColumn}" title="{App.i18n.get(\'Add Column\')}"><i class="uk-icon-plus"></i></a> <a class="uk-invisible" onclick="{parent.remove}"><i class="uk-text-danger uk-icon-trash-o"></i></a> </div> <div class="uk-margin"> <field-layout bind="columns[{idx}].children" child="true" components="{opts.components}" exclude="{opts.exclude}" preview="{opts.preview}"></field-layout> </div> </div> </div> </div> <div class="uk-modal uk-sortable-nodrag" ref="modalSettings"> <div class="uk-modal-dialog" if="{settingsComponent}"> <h3 class="uk-flex uk-flex-middle uk-margin-large-bottom"> <img class="uk-margin-small-right" riot-src="{App.base(\'/assets/app/media/icons/settings.svg\')}" width="30"> {App.i18n.get(\'Column\')} </h3> <field-set class="uk-margin" bind="settingsComponent.settings" fields="{fields}"></field-set> <div class="uk-modal-footer uk-text-right"> <a class="uk-button uk-button-link uk-button-large uk-modal-close">{App.i18n.get(\'Close\')}</a> </div> </div> </div>', '', '', function(opts) {

        var $this = this;

        riot.util.bind(this);

        this.columns = [];
        this.fields  = [
            {name: "id", type: "text" },
            {name: "class", type: "text" },
            {name: "style", type: "code", options: {syntax: "css", height: "100px"}  }
        ];
        this.settingsComponent = null;

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.columns) !== JSON.stringify(value)) {
                this.columns = value;
                this.update();
            }

        }.bind(this);

        this.$initBind = function() {
            this.root.$value = this.columns;
        };

        this.propagateUpdate = function() {

            var n = this;

            while (n.parent) {
                if (n.parent.root.tagName == 'field-layout' || n.parent.root.getAttribute('data-is') == 'field-layout') {
                    n.parent.$setValue(n.parent.items);
                }
                n = n.parent;
            }
        }

        this.on('mount', function() {

            App.$(this.refs.columns).on('change.uk.sortable', function(e, sortable, el, mode) {

                if (!el) return;

                e.stopPropagation();

                if ($this.refs.columns === sortable.element[0]) {

                    var columns = [];

                    App.$($this.refs.columns).children().each(function() {
                        columns.push(this._tag.column);
                    });

                    $this.$setValue(columns);
                    $this.update();

                    $this.propagateUpdate();
                }
            });

            UIkit.modal(this.refs.modalSettings, {modal:false}).on('hide.uk.modal', function(e) {

                if (e.target !== $this.refs.modalSettings) {
                    return;
                }

                $this.$setValue($this.columns);

                setTimeout(function() {
                    $this.settingsComponent = null;
                    $this.update();
                }, 50);
            });

            this.update();
        });

        this.addColumn = function() {

            var column = {
                settings: { id: '', 'class': '', style: '' },
                children: []
            };

            this.columns.push(column);
            this.$setValue(this.columns);

            this.propagateUpdate();
        }.bind(this)

        this.cloneColumn = function(e) {

            var column = JSON.parse(JSON.stringify(e.item.column)), idx = e.item.idx;

            this.columns.splice(idx + 1, 0, column);
            this.$setValue(this.columns);

            this.propagateUpdate();
        }.bind(this)

        this.settings = function(e) {

            this.settingsComponent = e.item.column;

            setTimeout(function() {
                UIkit.modal(this.refs.modalSettings).show();
            }.bind(this));
        }.bind(this)

        this.remove = function(e) {
            this.columns.splice(e.item.idx, 1);
        }.bind(this)

});

riot.tag2('field-location', '<div class="uk-alert" if="{!apiready}"> Loading maps api... </div> <div show="{apiready}"> <div class="uk-form uk-position-relative uk-margin-small-bottom uk-width-1-1" style="z-index:1001"> <input ref="autocomplete" class="uk-width-1-1" placeholder="{latlng.address || [latlng.lat, latlng.lng].join(\', \')}"> </div> <div ref="map" style="min-height:300px; z-index:0;"> Loading map... </div> </div>', '', '', function(opts) {

        var map, marker;

        var locale = document.documentElement.lang.toUpperCase();

        var loadApi = App.assets.require([
            'https://cdn.jsdelivr.net/npm/leaflet@1.3.1/dist/leaflet.min.css',
            'https://cdn.jsdelivr.net/npm/leaflet@1.3.1/dist/leaflet.min.js',
            'https://cdn.jsdelivr.net/npm/places.js@1.7.2/dist/cdn/places.min.js'
        ]);

        var $this = this, defaultpos = {lat:53.55909862554551, lng:9.998652343749995};

        this.latlng = defaultpos;

        this.$updateValue = function(value) {

            if (!value) {
                value = defaultpos;
            }

            if (this.latlng != value) {
                this.latlng = value;

                if (marker) {
                    marker.setLatLng([this.latlng.lat, this.latlng.lng]).update();
                    map.panTo(marker.getLatLng());
                }

                this.update();
            }

        }.bind(this);

        this.on('mount', function() {

            loadApi.then(function() {

                $this.apiready = true;

                setTimeout(function(){

                    var map = L.map($this.refs.map).setView([$this.latlng.lat, $this.latlng.lng], opts.zoomlevel || 13);

                    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    var marker = new L.marker([$this.latlng.lat, $this.latlng.lng], {draggable:'true'});

                    marker.on('dragend', function(e) {
                        $this.$setValue(marker.getLatLng());
                    });

                    map.addLayer(marker);

                    var pla = places({
                        container: $this.refs.autocomplete
                    }).on('change', function(e) {
                        e.suggestion.latlng.address = e.suggestion.value;
                        $this.$setValue(e.suggestion.latlng);
                        marker.setLatLng(e.suggestion.latlng).update();
                        map.panTo(marker.getLatLng());
                        pla.close();
                        pla.setVal('');
                    }).on('suggestions', function (e) {
                      var coords = e.query.match(/^(\-?\d+(?:\.\d+)?),\s*(\-?\d+(?:\.\d+)?)$/);

                      if (!coords) {
                        return;
                      }

                      var latlng = {
                        lat: parseFloat(coords[1]),
                        lng: parseFloat(coords[2])
                      };

                      $this.$setValue(latlng);
                      marker.setLatLng(latlng).update();
                      map.panTo(marker.getLatLng());
                      pla.close();
                      pla.setVal('');
                    });

                }, 50);

                $this.update();
            });

        });

});

riot.tag2('field-markdown', '<field-html ref="input" markdown="true" bind="{opts.bind}" height="{opts.height}"></field-html>', '', '', function(opts) {
});

riot.tag2('field-multipleselect', '<div if="{loading}"><i class="uk-icon-spinner uk-icon-spin"></i></div> <div class="{optionsLength > 10 ? \'uk-scrollable-box\':\'\'}" if="{!loading && Array.isArray(options)}"> <div class="uk-margin" each="{group in Object.keys(groups).sort()}"> <div class="uk-text-bold uk-text-upper uk-text-small uk-margin-small">{group}</div> <div class="uk-margin-small uk-margin-small-left uk-text-small" each="{option,idx in parent.groups[group]}"> <a data-value="{option.value}" class="{parent.selected.indexOf(option.value)!==-1 ? \'uk-text-primary\':\'uk-text-muted\'}" onclick="{toggle}" title="{option.label}"> <i class="uk-icon-{parent.selected.indexOf(option.value)!==-1 ? \'circle\':\'circle-o\'} uk-margin-small-right"></i> {option.label} </a> </div> </div> <div class="uk-margin-small-top uk-text-small" each="{option in options}"> <a data-value="{option.value}" class="{parent.selected.indexOf(option.value)!==-1 ? \'uk-text-primary\':\'uk-text-muted\'}" onclick="{parent.toggle}" title="{option.label}"> <i class="uk-icon-{parent.selected.indexOf(option.value)!==-1 ? \'circle\':\'circle-o\'} uk-margin-small-right"></i> {option.label} </a> </div> </div> <span class="uk-text-small uk-text-muted" if="{optionsLength > 10}">{selected.length} {App.i18n.get(\'selected\')}</span>', '', '', function(opts) {

        var $this = this;

        this.selected = [];
        this.optionsLength = 0;
        this.groups = {};
        this.options  = null;

        this.loading = opts.src && opts.src.url ? true : false;

        this.on('mount', function() {

            if (opts.src && opts.src.url && opts.src.value) {

                this.loading = true;

                var url = opts.src.url,
                    fieldVal = opts.src.value,
                    fieldLabel = opts.src.label || fieldVal
                    fieldGroup = opts.src.group || null;

                if (url.match('^collection=')) {
                    url = '/collections/find?'+url;
                }

                App.request(opts.src.url).then(function(data) {

                    $this.loading = false;

                    if (url.match('^\/collections\/find\?')) {
                        data = data.entries;
                    }

                    if (!Array.isArray(data)) {
                        $this.update();
                        return;
                    }

                    $this.options = [];

                    data.forEach(function(item, option) {

                        if (item[fieldVal] === undefined) return;

                        option = {
                            value: _.get(item, fieldVal),
                            label: _.get(item, fieldLabel),
                            group: fieldGroup ? _.get(item, fieldGroup) : false
                        };

                        if (option.group) {

                            if (!$this.groups[option.group]) {
                                $this.groups[option.group] = [];
                            }

                            $this.groups[option.group].push(option);
                        } else {
                            $this.options.push(option);
                        }

                        $this.optionsLength++;

                    })

                    $this.update();
                })
            }

            this.update();
        });

        this.on('update', function() {

            if (this.loading) return;

            if (!this.options) {

                this.options = [];

                if (typeof(opts.options) === 'string' || Array.isArray(opts.options)) {

                    (typeof(opts.options) === 'string' ? opts.options.split(',') : opts.options || []).forEach(function(option) {

                        option = {
                            value : (option.hasOwnProperty('value') ? option.value.toString().trim() : option.toString().trim()),
                            label : (option.hasOwnProperty('label') ? option.label.toString().trim() : option.toString().trim()),
                            group : (option.hasOwnProperty('group') ? option.group.toString().trim() : '')
                        };

                        if (option.group) {

                            if (!$this.groups[option.group]) {
                                $this.groups[option.group] = [];
                            }

                            $this.groups[option.group].push(option);
                        } else {
                            $this.options.push(option);
                        }

                        $this.optionsLength++;

                    });

                } else if(typeof(opts.options) === 'object') {

                    Object.keys(opts.options).forEach(function(key) {

                        $this.options.push({
                            value: key,
                            label: opts.options[key]
                        });

                        $this.optionsLength++;
                    });
                }
            }
        });

        this.$initBind = function() {
            this.root.$value = this.selected;
        };

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.selected) != JSON.stringify(value)) {
                this.selected = value;
                this.update();
            }

        }.bind(this);

        this.toggle = function(e) {

            var option = e.item.option.value,
                index  = this.selected.indexOf(option);

            if (index == -1) {
                this.selected.push(option);
            } else {
                this.selected.splice(index, 1);
            }

            this.$setValue(this.selected);
        }.bind(this)

});

riot.tag2('field-object', '<div ref="input" riot-style="height: {opts.height || \'300px\'}"></div>', '', '', function(opts) {

        var $this = this, editor;

        this.value = {};

        this.on('mount', function(){

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }
            App.assets.require([

                '/assets/lib/jsoneditor/jsoneditor.min.css',
                '/assets/lib/jsoneditor/jsoneditor.min.js'

            ], function() {

                editor = new JSONEditor(this.refs.input, {
                    modes: ['tree', 'code'],
                    mode: 'code',
                    onError: function(e) {},
                    onChange: function() {

                        try {
                            $this.value = editor.get() || {};
                            $this.$setValue($this.value, true);
                        } catch(e) {}
                    }
                });

                editor.set(this.value);

            }.bind(this));

        });

        this.$updateValue = function(value) {

            if (typeof(value) != 'object') {
                value = {};
            }

            if (JSON.stringify(this.value) != JSON.stringify(value)) {
                this.value = value || {};
                if (editor)  {
                    editor.set(this.value);
                }
            }

        }.bind(this);

});

riot.tag2('field-password', '<div class="uk-form-password uk-width-1-1"> <input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="password"> <a href="" class="uk-form-password-toggle" data-uk-form-password>Show</a> </div>', '', '', function(opts) {

        this.on('mount', function(){

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            App.assets.require(['/assets/lib/uikit/js/components/form-password.js'], function() {

                UIkit.init(this.root);

            }.bind(this));
        });

});

riot.tag2('field-rating', '<ul class="uk-grid uk-grid-small"> <li class="{(!hoverValue && Math.ceil(value) >= n) || (hoverValue && Math.ceil(hoverValue) >= n) ? \'uk-text-primary\' : \'\'}" each="{n,idx in ratingRange}" onmousemove="{hoverRating}" onmouseleave="{leaveHoverRating}" onclick="{setRating}" style="cursor:pointer;"><i class="uk-icon-{opts.icon ? opts.icon : \'star\'}" title="{(idx+1)}" data-uk-tooltip></i></li> <li show="{value}"><span class="uk-badge">{!hoverValue && value || hoverValue}</span></li> <li show="{value}"><a class="uk-text-danger" onclick="{removeRating}"><i class="uk-icon-trash-o"></i></a></li> </ul>', '', '', function(opts) {


        this.value = null;
        this.hoverValue = null;
        this.ratingRange = [];

        this.on('mount', function() {

            this.mininmum  = opts.mininmum  || 0;
            this.maximum   = opts.maximum   || 5;
            this.precision = opts.precision || 0;

            if (this.precision < 0 || this.precision > 0.5) {
                this.precision = this.precision - Math.floor(this.precision);

                if (this.precision > 0.5) {
                    this.precision = this.precision - 0.5;
                }
            }

            for (var j = this.mininmum + 1; j <= this.maximum; j = j +1) {
                this.ratingRange.push(j);
            }

            this.update();
        });

        this.setRating = function(e) {
            this.$setValue(this.getValue(e));
        }.bind(this)

        this.getValue = function(e) {

            var element = App.$(e.target).closest('li')[0];

            if (!element) return;

            if (this.precision === 0) {
                return e.item.n;
            }

            return Math.floor(((e.item.n - 1) + (Math.floor(e.layerX/element.clientWidth / this.precision) + 1) * this.precision) * 1000) / 1000;
        }.bind(this)

        this.hoverRating = function(e) {
            this.hoverValue = this.getValue(e);
        }.bind(this)

        this.leaveHoverRating = function() {
            this.hoverValue = null;
        }.bind(this)

        this.removeRating = function() {
            this.$setValue(null);
        }.bind(this)

        this.$updateValue = function(value) {

            if (value === null && !opts.remove) {
                value = this.mininmum;
            }

            if (value !== null) {

                if (value < this.mininmum) {
                    value = this.mininmum;
                }

                if (value > this.maximum) {
                    value = this.maximum;
                }
            }

            if (this.value != value) {
                this.value = value;
                this.update();
            }

        }.bind(this);

});

riot.tag2('field-repeater', '<div class="uk-alert" show="{!items.length}"> {App.i18n.get(\'No items\')}. </div> <div show="{mode==\'edit\' && items.length}"> <div class="uk-margin-small-bottom uk-panel-box uk-panel-card" each="{item,idx in items}" data-idx="{idx}"> <div class="uk-flex uk-flex-middle"> <a onclick="{parent.toggleVisibility}" class="uk-badge uk-display-block uk-text-left uk-flex-item-1 {!parent.visibility[idx] && \'uk-badge-outline uk-text-muted\'}" riot-style="{!parent.visibility[idx] && \'border-color: rgba(0,0,0,0)\'}"> <i class="uk-icon-ellipsis-v uk-margin-small-left uk-margin-small-right"></i> {App.Utils.ucfirst(parent.getMeta(item).label || parent.getMeta(item).type)} <raw content="{parent.getItemPreview(item,idx)}"></raw> </a> <a class="uk-margin-left" onclick="{parent.toggleVisibility}"><i class="uk-icon-eye{parent.visibility[idx] && \'-slash uk-text-muted\'}"></i></a> <a class="uk-margin-left" onclick="{parent.clone}" title="{App.i18n.get(\'Clone item\')}" data-uk-tooltip><i class="uk-icon-clone"></i></a> <a class="uk-margin-left" onclick="{parent.remove}"><i class="uk-icon-trash-o uk-text-danger"></i></a> </div> <div class="uk-margin" if="{parent.visibility[idx]}"> <cp-field type="{parent.getMeta(item).type || \'text\'}" bind="items[{idx}].value" opts="{parent.getMeta(item).options || {}}"></cp-field> </div> </div> </div> <div ref="itemscontainer" class="uk-sortable" show="{mode==\'reorder\' && items.length}"> <div class="uk-margin-small-bottom uk-panel-box uk-panel-card" each="{item,idx in items}" data-idx="{idx}"> <div class="uk-grid uk-grid-small"> <div class="uk-flex-item-1"><i class="uk-icon-bars uk-margin-small-right"></i> {App.Utils.ucfirst(parent.getMeta(item).label || parent.getMeta(item).type)}</div> <div class="uk-text-muted uk-text-small uk-text-truncate"> <raw content="{parent.getItemPreview(item,idx)}"></raw></div> </div> </div> </div> <div class="uk-margin"> <a class="uk-button" onclick="{add}" show="{mode==\'edit\'}" if="{!fields}"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add item\')}</a> <span show="{mode==\'edit\'}" if="{fields}" data-uk-dropdown="mode:\'click\'"> <a class="uk-button"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add item\')}</a> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown"> <li each="{field in fields}"><a class="uk-dropdown-close" onclick="{parent.add}">{field.label && field.label || App.Utils.ucfirst(typeof(field) == \'string\' ? field:field.type)}</a></li> </ul> </div> </span> <a class="uk-button uk-button-success" onclick="{updateorder}" show="{mode==\'reorder\'}"><i class="uk-icon-check"></i> {App.i18n.get(\'Update order\')}</a> <a class="uk-button uk-button-link uk-link-reset" onclick="{switchreorder}" show="{items.length > 1}"> <span show="{mode==\'edit\'}"><i class="uk-icon-arrows"></i> {App.i18n.get(\'Reorder\')}</span> <span show="{mode==\'reorder\'}">{App.i18n.get(\'Cancel\')}</span> </a> </div>', '', '', function(opts) {

        var $this = this;

        riot.util.bind(this);

        this.items  = [];
        this.field  = {type:'text'};
        this.fields = false;
        this.mode   = 'edit';

        this.visibility = {};

        this.on('mount', function() {

            UIkit.sortable(this.refs.itemscontainer, {
                animation: false
            });

            this.update();
        });

        this.on('update', function() {
            this.field  = opts.field || {type:'text'};
            this.fields = opts.fields && Array.isArray(opts.fields) && opts.fields  || false;
        })

        this.$initBind = function() {
            this.root.$value = this.items;
        };

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.items) != JSON.stringify(value)) {
                this.items = value;
                this.update();
            }

        }.bind(this);

        this.on('bindingupdated', function() {
            $this.$setValue(this.items);
        });

        this.add = function(e) {

            if (opts.limit && this.items.length >= opts.limit) {
                App.ui.notify('Maximum amount of items reached');
                return;
            }

            if (this.fields) {
                this.items.push({field:e.item.field, value:null});
            } else {
                this.items.push({value:null});
            }

            this.visibility[this.items.length-1] = true;
        }.bind(this)

        this.remove = function(e) {
            if (this.opts && this.opts.safeDelete) {
                UIkit.modal.confirm(App.i18n.get("Confirm removal?"), function() {
                    $this.items.splice(e.item.idx, 1);
                    $this.update();
                });
            } else {
                this.items.splice(e.item.idx, 1);
            }
        }.bind(this)

        this.clone = function(e) {
            UIkit.modal.confirm("Clone?", function() {
                $this.items.push(JSON.parse(JSON.stringify(e.item.item)));
                $this.update();
            });
        }.bind(this)

        this.switchreorder = function() {

            this.visibility = {};

            $this.mode = $this.mode == 'edit' ? 'reorder':'edit';
        }.bind(this)

        this.toggleVisibility = function(e) {
            this.visibility[e.item.idx] = this.visibility[e.item.idx] ? false:true;
        }.bind(this)

        this.updateorder = function() {

            var items = [];

            App.$(this.refs.itemscontainer).children().each(function(){
                items.push($this.items[Number(this.getAttribute('data-idx'))]);
            });

            $this.items = [];
            $this.update();

            setTimeout(function() {
                $this.mode = 'edit';
                $this.items = items;
                $this.$setValue(items);

                setTimeout(function(){
                    $this.update();
                }, 50)
            }, 50);
        }.bind(this)

        this.getItemPreview = function(item, idx) {

            var meta = this.getMeta(item), display = meta.display || false;

            if (display) {

                var value;

                if (display == '$value') {
                    value = App.Utils.renderValue(meta.type, item.value, meta);
                } else {
                    value = _.get(item.value, display) || 'Item '+(idx+1);
                }

                return value;
            }

            return 'Item '+(idx+1);
        }.bind(this)

        this.getMeta = function(item) {

            if (item.field) {
                return item.field;
            }

            if (this.opts.field) {
                return this.opts.field;
            }

            return {type:'text', options: {}};
        }.bind(this)

});

riot.tag2('field-select', '<div if="{loading}"><i class="uk-icon-spinner uk-icon-spin"></i></div> <select ref="input" class="uk-width-1-1 {opts.cls}" bind="{opts.bind}" show="{!loading}" multiple="{opts.multiple}"> <option value=""></option> <optgroup each="{group in Object.keys(groups).sort()}" label="{group}"> <option each="{option,idx in parent.groups[group]}" riot-value="{option.value}" selected="{isSelected(option.value)}">{option.label}</option> </optgroup> <option each="{option,idx in options}" riot-value="{option.value}" selected="{isSelected(option.value)}">{option.label}</option> </select>', '', '', function(opts) {

        var $this = this;

        this.loading = opts.src && opts.src.url ? true : false;
        this.groups = {};
        this.options = null;

        this.on('mount', function() {

            (['required']).forEach( function(key) {
                if (opts[key]) $this.refs.input.setAttribute(key, opts[key]);
            });

            if (opts.multiple) {
                $this.refs.input.style.height = (opts.height ? String(opts.height).replace('px', '') : 200)+'px';
            }

            if (opts.src && opts.src.url && opts.src.value) {

                this.loading = true;

                var url = opts.src.url,
                    fieldVal = opts.src.value,
                    fieldLabel = opts.src.label || fieldVal
                    fieldGroup = opts.src.group || null;

                if (url.match('^collection=')) {
                    url = '/collections/find?'+url;
                }

                App.request(opts.src.url).then(function(data) {

                    $this.loading = false;

                    if (url.match('^\/collections\/find\?')) {
                        data = data.entries;
                    }

                    if (!Array.isArray(data)) {
                        $this.update();
                        return;
                    }

                    $this.options = [];

                    data.forEach(function(item, option) {

                        if (item[fieldVal] === undefined) return;

                        option = {
                            value: _.get(item, fieldVal),
                            label: _.get(item, fieldLabel),
                            group: fieldGroup ? _.get(item, fieldGroup) : false
                        };

                        if (option.group) {

                            if (!$this.groups[option.group]) {
                                $this.groups[option.group] = [];
                            }

                            $this.groups[option.group].push(option);
                        } else {
                            $this.options.push(option);
                        }

                    })

                    $this.update();
                })
            }

            this.update();
        });

        this.on('update', function() {

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            if (this.loading) {
                return;
            }

            if (this.options === null) {

                this.options = [];

                if (typeof(opts.options) === 'string' || Array.isArray(opts.options)) {

                    (typeof(opts.options) === 'string' ? opts.options.split(',') : opts.options || []).forEach(function(option) {

                        option = {
                            value : (option.hasOwnProperty('value') ? option.value.toString().trim() : option.toString().trim()),
                            label : (option.hasOwnProperty('label') ? option.label.toString().trim() : option.toString().trim()),
                            group : (option.hasOwnProperty('group') ? option.group.toString().trim() : '')
                        };

                        if (option.group) {

                            if (!$this.groups[option.group]) {
                                $this.groups[option.group] = [];
                            }

                            $this.groups[option.group].push(option);
                        } else {
                            $this.options.push(option);
                        }

                    });

                } else if (typeof(opts.options) === 'object') {

                    Object.keys(opts.options).forEach(function(key) {

                        $this.options.push({
                            value: key,
                            label: opts.options[key],
                            group: ''
                        })
                    })
                }
            }

            if (!opts.multiple) {
                this.refs.input.value = this.root.$value;
            }

        });

        this.isSelected = function(value) {

            if (opts.multiple) {
                return (Array.isArray(this.root.$value) ? this.root.$value : []).indexOf(value) > -1;
            }

            return this.root.$value == value;
        }.bind(this)

});

riot.tag2('field-set', '<div> <div class="uk-alert" if="{fields && !fields.length}"> {App.i18n.get(\'Fields definition is missing\')} </div> <div class="uk-margin" each="{field,idx in fields}"> <label class="uk-display-block uk-text-bold uk-text-small">{field.label || field.name || \'\'}</label> <cp-field class="uk-display-block uk-margin-small-top" type="{field.type || \'text\'}" bind="value.{field.name}" opts="{field.options || {}}"></cp-field> <div class="uk-margin-small-top uk-text-small uk-text-muted" if="{field.info}"> {field.info || \' \'} </div> </div> </div>', '', '', function(opts) {

        var $this = this;

        this._field = null;
        this.set    = {};
        this.value  = {};
        this.fields = [];

        riot.util.bind(this);

        this.on('mount', function() {
            this.fields = opts.fields || [];
            this.update();
        });

        this.on('update', function() {
            this.fields = opts.fields || [];
        });

        this.$initBind = function() {
            this.root.$value = this.value;
        };

        this.$updateValue = function(value, field) {

            if (!App.Utils.isObject(value) || Array.isArray(value)) {

                value = {};

                this.fields.forEach(function(field){
                    value[field.name] = null;
                });
            }

            if (JSON.stringify(this.value) != JSON.stringify(value)) {
                this.value = value;
                this.update();
            }

            this._field = field;

        }.bind(this);

        this.on('bindingupdated', function() {
            $this.$setValue(this.value);
        });

});

riot.tag2('field-tags', '<div class="uk-grid uk-grid-small uk-flex-middle" data-uk-grid-margin="observe:true"> <div class="uk-text-primary" each="{_tag,idx in _tags}"> <span class="field-tag"><i class="uk-icon-tag"></i> {_tag} <a onclick="{parent.remove}"><i class="uk-icon-close"></i></a></span> </div> <div show="{allowInput}"> <div ref="autocomplete" class="uk-autocomplete uk-form-icon uk-form"> <i class="uk-icon-tag"></i> <input ref="input" class="uk-width-1-1 uk-form-blank" type="text" placeholder="{App.i18n.get(opts.placeholder || \'Add Tag...\')}"> </div> </div> </div>', 'field-tags .field-tag,[data-is="field-tags"] .field-tag{ display: inline-block; border: 1px currentColor solid; padding: .4em .5em; font-size: .9em; border-radius: 3px; line-height: 1; }', '', function(opts) {

        var $this = this;

        this._tags = [];
        this.allowInput = true;

        this.on('mount', function(){
            this.update()
        });

        this.on('update', function(){

            if ($this.opts.limit) {
                $this.allowInput = $this._tags.length < $this.opts.limit;
            }

            if (opts.autocomplete) {

                var _source = opts.autocomplete;

                if (Array.isArray(opts.autocomplete) && opts.autocomplete.length && !opts.autocomplete[0].value) {

                    _source = [];

                    opts.autocomplete.forEach(function(val) {
                        _source.push({value:val})
                    })
                }

                UIkit.autocomplete(this.refs.autocomplete, {source: _source, minLength: opts.minLength || 1});
            }

            App.$(this.root).on({

                'selectitem.uk.autocomplete': function() {
                    setTimeout(function(){
                        $this.refs.input.value = '';
                    }, 0)
                },

                'selectitem.uk.autocomplete keydown': function(e, data) {

                    var value = e.type=='keydown' ? $this.refs.input.value : data.value;

                    if (e.type=='keydown' && e.keyCode != 13 && e.keyCode != 188) {
                        return;
                    }

                    if (value.trim()) {

                        $this.refs.input.value = value;

                        e.stopImmediatePropagation();
                        e.stopPropagation();
                        e.preventDefault();
                        $this._tags.push($this.refs.input.value);
                        $this.refs.input.value = "";
                        $this.$setValue(_.uniq($this._tags));
                        $this.update();

                        return false;
                    }
                }
            });
        });

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this._tags !== value) {
                this._tags = value;
                this.update();
            }

        }.bind(this);

        this.remove = function(e) {
            this._tags.splice(e.item.idx, 1);
            this.$setValue(this._tags);
        }.bind(this)

});

riot.tag2('field-text', '<div class="uk-position-relative field-text-container"> <input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="{opts.type || \'text\'}" oninput="{updateLengthIndicator}" placeholder="{opts.placeholder}"> <span class="uk-text-muted" ref="lengthIndicator" show="{type==\'text\'}" hide="{opts.showCount === false}"></span> </div> <div class="uk-text-muted uk-text-small uk-margin-small-top" if="{opts.slug}" title="Slug"> {slug} </div>', 'field-text [ref="input"][type=text],[data-is="field-text"] [ref="input"][type=text]{ padding-right: 30px !important; } field-text .field-text-container span,[data-is="field-text"] .field-text-container span{ position: absolute; top: 50%; right: 0; font-family: monospace; transform: translateY(-50%) scale(.9); }', '', function(opts) {

        var $this = this;

        this.on('mount', function() {

            this.type = opts.type || 'text';

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            if (opts.slug) {
                this.slug = this.$getValue(opts.bind+'_slug') || '';
            }

            (['maxlength', 'minlength', 'step', 'placeholder', 'pattern', 'size', 'min', 'max']).forEach( function(key) {
                if (opts[key]) $this.refs.input.setAttribute(key, opts[key]);
            });

            this.updateLengthIndicator();

            this.update();
        });

        this.$updateValue = function(value) {

            if (opts.slug) {
                this.slug = App.Utils.sluggify(value || '');
                this.$setValue(this.slug, false, opts.bind+'_slug');
                this.update();
            }

            this.updateLengthIndicator();

        }.bind(this);

        this.updateLengthIndicator = function() {

            if (this.type != 'text' || opts.showCount === false) {
                return;
            }

            this.refs.lengthIndicator.innerText = Math.abs((opts.maxlength || 0) - this.refs.input.value.length);
        }

});

riot.tag2('field-textarea', '<textarea ref="input" class="uk-width-1-1 {opts.cls}" bind="{opts.bind}" bind-event="input" riot-rows="{opts.rows || 10}" riot-placeholder="{opts.placeholder || \'Text...\'}"></textarea> <div class="uk-text-right uk-text-small uk-text-muted" ref="lengthIndicator" hide="{opts.showCount === false}"></div>', 'field-textarea [ref="lengthIndicator"],[data-is="field-textarea"] [ref="lengthIndicator"]{ font-family: monospace; }', '', function(opts) {

        var $this = this;

        this.on('mount', function() {

            if (opts.allowtabs) {

                this.refs.input.onkeydown = function(e) {
                    if (e.keyCode === 9) {
                        var val = this.value, start = this.selectionStart, end = this.selectionEnd;
                        this.value = val.substring(0, start) + '\t' + val.substring(end);
                        this.selectionStart = this.selectionEnd = start + 1;
                        return false;
                    }
                };

                this.refs.input.style.tabSize = opts.allowtabs;
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            (['maxlength', 'minlength', 'placeholder', 'cols', 'rows']).forEach( function(key) {
                if (opts[key]) $this.refs.input.setAttribute(key, opts[key]);
            });

            this.updateLengthIndicator();

            this.update();
        });

        this.$updateValue = function(value) {
            this.updateLengthIndicator();
        }.bind(this);

        this.updateLengthIndicator = function() {

            if (opts.showCount === false) {
                return;
            }

            this.refs.lengthIndicator.innerText = Math.abs((opts.maxlength || 0) - this.refs.input.value.length);
        }

});

riot.tag2('field-time', '<input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="text">', '', '', function(opts) {

        var $this = this;

        this.on('mount', function(){

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            App.assets.require(['/assets/lib/uikit/js/components/timepicker.js'], function() {

                UIkit.timepicker(this.refs.input, opts).element.on('change', function() {
                    $this.refs.input.$setValue($this.refs.input.value);
                });

            }.bind(this));
        });

});

riot.tag2('field-wysiwyg', '<textarea ref="input" class="uk-width-1-1" rows="5" style="height:350px;visibility:hidden;"></textarea>', '', '', function(opts) {

        var $this     = this,
            lang      = App.$data.user.i18n || document.documentElement.getAttribute('lang') || 'en',
            languages = ['ar','az','ba','bg','by','ca','cs','da','de','el','eo','es_ar','es','fa','fi','fr','ge','he','hr','hu','id','it','ja','ko','lt','lv','mk','nl','no_NB','pl','pt_br','pt_pt','ro','ru','sl','sq','sr-cir','sr-lat','sv','th','tr','ua','vi','zh_cn','zh_tw'],
            editor;

        this.value = null;

        this.$updateValue = function(value, field, force) {

            if (this.value != value) {

                if (typeof(value) != 'string') {
                    value = '';
                }

                this.value = value;

                if (editor && force) {
                    editor.setContent(this.value);
                }
            }

        }.bind(this);

        this.on('mount', function(){

            if (opts.editor && opts.editor.language) {
                lang = opts.editor.language;
            }

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.rows) {
                this.refs.input.setAttribute('rows', opts.rows);
            }

            if (!this.refs.input.id) {
                this.refs.input.id = 'wysiwyg-'+parseInt(Math.random()*10000000, 10);
            }

            var assets = [
                '/assets/lib/tinymce/tinymce.min.js'
            ];

            var plugins = [];

            App.assets.require(assets, function() {

                App.assets.require(plugins, function() {

                    initPlugins();

                    setTimeout(function(){

                        if (!App.$('#'+this.refs.input.id).length) return;

                        tinymce.init(App.$.extend(true, {
                            language: lang,
                            language_url : lang == 'en' ? '' : PUBLIC_STORAGE_URL + '/assets/cockpit/i18n/tinymce/'+lang+'.js',
                            branding: false,
                            resize: true,
                            height: 350,
                            menubar: 'edit insert view format table tools',
                            plugins: [
                                "link image lists preview hr anchor",
                                "code fullscreen media cpmediapath cpassetpath",
                                "cpcollectionlink",
                                "table contextmenu paste"
                            ],
                            relative_urls: false
                        }, opts.editor || {}, {

                          selector: '#'+this.refs.input.id,
                          setup: function (ed) {

                              $this.refs.input.value = $this.value || '';

                              var clbChange = function(e){
                                ed.save();
                                $this.$setValue($this.refs.input.value || '', true);
                              };

                              ed.on('ExecCommand', clbChange);
                              ed.on('KeyUp', clbChange);
                              ed.on('Change', clbChange);
                              ed.on('focus', function() {
                                $this.root.dispatchEvent(new Event('focusin', { bubbles: true, cancelable: true }));
                              });

                              var clbSave = function(){
                                var form = App.$($this.root).closest('form');

                                if (form.length) {
                                    form.trigger('submit', [ed]);
                                }
                              };

                              ed.addShortcut('ctrl+s','Save', clbSave, ed);
                              ed.addShortcut('meta+s','Save', clbSave, ed);

                              editor = ed;

                              App.$(document).trigger('init-wysiwyg-editor', [editor]);
                          }

                        }));

                    }.bind(this), 10);

                }.bind(this));

            }.bind(this)).catch(function(){

                this.refs.input.value = this.value || '';

                App.$(this.refs.input).css('visibility','').on('change', function() {
                    $this.$setValue(this.value || '');
                });

            }.bind(this));
        });

        function initPlugins() {

            if (initPlugins.done) return;

            tinymce.PluginManager.add('cpmediapath', function(editor) {

                if (App.$data.acl.finder) {

                    editor.addMenuItem('mediapath', {
                        icon: 'image',
                        text: App.i18n.get('Insert image (Finder)'),
                        onclick: function(){

                            App.media.select(function(selected) {
                                editor.insertContent('<img src="' + SITE_URL+'/'+selected + '" alt="">');
                            }, { typefilter:'image', pattern: '*.jpg|*.jpeg|*.png|*.gif|*.svg' });
                        },
                        context: 'insert',
                        prependToContext: true
                    });
                }

            });

            tinymce.PluginManager.add('cpassetpath', function(editor) {

                editor.addMenuItem('assetpath', {
                    icon: 'image',
                    text: App.i18n.get('Insert Asset (Assets)'),
                    onclick: function(){

                        App.assets.select(function(assets){

                            if (Array.isArray(assets) && assets[0]) {

                                var asset = assets[0], content;

                                if (asset.mime.match(/^image\//)) {
                                    content = '<img src="' + ASSETS_URL+asset.path + '" alt="">';
                                } else {
                                    content = '<a href="' + ASSETS_URL+asset.path + '">'+asset.title+'<a>';
                                }

                                editor.insertContent(content);
                            }
                        });

                    },
                    context: 'insert',
                    prependToContext: true
                });

            });

            initPlugins.done = true;
        }

        initPlugins.done = false;

});

riot.tag2('picoedit', '<div class="uk-text-xlarge uk-text-center uk-text-primary uk-margin-large-top" show="{!isReady}"> <cp-preloader class="uk-container-center"></cp-preloader> </div> <div class="picoedit" show="{isReady}"> <div class="picoedit-toolbar uk-flex" if="{path}"> <div class="uk-flex-item-1 uk-text-truncate"> <strong class="uk-text-small"><i class="uk-icon-pencil uk-margin-small-right"></i> {path}</strong> </div> <div> <button type="button" class="uk-button uk-button-primary" onclick="{save}"><i class="uk-icon-save"></i></button> </div> </div> <codemirror ref="codemirror" height="{opts.height || 400}" readonly="{opts.readonly || false}"></codemirror> </div>', 'picoedit .picoedit-toolbar,[data-is="picoedit"] .picoedit-toolbar{ padding-top: 15px; padding-bottom: 15px; }', '', function(opts) {

        var root  = this.root,
            $this = this,
            editor;

        this.isReady = false;
        root.picoedit = this;

        this.path = null;

        this.on('mount', function() {

            this.ready = new Promise(function(resolve){

                $this.tags.codemirror.on('ready', function(){

                    editor = $this.refs.codemirror.editor;

                    editor.addKeyMap({
                        'Ctrl-S': function(){ $this.save(); },
                        'Cmd-S': function(){ $this.save(); }
                    });

                    resolve();
                });
            });

            if (opts.path) {
                this.open(opts.path);
            }
        });

        this.open = function(path) {

            this.ready.then(function(){

                this.path = path;

                editor.setValue('');
                editor.clearHistory();

                requestapi({"cmd":"readfile", "path":path}, function(content){
                    editor.setOption("mode", getMode(path));
                    editor.focus();
                    $this.isReady = true;

                    this.update();

                    editor.setValue(content);
                    editor.refresh();

                }.bind(this), "text");

            }.bind(this));
        }.bind(this)

        this.save = function() {

            if (!this.path) return;

            requestapi({"cmd":"writefile", "path": this.path, "content":editor.getValue()}, function(status){

                App.ui.notify("File saved", "success");

            }, "text");
        }.bind(this)

        function requestapi(data, fn, type) {

            data = Object.assign({"cmd":""}, data);

            return App.request('/media/api', data, type).then(fn);
        }

        function getMode(path) {

            var def = CodeMirror.findModeByFileName(path) || {},
                mode = def.mode || 'text';

            if (mode == 'php') {
                mode = 'application/x-httpd-php';
            }

            return mode;
        }

});

riot.tag2('raw', '<span></span>', '', '', function(opts) {

        var cache = null;

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function(){

            if (cache==opts.content) return;

            this.root.innerHTML = opts.content;
            cache = opts.content;
        });

});
