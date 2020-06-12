<field-multipleselect>
    <div if="{loading}"><i class="uk-icon-spinner uk-icon-spin"></i></div>
    <div class="{ optionsLength > 10 ? 'uk-scrollable-box':'' }" if="{!loading && Array.isArray(options)}">

        <div class="uk-margin" each="{group in Object.keys(groups).sort()}">

            <div class="uk-text-bold uk-text-upper uk-text-small uk-margin-small">{group}</div>

            <div class="uk-margin-small uk-margin-small-left uk-text-small" each="{option,idx in parent.groups[group]}">
                <a data-value="{ option.value }" class="{ parent.selected.indexOf(option.value)!==-1 ? 'uk-text-primary':'uk-text-muted' }" onclick="{ toggle }" title="{ option.label }">
                    <i class="uk-icon-{ parent.selected.indexOf(option.value)!==-1 ? 'circle':'circle-o' } uk-margin-small-right"></i>
                    { option.label }
                </a>
            </div>
        </div>

        <div class="uk-margin-small-top uk-text-small" each="{option in options}">
            <a data-value="{ option.value }" class="{ parent.selected.indexOf(option.value)!==-1 ? 'uk-text-primary':'uk-text-muted' }" onclick="{ parent.toggle }" title="{ option.label }">
                <i class="uk-icon-{ parent.selected.indexOf(option.value)!==-1 ? 'circle':'circle-o' } uk-margin-small-right"></i>
                { option.label }
            </a>
        </div>
    </div>
    <span class="uk-text-small uk-text-muted" if="{ optionsLength > 10}">{selected.length} { App.i18n.get('selected') }</span>

    <script>

        var $this = this;

        this.selected = [];
        this.optionsLength = 0;
        this.groups = {};
        this.options  = null;

        this.loading = opts.src && opts.src.url ? true : false;

        this.on('mount', function() {

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

                        $this.optionsLength++;

                    })

                    $this.update();
                })
            }

            this.update();
        });

        this.on('update', function() {

            if (this.loading) return;

            if (!this.options) {

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

                        $this.optionsLength++;

                    });

                } else if(typeof(opts.options) === 'object') {

                    Object.keys(opts.options).forEach(function(key) {

                        $this.options.push({
                            value: key,
                            label: opts.options[key]
                        });

                        $this.optionsLength++;
                    });
                }
            }
        });

        this.$initBind = function() {
            this.root.$value = this.selected;
        };

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.selected) != JSON.stringify(value)) {
                this.selected = value;
                this.update();
            }

        }.bind(this);

        toggle(e) {

            var option = e.item.option.value,
                index  = this.selected.indexOf(option);

            if (index == -1) {
                this.selected.push(option);
            } else {
                this.selected.splice(index, 1);
            }

            this.$setValue(this.selected);
        }

    </script>

</field-multipleselect>
