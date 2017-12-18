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

                        @if(!isset($group['_id']))
                        <div class="uk-grid-margin">
                            <div class="uk-form-row">
                                <div class="uk-margin-small-top">
                                    <field-boolean label="@lang('Also create a User with the Groups name')" onclick="{ toggle_alsoCreateUser }" ></field-boolean>
                                </div>
                            </div>
                        </div>
                        <div class="bulk-actions" ref="bulkactions">
                            <div class="uk-grid-margin">
                                <div class="uk-form-row">
                                    <div class="" style="float:left;">
                                        <field-boolean label="@lang('Also create a Collection for the fresh User')" onclick="{ toggle_alsoCreateCollection }" disabled ></field-boolean>
                                    </div>
                                    <div class="" style="float: right">
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
            <div class="uk-margin-small-top">
                <field-boolean bind="group.admin" label="@lang('Admin')"></field-boolean>
            </div>
        </div>
        <div class="uk-form-row">
            <div class="uk-margin-small-top">
                <field-boolean bind="group.backend" label="@lang('Backend')"></field-boolean>
            </div>
        </div>
        <div class="uk-form-row">
            <div class="uk-margin-small-top">
                <field-boolean bind="group.finder" label="@lang('Finder')"></field-boolean>
            </div>
        </div>

    </div>

   <script type="view/script">

       var $this = this;

       this.mixin(RiotBindMixin);

       this.group   = {{ json_encode(@$group) }};
       this.collections = {{ json_encode(@$collections) }};

       //var firstCollectionEntry = Object.keys(this.collections)[0];
       //this.selectedCollection = firstCollectionEntry;
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

       /*toggle_bulkAction (e) {
           //this.alsoCreateUser = !(this.alsoCreateUser);
           console.info(e);
           console.info(e.target);
           //console.info(this);
       }*/

       submit(e) {
           if(e) e.preventDefault();
           // TODO prevent creation of groups thats already exist!
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
                  console.info(data);
                  var acl = {};
                  acl[group] = {"collection_edit":true,"entries_view":true,"entries_edit":true,"entries_create":true,"entries_delete":true};
                  var data = {
                    'name' : group, // TODO this may not contain whitespaces!!
                    'label' : group,
                    'fields' : data.result.fields,
                    'acl' : acl
                  };
                  App.callmodule('collections:createCollection', [group, data]).then(function(data) {
                     //console.info(data);
                     App.ui.notify("Collection for the fresh new user created", "success");
                  });
               });
           }

           return false;
       }

   </script>

</div>