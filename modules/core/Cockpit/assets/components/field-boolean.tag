<field-boolean>

    <button type="button" name="button" class="uk-button uk-button-{ checked ? 'success':'default'}" onclick="{ toggle }">
        <i if="{parent.checked}" class="uk-icon-check"></i>
        <i if="{!parent.checked}" class="uk-icon-times"></i>
    </button>

    <script>

        if (opts.cls) {
            App.$(this.button).addClass(opts.cls.replace(/uk\-form\-/g, 'uk-button-'));
        }

        this.button.innerHTML = opts.label || '<i class="uk-icon-check"></i>';

        this.$updateValue = function(value) {

            if (this.checked != value) {

                this.checked = value;
                this.update();
            }

        }.bind(this);

        toggle() {
            this.$setValue(!this.checked);
        }

    </script>

</field-boolean>
