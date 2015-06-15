<field-gallery>

    <div class="uk-panel uk-panel-box">

        <div name="imagescontainer" class="uk-grid uk-grid-match uk-grid-small uk-grid-gutter uk-grid-width-medium-1-4" show="{ data.images && data.images.length }">
            <div class="uk-grid-margin" data-idx="{ idx }" each="{ img,idx in data.images }">
                <div class="uk-panel uk-panel-card">
                    <figure class="uk-display-block uk-overlay uk-overlay-hover">
                        <img riot-src="{ (SITE_URL+img.path) }">
                        <figcaption class="uk-overlay-panel uk-overlay-background">

                            <ul class="uk-subnav">
                                <li><a onclick="{ parent.title }"><i class="uk-icon-tag"></i></a></li>
                                <li><a onclick="{ parent.remove }"><i class="uk-icon-trash-o"></i></a></li>
                            </ul>

                            <p class="uk-text-small uk-text-truncate">{ img.title }</p>

                        </figcaption>
                    </figure>
                </div>
            </div>
        </div>

        <div class="{data.images && data.images.length ? 'uk-margin-top':'' }">
            <span if="{ data.images && !data.images.length }">{ App.i18n.get('Gallery is empty') }.</span>
            <a class="uk-button uk-button-link" onclick="{ selectimages }">
                <i class="uk-icon-plus-circle"></i>
                { App.i18n.get('Add images') }
            </a>
        </div>

    </div>

    <script>

        var $this = this;

        this.data = { images: [] };

        this.on('mount', function() {

            UIkit.sortable(this.imagescontainer, {

                animation: false,
                dragCustomClass:'uk-form'

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                ele = App.$(ele);

                var images = $this.data.images,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                images.splice(cidx, 0, images.splice(oidx, 1)[0]);

                $this.data.images = [];
                $this.update();

                setTimeout(function() {
                    $this.data.images = images;
                    $this.update();
                }, 0);

            });

        });

        this.$updateValue = function(value) {

            if (this.data.images !== value && Array.isArray(value)) {
                this.data.images = value;
                this.update();
            }

        }.bind(this);


        selectimages() {

            App.media.select(function(selected) {

                var images = [];

                selected.forEach(function(path){
                    images.push({title:'', path:path});
                });

                $this.$setValue($this.data.images.concat(images));

            }, { pattern: '*.jpg|*.png|*.gif|*.svg' });
        }

        remove(e) {
            this.data.images.splice(e.item.idx, 1);
            this.$setValue(this.data.images);
        }

        title(e) {

            App.ui.prompt('Title', this.data.images[e.item.idx].title, function(value) {
                $this.data.images[e.item.idx].title = value;
                $this.$setValue($this.data.images);
                $this.update();
            });
        }

    </script>

</field-gallery>
