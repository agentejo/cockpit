<field-asset>

    <div class="uk-placeholder uk-text-center uk-text-muted" if="{!asset}">

        <img class="uk-svg-adjust" riot-src="{ App.base('/assets/app/media/icons/assets.svg') }" width="100" data-uk-svg>

        <p>{ App.i18n.get('No asset selected') }. <a onclick="{ selectAsset }">{ App.i18n.get('Select one') }</a></p>

    </div>

    <div class="uk-panel uk-panel-box uk-panel-card uk-display-inline-block" if="{asset}">

        <div class="uk-overlay uk-display-block uk-position-relative">
            <canvas class="uk-responsive-width" width="200" height="150"></canvas>
            <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle">
                <div class="uk-width-1-1 uk-text-center">

                    <span if="{ asset.mime.match(/^image\//) == null }"><i class="uk-h1 uk-text-muted uk-icon-{ getIconCls(asset.path) }"></i></span>

                    <a href="{ASSETS_URL+asset.path}" if="{ asset.mime.match(/^image\//) }" data-uk-lightbox="type:'image'" title="{ asset.width && [asset.width, asset.height].join('x') }">
                        <cp-thumbnail riot-src="{asset && ASSETS_URL+asset.path}" width="100" height="75"></cp-thumbnail>
                    </a>
                </div>
            </div>
        </div>
        <div class="uk-margin-small-top uk-text-truncate"><a href="{ASSETS_URL+asset.path}" target="_blank">{ asset.title }</a></div>
        <div class="uk-text-small uk-text-muted">
            <strong>{ asset.mime }</strong>
            { App.Utils.formatSize(asset.size) }
        </div>

        <hr>
        <div class="uk-text-small">
            <a class="uk-margin-small-right" onclick="{ selectAsset }">{ App.i18n.get('Replace') }</a>
            <a onclick="{reset}"><i class="uk-icon-trash-o"></i></a>
        </div>

    </div>

    <script>

        var $this = this, typefilters = {
            'image'    : /\.(jpg|jpeg|png|gif|svg)$/i,
            'video'    : /\.(mp4|mov|ogv|webv|wmv|flv|avi)$/i,
            'audio'    : /\.(mp3|weba|ogg|wav|flac)$/i,
            'archive'  : /\.(zip|rar|7zip|gz)$/i,
            'document' : /\.(txt|pdf|md)$/i,
            'code'     : /\.(htm|html|php|css|less|js|json|yaml|xml|htaccess)$/i
        };

        this.asset = opts.default || false;

        this.$updateValue = function(value) {

            if (JSON.stringify(this.asset) != JSON.stringify(value)) {

                this.asset = value;
                this.update();
            }

        }.bind(this);

        selectAsset() {

            Cockpit.assets.select(function(assets){
                if (Array.isArray(assets)) {
                    $this.$setValue(assets[0]);
                }
            });
        }

        reset() {
            $this.asset = null;
            $this.$setValue($this.asset);
        }

        getIconCls(path) {

            var name = path.toLowerCase();

            if (name.match(typefilters.image)) {

                return 'image';

            } else if(name.match(typefilters.video)) {

                return 'video-camera';

            } else if(name.match(typefilters.audio)) {

                return 'music';

            } else if(name.match(typefilters.document)) {

                return 'file-text-o';

            } else if(name.match(typefilters.code)) {

                return 'code';

            } else if(name.match(typefilters.archive)) {

                return 'archive';

            } else {
                return 'paperclip';
            }
        }


    </script>

</field-asset>
