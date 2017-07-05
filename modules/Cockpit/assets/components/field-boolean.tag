<field-boolean>

    <div ref="container" class="uk-display-inline-block" onclick="{ toggle }" style="cursor:pointer;">
        <div class="uk-form-switch">
            <input ref="check" type="checkbox" id="{ id }"/>
            <label for="{ id }"></label>
        </div>

        <span show="{value && (opts.label !== 'false' && opts.label !== false)}">{ opts.label || 'On' }</span>
        <span class="uk-text-muted" show="{!value && (opts.label !== 'false' && opts.label !== false)}">{ opts.label || 'Off' }</span>

    </div>


    <script>

        this.id = 'switch'+Math.ceil(Math.random()*10000000);

        if (opts.cls) {
            App.$(this.refs.container).addClass(opts.cls);
        }

        this.value = opts.default || false;

        this.$updateValue = function(value) {
            
            if (this.value != value) {
                this.value = value;
                this.update();
            }
            this.refs.check.checked = Boolean(this.value);

        }.bind(this);

        toggle(e) {
            e.preventDefault();
            this.value = !Boolean(this.value);
            this.refs.check.checked = this.value;
            this.$setValue(this.value);
        }

    </script>

</field-boolean>
