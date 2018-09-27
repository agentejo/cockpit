<field-select>

    <select ref="input" class="uk-width-1-1 {opts.cls}" bind="{ opts.bind }">
        <option value=""></option>
        <option each="{ option,idx in options }" value="{ option.value }" selected="{ parent.root.$value === option.value }">{ option.label }</option>
    </select>

    <script>

        var $this = this;

        this.on('mount', function() {
            this.refs.input.value = this.root.$value;
            this.update();
        });

        this.on('update', function() {

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            this.options = [];

            if (typeof(opts.options) === 'string' || Array.isArray(opts.options)) {

                this.options = (typeof(opts.options) === 'string' ? opts.options.split(',') : opts.options || []).map(function(option) {

                    option = {
                      value : (option.hasOwnProperty('value') ? option.value.toString().trim() : option.toString().trim()),
                      label : (option.hasOwnProperty('label') ? option.label.toString().trim() : option.toString().trim())
                    };

                    return option;
                });

            } else if(typeof(opts.options) === 'object') {

                Object.keys(opts.options).forEach(function(key) {

                    $this.options.push({
                        value: key,
                        label: opts.options[key]
                    })
                })
            }

        });

    </script>

</field-select>
