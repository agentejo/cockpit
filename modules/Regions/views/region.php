{{ $app->assets(['regions:assets/regions.js','regions:assets/js/region.js']) }}

{{ $app->assets(['mediamanager:assets/pathpicker.directive.js']) }}

{{ $app->assets(['assets:vendor/codemirror/lib/codemirror.js','assets:vendor/codemirror/lib/codemirror.css','assets:vendor/codemirror/theme/monokai.css']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/xml/xml.js']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/htmlmixed/htmlmixed.js']) }}
{{ $app->assets(['assets:vendor/codemirror/addon/edit/matchbrackets.js', 'assets:vendor/codemirror/addon/selection/active-line.js']) }}
{{ $app->assets(['assets:angular/directives/codearea.js']) }}

{{ $app->assets(['assets:vendor/tinymce/tinymce.min.js']) }}
{{ $app->assets(['assets:angular/directives/wysiwyg.js']) }}

<div data-ng-controller="region" data-id="{{ $id }}">

    <h1><a href="@route("/regions")">Regions</a> / Entry</h1>

    <form class="uk-form" data-ng-submit="save()" data-ng-show="region">

            <div class="uk-grid">

                <div class="uk-width-medium-3-4">

                    <div class="app-panel">

                        <div class="uk-form-row">
                            <input class="uk-width-1-1 uk-form-large" type="text" placeholder="Region name" data-ng-model="region.name"  pattern="[a-zA-Z0-9]+" required>
                        </div>

                        <ul class="uk-subnav uk-subnav-pill">
                            <li data-ng-class="mode=='form' ? 'uk-active' : ''"><a href="#form" data-ng-click="mode='form'">Form</a></li>
                            <li data-ng-class="mode=='tpl' ? 'uk-active' : ''"><a href="#tpl" data-ng-click="mode='tpl'">Template</a></li>
                        </ul>

                        <div data-ng-show="mode=='form'">

                            <div class="uk-form-row">

                                <h3>
                                    Region fields
                                    
                                </h3>

                                <a href="#" class="uk-button uk-button-small" data-ng-click="(manageform = !manageform)">
                                    <span ng-show="!manageform">Manage form</span>
                                    <span ng-show="manageform">Done</span>
                                </a>

                            </div>

                            <div class="uk-alert" ng-show="region && !region.fields.length">
                              This region has no fields yet.
                            </div>

                            <div ng-show="manageform">

                               <ul class="uk-list uk-form">
                                   <li class="uk-margin-bottom" data-ng-repeat="field in region.fields">

                                       <input type="text" data-ng-model="field.name" placeholder="Field name" pattern="[a-zA-Z0-9]+" required>
                                       <select data-ng-model="field.type" title="Field type" data-uk-tooltip>
                                           <option value="text">Text</option>
                                           <option value="select">Select</option>
                                           <option value="boolean">Boolean</option>
                                           <option value="html">Html</option>
                                           <option value="wysiwyg">Html (WYSIWYG)</option>
                                           <option value="code">Code</option>
                                           <option value="date">Date</option>
                                           <option value="time">Time</option>
                                           <option value="media">Media</option>
                                       </select>

                                       <input type="text" data-ng-if="field.type=='select'" data-ng-model="field.options" ng-list placeholder="options....">

                                       <select data-ng-if="field.type=='code'" data-ng-model="field.syntax" title="Code syntax" data-uk-tooltip>
                                           <option value="text">Text</option>
                                           <option value="css">CSS</option>
                                           <option value="htmlmixed">Html</option>
                                           <option value="javascript">Javascript</option>
                                           <option value="markdown">Markdown</option>
                                       </select>

                                       <a data-ng-click="remove(field)" class="uk-close"></a>
                                   </li>
                               </ul>

                               <button data-ng-click="addfield()" type="button" class="uk-button uk-button-success"><i class="uk-icon-plus-circle" title="Add field"></i></button>
                            </div>

                            <div ng-show="!manageform">

                                <div class="uk-form-row" data-ng-repeat="field in region.fields" data-ng-switch="field.type" data-ng-show="field.name">

                                    <label class="uk-text-small">@@ field.name | uppercase @@</label>

                                    <div data-ng-switch-when="html">
                                        <textarea class="uk-width-1-1 uk-form-large" data-ng-model="region.fields[$index].value"></textarea>
                                    </div>

                                    <div data-ng-switch-when="code">
                                        <textarea codearea="{mode:'@@field.syntax@@'}" class="uk-width-1-1 uk-form-large" data-ng-model="region.fields[$index].value"></textarea>
                                    </div>

                                    <div data-ng-switch-when="wysiwyg">
                                        <textarea wysiwyg class="uk-width-1-1 uk-form-large" data-ng-model="region.fields[$index].value"></textarea>
                                    </div>

                                    <div data-ng-switch-when="select">
                                        <select class="uk-width-1-1 uk-form-large" data-ng-model="region.fields[$index].value" data-ng-init="fieldindex=$index">
                                            <option value="@@ option @@" data-ng-repeat="option in (field.options || [])" data-ng-selected="(region.fields[fieldindex].value==option)">@@ option @@</option>
                                        </select>
                                    </div>

                                    <div data-ng-switch-when="media">
                                        <input type="text" media-path-picker data-ng-model="region.fields[$index].value">
                                    </div>

                                    <div data-ng-switch-default>
                                        <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="region.fields[$index].value">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="uk-form-row" data-ng-show="mode=='tpl'">
                            <textarea codearea="{mode:'htmlmixed'}" class="uk-width-1-1 uk-form-large" style="height:450px !important;" placeholder="Region code" data-ng-model="region.tpl"  pattern="[a-zA-Z0-9]+"></textarea>
                            
                            <div class="uk-margin" ng-show="region.name">
                                <strong>Embed snippet:</strong>
                                <pre><code>&lt;?php region('@@region.name@@'); ?&gt;</code></pre>
                            </div>
                        </div>

                        <div class="uk-form-row">
                            <button type="submit" class="uk-button uk-button-primary uk-button-large">Save Region</button>
                            <a href="@route('/regions')">Cancel</a>
                        </div>
                    </div>
                </div>
          </div>
    </form>
</div>