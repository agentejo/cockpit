<field-multipleselect>
    <div class="{ options.length > 10 ? 'uk-scrollable-box':'' }">
        <div class="uk-margin-small-top" each="{option in options}">
            <a data-value="{ option.value }" class="{ parent.selected.indexOf(option.value)!==-1 ? 'uk-text-primary':'uk-text-muted' }" onclick="{ parent.toggle }" title="{ option.label }">
                <i class="uk-icon-{ parent.selected.indexOf(option.value)!==-1 ? 'circle':'circle-o' } uk-margin-small-right"></i>
                { option.label }
            </a>
        </div>
    </div>
    <span class="uk-text-small uk-text-muted" if="{ options.length > 10}">{selected.length} { App.i18n.get('selected') }</span>

    <script>

        var $this = this;

        this.selected = [];
        this.options  = [];

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function() {

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
