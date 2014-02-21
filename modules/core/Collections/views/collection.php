{{ $app->assets(['collections:assets/collections.js','collections:assets/js/collection.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/uikit/addons/css/sortable.almost-flat.min.css','assets:vendor/uikit/addons/js/sortable.min.js'], $app['cockpit/version']) }}

<div data-ng-controller="collection" data-id="{{ $id }}" ng-cloak>

    <h1>
        <a href="@route("/collections")">@lang('Collections')</a> /
        <span class="uk-text-muted" ng-show="!collection.name">@lang('Collection')</span>
        <span ng-show="collection.name">@@ collection.name @@</span>
    </h1>


    <form class="uk-form" data-ng-submit="save()" data-ng-show="collection">

        <div class="uk-grid">

            <div class="uk-width-3-4">
                <div class="app-panel">

                    <div class="uk-form-row">
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="collection.name" required>
                    </div>

                    <div class="uk-form-row uk-margin uk-text-center" data-ng-show="!collection.fields || !collection.fields.length">
                        <h2>@lang('Fields')</h2>
                        <p>
                            @lang('It seems you don\'t have any fields created.')
                        </p>
                        <button data-ng-click="addfield()" type="button" class="uk-button uk-button-success uk-button-large">@lang('Add field')</button>
                    </div>

                    <div class="uk-form-row uk-margin" data-ng-show="collection.fields && collection.fields.length">
                        <strong>@lang('Fields')</strong>
                    </div>

                    <div class="uk-form-row" data-ng-show="collection.fields && collection.fields.length">
                        <ul class="uk-list">
                            <li class="uk-margin-bottom" data-ng-repeat="field in collection.fields">


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

                                <input type="text" data-ng-if="field.type=='select'" data-ng-model="field.options" ng-list placeholder="options...." title="@lang('Separate different options by comma')" data-uk-tooltip>

                                <select data-ng-if="field.type=='code'" data-ng-model="field.syntax" title="Code syntax" data-uk-tooltip>
                                    <option value="text">Text</option>
                                    <option value="css">CSS</option>
                                    <option value="htmlmixed">Html</option>
                                    <option value="javascript">Javascript</option>
                                    <option value="markdown">Markdown</option>
                                </select>

                                <input type="text" data-ng-model="field.default" placeholder="default value...">

                                <span>
                                    <input type="checkbox" data-ng-model="field.required" />
                                    <label>Required</label>
                                </span>

                                <a data-ng-click="remove(field)" class="uk-close"></a>
                            </li>
                        </ul>

                        <button data-ng-click="addfield()" type="button" class="uk-button uk-button-success"><i class="uk-icon-plus-circle" title="@lang('Add field')"></i></button>
                    </div>
                    <br>
                    <br>

                    <div class="uk-form-row" data-ng-show="collection.fields && collection.fields.length">
                        <div class="uk-button-group">
                            <button type="submit" class="uk-button uk-button-primary uk-button-large">@lang('Save Collection')</button>
                            <a href="@route('/collections/entries')/@@ collection._id @@" class="uk-button uk-button-large" data-ng-show="collection._id"><i class="uk-icon-bars"></i> @lang('Goto entries')</a>
                        </div>
                        <a href="@route('/collections')">@lang('Cancel')</a>
                    </div>
                </div>
            </div>
            <div class="uk-width-1-4" data-ng-show="collection.fields && collection.fields.length">
                <strong>@lang('Settings')</strong>

                <div class="uk-margin">
                    <p>
                        @lang('Fields on entries list page'):
                    </p>
                    <ul id="fields-list" class="uk-sortable" data-uk-sortable="{maxDepth:1}">
                        <li data-ng-repeat="field in collection.fields">
                            <div class="uk-sortable-item uk-sortable-item-table">
                                <div class="uk-sortable-handle"></div>
                                <input type="checkbox" data-ng-checked="field.lst" data-ng-model="field.lst">
                                @@ field.name @@
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="uk-margin">
                    <p>
                        @lang('Order entries on list page'):
                    </p>
                    <select class="uk-width-1-1 uk-margin-bottom" data-ng-model="collection.sortfield">
                        <option value="created">@lang('created')</option>
                        <option value="modified">@lang('modified')</option>
                        <option value="@@ field.name @@" data-ng-repeat="field in collection.fields">@@ field.name @@</option>
                    </select>
                    <select class="uk-width-1-1" data-ng-model="collection.sortorder">
                        <option value="-1">@lang('descending')</option>
                        <option value="1">@lang('ascending')</option>
                    </select>
                </div>
            </div>

        </div>

    </form>
</div>
