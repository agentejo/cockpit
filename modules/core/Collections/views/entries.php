{{ $app->assets(['collections:assets/collections.js','collections:assets/js/entries.js'], $app['cockpit/version']) }}

<style>
    td .uk-grid+.uk-grid { margin-top: 5px; }
</style>

<script>

    var COLLECTION = {{ json_encode($collection) }};

</script>


<div data-ng-controller="entries">

    <nav class="uk-navbar uk-margin-bottom">
        <span class="uk-navbar-brand"><a href="@route("/collections")">@lang('Collections')</a> / {{ $collection['name'] }}</span>
        <ul class="uk-navbar-nav">
            @hasaccess?("Collections", 'manage.collections')
            <li><a href="@route('/collections/collection/'.$collection["_id"])" title="@lang('Edit collection')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-pencil"></i></a></li>
            @end
            <li><a href="@route('/collections/entry/'.$collection["_id"])" title="@lang('Add entry')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a></li>
        </ul>
    </nav>

    <div class="app-panel uk-margin uk-text-center" data-ng-show="entries && !entries.length">
        <h2><i class="uk-icon-list"></i></h2>
        <p class="uk-text-large">
            @lang('It seems you don\'t have any entries created.')
        </p>
        <a href="@route('/collections/entry/'.$collection["_id"])" class="uk-button uk-button-success uk-button-large">@lang('Add entry')</a>
    </div>

    <div class="uk-grid" data-uk-grid-margin data-ng-show="entries && entries.length">

        <div class="uk-width-medium-4-5">
            <div class="app-panel">
                <table class="uk-table uk-table-striped">
                    <thead>
                        <tr>
                            <th width="10"><input class="js-all" type="checkbox"></th>
                            <th>
                                @lang('Fields')
                            </th>
                            <th width="15%">@lang('Modified')</th>
                            <th width="10%">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-ng-repeat="entry in entries">
                            <td><input class="js-select" type="checkbox" data-id="@@ entry._id @@"></td>
                            <td>
                                <a class="uk-link-muted" href="@route('/collections/entry/'.$collection["_id"])/@@ entry._id @@">
                                    <div class="uk-grid uk-grid-preserve uk-text-small" data-ng-repeat="field in fields">
                                        <div class="uk-width-medium-1-5">
                                            <strong>@@ field.name @@</strong>
                                        </div>
                                        <div class="uk-width-medium-4-5">
                                            @@ entry[field.name] @@
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td>@@ entry.modified | fmtdate:'d M, Y' @@</td>
                            <td class="uk-text-right">
                                <div data-uk-dropdown>
                                    <i class="uk-icon-bars"></i>
                                    <div class="uk-dropdown uk-dropdown-flip uk-text-left">
                                        <ul class="uk-nav uk-nav-dropdown uk-nav-parent-icon">
                                            <li><a href="@route('/collections/entry/'.$collection["_id"])/@@ entry._id @@"><i class="uk-icon-pencil"></i> @lang('Edit entry')</a></li>
                                            <li><a href="#" data-ng-click="remove($index, entry._id)"><i class="uk-icon-trash-o"></i> @lang('Delete entry')</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="uk-margin-top">
                    <button class="uk-button uk-button-danger" data-ng-click="removeSelected()" data-ng-show="selected.length"><i class="uk-icon-trash-o"></i> @lang('Delete entries')</button>
                </div>

            </div>
        </div>
        <div class="uk-width-medium-1-5 uk-hidden-small">

        </div>
</div>