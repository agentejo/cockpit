<div>
    <ul class="uk-breadcrumb">
        @hasaccess?('cockpit', 'groups')
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li><a href="@route('/groups')">@lang('Groups')</a></li>
        @endif
        <li class="uk-active"><span>@lang('Group')</span></li>
    </ul>
</div>

<div class="uk-grid uk-margin-top uk-invisible" data-uk-grid-margin riot-view>

    <div class="uk-width-medium-2-3">

        <div class="uk-panel">

            <div class="uk-grid" data-uk-grid-margin>

                <div class="uk-width-medium-1-1">

                    <form id="account-form" class="uk-form" onsubmit="{ submit }">

                        <div>
                            <div class="uk-form-row">
                                <label class="uk-text-small">@lang('Group Name')</label>
                                <input class="uk-width-1-1 uk-form-large" type="text" bind="group.group" required>
                            </div>
                        </div>

                        <div class="_uk-grid-margin uk-margin-small-top">
                            <strong class="uk-text-uppercase">vars</strong>
                            <div class="uk-panel uk-panel-box uk-panel-card var-row uk-hidden">
                                <div class="uk-grid uk-grid-small">
                                    <div class="uk-flex-item-1 uk-flex">
                                        <input class="uk-width-1-4 uk-form-small" type="text" placeholder="key" >
                                        <i class="uk-width-1-4 uk-text-center uk-icon-arrows-h"></i>
                                        <input class="uk-width-1-4 uk-form-small" type="text" placeholder="value" >
                                        <div class="uk-width-1-4 uk-text-right">
                                            <i class="uk-icon-trash" style="cursor: pointer" onclick="$(this).parents('.var-row').remove()"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="uk-width-1-1 uk-margin-small-top" ref="vars">
                                @if(isset($group['vars']))
                                    @foreach( $group['vars'] as $key => $val)
                                    <div class="uk-panel uk-panel-box uk-panel-card var-row">
                                        <div class="uk-grid uk-grid-small">
                                            <div class="uk-flex-item-1 uk-flex">
                                                <input class="uk-width-1-4 uk-form-small" type="text" placeholder="key" value="{{$key}}">
                                                <i class="uk-width-1-4 uk-text-center uk-icon-arrows-h"></i>
                                                <input class="uk-width-1-4 uk-form-small" type="text" placeholder="value" value="{{$val}}">
                                                <div class="uk-width-1-4 uk-text-right">
                                                    <i class="uk-icon-trash" style="cursor: pointer" onclick="$(this).parents('.var-row').remove()"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" onclick="{ dupe_var_row }" class="uk-button uk-button-small uk-button-success uk-float-right uk-margin-small-top uk-margin-small-bottom">+</button>
                            <hr style="clear:both" class="uk-margin-small-top" />
                        </div>

                        @if(!isset($group['_id']))
                        <div class="_uk-grid-margin uk-margin-small-top">
                            <div class="uk-form-row">
                                <strong class="uk-text-uppercase">bulkactions</strong>
                                <div class="uk-margin-small-top">
                                    <field-boolean label="@lang('Also create a User with the Groups name')" onclick="{ toggle_alsoCreateUser }" ></field-boolean>
                                </div>
                            </div>
                        </div>
                        <div class="bulk-actions" ref="bulkactions">
                            <div class="uk-grid-margin">
                                <div class="uk-form-row">
                                    <div class="uk-float-left">
                                        <field-boolean label="@lang('Also create a Collection for the fresh User')" onclick="{ toggle_alsoCreateCollection }" disabled ></field-boolean>
                                    </div>
                                    <div class="uk-float-right">
                                        <select onchange="{ updateSelectedCollection }" ref="collections" disabled>
                                            <option value="-1">@lang('Choose a Collection as Template')</option>
                                            <option value="{c.name}" each="{c in collections}">{c.label} ({c.name})</option>
                                        </select>
                                    </div>
                                    <hr style="clear: both"/>
                                </div>
                            </div>
                            <div>
                                <div class="uk-form-row">
                                    <div class="uk-margin-small-top">
                                        <field-boolean label="@lang('Also create a Region for the fresh User (NYI)')" disabled ></field-boolean>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="uk-form-row">
                                    <div class="uk-margin-small-top">
                                        <field-boolean label="@lang('Also create a Form for the fresh User (NYI)')" disabled ></field-boolean>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @trigger('cockpit.groups.editview')

                        <div class="uk-margin-large-top">
                            <button class="uk-button uk-button-large uk-button-primary uk-width-1-3 uk-margin-right">@lang('Save')</button>
                            <a href="@route('/groups')">@lang('Cancel')</a>
                        </div>

                    </form>

                </div>

            </div>
        </div>

    </div>

    <div class="uk-width-medium-1-4 uk-form">

        <h3>@lang('Group Access')</h3>

        <div class="uk-form-row">
            <strong class="uk-text-uppercase">Generic</strong>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.admin" label="@lang('Admin')"></field-boolean>
            </div>
        </div>
        <div class="uk-form-row">
            <strong class="uk-text-uppercase">cockpit</strong>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.cockpit.backend" label="@lang('Backend')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.cockpit.accounts" label="@lang('Accounts')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.cockpit.groups" label="@lang('Groups')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.cockpit.finder" label="@lang('Finder')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.cockpit.settings" label="@lang('Settings')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.cockpit.rest" label="@lang('RestAPI')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.cockpit.webhooks" label="@lang('Webhooks')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.cockpit.info" label="@lang('SysInfo')"></field-boolean>
            </div>
        </div>
        <div class="uk-form-row">
            <strong class="uk-text-uppercase">collections</strong>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.collections.create" label="@lang('Create')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.collections.delete" label="@lang('Delete')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.collections.manage" label="@lang('Manage')"></field-boolean>
            </div>
        </div>
        <div class="uk-form-row">
            <strong class="uk-text-uppercase">regions</strong>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.regions.create" label="@lang('Create')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.regions.delete" label="@lang('Delete')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.regions.manage" label="@lang('Manage')"></field-boolean>
            </div>
        </div>
        <div class="uk-form-row">
            <strong class="uk-text-uppercase">forms</strong>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.forms.create" label="@lang('Create')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.forms.delete" label="@lang('Delete')"></field-boolean>
            </div>
            <div class="uk-margin-small-top">
                <field-boolean bind="group.forms.manage" label="@lang('Manage')"></field-boolean>
            </div>
        </div>

    </div>

   <script type="view/script">

       var $this = this;

       this.mixin(RiotBindMixin);

       this.group   = {{ json_encode(@$group) }};
       this.collections = {{ json_encode(@$collections) }};

       this.selectedCollection = null;

       this.on('mount', function(){

           this.root.classList.remove('uk-invisible');

           // bind clobal command + save
           Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

               e.preventDefault();
               $this.submit();
               return false;
           });

           $this.update();

       });

       toggle_alsoCreateUser (e) {
           var alsoCreateUser = this.alsoCreateUser = !(this.alsoCreateUser);
           $(App.$(this.refs.bulkactions).find('field-boolean, select')).each(function(){
               $(this).attr('disabled', !alsoCreateUser);
           });
       }

       toggle_alsoCreateCollection(e) {
           this.alsoCreateCollection = !(this.alsoCreateCollection);
       }

       updateSelectedCollection(e) {
           // collection => template
           this.selectedCollection = $(App.$(this.refs.collections)).val();
       }

       dupe_var_row (e) {
           //$(App.$(this.refs.vars)).find('.var-row').eq(0).clone().appendTo($(App.$(this.refs.vars))).find('input, i.uk-icon-trash').val('').removeClass('uk-hidden');
           //$(App.$(this.refs.vars)).find('.var-row').eq(0).clone().appendTo($(App.$(this.refs.vars))).find('input, i.uk-icon-trash').val('');
           $('.var-row.uk-hidden').clone().removeClass('uk-hidden').appendTo($(App.$(this.refs.vars)));
       }

       submit(e) {

           if(e) e.preventDefault();

           // gather vars
           var var_row = $(App.$(this.refs.vars)).find('.var-row');
           var vars = {};
           $(var_row).each(function(k,v){
               var pair = var_row[k].find('input');
               var key = $(pair[0]).val();
               var val = $(pair[1]).val();
               if(key)
                  vars[key] = val;
           });
           this.group.vars = vars;

           // TODO JB: prevent creation of groups thats already exist!
           App.request("/groups/save", {"group": this.group}).then(function(data){
               $this.group = data;
               App.ui.notify("Group saved", "success");
           });

           if(this.alsoCreateUser) {
                var account = {
                    "user":this.group.group,
                    "email":this.group.group+"@DUMMY.de",
                    "active":true,
                    "group":this.group.group,
                    "i18n":"en",
                    "api_key":"account-"+App.Utils.generateToken(120),
                    "name":this.group.group,
                    "password":this.group.group
                };
                App.request("/accounts/save", {"account": account}).then(function(data){
                    App.ui.notify("Account with Group-name created!", "success");
                });
           }

           if(this.alsoCreateCollection && this.selectedCollection) {
               // this.selectedCollection // < the collection that shall be used as template for the new collection
               var group = this.group.group;
               App.callmodule('collections:collection', [this.selectedCollection]).then(function(data) {
                  var acl = {};
                  acl[group] = {"collection_edit":true,"entries_view":true,"entries_edit":true,"entries_create":true,"entries_delete":true};
                  var slug_group_name = App.Utils.sluggify(group, {"delimiter" : ''});
                  var data = {
                    'name' : slug_group_name,
                    'label' : group,
                    'fields' : data.result.fields,
                    'acl' : acl
                  };
                  App.callmodule('collections:createCollection', [slug_group_name, data]).then(function(data) {
                     App.ui.notify("Collection for the fresh new user created", "success");
                  });
               });
           }

           return false;
       }

   </script>

</div>