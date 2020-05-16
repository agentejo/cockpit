<field-asset>

    <div ref="uploadprogress" class="uk-margin uk-hidden">
        <div class="uk-progress">
            <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div>
        </div>
    </div>

    <div class="uk-placeholder uk-text-center uk-text-muted" if="{!asset}">

        <img class="uk-svg-adjust" riot-src="{ App.base('/assets/app/media/icons/assets.svg') }" width="100" data-uk-svg>

        <p>{ App.i18n.get('No asset selected') }. <a onclick="{ selectAsset }">{ App.i18n.get('Select one') }</a></p>

    </div>

    <div class="uk-panel uk-panel-box uk-padding-remove uk-panel-card" if="{asset}">

        <div class="uk-overlay uk-display-block uk-position-relative { asset.mime.match(/^image\//) && 'uk-bg-transparent-pattern' }">
            <canvas class="uk-responsive-width" width="200" height="150"></canvas>
            <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle">
                <div class="uk-width-1-1 uk-text-center">

                    <span if="{ asset.mime.match(/^image\//) == null }"><i class="uk-h1 uk-text-muted uk-icon-{ getIconCls(asset.path) }"></i></span>

                    <a href="{ASSETS_URL+asset.path}" if="{ asset.mime.match(/^image\//) }" data-uk-lightbox="type:'image'" title="{ asset.width && [asset.width, asset.height].join('x') }">
                        <cp-thumbnail src="{asset && ASSETS_URL+asset.path}" height="160"></cp-thumbnail>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="uk-panel-body">
            <div class="uk-margin-small-top uk-text-truncate">
              <a href="{ASSETS_URL+asset.path}" target="_blank">{ asset.title }</a>
            </div>
            <div class="uk-text-small uk-text-muted">
                <strong>{ asset.mime }</strong>
                { App.Utils.formatSize(asset.size) }
            </div>

            <div class="uk-margin-top">
                <a class="uk-button uk-button-small uk-margin-small-right" onclick="{ selectAsset }">{ App.i18n.get('Replace') }</a>
                
                <span class="uk-button-group">
                <a class="uk-button uk-button-small" onclick="{edit}"><i class="uk-icon-pencil"></i></a>
                <a class="uk-button uk-button-small uk-text-danger" onclick="{reset}"><i class="uk-icon-trash-o"></i></a>
                </span>
            </div>
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

        this.$updateValue = function(value, field, force) {

            if (force || (JSON.stringify(this.asset) != JSON.stringify(value))) {
                
                if (value && !value._id) {
                    value = false;
                }

                this.asset = value;
                this.update();
            }

        }.bind(this);

        this.on('mount', function() {

            // handle uploads
            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                UIkit.uploadDrop($this.root, {

                    action: App.route('/assetsmanager/upload'),
                    type: 'json',
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
                            $this.$setValue(response.assets[0]);
                        }

                        if (!response) {
                            App.ui.notify("Something went wrong.", "danger");
                        }
                    }
                });
            });
        })

        selectAsset() {

            Cockpit.assets.select(function(assets){

                if (Array.isArray(assets)) {
                    $this.$setValue(assets[0]);
                }

            }, {typefilter: opts.typefilter});
        }

        reset() {
            $this.asset = null;
            $this.$setValue($this.asset);
        }
        
        edit() {
          
            var dialog = UIkit.modal.dialog([
                '<div>',
                    '<div class="uk-modal-header uk-text-large"><h3>'+App.i18n.get('Edit asset')+'</h3></div>',
                    '<cp-asset asset="'+this.asset._id+'"></cp-asset>',
                    '<div class="uk-modal-footer uk-text-right">',
                        '<button class="uk-button uk-button-primary uk-margin-right uk-button-large js-save-button">Save</button>',
                        '<a class="uk-button uk-button-large uk-button-link uk-modal-close">Close</a>',
                    '</div>',
                '</div>'
            ].join(''), {modal:false});
            
            dialog.dialog.addClass('uk-modal-dialog-large');
            
            riot.mount(dialog.element[0], '*', {});
            
            dialog.dialog.find('.js-save-button').on('click', function() {
              
                App.$('cp-asset', dialog.element)[0]._tag.updateAsset(function(asset) {
                    $this.$setValue(asset);
                });
            });
            
            dialog.show();
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
