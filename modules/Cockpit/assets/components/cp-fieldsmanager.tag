<cp-fieldsmanager>

    <div ref="fieldscontainer" class="uk-sortable uk-grid uk-grid-small uk-grid-gutter uk-form">

        <div class="uk-width-{field.width}" data-idx="{idx}" each="{ field,idx in fields }">

            <div class="uk-panel uk-panel-box uk-panel-card">

                <div class="uk-grid uk-grid-small">

                    <div class="uk-flex-item-1 uk-flex">
                        <input class="uk-flex-item-1 uk-form-small uk-form-blank" type="text" bind="fields[{idx}].name" placeholder="name" pattern="[a-zA-Z0-9_]+" required>
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

                            <li if="{parent.opts.listoption}">
                                <a class="uk-text-{ field.lst ? 'success':'muted'}" onclick="{ parent.togglelist }" title="{ App.i18n.get('Show field on list view') }">
                                    <i class="uk-icon-list"></i>
                                </a>
                            </li>

                            <li>
                                <a onclick="{ parent.fieldSettings }"><i class="uk-icon-cog uk-text-primary"></i></a>
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

        </div>

    </div>

    <div class="uk-modal uk-sortable-nodrag" ref="modalField">
        <div class="uk-modal-dialog" if="{field}">

            <div class="uk-form-row uk-text-large uk-text-bold">
                { field.name || 'Field' }
            </div>

            <div class="uk-tab uk-flex uk-flex-center uk-margin" data-uk-tab>
                <li class="uk-active"><a>{ App.i18n.get('General') }</a></li>
                <li><a>{ App.i18n.get('Access') }</a></li>
            </div>

            <div class="uk-margin-top ref-tab">
                <div>
                    <div class="uk-form-row">

                        <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Type') }:</label>
                        <div class="uk-form-select uk-width-1-1 uk-margin-small-top">
                            <a class="uk-text-capitalize">{ field.type }</a>
                            <select class="uk-width-1-1 uk-text-capitalize" bind="field.type">
                                <option each="{type,typeidx in fieldtypes}" value="{type.value}">{type.name}</option>
                            </select>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Label') }:</label>
                        <input class="uk-width-1-1 uk-margin-small-top" type="text" bind="field.label" placeholder="{ App.i18n.get('Label') }">
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Info') }:</label>
                        <input class="uk-width-1-1 uk-margin-small-top" type="text" bind="field.info" placeholder="{ App.i18n.get('Info') }">
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Group') }:</label>
                        <input class="uk-width-1-1 uk-margin-small-top" type="text" bind="field.group" placeholder="{ App.i18n.get('Group name') }">
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small uk-text-bold uk-margin-small-bottom">{ App.i18n.get('Options') } <span class="uk-text-muted">JSON</span></label>
                        <field-object cls="uk-width-1-1" bind="field.options" rows="6" allowtabs="2"></field-object>
                    </div>

                    <div class="uk-form-row">
                        <field-boolean bind="field.required" label="{ App.i18n.get('Required') }"></field-boolean>
                    </div>

                    <div class="uk-form-row" if="{opts.localize !== false}">
                        <field-boolean bind="field.localize" label="{ App.i18n.get('Localize') }"></field-boolean>
                    </div>

                </div>
                <div class="uk-hidden">
                    <field-access-list class="uk-margin-large uk-margin-large-top uk-display-block" bind="field.acl"></field-access-list>
                </div>
            </div>

            <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{ App.i18n.get('Close') }</button></div>

        </div>
    </div>

    <div class="uk-margin-top" show="{fields.length}">
        <a class="uk-button uk-button-outline uk-text-primary" onclick="{ addfield }"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Add field') }</a>
    </div>

    <div class="uk-width-medium-1-3 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle" if="{ !fields.length && !reorder }">

        <div class="uk-animation-fade">

            <p class="uk-text-xlarge">
                <img riot-src="{ App.base('/assets/app/media/icons/form-editor.svg') }" width="100" height="100" >
            </p>

            <hr>

            { App.i18n.get('No fields added yet') }.
            <span data-uk-dropdown="pos:'bottom-center'">
                <a onclick="{ addfield }">{ App.i18n.get('Add field') }.</a>
                <div class="uk-dropdown uk-dropdown-scrollable uk-text-left" if="{opts.templates && opts.templates.length}">
                    <ul class="uk-nav uk-nav-dropdown">
                        <li class="uk-nav-header">{ App.i18n.get('Choose from template') }</li>
                        <li each="{template in opts.templates}">
                            <a onclick="{ parent.fromTemplate.bind(parent, template) }"><i class="uk-icon-sliders uk-margin-small-right"></i> { template.label || template.name }</a>
                        </li>
                    </ul>
                </div>
            <span>

        </div>

    </div>


    <script>

        riot.util.bind(this);

        var $this = this;

        this.fields  = [];
        this.field = null;
        this.reorder = false;

        // get all available fields

        this.fieldtypes = [];

        for (var tag in riot.tags) {

            if (tag.indexOf('field-')==0) {

                f = tag.replace('field-', '');

                this.fieldtypes.push({name:f, value:f});
            }
        }
        // --

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this.fields !== value) {

                this.fields = value;

                this.fields.forEach(function(field) {
                    if (Array.isArray(field.options)) {
                        field.options = {};
                    }
                });

                this.update();
            }

        }.bind(this);

        this.on('bindingupdated', function(){
            $this.$setValue(this.fields);
        });

        this.one('mount', function(){

            UIkit.sortable(this.refs.fieldscontainer, {

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
                App.$($this.refs.fieldscontainer).css('height', App.$($this.refs.fieldscontainer).height());

                $this.fields = [];
                $this.reorder = true;
                $this.update();

                setTimeout(function() {
                    $this.reorder = false;
                    $this.fields = fields;
                    $this.update();
                    $this.$setValue(fields);

                    setTimeout(function(){
                        $this.refs.fieldscontainer.style.height = '';
                    }, 30)
                }, 0);

            });

            App.$(this.root).on('click', '.uk-modal [data-uk-tab] li', function(e) {
                var item = App.$(this),
                    idx = item.index();

                item.closest('.uk-tab')
                    .next('.ref-tab')
                    .children().addClass('uk-hidden').eq(idx).removeClass('uk-hidden')
            });
        });

        addfield() {

            this.fields.push({
                'name'    : '',
                'label'   : '',
                'type'    : 'text',
                'default' : '',
                'info'    : '',
                'group'   : '',
                'localize': false,
                'options' : {},
                'width'   : '1-1',
                'lst'     : true,
                'acl'     : []
            });

            $this.$setValue(this.fields);
        }

        removefield(e) {
            this.fields.splice(e.item.idx, 1);
            $this.$setValue(this.fields);
        }

        fieldSettings(e) {

            this.field = e.item.field;

            UIkit.modal(this.refs.modalField).show()
        }

        togglelist(e) {
            e.item.field.lst = !e.item.field.lst;
        }

        fromTemplate(template) {

            if (template && Array.isArray(template.fields) && template.fields.length) {
                this.fields = template.fields;
                $this.$setValue(this.fields);
            }
        }

    </script>

</cp-fieldsmanager>
