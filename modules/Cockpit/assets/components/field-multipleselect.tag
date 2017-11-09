<field-multipleselect>
    <div class="{ options.length > 10 ? 'uk-scrollable-box':'' }">
        <div class="uk-margin-small-top" each="{option in options}">
            <a data-value="{ option }" class="{ parent.selected.indexOf(option)!==-1 ? 'uk-text-primary':'uk-text-muted' }" onclick="{ parent.toggle }" title="{ option }">
                <i class="uk-icon-{ parent.selected.indexOf(option)!==-1 ? 'circle':'circle-o' } uk-margin-small-right"></i>
                { option }
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

            this.options = opts.options || [];

            if (typeof(this.options) === 'string') {

                var options = [];

                this.options.split(',').forEach(function(option) {
                    options.push(option.trim());
                });

                this.options = options;
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

            var option = e.item.option,
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
