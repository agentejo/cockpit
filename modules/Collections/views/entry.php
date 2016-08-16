<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/collections')">@lang('Collections')</a></li>
        <li data-uk-dropdown="mode:'hover, delay:300'">
            <a href="@route('/collections/entries/'.$collection['name'])"><i class="uk-icon-bars"></i> {{ @$collection['label'] ? $collection['label']:$collection['name'] }}</a>

            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Actions')</li>
                    <li><a href="@route('/collections/collection/'.$collection['name'])">@lang('Edit')</a></li>
                    <li class="uk-nav-divider"></li>
                    <li class="uk-text-truncate"><a href="@route('/collections/export/'.$collection['name'])" download="{{ $collection['name'] }}.collection.json">@lang('Export entries')</a></li>
                    <li class="uk-text-truncate"><a href="@route('/collections/import/collection/'.$collection['name'])">@lang('Import entries')</a></li>
                </ul>
            </div>
        </li>
        <li class="uk-active"><span>@lang('Entry')</span></li>
    </ul>
</div>

@if(isset($collection['color']) && $collection['color'])
<style>
    .app-header { border-top: 8px {{ $collection['color'] }} solid; }
</style>
@endif


<div class="uk-margin-top-large" riot-view>

    <div class="uk-alert" if="{ !fields.length }">
        @lang('No fields defined'). <a href="@route('/collections/collection')/{ collection.name }">@lang('Define collection fields').</a>
    </div>


    <div class="uk-grid" data-uk-grid-margin>

        <div class="uk-width-medium-3-4">

            <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

                <h3>{ entry._id ? 'Edit':'Add' } @lang('Entry')</h3>

                <br>

                <div class="uk-grid uk-grid-match uk-grid-gutter">

                    <div class="uk-width-medium-{field.width}" each="{field,idx in fields}" no-reorder>

                        <div class="uk-panel">

                            <label class="uk-text-bold">
                                { field.label || field.name }
                                <span if="{ field.localize }" class="uk-icon-globe" title="@lang('Localized field')" data-uk-tooltip="pos:'right'"></span>
                            </label>

                            <div class="uk-margin uk-text-small uk-text-muted">
                                { field.info || ' ' }
                            </div>

                            <div class="uk-margin">
                                <cp-field field="{ field }" bind="entry.{ field.localize && parent.lang ? (field.name+'_'+parent.lang):field.name }" cls="uk-form-large"></cp-field>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="uk-margin-top">
                    <button class="uk-button uk-button-large uk-button-primary uk-margin-right">@lang('Save')</button>
                    <a href="@route('/collections/entries/'.$collection['name'])">
                        <span show="{ !entry._id }">@lang('Cancel')</span>
                        <span show="{ entry._id }">@lang('Close')</span>
                    </a>
                </div>

            </form>

        </div>

        <div class="uk-width-medium-1-4">

            <div class="uk-panel">

                <div class="uk-margin uk-form" if="{ languages.length }">

                    <div class="uk-width-1-1 uk-form-select">

                        <label class="uk-text-small">@lang('Language')</label>
                        <div class="uk-margin-small-top">{ lang || 'Default' }</div>

                        <select bind="lang">
                            <option value="">@lang('Default')</option>
                            <option each="{language,idx in languages}" value="{language}">{language}</option>
                        </select>
                    </div>

                </div>

                <div class="uk-margin">
                    <label class="uk-text-small">@lang('Last Modified')</label>
                    <div class="uk-margin-small-top uk-text-muted" if="{entry._id}">
                        <i class="uk-icon-calendar uk-margin-small-right"></i> {  App.Utils.dateformat( new Date( 1000 * entry._modified )) }
                    </div>
                    <div class="uk-margin-small-top uk-text-muted" if="{!entry._id}">@lang('Not saved yet')</div>
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

        this.on('mount', function(){

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                e.preventDefault();
                $this.submit();
                return false;
            });
        });

        submit() {

            App.callmodule('collections:save',[this.collection.name, this.entry]).then(function(data) {

                if (data.result) {

                    App.ui.notify("Saving successful", "success");

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
