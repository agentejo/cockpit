<cp-finder>

    <style>

        .uk-offcanvas[ref=editor] .CodeMirror {
            height: auto;
        }

        .uk-offcanvas[ref=editor] .picoedit-toolbar {
            padding-left: 15px;
            padding-right: 15px;
        }

        .uk-modal .uk-panel-box.finder-folder,
        .uk-modal .uk-panel-box.finder-file {
            border: 1px rgba(0,0,0,0.1) solid;
        }

        .picoedit-toolbar {
            -webkit-position: sticky;
            position: sticky;
            top: 0;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            background: #fff;
            z-index: 10;
        }

    </style>

    <div show="{ App.Utils.count(data) }">

        <div class="uk-clearfix" data-uk-margin>

            <div class="uk-float-left">

                <span class="uk-button-group uk-margin-right">
                    <button class="uk-button uk-button-large {listmode=='list' && 'uk-button-primary'}" type="button" onclick="{ toggleListMode }"><i class="uk-icon-list"></i></button>
                    <button class="uk-button uk-button-large {listmode=='grid' && 'uk-button-primary'}" type="button" onclick="{ toggleListMode }"><i class="uk-icon-th"></i></button>
                </span>

                <div class="uk-form uk-form-icon uk-display-inline-block">
                    <i class="uk-icon-filter"></i>
                    <input ref="filter" type="text" class="uk-form-large" onkeyup="{ updatefilter }">
                </div>

                <span class="uk-margin-left" data-uk-dropdown="mode:'click'">

                    <a class="uk-text-{sortBy == 'name' ? 'muted':'primary'}" title="Sort files" data-uk-tooltip="pos:'right'"><i class="uk-icon-sort"></i> { App.Utils.ucfirst(sortBy) }</a>

                    <div class="uk-dropdown uk-margin-top uk-text-left">
                        <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                            <li class="uk-nav-header">{ App.i18n.get('Sort by') }</li>
                            <li><a class="uk-dropdown-close" onclick="{ doSortBy.bind(this, 'name') }">{ App.i18n.get('Name') }</a></li>
                            <li><a class="uk-dropdown-close" onclick="{ doSortBy.bind(this, 'filesize') }">{ App.i18n.get('Filesize') }</a></li>
                            <li><a class="uk-dropdown-close" onclick="{ doSortBy.bind(this, 'mime') }">{ App.i18n.get('Type') }</a></li>
                            <li><a class="uk-dropdown-close" onclick="{ doSortBy.bind(this, 'modified') }">{ App.i18n.get('Modified') }</a></li>
                        </ul>
                    </div>

                </span>

            </div>

            <div class="uk-float-right">

                <span class="uk-margin-right uk-position-relative" data-uk-dropdown="mode:'click', pos:'bottom-right'">

                    <button class="uk-button uk-button-outline uk-text-primary uk-button-large"><i class="uk-icon-magic"></i></button>

                    <div class="uk-dropdown uk-margin-top uk-text-left">
                        <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                            <li class="uk-nav-header">Create</li>
                            <li><a onclick="{ createfolder }"><i class="uk-icon-folder-o uk-icon-justify"></i> Folder</a></li>
                            <li><a onclick="{ createfile }"><i class="uk-icon-file-o uk-icon-justify"></i> File</a></li>
                        </ul>
                    </div>

                </span>

                <span class="uk-button-group">

                    <span class="uk-button uk-button-large uk-button-primary uk-form-file">
                        <input class="js-upload-select" type="file" multiple="true" title="">
                        <i class="uk-icon-upload"></i>
                    </span>

                    <button class="uk-button uk-button-large" onclick="{ refresh }">
                        <i class="uk-icon-refresh"></i>
                    </button>
                </span>

                <span class="uk-margin-left" if="{ selected.count }" data-uk-dropdown="mode:'click', pos:'bottom-right'">
                    <span class="uk-button uk-button-large"><strong>Batch:</strong> { selected.count } &nbsp;<i class="uk-icon-caret-down"></i></span>
                    <div class="uk-dropdown uk-margin-top uk-text-left">
                        <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                            <li class="uk-nav-header">Batch action</li>
                            <li class="uk-nav-item-danger"><a onclick="{ removeSelected }">Delete</a></li>
                        </ul>
                    </div>
                </span>

            </div>

        </div>

        <div class="uk-grid uk-grid-divider uk-margin-large-top" data-uk-grid-margin>

            <div class="uk-width-medium-1-4">

                <div class="uk-panel">

                    <ul class="uk-nav uk-nav-side">
                        <li class="uk-nav-header">Display</li>
                        <li class="{ !typefilter ? 'uk-active':'' }"><a data-type="" onclick="{ settypefilter }"><i class="uk-icon-circle-o uk-icon-justify"></i> All</a></li>
                        <li class="{ typefilter=='image' ? 'uk-active':'' }"><a data-type="image" onclick="{ settypefilter }"><i class="uk-icon-image uk-icon-justify"></i> Images</a></li>
                        <li class="{ typefilter=='video' ? 'uk-active':'' }"><a data-type="video" onclick="{ settypefilter }"><i class="uk-icon-video-camera uk-icon-justify"></i> Video</a></li>
                        <li class="{ typefilter=='audio' ? 'uk-active':'' }"><a data-type="audio" onclick="{ settypefilter }"><i class="uk-icon-volume-up uk-icon-justify"></i> Audio</a></li>
                        <li class="{ typefilter=='document' ? 'uk-active':'' }"><a data-type="document" onclick="{ settypefilter }"><i class="uk-icon-paper-plane uk-icon-justify"></i> Documents</a></li>
                        <li class="{ typefilter=='archive' ? 'uk-active':'' }"><a data-type="archive" onclick="{ settypefilter }"><i class="uk-icon-archive uk-icon-justify"></i> Archives</a></li>
                    </ul>
                </div>

            </div>

            <div class="uk-width-medium-3-4">

                <div class="uk-panel">
                    <ul class="uk-breadcrumb">
                        <li onclick="{ changedir }"><a title="Change dir to root"><i class="uk-icon-home"></i></a></li>
                        <li each="{folder, idx in breadcrumbs}"><a onclick="{ parent.changedir }" title="Change dir to { folder.name }">{ folder.name }</a></li>
                    </ul>
                </div>

                <div ref="uploadprogress" class="uk-margin uk-hidden">
                    <div class="uk-progress">
                        <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div>
                    </div>
                </div>

                <div class="uk-alert uk-text-center uk-margin" if="{ data && (this.typefilter || this.refs.filter.value) && (data.folders.length || data.files.length) }">
                     Filter is active
                </div>

                <div class="uk-alert uk-text-center uk-margin" if="{ data && (!data.folders.length && !data.files.length) }">
                    This is an empty folder
                </div>

                <div class="{modal && 'uk-overflow-container'}">

                    <div class="uk-margin-top" if="{data && data.folders.length}">

                        <strong class="uk-text-small uk-text-muted" if="{ !(this.refs.filter.value) }"><i class="uk-icon-folder-o uk-margin-small-right"></i> { data.folders.length } Folders</strong>

                        <ul class="uk-grid uk-grid-small uk-grid-match uk-grid-width-1-2 uk-grid-width-medium-1-4">

                            <li class="uk-grid-margin" each="{folder, idx in data.folders}" onclick="{ select }" if="{ infilter(folder) }">
                                <div class="uk-panel uk-panel-box finder-folder { folder.selected ? 'uk-selected':'' }">
                                    <div class="uk-flex">
                                        <div class="uk-margin-small-right">
                                            <i class="uk-icon-folder-o uk-text-muted js-no-item-select"></i>
                                        </div>
                                        <div class="uk-flex-item-1 uk-margin-small-right uk-text-truncate">
                                            <a class="uk-link-muted uk-noselect" onclick="{ parent.changedir }"><strong>{ folder.name }</strong></a>
                                        </div>
                                        <div>
                                            <span data-uk-dropdown="mode:'click', pos:'bottom-right'">
                                                <a><i class="uk-icon-ellipsis-v js-no-item-select"></i></a>
                                                <div class="uk-dropdown">
                                                    <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                                                        <li class="uk-nav-header uk-text-truncate">{ folder.name }</li>
                                                        <li><a class="uk-dropdown-close"onclick="{ parent.download }">Download</a></li>
                                                        <li><a class="uk-dropdown-close" onclick="{ parent.rename }">Rename</a></li>
                                                        <li class="uk-nav-divider"></li>
                                                        <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ parent.remove }">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </li>

                        </ul>

                    </div>

                    <div class="uk-margin-top" if="{data && data.files.length}">

                        <strong class="uk-text-small uk-text-muted" if="{ !(this.typefilter || this.refs.filter.value) }"><i class="uk-icon-file-o uk-margin-small-right"></i> { data.files.length } Files</strong>

                        <ul class="uk-grid uk-grid-small uk-grid-match uk-grid-width-1-2 uk-grid-width-medium-1-4" if="{ listmode=='grid' }">

                            <li class="uk-grid-margin" each="{file, idx in data.files}" onclick="{ select }" if="{ infilter(file) }">
                                <div class="uk-panel uk-panel-box finder-file { file.selected ? 'uk-selected':'' }">

                                    <div class="uk-panel-teaser uk-cover-background uk-position-relative">
                                        <div if="{ parent.getIconCls(file) != 'image' }">
                                            <canvas class="uk-responsive-width uk-display-block" width="400" height="300"></canvas>
                                            <div class="uk-position-center"><i class="uk-text-large uk-text-muted uk-icon-{ parent.getIconCls(file) }"></i></div>
                                        </div>
                                        <cp-thumbnail src="{file.url}" width="400" height="300" if="{ parent.getIconCls(file) == 'image' }"></cp-thumbnail>
                                    </div>


                                    <div class="uk-flex">
                                        <a class="uk-link-muted uk-flex-item-1 js-no-item-select uk-text-truncate uk-margin-small-right" onclick="{ parent.open }">{ file.name }</a>
                                        <span class="uk-margin-small-right" data-uk-dropdown="mode:'click', pos:'bottom-right'">
                                            <a><i class="uk-icon-ellipsis-v js-no-item-select"></i></a>
                                            <div class="uk-dropdown">
                                                <ul class="uk-nav uk-nav-dropdown">
                                                    <li class="uk-nav-header uk-text-truncate">{ file.name }</li>
                                                    <li><a class="uk-link-muted uk-dropdown-close js-no-item-select" onclick="{ parent.open }">Open</a></li>
                                                    <li><a class="uk-dropdown-close" onclick="{ parent.rename }">Rename</a></li>
                                                    <li><a class="uk-dropdown-close" onclick="{ parent.download }">Download</a></li>
                                                    <li if="{ file.ext == 'zip' }"><a onclick="{ parent.unzip }">Unzip</a></li>
                                                    <li class="uk-nav-divider"></li>
                                                    <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ parent.remove }">Delete</a></li>
                                                </ul>
                                            </div>
                                        </span>
                                    </div>

                                    <div class="uk-margin-small-top uk-text-small uk-text-muted">
                                        { file.size }
                                    </div>


                                </div>
                            </li>
                        </ul>

                        <table class="uk-table uk-panel-card" if="{ listmode=='list' && data.files.length }">
                            <thead>
                                <tr>
                                    <td width="30"></td>
                                    <th>{ App.i18n.get('Name') }</th>
                                    <th width="10%">{ App.i18n.get('Size') }</th>
                                    <th width="15%">{ App.i18n.get('Updated') }</th>
                                    <th width="30"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="{ file.selected ? 'uk-selected':'' }" each="{file, idx in data.files}" onclick="{ select }" if="{ infilter(file) }">
                                    <td class="uk-text-center">
                                        <span if="{ parent.getIconCls(file) != 'image' }"><i class="uk-text-muted uk-icon-{ parent.getIconCls(file) }"></i></span>
                                        <cp-thumbnail src="{file.url}" width="400" height="300" if="{ parent.getIconCls(file) == 'image' }"></cp-thumbnail>
                                    </td>
                                    <td><a class="js-no-item-select" onclick="{ parent.open }">{ file.name }</a></td>
                                    <td class="uk-text-small">{ file.size }</td>
                                    <td class="uk-text-small">{ App.Utils.dateformat( new Date( 1000 * file.modified )) }</td>
                                    <td>
                                        <span class="uk-float-right" data-uk-dropdown="mode:'click'">

                                            <a class="uk-icon-ellipsis-v"></a>

                                            <div class="uk-dropdown uk-dropdown-flip">
                                                <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                                                    <li class="uk-nav-header">{ App.i18n.get('Actions') }</li>
                                                    <li><a class="uk-link-muted uk-dropdown-close js-no-item-select" onclick="{ parent.open }">Open</a></li>
                                                    <li><a class="uk-dropdown-close" onclick="{ parent.rename }">Rename</a></li>
                                                    <li><a class="uk-dropdown-close" onclick="{ parent.download }">Download</a></li>
                                                    <li if="{ file.ext == 'zip' }"><a onclick="{ parent.unzip }">Unzip</a></li>
                                                    <li class="uk-nav-divider"></li>
                                                    <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ parent.remove }">Delete</a></li>
                                                </ul>
                                            </div>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>

        </div>

        <div ref="editor" class="uk-offcanvas">
            <div class="uk-offcanvas-bar uk-width-3-4">
                <picoedit height="auto"></picoedit>
            </div>
        </div>

    </div>


    <script>

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

            // handle uploads
            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

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
                                App.ui.notify("File(s) failed to uploaded.", "danger");
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

                UIkit.init(this.root);
            });
        });

        changedir(e, path) {

            if (e && e.item) {
                e.preventDefault();
                e.stopPropagation();
                path = e.item.folder.path;
            } else {
                path = opts.root;
            }

            this.loadPath(path);
        }

        open(e) {

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

            } else if(name.match(typefilters.text)) {

                UIkit.offcanvas.show(this.refs.editor);
                this.tags.picoedit.open(file.path);

            } else {
                App.ui.notify("Filetype not supported");
            }
        }

        refresh() {
            this.loadPath().then(function(){
                App.ui.notify('Folder reloaded');
            });
        }

        select(e, force) {

            if (e && e.item && force || !e.target.classList.contains('js-no-item-select') && !App.$(e.target).parents('.js-no-item-select').length) {

                // remove any text selection
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
        }

        rename(e, item) {

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
        }

        download(e, item) {

            e.stopPropagation();

            item = e.item.file || e.item.folder;

            window.open(App.route('/media/api?cmd=download&path='+item.path));
        }

        unzip(e, item) {

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
        }

        remove(e, item, index) {

            e.stopPropagation();

            item = e.item.folder || e.item.file;

            App.ui.confirm("Are you sure?", function() {

                requestapi({"cmd":"removefiles", "paths": item.path}, function(){

                    index = $this.data[item.is_file ? "files":"folders"].indexOf(item);

                    $this.data[item.is_file ? "files":"folders"].splice(index, 1);

                    App.ui.notify("Item(s) deleted", "success");

                    $this.update();
                });
            });
        }

        removeSelected() {

            var paths = Object.keys(this.selected.paths);

            if (paths.length) {

                App.ui.confirm("Are you sure?", function() {

                    requestapi({"cmd":"removefiles", "paths": paths}, function(){
                        $this.loadPath();
                        App.ui.notify("File(s) deleted", "success");
                    });
                });
            }
        }

        createfolder() {

            App.ui.prompt("Please enter a folder name:", "", function(name){

                if (name.trim()) {
                    requestapi({"cmd":"createfolder", "path": $this.currentpath, "name":name}, function(){
                        $this.loadPath();
                    });
                }
            });
        }

        createfile() {

            App.ui.prompt("Please enter a file name:", "", function(name){

                if (name.trim()) {
                    requestapi({"cmd":"createfile", "path": $this.currentpath, "name":name}, function(){
                        $this.loadPath();
                    });
                }
            });
        }

        loadPath(path, defer) {

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
        }

        settypefilter(e) {
            e.preventDefault();

            this.typefilter = e.target.dataset.type;
            this.resetselected();
        }

        updatefilter(e) {
            this.resetselected();
        }

        infilter(item) {

            var name = item.name.toLowerCase();

            if (this.typefilter && item.is_file && typefilters[this.typefilter]) {

                if (!name.match(typefilters[this.typefilter])) {
                    return false;
                }
            }

            return (!this.refs.filter.value || (name && name.indexOf(this.refs.filter.value.toLowerCase()) !== -1));
        }

        resetselected() {

            if (this.selected.paths) {
                Object.keys(this.selected.paths).forEach(function(path) {
                    $this.selected.paths[path].selected = false;
                });
            }

            this.selected  = {count:0, paths:{}};

            if (opts.onChangeSelect) {
                opts.onChangeSelect(this.selected);
            }
        }

        getIconCls(file) {

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
        }


        function requestapi(data, fn, type) {

            data = Object.assign({"cmd":""}, data);

            App.request('/media/api', data).then(fn);
        }

        doSortBy(sortby) {
            this.sortBy = sortby;

            $this.data.files = $this.data.files.sort(function(a,b) {
                a = $this.sortBy == 'name' ? a[$this.sortBy].toLowerCase() : a[$this.sortBy];
                b =  $this.sortBy == 'name' ? b[$this.sortBy].toLowerCase() : b[$this.sortBy];
                if (a < b) return -1;
                if (a> b) return 1;
                return 0;
            });
        }

        toggleListMode() {
            this.listmode = this.listmode=='list' ? 'grid':'list';
            App.session.set('app.finder.listmode', this.listmode);
        }


    </script>

</cp-finder>
