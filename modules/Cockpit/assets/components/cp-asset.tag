<cp-asset if="{asset}">

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


    <div class="uk-form">

        <h3 class="uk-text-bold">{ App.i18n.get('Edit Asset') }</h3>

        <form onsubmit="{ updateAsset }">

            <ul class="uk-tab uk-flex-center uk-margin" show="{ App.Utils.count(panels) }">
                <li class="{!panel && 'uk-active'}"><a onclick="{selectPanel}">Main</a></li>
                <li class="uk-text-capitalize {p.name == panel && 'uk-active'}" each="{p in panels}"><a onclick="{selectPanel}">{p.name}</a></li>
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

            <div class="uk-margin-large-top">
                <button type="submit" class="uk-button uk-button-large uk-button-primary">{ App.i18n.get('Save') }</button>
                <a class="uk-button uk-button-large uk-button-link" onclick="{ cancelEdit }">{ App.i18n.get('Cancel') }</a>
            </div>

        </form>

    </div>

    <script>

        this.mixin(RiotBindMixin);

        this.asset = opts.asset || false;

        this.panel    = null;
        this.panels   = [];


        for (var tag in riot.tags) {

            if (tag.indexOf('assetspanel-')==0) {

                f = tag.replace('assetspanel-', '');

                this.panels.push({name:f, value:f});
            }
        }

        var $this = this

        this.on('mount', function() {
            setTimeout(function() {
                $this.placeFocalPoint($this.asset.fp);
            }, 500)

            App.$(this.root).on('click', '.asset-fp-image canvas', function(e) {

                var x = e.offsetX, y = e.offsetY,
                    px = (x / this.offsetWidth),
                    py = (y / this.offsetHeight);

                $this.asset.fp = {x: px, y: py};
                $this.placeFocalPoint($this.asset.fp);
            });

        });

        updateAsset(e) {

            e.preventDefault();

            App.request('/assetsmanager/updateAsset', {asset:$this.asset}).then(function(asset) {
                App.$.extend($this.asset, asset);
                App.ui.notify("Asset updated", "success");
                $this.parent.updateAsset($this.asset);
            });

            return false;
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

        cancelEdit() {
            $this.parent.cancelEdit();
        }

        selectPanel(e) {
            this.panel = e.item ? e.item.p.name : null;
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
