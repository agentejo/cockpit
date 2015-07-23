<cp-fieldsmanager>

    <div name="fieldscontainer" class="uk-sortable uk-grid uk-grid-small uk-grid-gutter uk-form" show="{fields.length}">

        <div class="uk-grid-margin uk-width-{field.width}" data-idx="{idx}" each="{ field,idx in fields }">

            <div class="uk-panel uk-panel-box uk-panel-card">

                <div class="uk-grid uk-grid-small">

                    <div class="uk-flex-item-1 uk-flex">

                        <input class="uk-flex-item-1 uk-form-small uk-form-blank" type="text" bind="fields[{idx}].name" placeholder="name" required>
                    </div>

                    <div class="uk-width-1-4">
                        <div class="uk-form-select" data-uk-form-select>
                            <div class="uk-form-icon">
                                <i class="uk-icon-arrows-h"></i>
                                <input class="uk-width-1-1 uk-form-small uk-form-blank" value="{ field.width }">
                            </div>
                            <select bind="fields[{idx}].width">
                                <option value="1-1">1-1</option>
                                <option value="1-2">1-2</option>
                                <option value="1-3">1-3</option>
                                <option value="2-3">2-3</option>
                                <option value="1-4">1-4</option>
                                <option value="3-4">3-4</option>
                            </select>
                        </div>
                    </div>

                    <div class="uk-text-right">

                        <ul class="uk-subnav">

                            <li>
                                <a class="uk-text-{ field.lst ? 'success':'muted'}" onclick="{ parent.togglelist }" title="{ App.i18n.get('Show field on list view') }">
                                    <i class="uk-icon-list"></i>
                                </a>
                            </li>

                            <li>
                                <a onclick="UIkit.modal('#field-{idx}').show()"><i class="uk-icon-cog uk-text-primary"></i></a>
                            </li>

                            <li>
                                <a class="uk-text-danger" onclick="{ parent.removefield }">
                                    <i class="uk-icon-trash"></i>
                                </a>
                            </li>

                        </ul>

                    </div>

                </div>

            </div>

            <div class="uk-modal" id="field-{idx}">
                <div class="uk-modal-dialog">

                    <div class="uk-form-row uk-text-bold">
                        { field.name || 'Field' }
                    </div>

                    <div class="uk-form-row">

                        <div class="uk-form-select uk-width-1-1">
                            <div class="uk-form-icon uk-width-1-1">
                                <i class="uk-icon-tag"></i>
                                <input class="uk-width-1-1 uk-form-small uk-form-blank" value="{ field.type.toUpperCase() }">
                            </div>
                            <select class="uk-width-1-1" bind="fields[{idx}].type">
                                <option each="{type,typeidx in parent.fieldtypes}" value="{type.value}">{type.name}</option>
                            </select>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <input class="uk-width-1-1" type="text" bind="fields[{idx}].label" placeholder="{ App.i18n.get('label') }">
                    </div>

                    <div class="uk-form-row">
                        <input class="uk-width-1-1" type="text" bind="fields[{idx}].info" placeholder="{ App.i18n.get('info') }">
                    </div>

                    <div class="uk-form-row">
                        <div class="uk-text-small uk-text-bold">{ App.i18n.get('Options') } <span class="uk-text-muted">JSON</span></div>
                        <field-object cls="uk-width-1-1" bind="fields[{idx}].options" rows="6" allowtabs="2"></field-object>
                    </div>

                    <div class="uk-form-row">
                        <input type="checkbox" bind="fields[{idx}].required"> { App.i18n.get('Required') }
                    </div>

                    <div class="uk-form-row">
                        <input type="checkbox" bind="fields[{idx}].localize"> { App.i18n.get('Localize') }
                    </div>

                    <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{ App.i18n.get('Close') }</button></div>

                </div>
            </div>

        </div>

        <div class="uk-margin-top">
            <a class="uk-button uk-button-link" onclick="{ addfield }"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Add field') }</a>
        </div>

    </div>

    <div class="uk-width-medium-1-3 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle" if="{ !fields.length && !reorder }">

        <div class="uk-animation-fade">

            <p class="uk-text-xlarge">
                <i class="uk-icon-list-alt"></i>
            </p>

            <hr>

            { App.i18n.get('No fields added yet') }. <a onclick="{ addfield }">{ App.i18n.get('Add field') }.</a>

        </div>

    </div>


    <script>

        this.mixin(RiotBindMixin);

        var $this = this;

        this.fields  = [];
        this.reorder = false;

        // get all available fields

        this.fieldtypes = [];

        for (var tag in riot.tags) {

            if(tag.indexOf('field-')==0) {

                f = tag.replace('field-', '');

                this.fieldtypes.push({name:f.toUpperCase(), value:f});
            }
        }
        // --

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this.fields !== value) {
                this.fields = value;
                this.update();
            }

        }.bind(this);

        this.on('bindingupdated', function(){
            $this.$setValue(this.fields);
        });

        this.one('mount', function(){

            UIkit.sortable(this.fieldscontainer, {

                dragCustomClass:'uk-form'

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                if (App.$(e.target).is(':input')) {
                    return;
                }

                ele = App.$(ele);

                var fields = $this.fields,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                fields.splice(cidx, 0, fields.splice(oidx, 1)[0]);

                // hack to force complete fields rebuild
                $this.fieldscontainer.style.height = $this.fieldscontainer.clientHeight;
                $this.fields = [];
                $this.reorder = true;
                $this.update();

                setTimeout(function() {
                    $this.reorder = false;
                    $this.fields = fields;
                    $this.update();
                    $this.$setValue(fields);
                    $this.fieldscontainer.style.height = '';
                }, 0);

            });

        });

        addfield() {

            this.fields.push({
                'name'    : '',
                'label'   : '',
                'type'    : 'text',
                'default' : '',
                'info'    : '',
                'localize': false,
                'options' : {},
                'width'   : '1-1',
                'lst'     : true
            });

            $this.$setValue(this.fields);
        }

        removefield(e) {
            this.fields.splice(e.item.idx, 1);
            $this.$setValue(this.fields);
        }

        togglelist(e) {
            e.item.field.lst = !e.item.field.lst;
        }

    </script>

</cp-fieldsmanager>
