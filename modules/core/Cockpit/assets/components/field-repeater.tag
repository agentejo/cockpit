<field-repeater>

    <div class="uk-alert" show="{ !items.length }">
        { App.i18n.get('No items') }.
    </div>

    <div class="uk-margin uk-panel-box uk-panel-card" each="{ item,idx in items }">

        <cp-field class="uk-width-1-1" field="{ parent.field }" options="{ opts.options }" bind="items[{ idx }].value"></cp-field>

        <div class="uk-panel-box-footer uk-bg-light">
            <a onclick="{ parent.remove }"><i class="uk-icon-trash-o"></i></a>
        </div>
    </div>

    <a class="uk-button" onclick="{ add }"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Add item') }</a>

    <script>

        riot.util.bind(this);

        this.items = [];
        this.field = opts.field || {type:'text'};

        this.$initBind = function() {
            this.root.$value = this.items;
        };

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.items) != JSON.stringify(value)) {
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
