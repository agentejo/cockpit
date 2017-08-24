<field-image>

    <figure class="uk-display-block uk-panel uk-panel-box uk-panel-card uk-overlay uk-overlay-hover">

        <div class="uk-flex uk-flex-middle uk-flex-center uk-text-muted">
            <div class="uk-width-1-1" show="{ image.path }" riot-style="min-height:160px;background-size:contain;background-repeat:no-repeat;background-position:50% 50%;{ image.path ? 'background-image: url('+(image.path.match(/^(http\:|https\:|\/\/)/) ? image.path:encodeURI(SITE_URL+'/'+image.path))+')':''}"></div>
            <div class="uk-text-center uk-margin-top uk-margin-bottom" show="{ !image.path }">
                <img class="uk-svg-adjust uk-text-muted" riot-src="{App.base('/assets/app/media/icons/photo.svg')}" width="60" data-uk-svg>
                <div class="uk-margin-top">
                    <a class="uk-button uk-button-link" onclick="{ selectImage }">{ App.i18n.get('Select Image') }</a>
                    <a class="uk-button uk-button-link" onclick="{ selectAsset }">{ App.i18n.get('Select Asset') }</a>
                    <a class="uk-button uk-button-link" onclick="{ editUrl }">{ App.i18n.get('Enter Image Url') }</a>
                </div>
            </div>
        </div>

        <figcaption class="uk-overlay-panel uk-overlay-background" show="{ image.path }">

            <ul class="uk-subnav">
                <li><a onclick="{ selectImage }" title="{ App.i18n.get('Select image') }" data-uk-tooltip><i class="uk-icon-image"></i></a></li>
                <li><a onclick="{ editUrl }" title="{ App.i18n.get('Edit Image Url') }" data-uk-tooltip><i class="uk-icon-link"></i></a></li>
                <li><a onclick="{ showMeta }" title="{ App.i18n.get('Edit meta data') }" data-uk-tooltip><i class="uk-icon-cog"></i></a></li>
                <li><a onclick="{ remove }" title="{ App.i18n.get('Reset') }" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li>
            </ul>

            <p class="uk-text-small uk-text-truncate">{ image.title }</p>

        </figcaption>
    </figure>

    <div class="uk-modal uk-sortable-nodrag" ref="modalmeta">
        <div class="uk-modal-dialog">

            <div class="uk-modal-header"><h3>{ App.i18n.get('Image Meta') }</h3></div>

            <div class="uk-grid uk-grid-match uk-grid-gutter" if="{_meta}">

                <div class="uk-grid-margin uk-width-medium-{field.width}" each="{field, name in meta}" no-reorder>

                    <div class="uk-panel">

                        <label class="uk-text-bold">
                            { field.label || name }
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

        this.on('mount', function() { this.trigger('update'); });
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
        });

        this.$updateValue = function(value, field) {

            value = value || Object.create(_default);

            if (JSON.stringify(this.image) !== JSON.stringify(value)) {
                this.image = value;
                this.update();
            }

        }.bind(this);

        selectImage() {

            App.media.select(function(selected) {

                $this.image.path = selected[0];
                $this.$setValue($this.image);
                $this.update();

            }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });
        }

        selectAsset() {

            App.assets.select(function(assets){

                if (Array.isArray(assets) && assets[0]) {

                    $this.image.path = ASSETS_URL.replace(SITE_URL, '')+assets[0].path;
                    $this.$setValue($this.image);
                    $this.update();
                }
            });
        }

        remove() {
            this.$setValue({path:'', title:'', meta:{title:''}});
        }

        showMeta() {

            this._meta = this.image.meta;

            setTimeout(function() {
                UIkit.modal($this.refs.modalmeta, {modal:false}).show().on('close.uk.modal', function(){
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
