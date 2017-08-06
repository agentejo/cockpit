<field-layout>

    <div class="uk-text-center {opts.child ? 'uk-text-small':'uk-placeholder'}" show="{ !items.length }">
        { App.i18n.get('No Components') }
    </div>

    <div class="uk-sortable" ref="components" show="{mode=='edit' && items.length}" data-uk-sortable>
        <div class="uk-margin uk-panel-box uk-panel-card" each="{ item,idx in items }" data-idx="{idx}">
            
            <div class="uk-flex uk-flex-middle uk-text-small uk-visible-hover">
                <img class="uk-margin-small-right" riot-src="{ parent.components[item.component].icon ? parent.components[item.component].icon : App.base('/assets/app/media/icons/component.svg')}" width="16">
                <div class="uk-text-bold uk-text-truncate uk-flex-item-1">
                    { parent.components[item.component].label || App.Utils.ucfirst(item.component) }
                </div>
                <div class="uk-button-group uk-invisible">
                    <a class="uk-button uk-button-small" onclick="{ parent.addComponent }" title="{ App.i18n.get('Add Colum') }"><i class="uk-icon-plus"></i></a>
                    <a class="uk-button uk-button-small" onclick="{ parent.settings }"><i class="uk-icon-cogs"></i></a>
                    <a class="uk-button uk-button-small uk-button-danger" onclick="{ parent.remove }"><i class="uk-icon-trash-o"></i></a>
                </div>
            </div>

            <div class="uk-margin" if="{parent.components[item.component].children}">
                <field-layout bind="items[{idx}].children" child="true"></field-layout>
            </div>

            <div class="uk-margin" if="{item.component == 'grid'}">
                <field-layout-grid bind="items[{idx}].columns"></field-layout-grid>
            </div>
            
        </div>
    </div>

    <div class="uk-margin uk-text-center">
        <a class="uk-button { !opts.child ? 'uk-button-primary uk-button-large':'uk-button-small'}" onclick="{ addComponent }" title="{ App.i18n.get('Add component') }" data-uk-tooltip="pos:'bottom'"><i class="uk-icon-plus-circle"></i></a>
    </div>

    <div class="uk-modal uk-sortable-nodrag" ref="modalComponents">
        <div class="uk-modal-dialog">
            <h3 class="uk-flex uk-flex-middle">
                <img class="uk-margin-small-right" riot-src="{App.base('/assets/app/media/icons/component.svg')}" width="30">
                { App.i18n.get('Components') }
            </h3>

            <div class="uk-grid uk-grid-match uk-grid-small uk-grid-width-medium-1-4">
                 <div class="uk-grid-margin" each="{component,name in components}">
                    <div class="uk-panel uk-panel-framed uk-text-center">
                        <img riot-src="{ component.icon || App.base('/assets/app/media/icons/component.svg')}" width="30">
                        <p class="uk-text-small">{ component.label || App.Utils.ucfirst(name) }</p>
                        <a class="uk-position-cover" onclick="{ add }"></a>
                    </div>
                </div>
            </div>

            <div class="uk-text-right uk-margin-top">
                <a class="uk-button uk-button-link uk-button-large uk-modal-close">{ App.i18n.get('Close') }</a>
            </div>
        </div>
    </div>

    <div class="uk-modal uk-sortable-nodrag" ref="modalSettings">
        <div class="uk-modal-dialog" if="{settingsComponent}">
            
            <h3 class="uk-margin-large-bottom">
                <img class="uk-margin-small-right" riot-src="{ components[settingsComponent.component].icon ? components[settingsComponent.component].icon : App.base('/assets/app/media/icons/settings.svg')}" width="30">
                { components[settingsComponent.component].label || App.Utils.ucfirst(settingsComponent.component) }
            </h3>

            <div class="uk-margin" if="{components[settingsComponent.component].fields}">
                <field-set class="uk-margin" bind="settingsComponent.settings" fields="{components[settingsComponent.component].fields}"></field-set>
            </div>

            <div class="uk-margin">
                <field-set class="uk-margin" bind="settingsComponent.settings" fields="{generalSettingsFields}"></field-set>
            </div>

            <div class="uk-text-right uk-margin-top">
                <a class="uk-button uk-button-link uk-button-large uk-modal-close">{ App.i18n.get('Close') }</a>
            </div>
            
        </div>
    </div>

    <script>
        
        var $this = this;
        
        riot.util.bind(this);

        this.mode = 'edit';
        this.items = [];
        this.settingsComponent = null;
        this.generalSettingsFields  = [
            {name: "id", type: "text" },
            {name: "class", type: "text" },
            {name: "style", type: "code", options: {syntax: "css"} }
        ];

        this.on('mount', function() {

            App.$(this.refs.components).on('change.uk.sortable', function(e, sortable, el, mode) {
                if ($this.refs.components === sortable.element[0]) {

                    var items = [];

                    App.$($this.refs.components).children().each(function() {
                        items.push(this._tag.item);
                    });

                    $this.$setValue(items);
                    $this.update();
                }
            });

            UIkit.modal(this.refs.modalSettings, {modal:false}).on('hide.uk.modal', function(e) {
                if (e.target !== $this.refs.modalSettings) return;
                $this.settingsComponent = false;
                $this.update();
            });

            this.trigger('update');
        });

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

        addComponent(e) {

            this.refs.modalComponents.afterComponent = e.item.idx || e.item.idx === 0 ? e.item.idx : false;

            UIkit.modal(this.refs.modalComponents, {modal:false}).show();
        }

        add(e) {

            var item = {
                component: e.item.name,
                settings: { id: '', 'class': '', style: '' }
            };

            if (this.components[e.item.name].children) {
                item.children = [];
            }

            if (this.refs.modalComponents.afterComponent !== false) {
                this.items.splice(this.refs.modalComponents.afterComponent + 1, 0, item);
                this.refs.modalComponents.afterComponent = false;
            } else {
                this.items.push(item);
            }

            this.$setValue(this.items, true);

            setTimeout(function() {
                UIkit.modal(this.refs.modalComponents).hide();
            }.bind(this));
        }

        remove(e) {
            this.items.splice(e.item.idx, 1);
        }

        settings(e) {
            
            this.settingsComponent = e.item.item;

            setTimeout(function() {
                UIkit.modal(this.refs.modalSettings, {modal:false}).show();
            }.bind(this));
        }

        this.components = {
            "section": {
                "children":true
            },
            
            "grid": {

            },

            "text": {
                "icon": App.base('/assets/app/media/icons/text.svg'),
                "fields": [
                    {"name": "text", "type": "wysiwyg"},
                ]
            },

            "html": {
                "icon": App.base('/assets/app/media/icons/code.svg'),
                "fields": [
                    {"name": "html", "type": "html"},
                ]
            },

            "heading": {
                "icon": App.base('/assets/app/media/icons/heading.svg'),
                "fields": [
                    {"name": "text", "type": "text"},
                ]
            },

            "image": {
                "icon": App.base('/assets/app/media/icons/photo.svg'),
                "fields": [
                    {"name": "image", "type": "image"}
                ]
            },

            "divider": {
                "icon": App.base('/assets/app/media/icons/divider.svg'),
            },

            "button": {
                "icon": App.base('/assets/app/media/icons/button.svg'),
                "fields": [
                    {"name": "text", "type": "text"},
                    {"name": "url", "type": "text"},
                ]
            }
        };
        
    </script>

