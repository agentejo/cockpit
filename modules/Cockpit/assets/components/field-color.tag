<field-color>

    <input name="input" class="uk-width-1-1" type="text">

    <script>

        var $this = this;

        this.$updateValue = function(value, field) {

            if (value && this.input.value !== value) {
                this.input.value = value;
                this.update();
            }

            if (App.$.fn.spectrum) {
                App.$($this.input).spectrum("set", $this.root.$value);
            }

        }.bind(this);

        this.on('mount', function(){

            App.assets.require([
                '/assets/lib/spectrum/spectrum.js',
                '/assets/lib/spectrum/spectrum.css'
            ], function(){

                $this.input.value = $this.root.$value || '';

                App.$($this.input).spectrum(App.$.extend({
                    preferredFormat: 'rgb',
                    allowEmpty:true,
                    showInitial: true,
                    showInput: true,
                    showButtons: false,
                    showAlpha: true,
                    showSelectionPalette: true,
                    palette: [ ],
                    change: function() {
                        $this.$setValue($this.input.value);
                    }
                }, opts.spectrum));

            });
        });

    </script>

</field-color>
