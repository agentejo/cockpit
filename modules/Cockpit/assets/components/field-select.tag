<field-select>

    <select name="input" class="uk-width-1-1" bind="{ opts.bind }">
        <option value=""></option>
        <option each="{ option,idx in options }" value="{ option }">{ option }</option>
    </select>

    <script>

        this.options = opts.options || []

        if (typeof(this.options) === 'string') {

            var options = [];

            this.options.split(',').forEach(function(option) {
                options.push(option.trim());
            });

            this.options = options;
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

    </script>

</field-select>