</field-layout>

<field-layout-grid>

    <div class="uk-text-center uk-placeholder" if="{!columns.length}">
        <a class="uk-button uk-button-link" onclick="{ addColumn }">{ App.i18n.get('Add Colum') }</a>
    </div>

    <div class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-grid-width-medium-1-{columns.length}" show="{columns.length}" ref="columns" data-uk-sortable>
        <div class="uk-grid-margin" each="{column,idx in columns}">
            <div class="uk-panel uk-panel-framed">
                <div class="uk-flex uk-flex-middle uk-text-small uk-visible-hover">
                    <div class="uk-flex-item-1 uk-margin-small-right"><strong class="uk-text-muted uk-text-small">{ (idx+1) }</strong></div>
                    <a class="uk-invisible uk-margin-small-right" onclick="{ parent.addColumn }" title="{ App.i18n.get('Add Colum') }"><i class="uk-icon-plus"></i></a>
                    <a class="uk-invisible uk-margin-small-right" onclick="{ parent.settings }" title="{ App.i18n.get('Settings') }"><i class="uk-icon-cog"></i></a>
                    <a class="uk-invisible" onclick="{ parent.remove }"><i class="uk-text-danger uk-icon-trash-o"></i></a>
                </div>
                <div class="uk-margin">
                    <field-layout bind="columns[{idx}].children" child="true"></field-layout>
                </div>
            </div>
        </div>
    </div>

    <div class="uk-modal uk-sortable-nodrag" ref="modalSettings">
        <div class="uk-modal-dialog" if="{settingsComponent}">
            <h3 class="uk-flex uk-flex-middle uk-margin-large-bottom">
                <img class="uk-margin-small-right" riot-src="{App.base('/assets/app/media/icons/settings.svg')}" width="30">
                { App.i18n.get('Column') }
            </h3>
            <field-set class="uk-margin" bind="settingsComponent.settings" fields="{fields}"></field-set>
        
            <div class="uk-text-right uk-margin-top">
                <a class="uk-button uk-button-link uk-button-large uk-modal-close">{ App.i18n.get('Close') }</a>
            </div>

        </div>
    </div>

    <script>
    
        var $this = this;

        riot.util.bind(this);

        this.columns = [];
        this.fields  = [
            {name: "id", type: "text" },
            {name: "class", type: "text" },
            {name: "style", type: "code", options: {syntax: "css"}  }
        ];
        this.settingsComponent = null;

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.columns) != JSON.stringify(value)) {
                this.columns = value;
                this.update();
            }

        }.bind(this);

        this.$initBind = function() {
            this.root.$value = this.columns;
        };

        this.on('mount', function() {

            App.$(this.refs.columns).on('change.uk.sortable', function(e, sortable, el, mode) {
                
                if ($this.refs.columns === sortable.element[0]) {

                    var columns = [];

                    App.$($this.refs.columns).children().each(function() {
                        columns.push(this._tag.column);
                    });

                    $this.$setValue(columns);
                    $this.update();
                }
            });


            this.trigger('update');
        });

        addColumn() {
            
            var column = {
                settings: { id: '', 'class': '', style: '' },
                children: []
            };

            this.columns.push(column);
            this.$setValue(this.columns);
        }

        settings(e) {
            
            this.settingsComponent = e.item.column;

            setTimeout(function() {
                UIkit.modal(this.refs.modalSettings).show();
            }.bind(this));
        }

        remove(e) {
            this.columns.splice(e.item.idx, 1);
        }
    
    </script>

</field-layout-grid>