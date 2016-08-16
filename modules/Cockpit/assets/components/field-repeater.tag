<field-repeater>

    <div class="uk-alert" show="{ !items.length }">
        { App.i18n.get('No items') }.
    </div>

    <div show="{mode=='edit' && items.length}">
        <div class="uk-margin uk-panel-box uk-panel-card" each="{ item,idx in items }" data-idx="{idx}">

            <cp-field class="uk-width-1-1" field="{ item.field }" options="{ opts.options }" bind="items[{ idx }].value"></cp-field>

            <div class="uk-panel-box-footer uk-bg-light">
                <a onclick="{ parent.remove }"><i class="uk-icon-trash-o"></i></a>
            </div>
        </div>
    </div>

    <div name="itemscontainer" class="uk-sortable" show="{ mode=='reorder' && items.length }">
        <div class="uk-margin uk-panel-box uk-panel-card" each="{ item,idx in items }" data-idx="{idx}">
            <div class="uk-grid uk-grid-small">
                <div class="uk-flex-item-1"><i class="uk-icon-bars"></i> Item { (idx+1) }</div>
                <div class="uk-text-muted uk-text-small">{ App.Utils.ucfirst(typeof(item.field) == 'string' ? item.field:item.field.type) }</div>
            </div>
        </div>
    </div>

    <div class="uk-margin">
        <a class="uk-button" onclick="{ add }" show="{ mode=='edit' }" if="{ !fields }"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Add item') }</a>
        <span show="{ mode=='edit' }" if="{ fields }" data-uk-dropdown="mode:'click'">
            <a class="uk-button"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Add item') }</a>
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li each="{field in fields}"><a class="uk-dropdown-close" onclick="{ parent.add }">{ field.label && field.label || App.Utils.ucfirst(typeof(field) == 'string' ? field:field.type) }</a></li>
                </ul>
            </div>
        </span>
        <a class="uk-button" onclick="{ updateorder }" show="{ mode=='reorder' }"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Update order') }</a>
        <a class="uk-button" onclick="{ switchreorder }" show="{ items.length > 1 }">
            <span show="{ mode=='edit' }"><i class="uk-icon-arrows"></i> { App.i18n.get('Reorder') }</span>
            <span show="{ mode=='reorder' }">{ App.i18n.get('Cancel') }</span>
        </a>
    </div>

    <script>

        var $this = this;

        riot.util.bind(this);

        this.items  = [];
        this.field  = opts.field || {type:'text'};
        this.fields = opts.fields && Array.isArray(opts.fields) && opts.fields  || false;
        this.mode   = 'edit';

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

        this.on('mount', function() {

            UIkit.sortable(this.itemscontainer, {
                animation: false
            });

        });

        add(e) {

            if (this.fields) {
                this.items.push({field:e.item.field, value:null});
            } else {
                this.items.push({field:this.field, value:null});
            }
        }

        remove(e) {
            this.items.splice(e.item.idx, 1);
        }

        switchreorder() {
            $this.mode = $this.mode == 'edit' ? 'reorder':'edit';
        }

        updateorder() {

            var items = [];

            App.$($this.root).css('height', App.$($this.root).height());

            App.$(this.itemscontainer).children().each(function(){
                items.push($this.items[Number(this.getAttribute('data-idx'))]);
            });


            $this.items = [];
            $this.update();

            setTimeout(function() {
                $this.mode = 'edit'
                $this.items = items;
                $this.$setValue(items);
                $this.update();

                setTimeout(function(){
                    $this.root.style.height = '';
                }, 30)
            }, 10);
        }

    </script>

</field-repeater>
