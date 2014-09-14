@start('header')

    {{ $app->assets(['datastore:assets/datastore.js','datastore:assets/js/table.js'], $app['cockpit/version']) }}

    {{ $app->assets(['assets:vendor/codemirror/codemirror.js','assets:vendor/codemirror/codemirror.css','assets:vendor/codemirror/pastel-on-dark.css'], $app['cockpit/version']) }}

@end('header')

<div data-ng-controller="table" data-id="{{ $id }}" ng-cloak>

    <h1>
        <a href="@route("/datastore")">@lang('Datastore')</a> /
        <span class="uk-text-muted" ng-show="!table.name">@lang('Table')</span>
        <span ng-show="table.name">@@ table.name @@</span>
    </h1>


    <div class="uk-form" data-ng-show="table">

        <div class="uk-grid" data-uk-grid-margin>

            <div class="uk-width-medium-4-5">

                <div class="app-panel">

                    <form data-ng-submit="save()">

                        <div class="uk-form-row">
                            <input class="uk-width-1-1 uk-form-blank uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="table.name" required>
                        </div>

                        <div class="uk-form-row">
                            <input class="uk-width-1-1 uk-form-blank uk-form-large uk-text-muted" type="text" placeholder="@lang('Preview fields')" data-ng-model="table.preview" ng-list>
                        </div>

                        <div class="uk-form-row">
                            <button class="uk-button uk-button-primary">@lang('Save')</button>
                            <button type="button" class="uk-button uk-button-success" data-ng-show="table._id" title="@lang('Add entry')" data-uk-tooltip="{pos:'bottom'}" ng-click="edit({})"><i class="uk-icon-plus-circle"></i></button>
                        </div>
                    </form>

                    <div class="uk-margin-top uk-text-large uk-text-muted" data-ng-show="table._id && entries && !entries.length">
                        <hr>
                        @lang('No Entries')
                    </div>

                    <div class="uk-margin-top" data-ng-show="table._id && entries && entries.length">

                        <hr>

                        <div class="uk-form-row">
                            <span class="uk-badge app-badge">@lang('Entries')</span>
                        </div>

                        <table class="uk-table uk-table-striped" multiple-select="{model:entries}">
                            <thead>
                                <tr>
                                    <th width="10"><input class="js-select-all" type="checkbox"></th>
                                    <th>
                                        @lang('Data')
                                    </th>
                                    <th width="20%">@lang('Modified')</th>
                                    <th width="5%">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="js-multiple-select" data-ng-repeat="entry in entries track by entry._id">
                                    <td><input class="js-select" type="checkbox"></td>
                                    <td>

                                        <div class="uk-text-small" data-ng-repeat="field in table.preview" ng-if="table.preview && table.preview.length">
                                            <div><strong>@@ field @@:</strong></div>
                                            <div class="uk-margin-small-top uk-margin-small-bottom uk-text-muted">@@ entry[field] || 'n/a' @@</div>
                                        </div>

                                        <div class="uk-text-small" data-ng-repeat="(key, value) in entry" ng-if="!(table.preview && table.preview.length)">
                                            <div><strong>@@ key @@:</strong></div>
                                            <div class="uk-margin-small-top uk-margin-small-bottom uk-text-muted">@@ value @@</div>
                                        </div>
                                    </td>
                                    <td>@@ entry.modified | fmtdate:'d M, Y H:i' @@</td>
                                    <td class="uk-text-right">
                                        <a data-ng-click="edit(entry)" title="@lang('Edit entry')"><i class="uk-icon-pencil"></i></a>
                                        <a class="uk-text-danger" data-ng-click="remove($index, entry._id)" title="@lang('Delete entry')"><i class="uk-icon-trash-o"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="uk-margin-top">
                            <button class="uk-button uk-button-primary" data-ng-click="loadmore()" data-ng-show="entries && !nomore">@lang('Load more...')</button>
                            <button class="uk-button uk-button-danger" data-ng-click="removeSelected()" data-ng-show="selected"><i class="uk-icon-trash-o"></i> @lang('Delete entries')</button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="uk-width-medium-1-5">
                <ul class="uk-nav uk-nav-side" ng-show="table._id">
                    <li class="uk-nav-header">@lang('Actions')</li>
                    <li>
                        <a href="@route('/api/datastore/export/')@@ table._id @@" download="@@ table.name @@.json">
                            <i class="uk-icon-share-alt"></i> @lang('Export data')
                        </a>
                    </li>
                    <li><a class="uk-text-danger" ng-click="emptytable()"><i class="uk-icon-trash-o"></i> @lang('Empty table')</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div id="entry-editor">
    <nav class="uk-navbar">
        <div class="uk-navbar-content">
            <i class="uk-icon-database"></i> &nbsp; <strong class="uk-text-small filename"></strong>
        </div>
        <ul class="uk-navbar-nav">
            <li><a data-editor-action="save" title="@lang('Save entry')" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-save"></i></a></li>
        </ul>
        <div class="uk-navbar-flip">
            <ul class="uk-navbar-nav">
                <li><a data-editor-action="close" title="@lang('Close')" data-uk-tooltip="{pos:'left'}"><i class="uk-icon-times"></i></a></li>
            </ul>
        </div>
    </nav>
    <textarea></textarea>
</div>

<style>

    /* editor */

    #entry-editor {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.3);
        border: 10px rgba(0,0,0,0.3) solid;
        z-index: 100;
    }

    #entry-editor .uk-navbar {
        background: #f7f7f7;
        border-radius: 3px 3px 0 0;
    }

    #entry-editor .CodeMirror {
        border: none;
        border-radius:  0 0 3px 3px;
    }

    #entry-editor a { cursor: pointer; }

</style>
