<field-gallery>

    <div class="uk-panel">

        <div name="imagescontainer" class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-grid-gutter uk-grid-width-medium-1-4" show="{ images && images.length }">
            <div class="uk-grid-margin" data-idx="{ idx }" each="{ img,idx in images }">
                <div class="uk-panel uk-panel-box uk-panel-card">
                    <figure class="uk-display-block uk-overlay uk-overlay-hover">
                        <div class="uk-flex uk-flex-middle uk-flex-center" style="min-height:120px;">
                            <img riot-src="{ (SITE_URL+'/'+img.path) }">
                        </div>
                        <figcaption class="uk-overlay-panel uk-overlay-background">

                            <ul class="uk-subnav">
                                <li><a onclick="{ parent.title }" title="{ App.i18n.get('Set title') }" data-uk-tooltip><i class="uk-icon-tag"></i></a></li>
                                <li><a onclick="{ parent.remove }" title="{ App.i18n.get('Remove image') }" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li>
                            </ul>

                            <p class="uk-text-small uk-text-truncate">{ img.title }</p>

                        </figcaption>
                    </figure>
                </div>
            </div>
        </div>

        <div class="{images && images.length ? 'uk-margin-top':'' }">
            <div class="uk-alert" if="{ images && !images.length }">{ App.i18n.get('Gallery is empty') }.</div>
            <a class="uk-button uk-button-link" onclick="{ selectimages }">
                <i class="uk-icon-plus-circle"></i>
                { App.i18n.get('Add images') }
            </a>
        </div>

    </div>

    <script>

        var $this = this;

        this.images = [];
        this._field = null;

        this.on('mount', function() {

            UIkit.sortable(this.imagescontainer, {

                animation: false

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                ele = App.$(ele);

                var images = $this.images,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                images.splice(cidx, 0, images.splice(oidx, 1)[0]);

                $this.images = [];
                $this.update();

                setTimeout(function() {
                    $this.images = images;
                    $this.$setValue(images);
                    $this.update();
                }, 10);

            });

        });

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this.images !== value) {
                this.images = value;
                this.update();
            }

        }.bind(this);


        selectimages() {

            App.media.select(function(selected) {

                var images = [];

                selected.forEach(function(path){
                    images.push({title:'', path:path});
                });

                $this.$setValue($this.images.concat(images));

            }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });
        }

        remove(e) {
            this.images.splice(e.item.idx, 1);
            this.$setValue(this.images);
        }

        title(e) {

            App.ui.prompt('Title', this.images[e.item.idx].title, function(value) {
                $this.images[e.item.idx].title = value;
                $this.$setValue($this.images);
                $this.update();
            });
        }

    </script>

</field-gallery>
