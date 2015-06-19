<div>

    <ul class="uk-breadcrumb">
        <li><a href="@route('/regions')">@lang('Regions')</a></li>
        <li class="uk-active" data-uk-dropdown="{mode:'click'}">

            <a><i class="uk-icon-bars"></i> {{ @$region['label'] ? $region['label']:$region['name'] }}</a>

            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Actions')</li>
                    <li><a href="@route('/regions/region/'.$region['name'])">@lang('Edit')</a></li>
                </ul>
            </div>

        </li>
    </ul>

    @if(isset($region['description']) && $region['description'])
    <div class="uk-text-muted uk-margin uk-panel-box">
        <i class="uk-icon-info-circle"></i> {{ $region['description'] }}
    </div>
    @endif

    <div class="uk-margin-top-large" riot-view>

        <div class="uk-alert" if="{ !fields.length }">
            @lang('No fields defined'). <a href="@route('/regions/region')/{ region.name }">@lang('Define region fields').</a>
        </div>

        <form class="uk-form uk-width-medium-2-3" if="{ fields.length }" onsubmit="{ submit }">

            <div class="uk-grid uk-grid-match uk-grid-small uk-grid-gutter">

                <div class="uk-width-medium-{field.width} uk-grid-margin" each="{field,idx in fields}">

                    <div class="uk-panel">

                        <label class="uk-text-bold uk-text-small">{ field.label || field.name }</label>

                        <div class="uk-margin-small-top">
                            <cp-field field="{ field }" bind="data.{field.name}" cls="uk-form-large"></cp-field>
                        </div>

                        <div class="uk-margin-small-top uk-text-small uk-text-muted">
                            { field.info || ' ' }
                        </div>

                    </div>

                </div>

            </div>

            <div class="uk-margin-top">
                <button class="uk-button uk-button-large uk-button-primary uk-margin-right">@lang('Save')</button>
                <a href="@route('/regions')">@lang('Cancel')</a>
            </div>

        </form>


        <script type="view/script">

            var $this = this;

            riot.util.bind(this);

            this.region   = {{ json_encode($region) }};
            this.fields  = this.region.fields;

            this.data   = this.region.data || {};

            // fill with default values
            this.fields.forEach(function(field){

                if ($this.data[field.name] === undefined) {
                    $this.data[field.name] = field.options && field.options.default || null;
                }

                if (field.type == 'password') {
                    $this.data[field.name] = '';
                }
            });

            submit() {

                App.callmodule('regions:updateRegion',[this.region.name, {data:this.data}]).then(function(data) {

                    if (data.result) {

                        App.ui.notify("Saving successfull", "success");

                        $this.data = data.result.data;

                        $this.fields.forEach(function(field){

                            if (field.type == 'password') {
                                $this.data[field.name] = '';
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

</div>