<field-boolean>

    <button type="button" name="button" class="uk-button uk-button-{ value ? 'success':'default'}" onclick="{ toggle }">

        <span show="{value}">{ opts.label || 'On' }</span>
        <span show="{!value}">{ opts.label || 'Off' }</span>

    </button>


    <script>

        if (opts.cls) {
            App.$(this.button).addClass(opts.cls.replace(/uk\-form\-/g, 'uk-button-'));
        }

        this.value = opts.default || false;

        this.$updateValue = function(value) {

            if (this.value != value) {

                this.value = value;
                this.update();
            }

        }.bind(this);

        toggle() {
            this.$setValue(!this.value);
        }

    </script>

</field-boolean>
