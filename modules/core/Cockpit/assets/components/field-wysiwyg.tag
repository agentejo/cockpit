<field-wysiwyg>

    <textarea name="input" style="visibility:hidden;"></textarea>

    <script>

        var $this = this,
            lang  = document.documentElement.getAttribute('lang') || 'en';

        this.value = null;

        this.$updateValue = function(value) {

            if (this.value != value) {
                this.value = value;
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

                this.input.value = $this.value;


                App.$($this.input).redactor({
                    lang: lang,
                    plugins: ['table','textdirection','fontcolor','fontsize','video','fullscreen'],
                    changeCallback: function() {
                        $this.$setValue(this.code.get());
                    }
                });

            }.bind(this));
        });

    </script>

</field-wysiwyg>
