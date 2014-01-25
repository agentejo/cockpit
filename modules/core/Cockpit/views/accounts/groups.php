{{ $app->assets(['cockpit:assets/js/groups.js']) }}

<style>
    .group-list li {
        position: relative;
        overflow: hidden;
    }
    .group-actions {
        position: absolute;
        display:none;
        min-width: 60px;
        text-align: right;
        top: 5px;
        right: 10px;
        font-size: 12px;
    }

    .group-list li.uk-active a {
        color: #fff;
    }

    .group-list li.uk-active .group-actions { display:block; }
</style>


<h1>
    <a href="@route('/settingspage')">@lang('Settings')</a> / <a href="@route('/accounts/index')">@lang('Accounts')</a> / @lang('Groups')
</h1>

<script>
    var ACL_DATA = {{ json_encode($acl) }};
</script>

<div class="app-panel" data-ng-controller="groups" ng-cloak>

    <div class="uk-grid uk-grid-divider" data-uk-grid-margin>
        <div class="uk-width-medium-1-5">
            <ul class="uk-nav uk-nav-side group-list">
                <li class="uk-nav-header"><i class="uk-icon-group"></i> @lang('Groups')</li>
                <li data-ng-repeat="(group,data) in acl" data-ng-class="active==group ? 'uk-active':''">
                    <a href="#@@ group @@" data-ng-click="setActive(group)">@@ group @@</a>
                    <ul class="uk-subnav group-actions uk-animation-slide-right" data-ng-if="group!='admin'">
                        <li><a href="#" data-ng-click="addOrEditGroup(group)"><i class="uk-icon-pencil"></i></a></li>
                        <li><a href="#" data-ng-click="addOrEditGroup(group, true)"><i class="uk-icon-trash-o"></i></a></li>
                    </ul>
                </li>
            </ul>
            <hr>
            <button class="uk-button uk-button-success" data-ng-click="addOrEditGroup()" title="Add group" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus"></i></button>
            <button class="uk-button uk-button-primary" data-ng-click="save()">@lang('Save')</button>
        </div>
        <div class="uk-width-medium-4-5">   

            <div class="uk-margin" data-ng-repeat="(resource, actions) in acl[active]">
                
                <div class="uk-grid uk-grid-divider">
                    <div class="uk-width-medium-1-3 uk-text-small">
                        <strong><i class="uk-icon-cog"></i> @@ resource @@</strong>
                    </div>
                    <div class="uk-width-medium-2-3">
                        <table class="uk-table uk-table-hover uk-text-small">
                            <tbody>
                                <tr data-ng-repeat="(key, value) in actions">
                                    <td data-ng-class="value ? '':'uk-text-muted'" width="80%">@@ key @@</td>
                                    <td align="right"><input type="checkbox" data-ng-disabled="active=='admin'" data-ng-model="acl[active][resource][key]"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
            

        </div>
    </div>

</div>