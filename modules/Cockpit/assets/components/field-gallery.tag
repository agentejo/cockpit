<field-gallery>

    <div ref="panel">

        <div ref="imagescontainer" class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-grid-gutter uk-grid-width-medium-1-4" show="{ images && images.length }">
            <div data-idx="{ idx }" each="{ img,idx in images }">
                <div class="uk-panel uk-panel-box uk-panel-thumbnail uk-panel-card">
                    <figure class="uk-display-block uk-overlay uk-overlay-hover">
                        <div class="uk-flex uk-flex-middle uk-flex-center" style="min-height:120px;">
                            <div class="uk-width-1-1 uk-text-center">
                                <img class="uk-display-inline-block uk-responsive-width" riot-src="{ (SITE_URL+'/'+img.path) }">
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

        <div riot-class="{images && images.length ? 'uk-margin-top':'' }">
            <div class="uk-alert" if="{ images && !images.length }">{ App.i18n.get('Gallery is empty') }.</div>
            <a class="uk-button uk-button-link" onclick="{ selectimages }">
                <i class="uk-icon-plus-circle"></i>
                { App.i18n.get('Add images') }
            </a>
        </div>

        <div class="uk-modal uk-sortable-nodrag" ref="modalmeta">
            <div class="uk-modal-dialog">

                <div class="uk-modal-header"><h3>{ App.i18n.get('Image Meta') }</h3></div>

                <div class="uk-grid uk-grid-match uk-grid-gutter" if="{image}">

                    <div riot-class="uk-grid-margin uk-width-medium-{field.width}" each="{field,name in meta}" no-reorder>

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

        this.meta   = App.$.extend(opts.meta || {}, {
            title: {
                type: 'text',
                label: 'Title'
            }
        });

        this.on('mount', function() {

            UIkit.sortable(this.refs.imagescontainer, {

                animation: false

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

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

        remove(e) {
            this.images.splice(e.item.idx, 1);
            this.$setValue(this.images);
        }

    </script>

</field-gallery>
