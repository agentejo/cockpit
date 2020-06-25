<field-collectionlinkselect>

    <div if="{loading}"><i class="uk-icon-spinner uk-icon-spin"></i></div>

    <select class="uk-width-1-1 {opts.cls}" if="{collection && !opts.multiple && !loading}" oninput="{selectSingleItem}">
        <option value=""></option>
        <optgroup each="{group in Object.keys(groups).sort()}" label="{group}">
            <option each="{ option,idx in parent.groups[group] }" value="{ option._id }" selected="{ isSelected(option._id) }">{ option.display }</option>
        </optgroup>
        <option each="{ option,idx in options }" value="{ option._id }" selected="{ isSelected(option._id) }">{ option.display }</option>
    </select>


    <div class="{ App.Utils.count(idx) > 10 ? 'uk-scrollable-box':'' }" if="{collection && opts.multiple && !loading}">

        <div class="uk-margin" each="{group in Object.keys(groups).sort()}">

            <div class="uk-text-bold uk-text-upper uk-text-small uk-margin-small">{group}</div>

            <div class="uk-margin-small uk-margin-small-left uk-text-small" each="{option,idx in parent.groups[group]}">
                <a data-value="{ option._id }" class="{ isSelected(option._id) ? 'uk-text-primary':'uk-text-muted' }" onclick="{ toggleMultipleItem }" title="{ option.display }">
                    <i class="uk-icon-{ isSelected(option._id) ? 'circle':'circle-o' } uk-margin-small-right"></i>
                    { option.display }
                </a>
            </div>
        </div>

        <div class="uk-margin-small uk-margin-small-left uk-text-small" each="{option,idx in options}">
            <a data-value="{ option._id }" class="{ isSelected(option._id) ? 'uk-text-primary':'uk-text-muted' }" onclick="{ toggleMultipleItem }" title="{ option.display }">
                <i class="uk-icon-{ isSelected(option._id) ? 'circle':'circle-o' } uk-margin-small-right"></i>
                { option.display }
            </a>
        </div>

    </div>
    <span class="uk-text-small uk-text-muted" if="{ opts.multiple && App.Utils.count(idx) > 10}">{selected.length} { App.i18n.get('selected') }</span>


    <script>

        var $this = this;

        this.loading = true;
        this.collection = null;
        this.options = null;
        this.groups = {};
        this.idx = {};

        this.on('mount', function() {

            App.request('/collections/_collections').then(function(collections){

                $this.collection = (collections || {})[opts.link] || null;
                
                if (!$this.collection) {
                    $this.loading = false;
                    $this.update();
                    return;
                }

                var options = {};

                if (opts.filter) {
                    options.filter = opts.filter;
                }

                if (opts.sort) {
                    options.sort = opts.sort;
                }

                App.request('/collections/find', {collection:opts.link, options:options}).then(function(data) {

                    data = data.entries;

                    $this.options = [];

                    var fieldGroup = opts.group || null,
                        fieldDisplay = opts.display,
                        group;

                    data.forEach(function(item, option) {

                        option = {
                            _id: item._id,
                            link: opts.link,
                            display: fieldDisplay ? _.get(item, fieldDisplay) : item._id
                        };

                        $this.idx[item._id] = option;

                        group = fieldGroup ? _.get(item, fieldGroup) : false

                        if (group) {
                            
                            if (!$this.groups[group]) {
                                $this.groups[group] = [];
                            }

                            $this.groups[group].push(option);
                        } else {
                            $this.options.push(option);
                        }

                    });

                    $this.loading = false;
                    $this.update();
                })
            });
        })

        selectSingleItem(e) {
            if (!e.target.value) {
                this.$setValue(null);
                return;
            }
            this.$setValue(this.idx[e.target.value]);
        }

        toggleMultipleItem(e) {

            var id = e.item.option._id, value = this.root.$value, selected = false;

            if (!Array.isArray(value)) {
                value = [];
            }

            for (i=0;i<value.length;i++) {
                    
                if (value[i]._id == id) {
                    value.splice(i, 1);
                    selected = true;
                    break;
                }
            }

            if (!selected) {
                value.push(this.idx[id]);
            }

            this.$setValue(value);
        }

        isSelected(id) {

            if (!opts.multiple) {
                return this.root.$value && this.root.$value._id && (this.root.$value._id == id);
            }

            if (!Array.isArray(this.root.$value)) {
                return false;
            }

            for (i=0;i<this.root.$value.length;i++) {
                if (this.root.$value[i]._id == id) return true;
            }

            return false;
        }

    </script>

</field-collectionlinkselect>