<field-boolean>

    <div ref="container" class="uk-display-inline-block" style="cursor:pointer;">

        <div class="uk-form-switch">

            <input ref="check" type="checkbox" id="{ id }" onchange="{ toggle }"/>
            <label for="{ id }">
                <span show="{value && (opts.label !== 'false' && opts.label !== false)}">{ opts.label || 'On' }</span>
                <span class="uk-text-muted" show="{!value && (opts.label !== 'false' && opts.label !== false)}">{ opts.label || 'Off' }</span>
            </label>

        </div>

    </div>

    <script>

        this.id = 'switch'+Math.ceil(Math.random()*10000000);

        if (opts.cls) {
            App.$(this.refs.container).addClass(opts.cls);
        }

        this.value = undefined;

        this.$updateValue = function(value) {

            if (typeof(value) !== 'boolean') {
                return this.$setValue(!!value);
            }

            if (this.value !== value) {
                this.value = value;
                this.update();
            }
            this.refs.check.checked = Boolean(this.value);

        }.bind(this);

        toggle(e) {

            this.value = this.refs.check.checked;
            this.$setValue(this.value);
        }

    </script>

</field-boolean>
