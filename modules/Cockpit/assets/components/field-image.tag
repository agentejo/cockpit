<field-image>

    <div ref="uploadprogress" class="uk-margin uk-hidden">
        <div class="uk-progress">
            <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div>
        </div>
    </div>

    <div class="uk-display-block uk-panel uk-panel-box uk-panel-card uk-padding-remove">

        <div class="uk-flex uk-flex-middle uk-flex-center uk-text-muted">
            <div class="uk-width-1-1 uk-text-center uk-bg-transparent-pattern" if="{ image.path }">
                <cp-thumbnail src="{ image.path.match(/^(http\:|https\:|\/\/)/) ? image.path : (SITE_URL+'/'+image.path.replace(/^\//, '')) }" height="160"></cp-thumbnail>
            </div>
            <div class="uk-text-center uk-margin-top uk-margin-bottom" show="{ !image.path }">
                <img class="uk-svg-adjust uk-text-muted" riot-src="{App.base('/assets/app/media/icons/photo.svg')}" width="60" height="60" data-uk-svg>
                <div class="uk-margin-top">
                    <a class="uk-button uk-button-link" onclick="{ selectImage }" show="{App.$data.acl.finder}">{ App.i18n.get('Select Image') }</a>
                    <a class="uk-button uk-button-link" onclick="{ selectAsset }">{ App.i18n.get('Select Asset') }</a>
                    <a class="uk-button uk-button-link" onclick="{ editUrl }">{ App.i18n.get('Enter Image Url') }</a>
                </div>
            </div>
        </div>
        
        <div class="uk-panel-body" show="{ image.path }">
            <ul class="uk-grid uk-grid-small uk-flex-center ">
                <li data-uk-dropdown="pos:'bottom-center'">
                    <a class="uk-text-muted" onclick="{ selectAsset }" title="{ App.i18n.get('Select image') }" data-uk-tooltip><i class="uk-icon-image"></i></a>
                    <div class="uk-dropdown">
                        <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                            <li class="uk-nav-header">{ App.i18n.get('Source') }</li>
                            <li><a onclick="{ selectAsset }">{ App.i18n.get('Select Asset') }</a></li>
                            <li><a onclick="{ selectImage }" show="{App.$data.acl.finder}">{ App.i18n.get('Select Image') }</a></li>
                            <li><a onclick="{ editUrl }">{ App.i18n.get('Enter Image Url') }</a></li>
                        </ul>
                    </div>
                </li>
                <li><a class="uk-text-muted" onclick="{ showMeta }" title="{ App.i18n.get('Edit meta data') }" data-uk-tooltip><i class="uk-icon-cog"></i></a></li>
                <li><a class="uk-text-danger" onclick="{ remove }" title="{ App.i18n.get('Reset') }" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li>
            </ul>
        </div>

    </div>

    <div class="uk-modal uk-sortable-nodrag" ref="modalmeta">
        <div class="uk-modal-dialog">

            <div class="uk-modal-header"><h3>{ App.i18n.get('Image Meta') }</h3></div>

            <div class="uk-grid uk-grid-match uk-grid-gutter" if="{_meta}">

                <div class="uk-grid-margin uk-width-medium-{field.width}" each="{field, name in meta}" no-reorder>

                    <div class="uk-panel">

                        <label class="uk-text-small uk-text-bold">
                            <i class="uk-icon-pencil-square uk-margin-small-right"></i> { field.label || name }
                        </label>

                        <div class="uk-margin uk-text-small uk-text-muted">
                            { field.info || ' ' }
                        </div>

                        <div class="uk-margin">
                            <cp-field type="{ field.type || 'text' }" bind="image.meta['{name}']" opts="{ field.options || {} }"></cp-field>
                        </div>
                    </div>

                </div>
            </div>

            <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{ App.i18n.get('Close') }</button></div>

        </div>
    </div>


    <script>

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

            // handle uploads
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

        selectImage() {

            App.media.select(function(selected) {

                $this.image.path = selected[0];
                $this.$setValue($this.image);
                $this.update();

            }, { typefilter:'image', pattern: '*.jpg|*.jpeg|*.png|*.gif|*.svg' });
        }

        selectAsset() {

            App.assets.select(function(assets){

                if (Array.isArray(assets) && assets[0]) {

                    $this.image.path = ASSETS_URL.replace(SITE_URL, '')+assets[0].path;
                    $this.$setValue($this.image);
                    $this.update();
                }
                
            }, {typefilter: 'image'});
        }

        remove() {
            this.image = Object.create(_default);
            this.$setValue(this.image);
        }

        showMeta() {

            this._meta = this.image.meta || {};

            setTimeout(function() {
                UIkit.modal($this.refs.modalmeta, {modal:false}).show().one('close.uk.modal', function(){
                    $this._meta = null;
                });
            }, 50)
        }

        editUrl() {
            App.ui.prompt('Image Url', this.image.path, function (url) {
                $this.image.path = url;
                $this.$setValue($this.image);
                $this.update();
            });
        }

    </script>

</field-image>
