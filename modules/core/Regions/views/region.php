{{ $app->assets(['regions:assets/regions.js','regions:assets/js/region.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/uikit/js/addons/sortable.min.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/uikit/js/addons/timepicker.min.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/uikit/js/addons/datepicker.min.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/codemirror/codemirror.js','assets:vendor/codemirror/codemirror.css','assets:vendor/codemirror/pastel-on-dark.css'], $app['cockpit/version']) }}

{{ $app->assets(['assets:angular/directives/codearea.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/tinymce/tinymce.min.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/tinymce/langs/'.$app("i18n")->locale.'.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:angular/directives/wysiwyg.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:angular/directives/gallery.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:angular/directives/tags.js'], $app['cockpit/version']) }}

{{ $app->assets(['mediamanager:assets/pathpicker.directive.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/uikit/js/addons/htmleditor.min.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/marked.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:angular/directives/htmleditor.js'], $app['cockpit/version']) }}


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

                <div class="uk-button-group uk-width-1-1">
                  <button type="button" class="uk-button uk-button-danger uk-width-1-2" data-ng-click="clearVersions()" title="@lang('Clear version history')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-trash-o"></i></button>
                  <button type="button" class="uk-button uk-button-primary uk-width-1-2" onclick="$.UIkit.offcanvas.offcanvas.hide()" title="@lang('Close versions')" data-uk-tooltip="{pos:'bottom'}">@lang('Cancel')</button>
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
        <div class="uk-navbar-content">
            <a href="#region-versions" data-uk-offcanvas data-ng-show="versions.length"><i class="uk-icon-clock-o"></i> @lang('Versions') <span class="uk-badge">@@ versions.length @@</span></a>
        </div>
    </nav>


    <form class="uk-form" data-ng-submit="save()" data-ng-show="region">

            <div class="uk-grid">

                <div class="uk-width-medium-4-5">

                    <div class="app-panel">

                        <div class="uk-form-row">
                            <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="region.name" pattern="[a-zA-Z0-9\s]+" required>
                        </div>

                        <ul class="uk-tab uk-tab-flip uk-margin" style="margin:25px 0;">
                            <li data-ng-class="mode=='tpl' ? 'uk-active' : ''"><a href="#tpl" data-ng-click="mode='tpl'">@lang('Template')</a></li>
                            <li data-ng-class="mode=='form' ? 'uk-active' : ''"><a href="#form" data-ng-click="mode='form'">@lang('Form')</a></li>
                        </ul>

                        <div data-ng-show="mode=='form'">

                            <div class="uk-form-row">
                                <h3>@lang('Region fields')</h3>
                            </div>

                            <div class="uk-margin-bottom">
                              <button type="button" class="uk-button" data-ng-class="manageform ? 'uk-button-success':'uk-button-primary'" data-ng-click="(manageform = !manageform)" title="@lang('Manage form')">
                                  <span ng-show="!manageform"><i class="uk-icon-cog"></i></span>
                                  <span ng-show="manageform"><i class="uk-icon-check"></i></span>
                              </button>
                            </div>

                            <div class="uk-grid">
                              <div class="uk-width-1-1">

                                  <div class="uk-alert" ng-show="region && !region.fields.length">
                                    @lang('This region has no fields yet.')
                                  </div>

                                  <div ng-show="manageform">

                                    <ul id="manage-fields-list" class="uk-sortable" data-uk-sortable="{maxDepth:1}">
                                         <li data-ng-repeat="field in region.fields">
                                            <div class="uk-sortable-item uk-sortable-item-table">
                                               <div class="uk-sortable-handle"></div>
                                               <input type="text" data-ng-model="field.name" placeholder="@lang('Field name')" pattern="[a-zA-Z0-9]+" required>
                                               <select data-ng-model="field.type" title="@lang('Field type')" data-uk-tooltip>
                                                   <option value="text">Text</option>
                                                   <option value="select">Select</option>
                                                   <option value="boolean">Boolean</option>
                                                   <option value="html">Html</option>
                                                   <option value="wysiwyg">Html (WYSIWYG)</option>
                                                   <option value="code">Code</option>
                                                   <option value="markdown">Markdown</option>
                                                   <option value="date">Date</option>
                                                   <option value="time">Time</option>
                                                   <option value="media">Media</option>
                                                   <option value="gallery">Gallery</option>
                                                   <option value="tags">Tags</option>
                                               </select>

                                               <input type="text" data-ng-if="field.type=='select'" data-ng-model="field.options" ng-list placeholder="@lang('options...')">
                                               <input type="text" data-ng-if="field.type=='media'" data-ng-model="field.allowed" placeholder="*.*" title="@lang('Allowed media types')" data-uk-tooltip>

                                               <select data-ng-if="field.type=='code'" data-ng-model="field.syntax" title="@lang('Code syntax')" data-uk-tooltip>
                                                   <option value="text">Text</option>
                                                   <option value="css">CSS</option>
                                                   <option value="htmlmixed">Html</option>
                                                   <option value="javascript">Javascript</option>
                                                   <option value="markdown">Markdown</option>
                                               </select>

                                               <a data-ng-click="remove(field)" class="uk-close"></a>
                                            </div>
                                         </li>
                                     </ul>

                                     <button data-ng-click="addfield()" type="button" class="uk-button uk-button-success"><i class="uk-icon-plus-circle" title="@lang('Add field')"></i></button>
                                  </div>

                                  <div ng-show="!manageform">

                                      <div class="uk-form-row" data-ng-repeat="field in region.fields" data-ng-switch="field.type" data-ng-show="field.name">

                                          <label class="uk-text-small">@@ field.name | uppercase @@</label>

                                          <div data-ng-switch-when="html">
                                              <htmleditor data-ng-model="region.fields[$index].value"></htmleditor>
                                          </div>

                                          <div data-ng-switch-when="code">
                                              <textarea codearea="{mode:'@@field.syntax@@'}" class="uk-width-1-1 uk-form-large" data-ng-model="region.fields[$index].value" style="height:300px !important;"></textarea>
                                          </div>

                                          <div data-ng-switch-when="markdown">
                                              <htmleditor data-ng-model="region.fields[$index].value" options="{markdown:true}"></htmleditor>
                                          </div>

                                          <div data-ng-switch-when="wysiwyg">
                                              <textarea wysiwyg="{document_base_url:'{{ $app->pathToUrl('site:') }}'}" class="uk-width-1-1 uk-form-large" data-ng-model="region.fields[$index].value"></textarea>
                                          </div>

                                          <div data-ng-switch-when="select">
                                              <select class="uk-width-1-1 uk-form-large" data-ng-model="region.fields[$index].value" data-ng-init="fieldindex=$index">
                                                  <option value="@@ option @@" data-ng-repeat="option in (field.options || [])" data-ng-selected="(region.fields[fieldindex].value==option)">@@ option @@</option>
                                              </select>
                                          </div>

                                          <div data-ng-switch-when="media">
                                              <input type="text" media-path-picker="@@ field.allowed || '*' @@" data-ng-model="region.fields[$index].value">
                                          </div>

                                          <div data-ng-switch-when="boolean">
                                              <input type="checkbox" data-ng-model="region.fields[$index].value">
                                          </div>

                                          <div data-ng-switch-when="date">
                                              <input class="uk-width-1-1 uk-form-large" type="text" data-uk-datepicker="{format:'YYYY-MM-DD'}" data-ng-model="region.fields[$index].value">
                                          </div>

                                          <div data-ng-switch-when="time">
                                              <input class="uk-width-1-1 uk-form-large" type="text" data-uk-timepicker data-ng-model="region.fields[$index].value">
                                          </div>

                                          <div data-ng-switch-when="gallery">
                                              <gallery data-ng-model="region.fields[$index].value"></gallery>
                                          </div>

                                          <div data-ng-switch-when="tags">
                                              <tags data-ng-model="region.fields[$index].value"></tags>
                                          </div>

                                          <div data-ng-switch-default>
                                              <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="region.fields[$index].value">
                                          </div>
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

                            <textarea id="region-template" codearea="{mode:'application/x-httpd-php', autoCloseTags: true}" class="uk-width-1-1 uk-form-large" style="height:450px !important;" placeholder="Region code" data-ng-model="region.tpl"  pattern="[a-zA-Z0-9]+"></textarea>

                            <div class="uk-margin" ng-show="region.name">
                                <strong>@lang('Embed region snippet'):</strong>
                                <pre><code>&lt;?php <strong>region('@@region.name@@')</strong> ?&gt;</code></pre>
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
