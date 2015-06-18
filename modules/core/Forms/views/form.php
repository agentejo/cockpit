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
                   <input class="uk-width-1-1 uk-form-large" type="text" name="name" bind="form.name" required>
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Label')</label>
                   <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.label">
               </div>

               <div class="uk-grid-margin">
                   <label class="uk-text-small">@lang('Description')</label>
                   <textarea class="uk-width-1-1 uk-form-large" name="description" bind="form.description" rows="5"></textarea>
               </div>

               <div class="uk-margin">
                   <label class="uk-text-small">@lang('Email')</label>
                   <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.email_forward">

                    <div class="uk-alert">
                        @lang('Leave the email field empty if you don\'t want to recieve any form data via email.')
                    </div>
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
            <a href="@route('/forms')">@lang('Cancel')</a>
        </div>

    </form>

    <script type="view/script">

        var $this = this;

        this.form = {{ json_encode($form) }};

        riot.util.bind(this);

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

                    App.ui.notify("Saving successfull", "success");
                    $this.form = data.result;

                    stringifyOptionsField();

                    $this.update();

                } else {

                    App.ui.notify("Saving failed.", "danger");
                }
            });
        }

    </script>

</div>
