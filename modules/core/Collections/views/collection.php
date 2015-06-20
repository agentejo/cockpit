<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/collections')">@lang('Collections')</a></li>
        <li class="uk-active"><span>@lang('Collection')</span></li>
    </ul>
</div>

<div class="uk-margin-large-top" riot-view>

    <form class="uk-form" onsubmit="{ submit }">

        <div class="uk-grid uk-grid-divider">

            <div class="uk-width-medium-1-4">

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Name')</label>
                   <input class="uk-width-1-1 uk-form-large" type="text" name="name" bind="collection.name" pattern="[a-zA-Z0-9_]+" required>
                   <p class="uk-text-small uk-text-muted" if="{!collection._id}">
                       @lang('Only alpha nummeric value is allowed')
                   </p>
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Label')</label>
                   <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="collection.label">
               </div>

               <div class="uk-grid-margin">
                   <label class="uk-text-small">@lang('Description')</label>
                   <textarea class="uk-width-1-1 uk-form-large" name="description" bind="collection.description" rows="5"></textarea>
               </div>

                <div class="uk-margin">
                    <field-boolean bind="collection.sortable" title="@lang('Sortable entries')" cls="uk-form-small"></field-boolean>
                    <strong>@lang('Sortable entries')</strong>
                </div>

            </div>

            <div class="uk-width-medium-3-4">

                <div class="uk-form-row">

                    <div show="{ collection.fields.length }">

                            <h4>@lang('Fields')</h4>

                            <div name="fieldscontainer" class="uk-grid uk-grid-small uk-grid-gutter">

                                <div class="uk-grid-margin uk-width-{field.width}" data-idx="{idx}" each="{ field,idx in collection.fields }">

                                    <div class="uk-panel uk-panel-box uk-panel-card">

                                        <div class="uk-grid uk-grid-small">

                                            <div class="uk-flex-item-1 uk-flex">


                                                <input class="uk-flex-item-1 uk-form-small uk-form-blank" type="text" bind="collection.fields[{idx}].name" placeholder="name" required>
                                            </div>

                                            <div class="uk-width-1-4">
                                                <div class="uk-form-select" data-uk-form-select>
                                                    <div class="uk-form-icon">
                                                        <i class="uk-icon-arrows-h"></i>
                                                        <input class="uk-width-1-1 uk-form-small uk-form-blank" value="{ field.width }">
                                                    </div>
                                                    <select bind="collection.fields[{idx}].width">
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
                                                        <a class="uk-text-{ field.lst ? 'success':'muted'}" onclick="{ parent.togglelist }" title="@lang('Show field on list view')">
                                                            <i class="uk-icon-list"></i>
                                                        </a>
                                                    </li>

                                                    <li>

                                                        <a data-uk-dropdown="\{mode:'click'\}">

                                                            <i class="uk-icon-cog uk-text-primary"></i>

                                                            <div class="uk-dropdown uk-dropdown-center uk-text-left uk-dropdown-width-2">

                                                                <div class="uk-form-row uk-text-bold">
                                                                    { field.name || 'Field' }
                                                                </div>

                                                                <div class="uk-form-row">

                                                                    <div class="uk-form-select uk-width-1-1" data-uk-form-select>
                                                                        <div class="uk-form-icon uk-width-1-1">
                                                                            <i class="uk-icon-tag"></i>
                                                                            <input class="uk-width-1-1 uk-form-small uk-form-blank" value="{ field.type }">
                                                                        </div>
                                                                        <select class="uk-width-1-1" bind="collection.fields[{idx}].type">
                                                                            <option value="text">Text</option>
                                                                            <option value="textarea">Textarea</option>
                                                                            <option value="html">HTML</option>
                                                                            <option value="markdown">Markdown</option>
                                                                            <option value="wysiwyg">WYSIWYG</option>
                                                                            <option value="code">Code</option>
                                                                            <option value="url">Url</option>
                                                                            <option value="email">Email</option>
                                                                            <option value="password">Password</option>
                                                                            <option value="number">Number</option>
                                                                            <option value="boolean">Boolean</option>
                                                                            <option value="select">Select</option>
                                                                            <option value="file">File</option>
                                                                            <option value="gallery">Gallery</option>
                                                                            <option value="tags">Tags</option>
                                                                            <option value="date">Date</option>
                                                                            <option value="time">Time</option>
                                                                            <option value="location">Location</option>
                                                                            <option value="object">Object</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="uk-form-row">
                                                                    <input class="uk-width-1-1" type="text" bind="collection.fields[{idx}].label" placeholder="@lang('label')">
                                                                </div>

                                                                <div class="uk-form-row">
                                                                    <input class="uk-width-1-1" type="text" bind="collection.fields[{idx}].info" placeholder="@lang('info')">
                                                                </div>

                                                                <div class="uk-form-row">
                                                                    <div class="uk-text-small uk-text-bold">@lang('Options') <span class="uk-text-muted">JSON</span></div>
                                                                    <field-longtext cls="uk-width-1-1" bind="collection.fields[{idx}].options" rows="6" allowtabs="2"></field-longtext>
                                                                </div>

                                                                <div class="uk-form-row">
                                                                    <input type="checkbox" bind="collection.fields[{idx}].required"> @lang('Required')
                                                                </div>

                                                                <div class="uk-form-row">
                                                                    <input type="checkbox" bind="collection.fields[{idx}].localize"> @lang('Localize')
                                                                </div>

                                                            </div>

                                                        </a>
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

                            <div class="uk-margin-top">
                                <a class="uk-button uk-button-link" onclick="{ addfield }"><i class="uk-icon-plus-circle"></i> @lang('Add field')</a>
                            </div>

                        </div>

                    </div>

                    <div class="uk-width-medium-1-3 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle" if="{ !collection.fields.length && !reorder }">

                        <div class="uk-animation-fade">

                            <p class="uk-text-xlarge">
                                <i class="uk-icon-list-alt"></i>
                            </p>

                            <hr>

                            @lang('No fields added yet'). <a onclick="{ addfield }">@lang('Add field').</a>

                        </div>

                    </div>

                    <div class="uk-margin-large-top" show="{ collection.fields.length }">

                        <div class="uk-button-group uk-margin-right">
                            <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                            <a class="uk-button uk-button-large" href="@route('/collections/entries')/{ collection.name }" if="{ collection._id }"><i class="uk-icon-list"></i> @lang('Show entries')</a>
                        </div>

                        <a href="@route('/collections')">@lang('Cancel')</a>
                    </div>

            </div>

        </div>


    </form>

    <script type="view/script">

        var $this = this;

        this.collection = {{ json_encode($collection) }};

        stringifyOptionsField();

        riot.util.bind(this);

        this.one('mount', function(){

            UIkit.sortable(this.fieldscontainer, {

                dragCustomClass:'uk-form'

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                if (App.$(e.target).is(':input')) {
                    return;
                }

                ele = App.$(ele);

                var fields = $this.collection.fields,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                fields.splice(cidx, 0, fields.splice(oidx, 1)[0]);

                // hack to force complete fields rebuild
                $this.fieldscontainer.style.height = $this.fieldscontainer.clientHeight;
                $this.collection.fields = [];
                $this.reorder = true;
                $this.update();

                setTimeout(function() {
                    $this.collection.fields = fields;
                    $this.reorder = false;
                    $this.update();
                    $this.fieldscontainer.style.height = '';
                }, 0);

            });

        });

        this.on('update', function(){

            // lock name if saved
            if (this.collection._id) {
                this.name.disabled = true;
            }
        });

        addfield() {

            this.collection.fields.push({
                'name'    : '',
                'label'   : '',
                'type'    : 'text',
                'default' : '',
                'info'    : '',
                'localize': false,
                'options' : '{}',
                'width'   : '1-1',
                'lst'     : true
            });
        }

        togglelist(e) {
            e.item.field.lst = !e.item.field.lst;
        }

        removefield(e) {
            this.collection.fields.splice(e.item.idx, 1);
        }

        submit() {

            var collection = this.collection;

            collection.fields.forEach(function(field){
                field.options = App.Utils.str2json(field.options) || {};
            });

            App.callmodule('collections:saveCollection', [this.collection.name, collection]).then(function(data) {

                if (data.result) {

                    App.ui.notify("Saving successfull", "success");
                    $this.collection = data.result;

                    stringifyOptionsField();

                    $this.update();

                } else {

                    App.ui.notify("Saving failed.", "danger");
                }
            });
        }

        function stringifyOptionsField() {

            $this.collection.fields.forEach(function(field, options){

                options = field.options ? JSON.stringify(field.options, null, 2) : '{}';

                if (options == '[]') {
                    options = '{}';
                }

                field.options = options;
            });
        }

    </script>

</div>
