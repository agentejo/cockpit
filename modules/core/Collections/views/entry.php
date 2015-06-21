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


    <div class="uk-grid">

        <div class="uk-width-medium-2-3">


            <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

                <h3>{ entry._id ? 'Edit':'Add' } @lang('Entry')</h3>

                <div class="uk-grid uk-grid-match uk-grid-small uk-grid-gutter">

                    <div class="uk-width-medium-{field.width} uk-grid-margin" each="{field,idx in fields}">

                        <div class="uk-panel">

                            <label class="uk-text-bold uk-text-small">{ field.label || field.name }</label>

                            <div class="uk-margin-small-top">
                                <cp-field field="{ field }" bind="entry.{ field.localize && parent.lang ? (parent.lang+'_'+field.name):field.name }" cls="uk-form-large"></cp-field>
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

        </div>

        <div class="uk-width-medium-1-4">

            <div class="uk-margin uk-form" if="{ languages.length }">

                <div class="uk-width-1-1 uk-form-select">

                    <label class="uk-text-small">@lang('Language')</label>

                    <input class="uk-width-1-1" type="text" value="{ lang || 'Default' }">

                    <select bind="lang">
                        <option value="">@lang('Default')</option>
                        <option each="{language,idx in languages}" value="{language}">{language}</option>
                    </select>
                </div>

            </div>

        </div>

    </div>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.collection   = {{ json_encode($collection) }};
        this.fields       = this.collection.fields;

        this.entry        = {{ json_encode($entry) }};

        this.languages    = App.$data.languages;

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
