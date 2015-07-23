<field-set>

    <div>

        <div class="uk-alert" if="{!fields.length}">
            { App.i18n.get('Fields definition is missing') }
        </div>

        <div class="uk-margin" each="{field,idx in fields}">
            <label><span class="uk-badge">{ field.label || field.name || ''}</span></label>

            <cp-field class="uk-width-1-1" field="{field}" bind="value.{field.name}"></cp-field>
        </div>

    </div>

    <script>

        var $this = this;

        this._field = null;

        riot.util.bind(this);

        this.set    = opts.multiple ? []:{};
        this.fields = opts.fields || [];
        this.value  = {};

        this.bind = opts.bind || '';

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
            this.$setValue(this.value);
        });

    </script>

</field-set>
