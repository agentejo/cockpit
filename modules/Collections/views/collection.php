<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/collections')">@lang('Collections')</a></li>
        <li class="uk-active"><span>@lang('Collection')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>

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

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Icon')</label>
                   <div data-uk-dropdown="pos:'right-center'">
                       <img class="uk-display-block uk-margin uk-container-center" riot-src="{ collection.icon ? '@url('assets:app/media/icons/')'+collection.icon : '@url('collections:icon.svg')'}" alt="icon" style="max-width: 50%;">
                       <div class="uk-dropdown uk-dropdown-scrollable uk-dropdown-width-2">
                            <div class="uk-grid uk-grid-gutter">
                                <div>
                                    <a class="uk-dropdown-close" onclick="{ selectIcon }" icon=""><img src="@url('collections:icon.svg')" width="30" icon=""></a>
                                </div>
                                @foreach($app->helper("fs")->ls('*.svg', 'assets:app/media/icons') as $icon)
                                <div>
                                    <a class="uk-dropdown-close" onclick="{ selectIcon }" icon="{{ $icon->getFilename() }}"><img src="@url($icon->getRealPath())" width="30" icon="{{ $icon->getFilename() }}"></a>
                                </div>
                                @endforeach
                            </div>
                       </div>
                   </div>
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Color')</label>
                   <div class="uk-margin-small-top">
                       <field-colortag bind="collection.color" title="@lang('Color')" size="20px"></field-colortag>
                   </div>
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Description')</label>
                   <textarea class="uk-width-1-1 uk-form-large" name="description" bind="collection.description" rows="5"></textarea>
               </div>

                <div class="uk-margin">
                    <field-boolean bind="collection.sortable" title="@lang('Sortable entries')" label="@lang('Sortable entries')"></field-boolean>
                </div>

                <div class="uk-margin">
                    <field-boolean bind="collection.in_menu" title="@lang('Show in system menu')" label="@lang('Show in system menu')"></field-boolean>
                </div>

            </div>

            <div class="uk-width-medium-3-4">

                <div class="uk-form-row">


                    <h4>@lang('Fields')</h4>

                    <cp-fieldsmanager bind="collection.fields" listoption="true" templates="{ templates }"></cp-fieldsmanager>


                    <div class="uk-margin-large-top" show="{ collection.fields.length }">

                        <div class="uk-button-group uk-margin-right">
                            <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                            <a class="uk-button uk-button-large" href="@route('/collections/entries')/{ collection.name }" if="{ collection._id }"><i class="uk-icon-list"></i> @lang('Show entries')</a>
                        </div>

                        <a href="@route('/collections')">
                            <span show="{ !collection._id }">@lang('Cancel')</span>
                            <span show="{ collection._id }">@lang('Close')</span>
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </form>

    <script type="view/script">

        var $this = this, f;

        this.mixin(RiotBindMixin);

        this.collection = {{ json_encode($collection) }};
        this.templates = {{ json_encode($templates) }};

        this.on('update', function(){

            // lock name if saved
            if (this.collection._id) {
                this.name.disabled = true;
            }
        });

        this.on('mount', function(){

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                e.preventDefault();
                $this.submit();
                return false;
            });
        });

        selectIcon(e) {
            this.collection.icon = e.target.getAttribute('icon');
        }

        submit() {

            var collection = this.collection;

            App.callmodule('collections:saveCollection', [this.collection.name, collection]).then(function(data) {

                if (data.result) {

                    App.ui.notify("Saving successful", "success");
                    $this.collection = data.result;
                    $this.update();

                } else {

                    App.ui.notify("Saving failed.", "danger");
                }
            });
        }

    </script>

</div>
