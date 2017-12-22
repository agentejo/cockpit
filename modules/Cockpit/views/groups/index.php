<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('Groups')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>

    @if($app->module('cockpit')->isSuperAdmin())
    <div class="uk-form uk-clearfix" show="{!loading}">

        <span class="uk-form-icon">
            <i class="uk-icon-filter"></i>
            <input type="text" class="uk-form-large uk-form-blank" ref="txtfilter" placeholder="@lang('Filter by name...')" onchange="{ updatefilter }">
        </span>

        <div class="uk-form-select">
            <span class="uk-button uk-button-outline uk-text-uppercase {(aclfilter != '_all' && 'uk-text-primary') || 'uk-text-muted'}">
                <i class="uk-icon-group"></i> {filterGroup == '_all' ? App.i18n.get('All') : filterGroup }
            </span>
            <select onchange="{ updatefilter }" ref="aclfilter">
                <option value="_all">@lang('All')</option>
                <option value="{acl}" each="{acl in acls}">{acl}</option>
            </select>
        </div>

        <div class="uk-float-right">
            <a class="uk-button uk-button-primary uk-button-large" href="@route('/groups/create')">
                <i class="uk-icon-plus-circle uk-icon-justify"></i> @lang('Group')
            </a>
        </div>

    </div>
    @endif

    <div class="uk-text-xlarge uk-text-center uk-text-primary uk-margin-large-top" show="{ loading }">
        <i class="uk-icon-spinner uk-icon-spin"></i>
    </div>

    <div class="uk-text-large uk-text-center uk-margin-large-top uk-text-muted" show="{ !loading && !groups.length }">
        <img class="uk-svg-adjust" src="@url('assets:app/media/icons/accounts.svg')" width="100" height="100" alt="@lang('Accounts')" data-uk-svg />
        <p>@lang('No groups found')</p>
    </div>

    <table class="uk-table uk-table-tabbed uk-table-striped uk-margin-top" if="{ ready && !loading && groups.length }">
        <thead>
            <tr>
                <th class="uk-text-small" data-sort="group">
                    <a class="uk-link-muted uk-noselect {sortedBy == 'group' && 'uk-text-primary'}">
                        @lang('Name') <span if="{sortedBy == 'group'}" class="uk-icon-long-arrow-{ sortedOrder == -1 ? 'up':'down'}"></span>
                    </a>
                </th>
                <th class="uk-text-small" data-sort="admin">
                    <a class="uk-link-muted uk-noselect {sortedBy == 'admin' && 'uk-text-primary'}">
                        @lang('Admin') <span if="{sortedBy == 'admin'}" class="uk-icon-long-arrow-{ sortedOrder == -1 ? 'up':'down'}"></span>
                    </a>
                </th>
                <th class="uk-text-small" data-sort="backend">
                    <a class="uk-link-muted uk-noselect {sortedBy == 'backend' && 'uk-text-primary'}">
                        @lang('Backend') <span if="{sortedBy == 'backend'}" class="uk-icon-long-arrow-{ sortedOrder == -1 ? 'up':'down'}"></span>
                    </a>
                </th>
                <th class="uk-text-small" data-sort="finder">
                    <a class="uk-link-muted uk-noselect {sortedBy == 'finder' && 'uk-text-primary'}">
                        @lang('Finder') <span if="{sortedBy == 'finder'}" class="uk-icon-long-arrow-{ sortedOrder == -1 ? 'up':'down'}"></span>
                    </a>
                </th>
                <th class="uk-text-small" data-sort="_created">
                    <a class="uk-link-muted uk-noselect {sortedBy == '_created' && 'uk-text-primary'}">
                        @lang('Created') <span if="{sortedBy == '_created'}" class="uk-icon-long-arrow-{ sortedOrder == -1 ? 'up':'down'}"></span>
                    </a>
                </th>
                <th class="uk-text-small" data-sort="_modified">
                    <a class="uk-link-muted uk-noselect {sortedBy == '_modified' && 'uk-text-primary'}">
                        @lang('Modified')  <span if="{sortedBy == '_modified'}" class="uk-icon-long-arrow-{ sortedOrder == -1 ? 'up':'down'}"></span>
                    </a>
                </th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
            <tr each="{group, $index in groups}" if="{ infilter(group) }">
                <td>
                    <a class="uk-link-muted" href="@route('/groups/group')/{ group._id }" title="@lang('Edit account')">
                        { group.group }
                    </a>
                </td>
                <td>
                   <!--
                   TODO JB: this is not working properly
                   -->
                    { group.admin ? "@lang('Yes')" : "@lang('No')" }
                </td>
                <td>
                    { group.cockpit.backend ? "@lang('Yes')" : "@lang('No')" }
                </td>
                <td>
                    { group.cockpit.finder ? "@lang('Yes')" : "@lang('No')" }
                </td>

                <td><span class="uk-badge uk-badge-outline uk-text-muted">{ App.Utils.dateformat( new Date( 1000 * group._created )) }</span></td>
                <td><span class="uk-badge uk-badge-outline uk-text-primary">{ App.Utils.dateformat( new Date( 1000 * group._modified )) }</span></td>
                <td>
                    <span data-uk-dropdown="pos:'bottom-right'">

                        <a class="uk-icon-bars"></a>

                        <div class="uk-dropdown">
                            <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                                <li class="uk-nav-header">@lang('Actions')</li>
                                <li><a href="@route('/groups/group')/{ group._id }">@lang('Edit')</a></li>
                                <li class="uk-nav-item-danger"><a onclick="{ this.parent.remove }" href="#">@lang('Delete')</a></li>
                            </ul>
                        </div>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>

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

    <script type="view/script">

        var $this = this, limit = 20;

        this.groups   = [];
        this.current  = {{ json_encode($current) }};
        this.filter   = '';
        this.aclfilter = '_all'
        this.sort     = {'_created': -1};
        this.page     = 1;
        this.count    = 0;
        this.page     = 1;

        this.loading  = true;
        this.ready    = false;

        this.on('mount', function() {

            App.$(this.root).on('click', '[data-sort]', function() {
                $this.updatesort(this.getAttribute('data-sort'));
            });

            this.load();
        });

        remove(evt) {
            var group = evt.item.group;

            if (group._id == this.current) {
                App.ui.notify("You can't delete your own group", "danger");
                return;
            }

            App.ui.confirm("Are you sure?", function() {

                App.request('/groups/remove', { "group": group }).then(function(data){

                    App.ui.notify("Group removed", "success");
                    $this.groups.splice(evt.item.$index, 1);
                    $this.update();
                });
            });
        }

        updatefilter() {
            var load = this.filter ? true : false;

            this.filter = this.refs.txtfilter.value || null;
            this.aclfilter = this.refs.aclfilter.value || null;

            if (this.filter || this.aclfilter || load) {
            //if (this.filter || load) {
                this.groups = [];
                this.loading = true;
                this.page = 1;
                this.load();
            }
        }

        infilter(group) {
            var group = group.group.toLowerCase();
            return (!this.filter || (group && group.indexOf(this.filter) !== -1));
        }

        updatesort(field) {

            if (!field) {
                return;
            }

            var col = field;

            if (!this.sort[col]) {
                this.sort      = {};
                this.sort[col] = 1;
            } else {
                this.sort[col] = this.sort[col] == 1 ? -1 : 1;
            }

            this.sortedBy = field;
            this.sortedOrder = this.sort[col];

            this.accounts = [];

            this.load();
        }

        load() {

            var options = { sort:this.sort };

            if (this.filter || this.filterGroup) {
                options.filter = {};
            }

            /*
            if (this.filter) {
                options.filter.$or = [
                    {name  : {$regex : this.filter}},
                    {user  : {$regex : this.filter}},
                    {email : {$regex : this.filter}}
                ];
            }
            */

            /*
            if (this.filterGroup && this.filterGroup != '_all') {
                options.filter.group = this.filterGroup;
            }
            */

            options.limit = limit;
            options.skip  = (this.page - 1) * limit;

            this.loading = true;

            return App.request('/groups/find', {options:options}).then(function(data){

                this.groups   = data.groups;
                this.pages    = data.pages;
                this.page     = data.page;
                this.count    = data.count;

                this.ready    = true;
                this.loadmore = data.groups.length && data.groups.length == limit;

                this.loading = false;

                this.update();

            }.bind(this))
        }

        loadpage(page) {
            this.page = page > this.pages ? this.pages:page;
            this.load();
        }

    </script>

</div>
