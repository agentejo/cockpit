
<div class="uk-text-xlarge uk-text-muted uk-viewport-height-1-3 uk-flex uk-flex-center uk-flex-middle" if="{ !entries.length && filter && !loading }">
    <div>@lang('No entries found')</div>
</div>

<table class="uk-table uk-table-border uk-table-striped" if="{ entries.length && !loading }">
    <thead>
        <tr>
            @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
            <th width="20"><input type="checkbox" data-check="all"></th>
            @endif
            <th width="{field.name == '_modified' || field.name == '_created' ? '80':''}" class="uk-text-small" each="{field,idx in fields}" if="{ hasFieldAccess(field.name) }">
                <a class="uk-link-muted { parent.sortedBy == field.name ? 'uk-text-primary':'' }" onclick="{ parent.updatesort }" data-sort="{ field.name }">

                    { field.label || field.name }

                    <span if="{parent.sortedBy == field.name}" class="uk-icon-long-arrow-{ parent.sortedOrder == 1 ? 'up':'down'}"></span>
                </a>
            </th>
            <th width="20"></th>
        </tr>
    </thead>
    <tbody>
        <tr each="{entry,idx in entries}">
            @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
            <td><input type="checkbox" data-check data-id="{ entry._id }"></td>
            @endif
            <td class="uk-text-truncate" each="{field,idy in parent.fields}" if="{ field.name != '_modified' && field.name != '_created' && hasFieldAccess(field.name) }">
                <a class="uk-link-muted" href="@route('/collections/entry/'.$collection['name'])/{ parent.entry._id }">
                    <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name]) }"></raw>
                </a>
            </td>
            <td><span class="uk-badge uk-badge-outline uk-text-muted">{ App.Utils.dateformat( new Date( 1000 * entry._created )) }<span></td>
            <td><span class="uk-badge uk-badge-outline uk-text-primary">{ App.Utils.dateformat( new Date( 1000 * entry._modified )) }</span></td>
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

                            @if($app->module('collections')->hasaccess($collection['name'], 'entries_create'))
                            <li class="uk-nav-divider"></li>
                            <li><a class="uk-dropdown-close" onclick="{ parent.duplicateEntry }">@lang('Duplicate')</a></li>
                            @endif
                        </ul>
                    </div>
                </span>
            </td>
        </tr>
    </tbody>
</table>

<div class="uk-alert" if="{ loading }">
    <i class="uk-icon-spinner uk-icon-spin"></i> @lang('Loading...').
</div>

<div class="uk margin uk-flex uk-flex-middle" if="{ !loading && pages > 1 }">

    <ul class="uk-breadcrumb uk-margin-remove">
        <li class="uk-active"><span>{ page }</span></li>
        <li data-uk-dropdown="mode:'click'">

            <a><i class="uk-icon-bars"></i> { pages }</a>

            <div class="uk-dropdown">

                <strong class="uk-text-small">@lang('Pages')</strong>

                <div class="uk-margin-small-top { pages > 5 ? 'uk-scrollable-box':'' }">
                    <ul class="uk-nav uk-nav-dropdown">
                        <li class="uk-text-small" each="{k,v in new Array(pages)}"><a class="uk-dropdown-close" onclick="{ parent.loadpage.bind(parent, v+1) }">@lang('Page') {v + 1}</a></li>
                    </ul>
                </div>
            </div>

        </li>
    </ul>

    <div class="uk-button-group uk-margin-small-left">
        <a class="uk-button uk-button-small" onclick="{ loadpage.bind(this, page-1) }" if="{page-1 > 0}">@lang('Previous')</a>
        <a class="uk-button uk-button-small" onclick="{ loadpage.bind(this, page+1) }" if="{page+1 <= pages}">@lang('Next')</a>
    </div>

</div>
