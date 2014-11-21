@start('header')

    {{ $app->assets(['regions:assets/regions.js','regions:assets/js/region.js'], $app['cockpit/version']) }}
    {{ $app->assets(['assets:vendor/uikit/js/components/nestable.min.js'], $app['cockpit/version']) }}

    @trigger('cockpit.content.fields.sources')

    <script>
        var LOCALES = {{ json_encode($locales) }};
    </script>

@end('header')


<div data-ng-controller="region" data-id="{{ $id }}" ng-cloak>

    <div id="region-versions" class="uk-offcanvas">
        <div class="uk-offcanvas-bar">
            <div class="uk-panel">

                <div data-ng-show="versions.length">
                    <h3 class="uk-panel-title">@lang('Versions')</h3>

                    <ul class="uk-nav uk-nav-offcanvas" data-ng-show="versions.length">
                        <li data-ng-repeat="version in versions">
                            <a href="#v-@@ version.uid @@" data-ng-click="restoreVersion(version.uid)" title="@lang('Restore this version')" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-clock-o"></i> @@ version.time | fmtdate:'d M, Y H:i:s' @@</a>
                        </li>
                    </ul>
                    <br>

                    <div class="uk-button-group">
                        <button type="button" class="uk-button uk-button-danger" data-ng-click="clearVersions()" title="@lang('Clear version history')" data-uk-tooltip="{pos:'bottom-left'}"><i class="uk-icon-trash-o"></i></button>
                        <button type="button" class="uk-button uk-button-primary" onclick="UIkit.offcanvas.hide()" title="@lang('Close versions')" data-uk-tooltip="{pos:'bottom-left'}">@lang('Cancel')</button>
                    </div>
                </div>

                <div class="uk-text-muted uk-text-center" data-ng-show="!versions.length">
                    <div class="uk-margin-small-bottom"><i class="uk-icon-clock-o"></i></div>
                    <div>@lang('Empty')</div>
                </div>
            </div>
        </div>
    </div>

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">
            <a href="@route("/regions")">@lang('Regions')</a> /
            <span class="uk-text-muted" ng-show="!region.name">@lang('Entry')</span>
            <span ng-show="region.name">@@ region.name @@</span>
        </span>
        @if(count($locales))
        <div class="uk-navbar-content uk-form" ng-show="hasLocals">
            <select ng-model="locale" data-uk-tooltip title="@lang('Language')">
                <option value="">Default</option>
                @foreach($locales as $locale)
                <option value="{{ $locale }}">{{ \Lime\Helper\I18n::$locals[$locale] }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="uk-navbar-content">
            <a href="#region-versions" data-uk-offcanvas data-ng-show="versions.length"><i class="uk-icon-clock-o"></i> @lang('Versions') <span class="uk-badge">@@ versions.length @@</span></a>
        </div>
    </nav>


    <form class="uk-form" data-ng-submit="save()" data-ng-show="region">

            <div class="uk-grid">

                <div class="uk-width-medium-4-5">

                    <div class="app-panel">

                        <div class="uk-form-row">
                            <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="region.name" required>
                            <div class="uk-margin-top">
                                <input class="uk-width-1-1 uk-form-blank uk-text-muted" type="text" data-ng-model="region.slug" app-slug="region.name" placeholder="@lang('Slug...')" title="slug" data-uk-tooltip="{pos:'left'}">
                            </div>
                        </div>

                        <ul class="uk-tab uk-tab-flip uk-margin" style="margin:25px 0;">
                            <li data-ng-class="mode=='tpl' ? 'uk-active' : ''"><a href="#tpl" data-ng-click="mode='tpl'">@lang('Template')</a></li>
                            <li data-ng-class="mode=='form' ? 'uk-active' : ''"><a href="#form" data-ng-click="mode='form'">@lang('Form')</a></li>
                        </ul>

                        <div data-ng-show="mode=='form'">

                            <div class="uk-form-row uk-clearfix">

                                <h3 class="uk-float-left">@lang('Region fields')</h3>

                                @hasaccess?("Regions", 'manage.region.fields')
                                <button type="button" class="uk-button uk-button-small uk-float-right" data-ng-class="manageform ? 'uk-button-success':'uk-button-primary'" data-ng-click="switchFieldsForm(manageform)" title="@lang('Manage form')">
                                    <span ng-show="!manageform"><i class="uk-icon-cog"></i></span>
                                    <span ng-show="manageform"><i class="uk-icon-check"></i></span>
                                </button>
                                @end

                            </div>

                            <div class="uk-grid">
                                <div class="uk-width-1-1">

                                    <div class="uk-alert" ng-show="region && !region.fields.length">
                                        @lang('This region has no fields yet.')
                                    </div>

                                    <div ng-show="manageform">

                                    <ul id="manage-fields-list" class="uk-nestable" data-uk-nestable="{maxDepth:1}">
                                        <li class="uk-nestable-list-item" data-ng-repeat="field in region.fields">
                                            <div class="uk-nestable-item uk-nestable-item-table">

                                                <div class="uk-grid uk-grid-small">
                                                    <div class="uk-width-3-4">
                                                        <div class="uk-nestable-handle"></div>
                                                        <input class="uk-width-2-3 uk-form-blank" type="text" data-ng-model="field.name" placeholder="@lang('Field name')" pattern="[a-zA-Z0-9_]+" required>
                                                    </div>
                                                    <div class="uk-width-1-4 uk-text-right">
                                                    <a ng-click="toggleOptions($index)"><i class="uk-icon-cog"></i></a>
                                                    <a data-ng-click="remove(field)" class="uk-close"></a>
                                                    </div>
                                                </div>
                                                <div id="options-field-@@ $index @@" class="app-panel uk-margin-small-top uk-hidden">
                                                    <div class="uk-grid uk-grid-small">
                                                        <div class="uk-width-1-2">

                                                            <label class="uk-text-small">@lang('Field type')</label>
                                                            <select class="uk-width-1-1" data-ng-model="field.type" title="@lang('Field type')" ng-options="f.name as f.label for f in contentfields"></select>
                                                        </div>
                                                        <div class="uk-width-1-2">
                                                        <label class="uk-text-small">@lang('Field label')</label>
                                                        <input class="uk-width-1-1" type="text" data-ng-model="field.label" placeholder="@lang('Field label')">
                                                        </div>

                                                        <div class="uk-width-1-1 uk-grid-margin">

                                                            <strong class="uk-text-small">Extra options</strong>
                                                            <hr>
                                                            <div class="uk-form uk-form-horizontal">

                                                                @if(count($locales))
                                                                <div class="uk-form-row">
                                                                    <label class="uk-form-label">@lang('Localize')</label>
                                                                    <div class="uk-form-controls">
                                                                        <input type="checkbox" data-ng-model="field.localize" />
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <div class="uk-form-row" data-ng-if="field.type=='select'">
                                                                    <label class="uk-form-label">@lang('Options')</label>
                                                                    <div class="uk-form-controls">
                                                                        <input class="uk-form-blank" type="text" data-ng-model="field.options" ng-list placeholder="@lang('options...')" title="@lang('Separate different options by comma')" data-uk-tooltip>
                                                                    </div>
                                                                </div>

                                                                <div class="uk-form-row" data-ng-if="field.type=='media'">
                                                                    <label class="uk-form-label">@lang('Extensions')</label>
                                                                    <div class="uk-form-controls">
                                                                        <input class="uk-form-blank" type="text" data-ng-model="field.allowed" placeholder="*.*" title="@lang('Allowed media types')" data-uk-tooltip>
                                                                    </div>
                                                                </div>

                                                                <div class="uk-form-row" data-ng-if="field.type=='code'">
                                                                    <label class="uk-form-label">@lang('Syntax')</label>
                                                                    <div class="uk-form-controls">
                                                                        <select data-ng-model="field.syntax" title="@lang('Code syntax')" data-uk-tooltip>
                                                                            <option value="text">Text</option>
                                                                            <option value="css">CSS</option>
                                                                            <option value="htmlmixed">Html</option>
                                                                            <option value="javascript">Javascript</option>
                                                                            <option value="markdown">Markdown</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="uk-form-row" data-ng-if="field.type=='link-collection'">
                                                                    <label class="uk-form-label">@lang('Collection')</label>
                                                                    <div class="uk-form-controls">
                                                                        <select ng-options="c._id as c.name for c in collections" data-ng-model="field.collection" title="@lang('Related collection')" data-uk-tooltip required></select>
                                                                        <input type="checkbox" data-ng-model="field.multiple"> @lang('multiple')
                                                                    </div>
                                                                </div>

                                                                <div class="uk-form-row" data-ng-if="field.type=='multifield'">
                                                                    <label class="uk-form-label">@lang('Multi field')</label>
                                                                    <div class="uk-form-controls">
                                                                        <span class="uk-text-muted uk-text-small">@lang('Allowed fields'):</span>
                                                                        <div class="uk-scrollable-box uk-panel-box uk-margin-small-top">
                                                                            <div ng-repeat="cf in contentfields" ng-if="(['select', 'boolean', 'multifield'].indexOf(cf.name) == -1)">
                                                                                <input type="checkbox" data-ng-model="field.allowedfields[cf.name]"> @@ cf.label @@
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                @trigger('cockpit.content.fields.settings')

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>

                                    <button data-ng-click="addfield()" type="button" class="uk-button uk-button-success"><i class="uk-icon-plus-circle" title="@lang('Add field')"></i></button>
                                    </div>

                                    <div ng-show="!manageform">

                                        <div class="uk-form-row" data-ng-repeat="field in region.fields" data-ng-show="field.name">

                                            <label class="uk-text-small">
                                            <span ng-if="field.localize"><i class="uk-icon-comments-o"></i></span>
                                            @@ (field.label || field.name) | uppercase @@
                                            </label>

                                            <contentfield options="@@ field @@" ng-model="region.fields[$index][locale ? ('value'+'_'+locale):'value']"></contentfield>
                                        </div>
                                    </div>


                                </div>
                            </div>

                        </div>

                        <div class="uk-form-row" data-ng-show="mode=='tpl'">

                            <div class="uk-margin uk-clearfix">

                                <div class="uk-button-dropdown uk-float-right" data-uk-dropdown>
                                    <button type="button" class="uk-button">
                                        <i class="uk-icon-indent"></i> @lang('Insert form field')
                                    </button>

                                    <div class="uk-dropdown uk-dropdown-flip">
                                        <ul class="uk-nav uk-nav-dropdown" ng-show="region.fields && region.fields.length">
                                            <li class="uk-nav-header">@lang('Form fields')</li>
                                            <li ng-repeat="field in region.fields">
                                            <a ng-click="insertfield(field.name)">@@ field.name @@</a>
                                            </li>
                                        </ul>

                                        <div class="uk-text-muted" ng-show="region.fields && !region.fields.length">
                                        @lang('You have no fields added.')
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <textarea id="region-template" codearea="{mode:'application/x-httpd-php', autoCloseTags: true}" class="uk-width-1-1 uk-form-large" style="height:450px !important;" placeholder="@lang('Region code')" data-ng-model="region.tpl"></textarea>

                            <div class="uk-margin" ng-show="region.name">
                                <strong>@lang('Embed region snippet'):</strong>
                                <highlightcode>&lt;?php <strong>region('@@region.name@@')</strong> ?&gt;</highlightcode>
                            </div>
                        </div>

                        <div class="uk-form-row">
                            <button type="submit" class="uk-button uk-button-primary uk-button-large">@lang('Save Region')</button>
                            <a href="@route('/regions')">@lang('Cancel')</a>
                        </div>
                    </div>
                </div>

                <div class="uk-width-medium-1-5">

                    <div class="uk-form-row">
                        <label><strong>@lang("Group")</strong></label>
                        <div class="uk-form-controls uk-margin-small-top">
                            <div class="uk-form-select">
                                <i class="uk-icon-sitemap uk-margin-small-right"></i>
                                <a>@@ region.group || '- @lang("No group") -' @@</a>
                                <select class="uk-width-1-1" data-ng-model="region.group">
                                    <option ng-repeat="group in groups" value="@@ group @@">@@ group @@</option>
                                    <option value="">- @lang("No group") -</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
          </div>
    </form>

</div>
