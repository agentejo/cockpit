<field-multipleselect>

    <div class="uk-grid-gutter">
        <div name="container" class="uk-grid uk-grid-match uk-grid-width-medium-1-6">
            <div class="uk-grid-margin" each="{option in options}">
                <a data-value="{ option }" class="{ parent.selected.indexOf(option)!==-1 ? 'uk-link-muted':'uk-text-muted' }" onclick="{ toggle }" title="{ option }">
                    <i class="uk-icon-{ parent.selected.indexOf(option)!==-1 ? 'circle':'circle-o' }"></i>
                    { option }
                </a>
            </div>
        </div>
    </div>

    <script>

        var $this = this;

        this.selected   = [];
        this.options = opts.options || []

        if (typeof(this.options) === 'string') {

            var options = [];

            this.options.split(',').forEach(function(option) {
                options.push(option.trim());
            });

            this.options = options;
        }

        this.on('mount', function() {

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
