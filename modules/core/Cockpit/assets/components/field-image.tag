<field-image>

    <figure class="uk-display-block uk-overlay uk-overlay-hover">

        <div class="uk-placeholder uk-flex uk-flex-middle uk-flex-center uk-text-muted" style="min-height:120px;">
            <img riot-src="{ (SITE_URL+'/'+image.path) }" show="{ image.path }">
            <i class="uk-icon-image" show="{ !image.path }"></i>
        </div>

        <figcaption class="uk-overlay-panel uk-overlay-background">

            <ul class="uk-subnav">
                <li><a onclick="{ selectimage }" title="{ App.i18n.get('Select image') }" data-uk-tooltip><i class="uk-icon-image"></i></a></li>
                <li><a onclick="{ title }" title="{ App.i18n.get('Set title') }" data-uk-tooltip><i class="uk-icon-tag"></i></a></li>
                <li><a onclick="{ remove }" title="{ App.i18n.get('Reset') }" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li>
            </ul>

            <p class="uk-text-small uk-text-truncate">{ image.title }</p>

        </figcaption>
    </figure>

    <script>

        var $this = this;

        this.image = {path:'', title:''};

        this.$updateValue = function(value, field) {

            if (value && this.image !== value) {
                this.image = value;
                this.update();
            }

        }.bind(this);

        selectimage() {

            App.media.select(function(selected) {

                $this.image.path = selected[0];
                $this.$setValue($this.image);
                $this.update();

            }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });
        }

        remove() {
            this.$setValue({path:'', title:''});
        }

        title() {

            App.ui.prompt('Title', this.image.title, function(value) {
                $this.image.title = value;
                $this.$setValue($this.image);
                $this.update();
            });
        }

    </script>

</field-image>
