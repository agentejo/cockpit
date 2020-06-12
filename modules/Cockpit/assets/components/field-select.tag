<field-select>
    <div if="{loading}"><i class="uk-icon-spinner uk-icon-spin"></i></div>
    <select ref="input" class="uk-width-1-1 {opts.cls}" bind="{ opts.bind }" show="{!loading}" multiple="{opts.multiple}">
        <option value=""></option>
        <optgroup each="{group in Object.keys(groups).sort()}" label="{group}">
            <option each="{ option,idx in parent.groups[group] }" value="{ option.value }" selected="{ isSelected(option.value) }">{ option.label }</option>
        </optgroup>
        <option each="{ option,idx in options }" value="{ option.value }" selected="{ isSelected(option.value) }">{ option.label }</option>
    </select>

    <script>

        var $this = this;

        this.loading = opts.src && opts.src.url ? true : false;
        this.groups = {};
        this.options = null;

        this.on('mount', function() {

            (['required']).forEach( function(key) {
                if (opts[key]) $this.refs.input.setAttribute(key, opts[key]);
            });

            if (opts.multiple) {
                $this.refs.input.style.height = (opts.height ? String(opts.height).replace('px', '') : 200)+'px';
            }

            if (opts.src && opts.src.url && opts.src.value) {
                
                this.loading = true;

                var url = opts.src.url, 
                    fieldVal = opts.src.value, 
                    fieldLabel = opts.src.label || fieldVal
                    fieldGroup = opts.src.group || null;

                if (url.match('^collection=')) {
                    url = '/collections/find?'+url;
                }

                App.request(opts.src.url).then(function(data) {

                    $this.loading = false;

                    if (url.match('^\/collections\/find\?')) {
                        data = data.entries;
                    }

                    if (!Array.isArray(data)) {
                        $this.update();
                        return;
                    }

                    $this.options = [];

                    data.forEach(function(item, option) {

                        if (item[fieldVal] === undefined) return;

                        option = {
                            value: _.get(item, fieldVal),
                            label: _.get(item, fieldLabel),
                            group: fieldGroup ? _.get(item, fieldGroup) : false
                        };

                        if (option.group) {
                            
                            if (!$this.groups[option.group]) {
                                $this.groups[option.group] = [];
                            }

                            $this.groups[option.group].push(option);
                        } else {
                            $this.options.push(option);
                        }

                    })

                    $this.update();
                })
            }

            this.update();
        });

        this.on('update', function() {

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            if (this.loading) {
                return;
            }

            if (this.options === null) {

                this.options = [];

                if (typeof(opts.options) === 'string' || Array.isArray(opts.options)) {

                    (typeof(opts.options) === 'string' ? opts.options.split(',') : opts.options || []).forEach(function(option) {

                        option = {
                            value : (option.hasOwnProperty('value') ? option.value.toString().trim() : option.toString().trim()),
                            label : (option.hasOwnProperty('label') ? option.label.toString().trim() : option.toString().trim()),
                            group : (option.hasOwnProperty('group') ? option.group.toString().trim() : '')
                        };

                        if (option.group) {
                            
                            if (!$this.groups[option.group]) {
                                $this.groups[option.group] = [];
                            }

                            $this.groups[option.group].push(option);
                        } else {
                            $this.options.push(option);
                        }

                    });

                } else if (typeof(opts.options) === 'object') {

                    Object.keys(opts.options).forEach(function(key) {

                        $this.options.push({
                            value: key,
                            label: opts.options[key],
                            group: ''
                        })
                    })
                }
            }

            if (!opts.multiple) {
                this.refs.input.value = this.root.$value;
            }

        });

        isSelected(value) {

            if (opts.multiple) {
                return (Array.isArray(this.root.$value) ? this.root.$value : []).indexOf(value) > -1;
            }

            return this.root.$value == value;
        }

    </script>

</field-select>
