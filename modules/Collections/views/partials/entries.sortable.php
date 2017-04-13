
<table class="uk-table uk-table-border uk-table-striped" show="{ entries.length }">
    <thead>
        <tr>
            @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
            <th width="20"><input type="checkbox" data-check="all"></th>
            @endif
            <th width="{field.name == '_modified' ? '120':''}" class="uk-text-small" each="{field,idx in fields}">
                { field.label || field.name }
            </th>
            <th width="20"></th>
        </tr>
    </thead>
    <tbody ref="sortableroot">
        <tr class="uk-visible-hover" each="{entry,idx in entries}" data-id="{ entry._id }">
            @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
            <td><input type="checkbox" data-check data-id="{ entry._id }"></td>
            @endif
            <td class="uk-text-truncate" each="{field,idy in parent.fields}" if="{ field.name != '_modified' }">
                <a class="uk-link-muted" href="@route('/collections/entry/'.$collection['name'])/{ parent.entry._id }">
                    <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name]) }"></raw>
                </a>
            </td>
            <td class="uk-text-muted">{  App.Utils.dateformat( new Date( 1000 * entry._modified )) }</td>
            <td>
                <span data-uk-dropdown="mode:'click'">

                    <a class="uk-icon-bars"></a>

                    <div class="uk-dropdown uk-dropdown-flip">
                        <ul class="uk-nav uk-nav-dropdown">
                            <li class="uk-nav-header">@lang('Actions')</li>
                            <li><a href="@route('/collections/entry/'.$collection['name'])/{ entry._id }">@lang('Edit')</a></li>

                            @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
                            <li><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                            @endif
                        </ul>
                    </div>
                </span>
            </td>
        </tr>
    </tbody>
    <tbody>
        <tr>
            <td colspan="{ (2+fields.length ) }">
                <div class="uk-alert uk-text-small uk-margin-remove">
                    @lang('Drag rows to reorder')
                </div>
            </td>
        </tr>
    </tbody>
</table>
