
<style>
@if($collection['color'])
.app-header { border-top: 8px {{ $collection['color'] }} solid; }
@endif
</style>

<script>

function _CollectionHasFieldAccess(field, acl) {
    if (field.name == '_modified' ||
        App.$data.user.group == 'admin' ||
        !acl ||
        (Array.isArray(acl) && !acl.length) ||
        acl.indexOf(App.$data.user.group) > -1 ||
        acl.indexOf(App.$data.user._id) > -1
    ) { return true; }

    return false;
}

function CollectionHasFieldAccess(field) {
    var acl = [];
    if (field.acl   ) { acl = acl.concat(field.acl);    }
    if (field.acl_ro) { acl = acl.concat(field.acl_ro); }

    return _CollectionHasFieldAccess(field, acl);
}

function CollectionHasFieldRwAccess(field) {
    var acl_rw = field.acl    || [];
    var acl_ro = field.acl_ro || [];

    // default to everyone having rw access when no acl present
    if (!acl_rw.length && !acl_ro.length) { return true; }

    if(App.$data.user.group == 'admin') { return true; }

    // treat acl_rw as a whitelist when it has any values
    if(acl_rw.length && _CollectionHasFieldAccess(field, acl_rw)){ return true; }

    // treat acl_ro as a blacklist
    return !this._CollectionHasFieldAccess(field, acl_ro);
}

</script>


<script type="riot/tag" src="@base('collections:assets/entries-batchedit.tag')"></script>

<div>

    <ul class="uk-breadcrumb">
        <li><a href="@route('/collections')">@lang('Collections')</a></li>
        <li class="uk-active" data-uk-dropdown="mode:'hover', delay:300">

            <a><i class="uk-icon-bars"></i> {{ htmlspecialchars(@$collection['label'] ? $collection['label']:$collection['name'], ENT_QUOTES, 'UTF-8') }}</a>

            @if($app->module('collections')->hasaccess($collection['name'], 'collection_edit'))
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Actions')</li>
                    <li><a href="@route('/collections/collection/'.$collection['name'])">@lang('Edit')</a></li>
                    @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
                    <li class="uk-nav-divider"></li>
                    <li><a href="@route('/collections/trash/collection/'.$collection['name'])">@lang('Trash')</a></li>
                    @endif
                    <li class="uk-nav-divider"></li>
                    <li class="uk-text-truncate"><a href="@route('/collections/export/'.$collection['name'])" download="{{ $collection['name'] }}.collection.json">@lang('Export entries')</a></li>
                    <li class="uk-text-truncate"><a href="@route('/collections/import/collection/'.$collection['name'])">@lang('Import entries')</a></li>
                </ul>
            </div>
            @endif

        </li>
    </ul>

</div>

@render('collections:views/partials/entries'.($collection['sortable'] ? '.sortable':'').'.php', compact('collection'))
