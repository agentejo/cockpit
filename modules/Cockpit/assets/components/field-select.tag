<field-select>

    <select ref="input" class="uk-width-1-1 {opts.cls}" bind="{ opts.bind }">
        <option value=""></option>
        <option each="{ option,idx in options }" value="{ option }">{ option }</option>
    </select>

    <script>

        this.on('mount', function() { this.trigger('update'); });

        this.on('update', function() {

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            this.options = opts.options || [];

            if (typeof(this.options) === 'string') {

                var options = [];

                this.options.split(',').forEach(function(option) {
                    options.push(option.trim());
                });

                this.options = options;
            }

            this.refs.input.value = this.root.$value;
        });

    </script>

</field-select>
