<cp-assets>

    <div class="uk-text-center uk-text-muted uk-h2" show="{ loading }">
        <i class="uk-icon-spinner uk-icon-spin"></i>
    </div>

    <div class="uk-form" name="list" show="{ !loading && mode=='list' }">

        <div class="uk-grid uk-grid-width-1-2">
            <div>
                <div class="uk-grid">
                    <div class="uk-flex-item-1">
                        <div class="uk-form-icon uk-display-block uk-width-1-1">
                            <i class="uk-icon-search"></i>
                            <input class="uk-width-1-1 uk-form-large" type="text" name="filter" onchange="{ listAssets }">
                        </div>
                    </div>
                    <div>
                        <select class="uk-form-large">
                            <option>All</option>
                            <option>Image</option>
                            <option>Video</option>
                            <option>Audio</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="uk-text-right">

                <button class="uk-button uk-button-large uk-button-danger" type="button" onclick="{ removeSelected }" show="{ selected.length }">
                    { App.i18n.get('Delete') } <span class="uk-badge uk-badge-contrast uk-margin-small-left">{ selected.length }</span>
                </button>

                <span class="uk-button-group uk-button-large">
                    <button class="uk-button uk-button-large {listmode=='list' ? 'uk-button-primary':''}" type="button" onclick="{ toggleListMode }"><i class="uk-icon-list"></i></button>
                    <button class="uk-button uk-button-large {listmode=='grid' ? 'uk-button-primary':''}" type="button" onclick="{ toggleListMode }"><i class="uk-icon-th"></i></button>
                </span>

                <span class="uk-button uk-button-large uk-button-primary uk-margin-small-right uk-form-file">
                    <input class="js-upload-select" type="file" multiple="true">
                    <i class="uk-icon-upload"></i>
                </span>
            </div>
        </div>

        <div name="uploadprogress" class="uk-margin uk-hidden">
            <div class="uk-progress">
                <div name="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div>
            </div>
        </div>

        <div class="uk-margin-large-top uk-panel-space uk-text-center" show="{ !assets.length }">
            <span class="uk-text-muted uk-h2">{ App.i18n.get('No Assets found') }</span>
        </div>

        <div class="uk-margin-large-top" if="{ assets.length }">

            <div class="uk-grid uk-grid-small uk-grid-width-medium-1-5" if="{ listmode=='grid' }">
                <div class="uk-grid-margin" each="{ asset,idx in assets }" each="{ asset,idx in assets }" onclick="{ select }">
                    <div class="uk-panel uk-panel-box uk-panel-card { selected.length && selected.indexOf(asset) != -1 ? 'uk-selected':''}">
                        <div class="uk-overlay uk-display-block uk-position-relative">
                            <canvas class="uk-responsive-width" width="200" height="150"></canvas>
                            <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle">
                                <div class="uk-width-1-1 uk-text-center">
                                    <span if="{ asset.mime.match(/^image\//) == null }"><i class="uk-h1 uk-icon-paperclip"></i></span>

                                    <a href="{ASSETS_URL+asset.path}" if="{ asset.mime.match(/^image\//) }" data-uk-lightbox="type:'image'" title="{ asset.width && [asset.width, asset.height].join('x') }">
                                        <cp-thumbnail src="{ASSETS_URL+asset.path}" width="100" height="75"></cp-thumbnail>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="uk-margin-small-top uk-text-truncate"><a onclick="{ parent.edit }">{ asset.name }</a></div>
                        <div class="uk-text-small uk-text-muted">
                            <strong>{ asset.mime }</strong>
                            { App.Utils.formatSize(asset.size) }
                        </div>
                    </div>
                </div>
            </div>

            <table class="uk-table uk-panel-card" if="{ listmode=='list' }">
                <thead>
                    <tr>
                        <td width="30"></td>
                        <th>{ App.i18n.get('Name') }</th>
                        <th width="20%">{ App.i18n.get('Type') }</th>
                        <th width="10%">{ App.i18n.get('Size') }</th>
                        <th width="10%">{ App.i18n.get('Updated') }</th>
                        <th width="30"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="{ selected.length && selected.indexOf(asset) != -1 ? 'uk-selected':''}" each="{ asset,idx in assets }" onclick="{ select }">
                        <td class="uk-text-center">

                            <span if="{ asset.mime.match(/^image\//) == null }"><i class="uk-icon-paperclip"></i></span>

                            <a href="{ASSETS_URL+asset.path}" if="{ asset.mime.match(/^image\//) }" data-uk-lightbox="type:'image'" title="{ asset.width && [asset.width, asset.height].join('x') }">
                                <cp-thumbnail src="{ASSETS_URL+asset.path}" width="20" height="20"></cp-thumbnail>
                            </a>
                        </td>
                        <td><a onclick="{ parent.edit }">{ asset.name }</a></td>
                        <td class="uk-text-small">{ asset.mime }</td>
                        <td class="uk-text-small">{ App.Utils.formatSize(asset.size) }</td>
                        <td class="uk-text-small">{ App.Utils.dateformat( new Date( 1000 * asset.modified )) }</td>
                        <td>
                            <span class="uk-float-right" data-uk-dropdown="\{mode:'click'\}">

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

    <div class="uk-form" show="{asset && mode=='edit'}">
        <form onsubmit="{ updateAsset }">
            <div class="uk-grid">
                <div class="uk-width-2-3">
                    <div class="uk-form-row">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Name') }</label>
                        <input class="uk-width-1-1" type="text" name="assetname" required>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Description') }</label>
                        <textarea class="uk-width-1-1" name="assetdescription"></textarea>
                    </div>

                    <div class="uk-margin uk-panel uk-panel-box uk-panel-space uk-text-center">
                        <span class="uk-h1" if="{ asset && asset.mime.match(/^image\//) == null }"><i class="uk-icon-paperclip"></i></span>
                        <cp-thumbnail src="{ASSETS_URL+asset.path}" width="400" height="250" if="{ asset && asset.mime.match(/^image\//) }"></cp-thumbnail>
                    </div>
                </div>
                <div class="uk-width-1-3">

                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Id') }</label>
                        <div class="uk-margin-small-top uk-text-muted">{ asset._id }</div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Type') }</label>
                        <div class="uk-margin-small-top uk-text-muted">{ asset.mime }</div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Size') }</label>
                        <div class="uk-margin-small-top uk-text-muted">{ App.Utils.formatSize(asset.size) }</div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Created') }</label>
                        <div class="uk-margin-small-top uk-text-muted">{ App.Utils.dateformat( new Date( 1000 * asset.modified )) }</div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-text-small uk-text-bold">{ App.i18n.get('Url') }</label>
                        <div class="uk-margin-small-top uk-text-truncate uk-text-muted"><a href="{ASSETS_URL+asset.path}" target="_blank">{ASSETS_URL+asset.path}</a></div>
                    </div>

                </div>
            </div>

            <div class="uk-margin">
                <button type="submit" class="uk-button uk-button-large uk-button-primary uk-margin-small-right">{ App.i18n.get('Save') }</button>
                <a onclick="{ cancelEdit }">{ App.i18n.get('Cancel') }</a>
            </div>

        </form>

    </div>


    <script>

        var $this = this;

        this.mode     = 'list';
        this.listmode = App.session.get('app.assets.listmode', 'list');
        this.loading  = false;
        this.selected = [];

        this.on('mount', function() {

            this.listAssets();

            // handle uploads
            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                var uploadSettings = {

                        action: App.route('/assetsmanager/upload'),
                        type: 'json',
                        before: function(options) {

                        },
                        loadstart: function() {
                            $this.uploadprogress.classList.remove('uk-hidden');
                        },
                        progress: function(percent) {

                            percent = Math.ceil(percent) + '%';

                            $this.progressbar.innerHTML   = '<span>'+percent+'</span>';
                            $this.progressbar.style.width = percent;
                        },
                        allcomplete: function(response) {

                            $this.uploadprogress.classList.add('uk-hidden');

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

                                $this.update();
                            }

                            if (!response) {
                                App.ui.notify("Something went wrong.", "danger");
                            }

                        }
                },

                uploadselect = UIkit.uploadSelect(App.$('.js-upload-select', $this.root)[0], uploadSettings),
                uploaddrop   = UIkit.uploadDrop($this.list, uploadSettings);

                UIkit.init(this.root);
            });

        });

        toggleListMode() {
            this.listmode = this.listmode=='list' ? 'grid':'list';
            App.session.set('app.assets.listmode', this.listmode);
        }

        listAssets() {

            this.loading = true;

            var options = {};

            if (this.filter.value) {
                options.filter = {
                    'name':{'$regex':this.filter.value}
                };
            }

            App.request('/assetsmanager/listAssets', options).then(function(assets){

                $this.assets = Array.isArray(assets) ? assets:[];
                $this.loading = false;
                $this.selected = [];
                $this.update();
            });

        }

        remove(e) {
            var asset = e.item.asset,
                idx   = e.item.idx;

            App.ui.confirm("Are you sure?", function() {

                App.request('/assetsmanager/removeAssets', {assets:[asset]}).then(function(data) {

                    App.ui.notify("Asset removed", "success");

                    $this.assets.splice(idx, 1);

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

                    App.ui.notify("Assets removed", "success");
                    $this.selected = [];
                    $this.update();
                });
            });

        }

        edit(e) {
            this.asset = e.item.asset;
            this.mode  = 'edit';
            this.assetname.value = this.asset.name;
            this.assetdescription.value = this.asset.description;
        }

        cancelEdit() {
            this.asset = null;
            this.mode  = 'list';
        }

        updateAsset(e) {

            e.preventDefault();

            this.asset.name = this.assetname.value;
            this.asset.description = this.assetdescription.value;

            App.request('/assetsmanager/updateAsset', {asset:$this.asset}).then(function(asset) {

                $this.asset = asset;

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
        }


    </script>

</cp-assets>
