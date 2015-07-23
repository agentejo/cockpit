<field-wysiwyg>

    <textarea name="input" class="uk-width-1-1" rows="5"></textarea>

    <script>

        var $this = this,
            lang  = document.documentElement.getAttribute('lang') || 'en',
            wysiwyg;

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.rows) {
            this.input.setAttribute('rows', opts.rows);
        }

        this.value = null;
        this._field = null;

        this.$updateValue = function(value, field) {

            if (this.value != value) {

                this.value = value;

                if (wysiwyg && this._field != field) {
                    wysiwyg.html(this.value || '');
                }
            }

            this._field = field;

        }.bind(this);


        this.on('mount', function(){

            var assets = [
                '/assets/lib/trumbowyg/trumbowyg.min.js',
                '/assets/lib/trumbowyg/ui/trumbowyg.min.css',
            ];

            if (lang != 'en') {
                assets.push('/assets/lib/trumbowyg/langs/'+lang+'.js');
            }

            App.assets.require(assets, function() {

                this.input.value = this.value;

                wysiwyg = App.$($this.input).trumbowyg({
                    lang: lang,
                    autogrow: true
                }).on('tbwchange', function()  {
                    $this.$setValue(wysiwyg.html());
                }).data('trumbowyg');

            }.bind(this)).catch(function(){

                // fallback if wysiwyg is not available

                this.input.value = this.value;

                App.$(this.input).css('visibility','').on('change', function() {
                    $this.$setValue(this.value);
                });

            }.bind(this));
        });

    </script>

</field-wysiwyg>
