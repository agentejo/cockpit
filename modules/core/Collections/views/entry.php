@start('header')

    {{ $app->assets(['collections:assets/collections.js','collections:assets/js/entry.js'], $app['cockpit/version']) }}

    @trigger('cockpit.content.fields.sources')

    <style>
        textarea { min-height: 150px; }
    </style>

    <script>
     var COLLECTION       = {{ json_encode($collection) }},
         COLLECTION_ENTRY = {{ json_encode($entry) }},
         LOCALES          = {{ json_encode($locales) }};
    </script>

@end('header')

<div data-ng-controller="entry" ng-cloak>

    <div id="entry-versions" class="uk-offcanvas">
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
          <a href="@route("/collections")">@lang('Collections')</a> /
          <a href="@route("/collections/entries")/@@ collection._id @@">@@ collection.name @@</a> /
          @lang('Entry')
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
            <a href="#entry-versions" data-uk-offcanvas data-ng-show="versions.length"><i class="uk-icon-clock-o"></i> @lang('Versions') <span class="uk-badge">@@ versions.length @@</span></a>
        </div>
    </nav>

    <form class="uk-form" data-ng-submit="save()" data-ng-show="collection">

        <div class="uk-grid" data-uk-grid-margin>

            <div class="uk-width-medium-3-4">
                <div class="app-panel">

                    <div class="uk-form-row" data-ng-repeat="field in fieldsInArea('main')">

                        <label class="uk-text-small">
                            <span ng-if="field.localize"><i class="uk-icon-comments-o"></i></span>
                            @@ (field.label || field.name) | uppercase @@
                            <span ng-if="field.required">*</span>
                        </label>

                        <div class="uk-text-small uk-text-danger uk-float-right uk-animation-slide-top" data-ng-if="field.error">@@ field.error @@</div>

                        <contentfield options="@@ field @@" ng-model="entry[getFieldname(field)]"></contentfield>

                        <div class="uk-margin-top" ng-if="field.slug">
                            <input class="uk-width-1-1 uk-form-blank uk-text-muted" type="text" data-ng-model="entry[getFieldname(field)+'_slug']" app-slug="entry[getFieldname(field)]" placeholder="@lang('Slug...')" title="@@ (field.label || field.name) @@ slug" data-uk-tooltip="{pos:'left'}">
                        </div>

                    </div>

                    <div class="uk-form-row">
                        <button type="submit" class="uk-button uk-button-primary uk-button-large">@lang('Save')</button>
                        <a href="@route('/collections/entries/'.$collection["_id"])" >@lang('Cancel')</a>
                    </div>

                </div>
            </div>

            <div class="uk-width-medium-1-4">
                    <div class="uk-form-row" data-ng-repeat="field in fieldsInArea('side')">

                        <label class="uk-text-small">
                            <span ng-if="field.localize"><i class="uk-icon-comments-o"></i></span>
                            @@ (field.label || field.name) | uppercase @@
                            <span ng-if="field.required">*</span>
                        </label>

                        <div class="uk-text-small uk-text-danger uk-float-right uk-animation-slide-top" data-ng-if="field.error">@@ field.error @@</div>

                        <contentfield options="@@ field @@" ng-model="entry[getFieldname(field)]"></contentfield>
                    </div>

                </div>
            </div>
        </div>

    </form>

</div>
