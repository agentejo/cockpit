<field-select>

    <select ref="input" class="uk-width-1-1 {opts.cls}" bind="{ opts.bind }">
        <option each="{ option,idx in options }" value="{ idx }" selected={ opts.default == idx }>{ option }</option>
    </select>

    <script>

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function() {

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            this.options = opts.options || [];

            this.refs.input.value = this.root.$value;
        });

    </script>

</field-select>
