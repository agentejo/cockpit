{{ $app->assets(['collections:assets/collections.js','collections:assets/js/entry.js']) }}

{{ $app->assets(['mediamanager:assets/pathpicker.directive.js']) }}

{{ $app->assets(['assets:vendor/codemirror/lib/codemirror.js','assets:vendor/codemirror/lib/codemirror.css','assets:vendor/codemirror/theme/monokai.css']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/xml/xml.js']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/htmlmixed/htmlmixed.js']) }}
{{ $app->assets(['assets:vendor/codemirror/addon/edit/matchbrackets.js', 'assets:vendor/codemirror/addon/selection/active-line.js']) }}
{{ $app->assets(['assets:angular/directives/codearea.js']) }}

{{ $app->assets(['assets:vendor/tinymce/tinymce.min.js']) }}
{{ $app->assets(['assets:angular/directives/wysiwyg.js']) }}

<style>
    textarea { min-height: 150px; }
</style>

<div data-ng-controller="entry" data-collection='{{ json_encode($collection) }}' data-entry='{{ json_encode($entry) }}'>

    <h1><a href="@route("/collections")">Collections</a> / <a href="@route("/collections/entries")/@@ collection._id @@">@@ collection.name @@</a> / Entry</h1>

    <form class="uk-form" data-ng-submit="save()" data-ng-show="collection">

        <div class="uk-grid" data-uk-grid-margin>

            <div class="uk-width-medium-3-4">
                <div class="app-panel">

                    <div class="uk-form-row" data-ng-repeat="field in fieldsInArea('main')" data-ng-switch="field.type">

                        <label class="uk-text-small">@@ field.name | uppercase @@</label>

                        <div data-ng-switch-when="html">
                            <textarea class="uk-width-1-1 uk-form-large" data-ng-model="entry[field.name]"></textarea>
                        </div>

                        <div data-ng-switch-when="code">
                            <textarea codearea="{mode:'@@field.syntax@@'}" class="uk-width-1-1 uk-form-large" data-ng-model="entry[field.name]"></textarea>
                        </div>

                        <div data-ng-switch-when="wysiwyg">
                            <textarea wysiwyg class="uk-width-1-1 uk-form-large" data-ng-model="entry[field.name]"></textarea>
                        </div>

                        <div data-ng-switch-default>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="entry[field.name]">
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <button type="submit" class="uk-button uk-button-primary uk-button-large">Save Collection</button>
                        <a href="@route('/collections/entries/'.$collection["_id"])" >Cancel</a>
                    </div>

                </div>
            </div>

            <div class="uk-width-medium-1-4">
                    <div class="uk-form-row" data-ng-repeat="field in fieldsInArea('side')" data-ng-switch="field.type">

                        <label class="uk-text-small">@@ field.name | uppercase @@</label>

                        <div data-ng-switch-when="select">
                            <select class="uk-width-1-1 uk-form-large" data-ng-model="entry[field.name]">
                                <option value="@@ option @@" data-ng-repeat="option in (field.options || [])" data-ng-selected="(entry[field.name]==option)">@@ option @@</option>
                            </select>
                        </div>

                        <div data-ng-switch-when="media">
                            <input type="text" media-path-picker data-ng-model="entry[field.name]">
                        </div>

                        <div data-ng-switch-default>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="entry[field.name]">
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </form>

    @@ entry @@

</div>