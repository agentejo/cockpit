<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/regions')">@lang('Regions')</a></li>
        <li class="uk-active"><span>@lang('Region')</span></li>
    </ul>
</div>


<div class="uk-margin" riot-view>

    <form class="uk-form" onsubmit="{ submit }">

        <div class="uk-grid uk-grid-divider">

            <div class="uk-width-medium-1-4">

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Name')</label>
                   <input class="uk-width-1-1 uk-form-large" type="text" name="name" bind="region.name" pattern="[a-zA-Z0-9_]+" required>
                   <p class="uk-text-small uk-text-muted" if="{!region._id}">
                       @lang('Only alpha nummeric value is allowed')
                   </p>
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Label')</label>
                   <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="region.label">
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Color')</label>
                   <div class="uk-margin-small-top">
                       <field-colortag bind="region.color" title="@lang('Color')" size="20px"></field-colortag>
                   </div>
               </div>

               <div class="uk-grid-margin">
                   <label class="uk-text-small">@lang('Description')</label>
                   <textarea class="uk-width-1-1 uk-form-large" name="description" bind="region.description" rows="5"></textarea>
               </div>

            </div>

            <div class="uk-width-medium-3-4">

                <div class="uk-form-row">

                    <ul class="uk-tab uk-flex uk-flex-right uk-margin">
                        <li class="{ view==='template' ? 'uk-active':'' }"><a onclick="{ toggleview }">Template</a></li>
                        <li class="{ view==='fields' ? 'uk-active':'' }"><a onclick="{ toggleview }">Fields</a></li>
                    </ul>

                    <div class="uk-margin-large-top" show="{ view==='fields'}">

                        <h4>@lang('Fields')</h4>

                        <cp-fieldsmanager bind="region.fields"></cp-fieldsmanager>

                    </div>

                    <div class="uk-margin-large-top" show="{ view==='template'}">
                        <h4>@lang('Template')</h4>
                        <field-code bind="region.template" syntax="php"></field-code>
                    </div>

                    <div class="uk-margin-large-top">

                        <div class="uk-button-group uk-margin-right">
                            <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                            <a class="uk-button uk-button-large" href="@route('/regions/form')/{ region.name }" if="{ region._id }"><i class="uk-icon-eye"></i> @lang('Show form')</a>
                        </div>

                        <a href="@route('/regions')">
                            <span show="{ !region._id }">@lang('Cancel')</span>
                            <span show="{ region._id }">@lang('Close')</span>
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </form>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.view = 'template';

        this.region = {{ json_encode($region) }};

        this.on('mount', function(){

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                e.preventDefault();
                $this.submit();
                return false;
            });
        });

        this.on('update', function(){

            // lock name if saved
            if (this.region._id) {
                this.name.disabled = true;
            }
        });

        submit() {

            var region = this.region;

            App.callmodule('regions:saveRegion', [this.region.name, region]).then(function(data) {

                if (data.result) {

                    App.ui.notify("Saving successful", "success");
                    $this.region = data.result;
                    $this.update();

                } else {

                    App.ui.notify("Saving failed.", "danger");
                }
            });
        }

        toggleview() {
            this.view = this.view=='template' ? 'fields':'template';
        }

    </script>
</div>
