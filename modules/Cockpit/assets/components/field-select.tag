<field-select>

    <select ref="input" class="uk-width-1-1 {opts.cls}" bind="{ opts.bind }">
        <option value=""></option>
        <option each="{ option,idx in options }" value="{ option }" selected="{ parent.root.$value === option }">{ option }</option>
    </select>

    <script>

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function() {
            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            this.options = (typeof(opts.options) === 'string' ? opts.options.split(',') : opts.options || [])
                .map(function(option) {
                    return option.toString().trim();
                });

            this.refs.input.value = this.root.$value;
        });

    </script>

</field-select>
