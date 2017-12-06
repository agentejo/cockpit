<field-file>

    <div class="uk-panel uk-panel-box uk-panel-card ">

        <div ref="uploadprogress" class="uk-margin uk-hidden">
            <div class="uk-progress">
                <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div>
            </div>
        </div>

        <div class="uk-flex uk-flex-middle">

            <input class="uk-form-blank uk-flex-item-1" type="text" ref="input" bind="{ opts.bind }" placeholder="{ opts.placeholder || App.i18n.get('No file selected...') }">

            <span class="uk-margin-small-left" data-uk-dropdown="pos:'bottom-center'">

                <button type="button" class="uk-button" ref="picker" title="{ App.i18n.get('Pick file') }" onclick="{ selectFile }"><i class="uk-icon-paperclip"></i></button>

                <div class="uk-dropdown">
                    <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                        <li class="uk-nav-header">{ App.i18n.get('Source') }</li>
                        <li><a onclick="{ selectAsset }">{ App.i18n.get('Select Asset') }</a></li>
                        <li><a onclick="{ selectFile }">{ App.i18n.get('Select File') }</a></li>
                    </ul>
                </div>

            </span>

        </div>

    </div>

    <script>

        var $this = this, $input;

        this.on('mount', function() {

            $input = App.$(this.refs.input);

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
                App.$(this.refs.picker).addClass(opts.cls);
            }

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
                            App.ui.notify("File(s) failed to uploaded.", "danger");
                        }

                        if (response && Array.isArray(response.assets) && response.assets.length) {
                            $this.refs.input.$setValue(ASSETS_URL.replace(SITE_URL+'/', '')+response.assets[0].path);
                        }

                        if (!response) {
                            App.ui.notify("Something went wrong.", "danger");
                        }

                    }
                });
            });

        });

        selectFile() {

            App.media.select(function(selected) {
                $this.refs.input.$setValue(selected[0]);
            }, {});
        }

        selectAsset() {

            App.assets.select(function(assets){

                if (Array.isArray(assets) && assets[0]) {
                    $this.refs.input.$setValue(ASSETS_URL.replace(SITE_URL+'/', '')+assets[0].path);
                    $this.update();
                }
            });
        }

    </script>

</field-file>
