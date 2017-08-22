<field-gallery>

    <div ref="panel">

        <div ref="imagescontainer" class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-flex-center uk-grid-gutter uk-grid-width-medium-1-4" show="{ images && images.length }">
            <div data-idx="{ idx }" each="{ img,idx in images }">
                <div class="uk-panel uk-panel-box uk-panel-thumbnail uk-panel-card">
                    <figure class="uk-display-block uk-overlay uk-overlay-hover">
                        <div class="uk-flex uk-flex-middle uk-flex-center" style="min-height:120px;">
                            <div class="uk-width-1-1 uk-text-center">
                                <cp-thumbnail src="{ (SITE_URL+'/'+img.path) }" width="400" height="250"></cp-thumbnail>
                            </div>
                        </div>
                        <figcaption class="uk-overlay-panel uk-overlay-background uk-flex uk-flex-middle uk-flex-center">

                            <div>
                                <ul class="uk-subnav">
                                    <li><a onclick="{ parent.showMeta }" title="{ App.i18n.get('Edit meta data') }" data-uk-tooltip><i class="uk-icon-cog"></i></a></li>
                                    <li><a href="{ (SITE_URL+'/'+img.path) }" data-uk-lightbox="type:'image'" title="{ App.i18n.get('Full size') }" data-uk-tooltip><i class="uk-icon-eye"></i></a></li>
                                    <li><a onclick="{ parent.remove }" title="{ App.i18n.get('Remove image') }" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li>
                                </ul>

                                <p class="uk-text-small uk-text-truncate">{ img.title }</p>
                            </div>

                        </figcaption>
                    </figure>
                </div>

            </div>
        </div>

        <div class="uk-text-center {images && images.length ? 'uk-margin-top':'' }">
            <div class="uk-text-muted" if="{ images && !images.length }">
                <img class="uk-svg-adjust" riot-src="{ App.base('/assets/app/media/icons/gallery.svg') }" width="100" data-uk-svg>
                <p>{ App.i18n.get('Gallery is empty') }</p>
            </div>
            <div class="uk-display-inline-block uk-position-relative" data-uk-dropdown="pos:'bottom-center'">
                <a class="uk-button uk-text-primary uk-button-outline uk-button-large" onclick="{ selectimages }">
                    <i class="uk-icon-plus-circle" title="{ App.i18n.get('Add images') }" data-uk-tooltip></i>
                </a>
                <div class="uk-dropdown">
                    <ul class="uk-nav uk-nav-dropdown uk-text-left uk-dropdown-close">
                        <li class="uk-nav-header">{ App.i18n.get('Select') }</li>
                        <li><a onclick="{ selectimages }">File</a></li>
                        <li><a onclick="{ selectAssetsImages }">Asset</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="uk-modal uk-sortable-nodrag" ref="modalmeta">
            <div class="uk-modal-dialog">

                <div class="uk-modal-header"><h3>{ App.i18n.get('Image Meta') }</h3></div>

                <div class="uk-grid uk-grid-match uk-grid-gutter" if="{image}">

                    <div class="uk-grid-margin uk-width-medium-{field.width}" each="{field,name in meta}" no-reorder>

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

    </div>

    <script>

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

                // hack to force complete images rebuild
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

        showMeta(e) {

            this.image = this.images[e.item.idx];

            setTimeout(function() {
                UIkit.modal($this.refs.modalmeta).show().on('close.uk.modal', function(){
                    $this.image = null;
                });
            }, 50)
        }

        selectimages() {

            App.media.select(function(selected) {

                var images = [];

                selected.forEach(function(path){
                    images.push({meta:{title:''}, path:path});
                });

                $this.$setValue($this.images.concat(images));

            }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });

        }

        selectAssetsImages() {

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
            });
        }

        remove(e) {
            this.images.splice(e.item.idx, 1);
            this.$setValue(this.images);
        }

    </script>

</field-gallery>
