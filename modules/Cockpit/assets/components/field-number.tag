<field-number>
    <div class="uk-position-relative field-number-container">
        <input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="number"  placeholder="{opts.placeholder}">
    </div>

    <script>
        this.on('mount', function() {

            opts.cls && App.$(this.refs.input).addClass(opts.cls);

            opts.required && this.refs.input.setAttribute('required', 'required');

            ['max', 'min', 'placeholder', 'readonly', 'step'].forEach( function(key) {
                opts[key] && this.refs.input.setAttribute(key, opts[key]);
            }.bind(this));

            this.update();
        });
    </script>
</field-number>
