{{ $app->assets(['collections:assets/collections.js','collections:assets/js/entry.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/codemirror/lib/codemirror.js','assets:vendor/codemirror/lib/codemirror.css','assets:vendor/codemirror/theme/pastel-on-dark.css'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/xml/xml.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/htmlmixed/htmlmixed.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/codemirror/addon/edit/matchbrackets.js', 'assets:vendor/codemirror/addon/selection/active-line.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:angular/directives/codearea.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/tinymce/tinymce.min.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/tinymce/langs/'.$app("i18n")->locale.'.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:angular/directives/wysiwyg.js'], $app['cockpit/version']) }}

{{ $app->assets(['mediamanager:assets/pathpicker.directive.js'], $app['cockpit/version']) }}

<style>
    textarea { min-height: 150px; }
</style>

<script>
 var COLLECTION = {{ json_encode($collection) }},
     COLLECTION_ENTRY = {{ json_encode($entry) }};
</script>

<div data-ng-controller="entry">

    <div id="entry-versions" class="uk-offcanvas">
        <div class="uk-offcanvas-bar">
          <div class="uk-panel">
              <h3 class="uk-panel-title">@lang('Versions')</h3>

              <p class="uk-text-muted" data-ng-show="!versions.length">
                @lang('Empty')
              </p>

              <ul class="uk-nav uk-nav-offcanvas" data-ng-show="versions.length">
                <li data-ng-repeat="version in versions">
                  <a href="#v-@@ version.uid @@" data-ng-click="restoreVersion(version.uid)" title="@lang('Restore this version')" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-clock-o"></i> @@ version.time | fmtdate:'d M, Y H:i:s' @@</a>
                </li>
              </ul>
              <br>

              <div class="uk-button-group uk-width-1-1">
                <button type="button" class="uk-button uk-button-large uk-button-danger uk-width-1-2" data-ng-click="clearVersions()" title="@lang('Clear version history')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-trash-o"></i></button>
                <button type="button" class="uk-button uk-button-large uk-button-primary uk-width-1-2" onclick="$.UIkit.offcanvas.offcanvas.hide()" title="@lang('Close versions')" data-uk-tooltip="{pos:'bottom'}">@lang('Cancel')</button>
              </div>
          </div>
        </div>
    </div>


    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">
          <a href="@route("/collections")">@lang('Collections')</a> /
          <a href="@route("/collections/entries")/@@ collection._id @@">@@ collection.name @@</a> /
          @lang('Entry')
        </span>
        <div class="uk-navbar-content">
            <a href="#entry-versions" data-uk-offcanvas data-ng-show="versions.length"><i class="uk-icon-clock-o"></i> @lang('Versions') <span class="uk-badge">@@ versions.length @@</span></a>
        </div>
    </nav>

    <form class="uk-form" data-ng-submit="save()" data-ng-show="collection">

        <div class="uk-grid" data-uk-grid-margin>

            <div class="uk-width-medium-3-4">
                <div class="app-panel">

                    <div class="uk-form-row" data-ng-repeat="field in fieldsInArea('main')" data-ng-switch="field.type">

                        <label class="uk-text-small">@@ field.name | uppercase @@ <span ng-show="field.required">*</span></label>
                        <div class="uk-text-small uk-text-danger uk-float-right uk-animation-slide-top" data-ng-if="field.error">@@ field.error @@</div>

                        <div data-ng-switch-when="html">
                            <textarea class="uk-width-1-1 uk-form-large" data-ng-class="{'uk-form-danger':field.error}" data-ng-model="entry[field.name]"></textarea>
                        </div>

                        <div data-ng-switch-when="code">
                            <textarea codearea="{mode:'@@field.syntax@@'}" class="uk-width-1-1 uk-form-large" data-ng-class="{'uk-form-danger':field.error}" data-ng-model="entry[field.name]" style="height:350px !important;"></textarea>
                        </div>

                        <div data-ng-switch-when="wysiwyg">
                            <textarea wysiwyg="{document_base_url:'{{ $app->pathToUrl('site:') }}'}" class="uk-width-1-1 uk-form-large" data-ng-class="{'uk-form-danger':field.error}" data-ng-model="entry[field.name]"></textarea>
                        </div>

                        <div data-ng-switch-default>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-class="{'uk-form-danger':field.error}" data-ng-model="entry[field.name]">
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <button type="submit" class="uk-button uk-button-primary uk-button-large">@lang('Save')</button>
                        <a href="@route('/collections/entries/'.$collection["_id"])" >@lang('Cancel')</a>
                    </div>

                </div>
            </div>

            <div class="uk-width-medium-1-4">
                    <div class="uk-form-row" data-ng-repeat="field in fieldsInArea('side')" data-ng-switch="field.type">

                        <label class="uk-text-small">@@ field.name | uppercase @@ <span ng-show="field.required">*</span></label>
                        <div class="uk-text-small uk-text-danger uk-float-right uk-animation-slide-top" data-ng-if="field.error">@@ field.error @@</div>

                        <div data-ng-switch-when="select">
                            <select class="uk-width-1-1 uk-form-large" data-ng-model="entry[field.name]" data-ng-class="{'uk-form-danger':field.error}">
                                <option value="@@ option @@" data-ng-repeat="option in (field.options || [])" data-ng-selected="(entry[field.name]==option)">@@ option @@</option>
                            </select>
                        </div>

                        <div data-ng-switch-when="media">
                            <input type="text" media-path-picker data-ng-class="{'uk-form-danger':field.error}" data-ng-model="entry[field.name]">
                        </div>

                        <div data-ng-switch-when="boolean">
                            <input type="checkbox" data-ng-model="entry[field.name]">
                        </div>

                        <div data-ng-switch-default>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-class="{'uk-form-danger':field.error}" data-ng-model="entry[field.name]">
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </form>



</div>
