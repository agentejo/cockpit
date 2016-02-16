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

            App.assets.require(['/assets/lib/tinycolorpicker/colors.js'], function(){

                App.assets.require(['/assets/lib/tinycolorpicker/jqColorPicker.js'], function(){

                    $this.input.id = $this.input.id || 'colorpicker'+Math.ceil(Math.random()*10000);

                    $this.input.onfocus = function(){
                        $('#'+$this.input.id).colorPicker({
                            renderCallback: function($elm, toggled) {
                                if (toggled === false) {
                                    $this.$setValue($elm.val());
                                }
                            }
                        });
                    };

                    $this.input.onkeyup = function(){
                        $this.$setValue($this.input.value);
                    };
                });
            });
        });

    </script>

</field-color>
