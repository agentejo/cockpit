{{ $app->assets(['assets:lib/uikit/js/components/form-password.min.js']) }}

<div>
    <ul class="uk-breadcrumb">
        @hasaccess?('cockpit', 'groups')
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li><a href="@route('/groups')">@lang('Groups')</a></li>
        @endif
        <li class="uk-active"><span>@lang('Group')</span></li>
    </ul>
</div>

<div riot-view>

   <article class="col-sm-12 col-md-12 col-lg-12">

      <div class="jarviswidget" id="wid-id-0" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false">

         <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
            <h2>@lang('Create new Group')</h2>

         </header>

         <div>

            <div class="jarviswidget-editbox"></div>

            <div class="widget-body no-padding mt-10-imp">

               <form class="smart-form uk-form" onsubmit="{ submit }">
                  <!--<header>
                     @lang('Main')
                  </header>-->

                  <div class="row">

                     <section class="col col-6">
                        <label class="_uk-text-small">@lang('Group Name')</label>
                        <!--<header class="ml-0-imp mr-0-imp">
                           @lang('Group Name')
                        </header>-->
                        <input class="uk-width-1-1 uk-form-large" type="text" bind="group.group" autocomplete="off" required>
                     </section>

                     <section class="col col-6">
                        <label class="label">@lang('Group Access')</label>


                        <div class="uk-margin-small-top">
                            <field-boolean bind="group.admin" label="@lang('Admin')"></field-boolean>
                        </div>

                        <!--
                        <label class="toggle">
                           <input type="checkbox" name="checkbox-toggle" { group.admin ? 'checked':'' } >
                           <i data-swchon-text="@lang('ON')" data-swchoff-text="@lang('OFF')" onclick="{ toggle_admin }"></i>@lang('Admin')
                        </label>
                        -->
                        <label class="toggle">
                           <input type="checkbox" name="checkbox-toggle" { group.backend ? 'checked':'' } >
                           <i data-swchon-text="@lang('ON')" data-swchoff-text="@lang('OFF')" onclick="{ toggle_backend }"></i>@lang('Backend')</label>
                        <label class="toggle">
                           <input type="checkbox" name="checkbox-toggle" { group.finder ? 'checked':'' }>
                           <i data-swchon-text="@lang('ON')" data-swchoff-text="@lang('OFF')" onclick="{ toggle_finder }"></i>@lang('Finder')</label>
                     </section>
                  </div>

                  @trigger('cockpit.group.editview')

                  <footer>
                     <button type="submit" class="btn btn-primary">
                        @lang('Save')
                     </button>
                     <button type="button" class="btn btn-default" onclick="window.history.back();">
                        @lang('Back')
                     </button>
                  </footer>

               </form>

            </div>
         </div>
      </div>
   </article>


   <script type="view/script">

       var $this = this;

       this.mixin(RiotBindMixin);

       this.group   = {{ json_encode(@$group) }};

       console.info(this.group);

       this.tabs      = [];
       this.tab       = 'general';
       this.fields    = {{ (isset($fields)) ? json_encode($fields) : "null" }} || {};
       this.meta      = {};

       /*
       Object.keys(this.fields || {}).forEach(function(key, group){

           group = $this.fields[key].group || 'Additional';

           if (!$this.meta[group]) {
               $this.meta[group] = {};
           }

           if ($this.tabs.indexOf(group) < 0) {
               $this.tabs.push(group);
           }

           $this.meta[group][key] = $this.fields[key];

           if ($this.account[key] === undefined) {
               $this.account[key] = $this.fields[key].options && $this.fields[key].options.default || null;
           }
       });
       */

       selectTab(e) {

           this.tab = e.target.getAttribute('select');

           setTimeout(function(){
               UIkit.Utils.checkDisplay();
           }, 50);
       }


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

        //-------------
       toggle_admin() {
           console.info("new admin val: "+!(this.group.admin));
           this.group.admin = !(this.group.admin);
       }

       toggle_backend() {
           this.group.backend = !(this.group.backend);
       }

       toggle_finder() {
           this.group.finder = !(this.group.finder);
       }
        //--------------

       toggleactive() {
           this.group.active = !(this.group.active);
       }

       submit(e) {

           if(e) e.preventDefault();

           App.request("/groups/save", {"group": this.group}).then(function(data){
               $this.group = data;
               App.ui.notify("Group saved", "success");
           });

           return false;
       }

   </script>

</div>
