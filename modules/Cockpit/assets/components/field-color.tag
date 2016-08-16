<field-color>

    <input name="input" class="uk-width-1-1" bind="{opts.bind}" type="{ opts.type || 'text' }" placeholder="{ opts.placeholder }">

    <script>

        var $this = this;

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.$updateValue = function(value, field) {

            if (value && this.input.value !== value) {
                this.input.value = value;
                this.update();
            }

        }.bind(this);

        this.on('mount', function(){

            App.assets.require([
                '/assets/lib/spectrum/spectrum.js',
                '/assets/lib/spectrum/spectrum.css'
            ], function(){

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

                $this.input.oninput = function(){
                    $this.$setValue($this.input.value);
                };
    
            });
        });

    </script>

</field-color>
