
<div class="uk-text-xlarge uk-text-muted uk-viewport-height-1-3 uk-flex uk-flex-center uk-flex-middle" if="{ !entries.length && filter && !loading }">
    <div>@lang('No entries found')</div>
</div>


<div class="uk-grid uk-grid-match uk-grid-width-medium-1-4 uk-flex-center" if="{ entries.length && !loading && listmode=='grid' }">

    <div class="uk-grid-margin" each="{entry,idx in entries}">

        <div class="uk-panel uk-panel-box uk-panel-card">

            <div class="uk-position-relative uk-nbfc">
                <canvas width="400" height="250"></canvas>
                <div class="uk-position-cover uk-flex uk-flex-center uk-flex-middle">

                    <cp-thumbnail src="{ parent.isImageField(entry) }" width="400" height="250" if="{ parent.isImageField(entry) }"></cp-thumbnail>

                    <div class="uk-svg-adjust uk-text-primary" style="color:{{ @$collection['color'] }} !important;" if="{ !parent.isImageField(entry) }">
                        <img src="@url($collection['icon'] ? 'assets:app/media/icons/'.$collection['icon']:'collections:icon.svg')" width="80" alt="icon" data-uk-svg>
                    </div>
                </div>
                <a class="uk-position-cover" href="@route('/collections/entry/'.$collection['name'])/{ entry._id }"></a>
            </div>
            <div class="collection-grid-avatar-container">
                <div class="collection-grid-avatar">
                    <cp-account account="{entry._mby || entry._by}" label="{false}" size="40" if="{entry._mby || entry._by}"></cp-account>
                    <cp-gravatar alt="?" size="40" if="{!(entry._mby || entry._by)}"></cp-gravatar>
                </div>
            </div>
            <div class="uk-flex uk-flex-middle uk-margin-small-top">

                <div class="uk-flex-item-1 uk-margin-small-right uk-text-small">
                    <span class="uk-text-success uk-margin-small-right">{ App.Utils.dateformat( new Date( 1000 * entry._created )) }</span>
                    <span class="uk-text-primary">{ App.Utils.dateformat( new Date( 1000 * entry._modified )) }</span>
                </div>

                <span data-uk-dropdown="mode:'click', pos:'bottom-right'">

                    <a class="uk-icon-bars"></a>

                    <div class="uk-dropdown uk-dropdown-flip">
                        <ul class="uk-nav uk-nav-dropdown">
                            <li class="uk-nav-header">@lang('Actions')</li>
                            <li><a href="@route('/collections/entry/'.$collection['name'])/{ entry._id }">@lang('Edit')</a></li>

                            @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
                            <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                            @endif

                            @if($app->module('collections')->hasaccess($collection['name'], 'entries_create'))
                            <li class="uk-nav-divider"></li>
                            <li><a class="uk-dropdown-close" onclick="{ parent.duplicateEntry }">@lang('Duplicate')</a></li>
                            @endif
                        </ul>
                    </div>
                </span>
            </div>

            <div class="uk-margin-top uk-scrollable-box">
                <div class="uk-margin-small-bottom" each="{field,idy in parent.fields}" if="{ field.name != '_modified' && field.name != '_created' && hasFieldAccess(field.name) }">
                    <span class="uk-text-small uk-text-uppercase uk-text-muted">{ field.label || field.name }</span>
                    <a class="uk-link-muted uk-text-small uk-display-block uk-text-truncate" href="@route('/collections/entry/'.$collection['name'])/{ parent.entry._id }">
                        <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name]) }" if="{parent.entry[field.name] !== undefined}"></raw>
                        <span class="uk-icon-eye-slash uk-text-muted" if="{parent.entry[field.name] === undefined}"></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="uk-overflow-container uk-margin-large-top" if="{ entries.length && !loading && listmode=='list' }">
    <table class="uk-table uk-table-tabbed uk-table-striped">
        <thead>
            <tr>
                @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
                <th width="20"><input type="checkbox" data-check="all"></th>
                @endif
                <th width="{field.name == '_modified' || field.name == '_created' ? '80':''}" class="uk-text-small" each="{field,idx in fields}" if="{ hasFieldAccess(field.name) }">
                    <a class="uk-link-muted uk-noselect { parent.sortedBy == field.name ? 'uk-text-primary':'' }" onclick="{ parent.updatesort }" data-sort="{ field.name }">

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
                        <raw content="{ App.Utils.renderValue(field.type, parent.entry[field.name]) }" if="{parent.entry[field.name] !== undefined}"></raw>
                        <span class="uk-icon-eye-slash uk-text-muted" if="{parent.entry[field.name] === undefined}"></span>
                    </a>
                </td>
                <td><span class="uk-badge uk-badge-outline uk-text-muted">{ App.Utils.dateformat( new Date( 1000 * entry._created )) }</span></td>
                <td><span class="uk-badge uk-badge-outline uk-text-primary">{ App.Utils.dateformat( new Date( 1000 * entry._modified )) }</span></td>
                <td>
                    <span data-uk-dropdown="mode:'click'">

                        <a class="uk-icon-bars"></a>

                        <div class="uk-dropdown uk-dropdown-flip">
                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-nav-header">@lang('Actions')</li>
                                <li><a href="@route('/collections/entry/'.$collection['name'])/{ entry._id }">@lang('Edit')</a></li>

                                @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
                                <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
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
</div>

<div class="uk-margin uk-flex uk-flex-middle" if="{ !loading && pages > 1 }">

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
