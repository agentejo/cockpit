<field-set>

    <div>

        <div class="uk-alert" if="{fields && !fields.length}">
            { App.i18n.get('Fields definition is missing') }
        </div>

        <div class="uk-margin" each="{field,idx in fields}">
            <label class="uk-display-block uk-text-bold uk-text-small">{ field.label || field.name || ''}</label>
            <cp-field class="uk-display-block uk-margin-small-top" type="{ field.type || 'text' }" bind="value.{field.name}" opts="{ field.options || {} }"></cp-field>
            <div class="uk-margin-small-top uk-text-small uk-text-muted" if="{field.info}">
                { field.info || ' ' }
            </div>
        </div>

    </div>

    <script>

        var $this = this;

        this._field = null;
        this.set    = {};
        this.value  = {};
        this.fields = [];

        riot.util.bind(this);

        this.on('mount', function() {
            this.fields = opts.fields || [];
            this.update();
        });

        this.on('update', function() {
            this.fields = opts.fields || [];
        });

        this.$initBind = function() {
            this.root.$value = this.value;
        };

        this.$updateValue = function(value, field) {

            if (!App.Utils.isObject(value) || Array.isArray(value)) {

                value = {};

                this.fields.forEach(function(field){
                    value[field.name] = null;
                });
            }

            if (JSON.stringify(this.value) != JSON.stringify(value)) {
                this.value = value;
                this.update();
            }

            this._field = field;

        }.bind(this);

        this.on('bindingupdated', function() {
            $this.$setValue(this.value);
        });

    </script>

</field-set>
