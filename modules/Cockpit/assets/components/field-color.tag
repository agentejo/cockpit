<field-color>

    <input ref="input" class="uk-width-1-1" type="text">

    <script>

        this.on('mount', function() { this.update(); });
        this.on('update', function() { if (opts.opts) App.$.extend(opts, opts.opts); });

        var $this = this;

        this.$updateValue = function(value, field) {

            if (value && this.refs.input.value !== value) {
                this.refs.input.value = value;
                this.update();
            }

            if (App.$.fn.spectrum) {
                App.$($this.refs.input).spectrum("set", $this.root.$value);
            }

        }.bind(this);

        this.on('mount', function(){

            App.assets.require([
                '/assets/lib/spectrum/spectrum.js',
                '/assets/lib/spectrum/spectrum.css'
            ], function(){

                $this.refs.input.value = $this.root.$value || '';

                App.$($this.refs.input).spectrum(App.$.extend({
                    preferredFormat: 'rgb',
                    allowEmpty:true,
                    showInitial: true,
                    showInput: true,
                    showButtons: false,
                    showAlpha: true,
                    showSelectionPalette: true,
                    palette: [ ],
                    change: function() {
                        $this.$setValue($this.refs.input.value);
                    }
                }, opts.spectrum));

            });
        });

    </script>

</field-color>
