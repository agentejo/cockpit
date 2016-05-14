<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/forms')">@lang('Forms')</a></li>
        <li class="uk-active"><span>@lang('Form')</span></li>
    </ul>
</div>

<div class="uk-margin-large-top" riot-view>

    <form class="uk-form" onsubmit="{ submit }">

        <div class="uk-grid uk-grid-divider">

            <div class="uk-width-medium-1-2">

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Name')</label>
                   <input class="uk-width-1-1 uk-form-large" type="text" name="name" bind="form.name" pattern="[a-zA-Z0-9_]+" required>
                   <p class="uk-text-small uk-text-muted" if="{!form._id}">
                       @lang('Only alpha nummeric value is allowed')
                   </p>
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Label')</label>
                   <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.label">
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Color')</label>
                   <div class="uk-margin-small-top">
                       <field-colortag bind="form.color" title="@lang('Color')" size="20px"></field-colortag>
                   </div>
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Description')</label>
                   <textarea class="uk-width-1-1 uk-form-large" name="description" bind="form.description" rows="5"></textarea>
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Email')</label>
                   <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.email_forward">

                    <div class="uk-alert">
                        @lang('Leave the email field empty if you don`t want to recieve any form data via email.')
                    </div>
               </div>

               <div class="uk-margin">
                    <field-boolean bind="form.in_menu" title="@lang('Show in system menu')" cls="uk-form-small"></field-boolean>
                    <strong>@lang('Show in system menu')</strong>
               </div>

               <div class="uk-margin">
                    <field-boolean bind="form.save_entry" title="@lang('Save form data')" cls="uk-form-small"></field-boolean>
                    <strong>@lang('Save form data')</strong>
                </div>

            </div>

            <div class="uk-width-medium-1-2">

            </div>

        </div>

        <div class="uk-margin-large-top">

            <button class="uk-button uk-button-large uk-button-primary uk-margin-right">@lang('Save')</button>
            <a href="@route('/forms')">
                <span show="{ !form._id }">@lang('Cancel')</span>
                <span show="{ form._id }">@lang('Close')</span>
            </a>
        </div>

    </form>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.form = {{ json_encode($form) }};

        this.on('mount', function(){

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                if (e.preventDefault) {
                    e.preventDefault();
                } else {
                    e.returnValue = false; // ie
                }
                $this.submit();
                return false;
            });
        });

        this.on('update', function(){

            // lock name if saved
            if (this.form._id) {
                this.name.disabled = true;
            }
        });

        submit() {

            var form = this.form;

            App.callmodule('forms:saveForm', [this.form.name, form]).then(function(data) {

                if (data.result) {

                    App.ui.notify("Saving successful", "success");
                    $this.form = data.result;

                    $this.update();

                } else {

                    App.ui.notify("Saving failed.", "danger");
                }
            });
        }

    </script>

</div>
