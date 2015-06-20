<field-wysiwyg>

    <textarea name="input" style="visibility:hidden;" class="uk-width-1-1" rows="5"></textarea>

    <script>

        var $this = this,
            lang  = document.documentElement.getAttribute('lang') || 'en',
            redactor;

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.rows) {
            this.input.setAttribute('rows', opts.rows);
        }

        this.value = null;

        this.$updateValue = function(value, field) {

            if (this.value != value) {

                this.value = value;

                if (redactor && redactor._field != field) {
                    redactor.code.set(this.value || '');
                    redactor._field = field;
                }
            }

        }.bind(this);


        this.on('mount', function(){

            var assets = [
                '/assets/lib/redactor/redactor.min.js',
                '/assets/lib/redactor/redactor.css',

                // load plugins
                '/assets/lib/redactor/plugins/fullscreen/fullscreen.js',
                '/assets/lib/redactor/plugins/fontcolor/fontcolor.js',
                '/assets/lib/redactor/plugins/fontsize/fontsize.js',
                '/assets/lib/redactor/plugins/textdirection/textdirection.js',
                '/assets/lib/redactor/plugins/table/table.js',
                '/assets/lib/redactor/plugins/video/video.js'
            ];

            if (lang != 'en') {
                assets.push('/assets/lib/redactor/lang/'+lang+'.js');
            }


            App.assets.require(assets, function() {

                this.input.value = this.value;

                App.$($this.input).redactor({
                    lang: lang,
                    plugins: opts.plugins ||  ['table','textdirection','fontcolor','fontsize','video','fullscreen'],
                    initCallback: function() {
                        redactor = this;
                    },
                    changeCallback: function() {
                        $this.$setValue(this.code.get());
                    }
                });

            }.bind(this)).catch(function(){

                // fallback if redactor is not available

                this.input.value = this.value;

                App.$(this.input).css('visibility','').on('change', function() {
                    $this.$setValue(this.value);
                });

            }.bind(this));
        });

    </script>

</field-wysiwyg>
