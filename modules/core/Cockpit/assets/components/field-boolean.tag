<field-boolean>
    
    <button type="button" name="button" class="uk-button uk-button-{ checked ? 'success':'default'}" onclick="{ toggle }">
        <i show="{checked}" class="uk-icon-check-circle"></i>
        <i show="{!checked}" class="uk-icon-circle-o"></i>
    </button>

    <script>

        if (opts.cls) {
            App.$(this.button).addClass(opts.cls.replace(/uk\-form\-/g, 'uk-button-'));
        }

        if (opts.label) {
            this.button.innerHTML = opts.label;
        }

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
