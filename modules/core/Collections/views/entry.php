<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/collections')">@lang('Collections')</a></li>
        <li><a href="@route('/collections/entries/'.$collection['name'])">{{ $collection['name'] }}</a></li>
        <li class="uk-active"><span>@lang('Entry')</span></li>
    </ul>
</div>


<div class="uk-margin-top-large" riot-view>

    <div class="uk-alert" if="{ !fields.length }">
        @lang('No fields defined'). <a href="@route('/collections/collection')/{ collection.name }">@lang('Define collection fields').</a>
    </div>

    <form class="uk-form uk-width-medium-2-3" if="{ fields.length }" onsubmit="{ submit }">

        <div class="uk-grid uk-grid-match uk-grid-small uk-grid-gutter">

            <div class="uk-width-medium-{field.width} uk-grid-margin" each="{field,idx in fields}">

                <div class="uk-panel">

                    <label class="uk-text-bold">{ field.label || field.name }</label>

                    <div class="uk-margin-small-top">
                        <cockpit-field field="{ field }" bind="entry.{field.name}" cls="uk-form-large"></cockpit-field>
                    </div>

                    <div class="uk-margin-small-top uk-text-small uk-text-muted">
                        { field.info || ' ' }
                    </div>

                </div>

            </div>

        </div>

        <div class="uk-margin-top">
            <button class="uk-button uk-button-large uk-button-primary uk-margin-right">@lang('Save')</button>
            <a href="@route('/collections/entries/'.$collection['name'])">@lang('Cancel')</a>
        </div>

    </form>


    <script type="view/script">

        var $this = this;

        riot.util.bind(this);

        this.collection   = {{ json_encode($collection) }};
        this.fields  = this.collection.fields;

        this.entry   = {{ json_encode($entry) }};

        // fill with default values
        this.fields.forEach(function(field){

            if ($this.entry[field.name] === undefined) {
                $this.entry[field.name] = field.options && field.options.default || null;
            }

            if (field.type == 'password') {
                $this.entry[field.name] = '';
            }
        });

        submit() {

            App.callmodule('collections:save',[this.collection.name, this.entry]).then(function(data) {

                if (data.result) {

                    App.ui.notify("Saving successfull", "success");

                    $this.entry = data.result;

                    $this.fields.forEach(function(field){

                        if (field.type == 'password') {
                            $this.entry[field.name] = '';
                        }
                    });

                    $this.update();

                } else {
                    App.ui.notify("Saving failed.", "danger");
                }
            });
        }

    </script>

</div>
