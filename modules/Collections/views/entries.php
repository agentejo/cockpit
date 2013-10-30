{{ $app->assets(['collections:assets/collections.js','collections:assets/js/entries.js']) }}

<style>
    td .uk-grid+.uk-grid { margin-top: 5px; }
</style>

<div data-ng-controller="entries" data-collection='{{ json_encode($collection) }}'>

    <nav class="uk-navbar uk-margin-bottom" data-ng-show="entries && entries.length">
        <span class="uk-navbar-brand"><a href="@route("/collections")">Collections</a> / {{ $collection['name'] }}</span>
        <ul class="uk-navbar-nav">
            <li><a href="@route('/collections/entry/'.$collection["_id"])" title="Add entry" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-plus-sign"></i></a></li>
        </ul>
    </nav>

    <div class="app-panel uk-margin uk-text-center" data-ng-show="entries && !entries.length">
        <h2>{{ $collection['name'] }}</h2>
        <p class="uk-text-large">
            It seems you don't have any entries created.
        </p>
        <a href="@route('/collections/entry/'.$collection["_id"])" class="uk-button uk-button-success uk-button-large">Add entry</a>
    </div>

    <div class="uk-grid" data-uk-grid-margin data-ng-show="entries && entries.length">

        <div class="uk-width-medium-4-5">
            <div class="app-panel">
                <table class="uk-table uk-table-striped">
                    <thead>
                        <tr>
                            <th>
                                Fields
                            </th>
                            <th width="15%">Modified</th>
                            <th width="10%">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-ng-repeat="entry in entries">
                            <td>
                                <div class="uk-grid uk-grid-preserve uk-text-small" data-ng-repeat="field in fields">
                                    <div class="uk-width-medium-1-5">
                                        <strong>@@ field.name @@</strong>
                                    </div>
                                    <div class="uk-width-medium-4-5">
                                        @@ entry[field.name] @@
                                    </div>
                                </div>
                            </td>
                            <td>@@ entry.modified | fmtdate:'d M, Y' @@</td>
                            <td class="uk-text-right">
                                <a href="@route('/collections/entry/'.$collection["_id"])/@@ entry._id @@"><i class="uk-icon-pencil"></i></a> &nbsp;
                                <a href="#" data-ng-click="remove($index, entry._id)"><i class="uk-icon-remove"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="uk-width-medium-1-5 uk-hidden-small">
            <strong>Info</strong>

            This collection has @@ collection.count @@ entries.
        </div>
</div>