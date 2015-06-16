<field-repeater>

    <div class="uk-margin" each="{ item,idx in items }">

        <cockpit-field class="uk-width-1-1" field="{ parent.field }" options="{ opts.options }" bind="items[{ idx }].value"></cockpit-field>

        <div class="uk-margin-small-top">
            <a class="uk-button uk-button-link" onclick="{ parent.remove }"><i class="uk-icon-trash-o"></i></a>
        </div>
    </div>

    <a class="uk-button uk-button-link" onclick="{ add }"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Add item') }</a>

    <script>

        riot.util.bind(this);

        this.items = [];
        this.field = opts.field || {type:'text'};

        this.$initBind = function() {
            this.root.$value = this.items;
        };

        this.$updateValue = function(value) {

            if (Array.isArray(value) && JSON.stringify(this.items) != JSON.stringify(value)) {
                this.items = value;
                this.update();
            }

        }.bind(this);

        this.on('bindingupdated', function() {
            this.$setValue(this.items);
        });

        add() {
            this.items.push({type:this.field.type, value:''});
        }

        remove(e) {
            this.items.splice(e.item.idx, 1);
        }

    </script>

</field-repeater>
