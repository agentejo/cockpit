<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/singletons')">@lang('Singletons')</a></li>
        <li class="uk-active"><span>@lang('Singleton')</span></li>
    </ul>
</div>


<div class="uk-margin" riot-view>

    <form class="uk-form" onsubmit="{ submit }">

        <div class="uk-grid">

            <div class="uk-width-medium-1-4">

                <div class="uk-panel uk-panel-box uk-panel-card">

                   <div class="uk-margin">
                       <label class="uk-text-small">@lang('Name')</label>
                       <input aria-label="@lang('Name')" class="uk-width-1-1 uk-form-large" type="text" ref="name" bind="singleton.name" pattern="[a-zA-Z0-9_]+" required>
                       <p class="uk-text-small uk-text-muted" if="{!singleton._id}">
                           @lang('Only alpha nummeric value is allowed')
                       </p>
                   </div>

                   <div class="uk-margin">
                       <label class="uk-text-small">@lang('Label')</label>
                       <input aria-label="@lang('Label')" class="uk-width-1-1 uk-form-large" type="text" ref="label" bind="singleton.label">
                   </div>

                   <div class="uk-margin">
                       <label class="uk-text-small">@lang('Group')</label>
                       <input aria-label="@lang('Group')" class="uk-width-1-1 uk-form-large" type="text" ref="group" bind="singleton.group">
                   </div>

                   <div class="uk-margin">
                       <label class="uk-text-small">@lang('Icon')</label>
                       <div data-uk-dropdown="pos:'right-center', mode:'click'">
                           <a><img class="uk-display-block uk-margin uk-container-center" riot-src="{ singleton.icon ? '@url('assets:app/media/icons/')'+singleton.icon : '@url('singletons:icon.svg')'}" alt="icon" width="100"></a>
                           <div class="uk-dropdown uk-dropdown-scrollable uk-dropdown-width-2">
                                <div class="uk-grid uk-grid-gutter">
                                    <div>
                                        <a class="uk-dropdown-close" onclick="{ selectIcon }" icon=""><img src="@url('singletons:icon.svg')" width="30" icon=""></a>
                                    </div>
                                    @foreach($app->helper("fs")->ls('*.svg', 'assets:app/media/icons') as $icon)
                                    <div>
                                        <a class="uk-dropdown-close" onclick="{ selectIcon }" icon="{{ $icon->getFilename() }}"><img src="@url($icon->getRealPath())" width="30" icon="{{ $icon->getFilename() }}"></a>
                                    </div>
                                    @endforeach
                                </div>
                           </div>
                       </div>
                   </div>

                   <div class="uk-margin">
                       <label class="uk-text-small">@lang('Color')</label>
                       <div class="uk-margin-small-top">
                           <field-colortag bind="singleton.color" title="@lang('Color')" size="20px"></field-colortag>
                       </div>
                   </div>

                   <div class="uk-grid-margin">
                       <label class="uk-text-small">@lang('Description')</label>
                       <textarea aria-label="@lang('Description')" class="uk-width-1-1 uk-form-large" name="description" bind="singleton.description" rows="5"></textarea>
                   </div>

                   @trigger('singletons.settings.aside')

               </div>

            </div>

            <div class="uk-width-medium-3-4">

                <div class="uk-form-row">

                    <ul class="uk-tab uk-flex uk-margin">
                        <li class="{ view==='fields' ? 'uk-active':'' }" data-view="fields"><a onclick="{ toggleview }">@lang('Fields')</a></li>
                        <li class="{ view==='acl' ? 'uk-active':'' }" data-view="acl"><a onclick="{ toggleview }">@lang('Permissions')</a></li>
                    </ul>

                    <div class="uk-margin-large-top" show="{ view==='fields' }">

                        <cp-fieldsmanager bind="singleton.fields"></cp-fieldsmanager>

                    </div>

                    <div class="uk-margin-top" show="{ view==='acl' }">

                        <div class="uk-panel-space">

                            <div class="uk-grid">
                                <div class="uk-width-1-3 uk-flex uk-flex-middle uk-flex-center">
                                    <div class="uk-text-center">
                                        <p class="uk-text-uppercase uk-text-small uk-text-bold">@lang('Public')</p>
                                        <img class="uk-text-primary uk-svg-adjust" src="@url('assets:app/media/icons/globe.svg')" alt="icon" width="80" data-uk-svg>
                                    </div>
                                </div>
                                <div class="uk-flex-item-1">
                                    <div class="uk-margin uk-text-small">
                                        <strong class="uk-text-uppercase">@lang('Singleton')</strong>
                                        <div class="uk-margin-top"><field-boolean bind="singleton.acl.public.form" label="@lang('Form')"></field-boolean></div>
                                        <div class="uk-margin-top"><field-boolean bind="singleton.acl.public.edit" label="@lang('Edit Singleton')"></field-boolean></div>
                                        <div class="uk-margin-top"><field-boolean bind="singleton.acl.public.data" label="@lang('Get Singleton Data')"></field-boolean></div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="uk-panel uk-panel-box uk-panel-space uk-panel-card uk-margin" each="{group in aclgroups}">

                            <div class="uk-grid">
                                <div class="uk-width-1-3 uk-flex uk-flex-middle uk-flex-center">
                                    <div class="uk-text-center">
                                        <p class="uk-text-uppercase uk-text-small">{ group }</p>
                                        <img class="uk-text-muted uk-svg-adjust" src="@url('assets:app/media/icons/accounts.svg')" alt="icon" width="80" data-uk-svg>
                                    </div>
                                </div>
                                <div class="uk-flex-item-1">
                                    <div class="uk-margin uk-text-small">
                                        <strong class="uk-text-uppercase">@lang('Singleton')</strong>
                                        <div class="uk-margin-top"><field-boolean bind="singleton.acl.{group}.form" label="@lang('Form')"></field-boolean></div>
                                        <div class="uk-margin-top"><field-boolean bind="singleton.acl.{group}.edit" label="@lang('Edit Singleton')"></field-boolean></div>
                                        <div class="uk-margin-top"><field-boolean bind="singleton.acl.{group}.data" label="@lang('Get Singleton Data')"></field-boolean></div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>

        <cp-actionbar>
            <div class="uk-container uk-container-center">

                <div class="uk-button-group">
                    <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                    <a class="uk-button uk-button-large" href="@route('/singletons/form')/{ singleton.name }" if="{ singleton._id }">@lang('Show form')</a>
                </div>

                <a class="uk-button uk-button-large uk-button-link" href="@route('/singletons')">
                    <span show="{ !singleton._id }">@lang('Cancel')</span>
                    <span show="{ singleton._id }">@lang('Close')</span>
                </a>
            </div>
        </cp-actionbar>
    </form>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.view = 'fields';

        this.singleton = {{ json_encode($singleton) }};
        this.aclgroups  = {{ json_encode($aclgroups) }};

        if (!this.singleton.acl) {
            this.singleton.acl = {};
        }

        if (Array.isArray(this.singleton.acl)) {
            this.singleton.acl = {};
        }

        this.on('mount', function(){

            this.trigger('update');

            // bind global command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                if (App.$('.uk-modal.uk-open').length) {
                    return;
                }

                e.preventDefault();
                $this.submit();
                return false;
            });

            // lock resource
            var idle = setInterval(function() {
                if (!$this.singleton._id) return;
                Cockpit.lockResource($this.singleton._id);
                clearInterval(idle);
            }, 60000);
        });

        this.on('update', function(){

            // lock name if saved
            if (this.singleton._id) {
                this.refs.name.disabled = true;
            }
        });

        selectIcon(e) {
            this.singleton.icon = e.target.getAttribute('icon');
        }

        submit(e) {

            if(e) e.preventDefault();

            var singleton = this.singleton;

            App.callmodule('singletons:saveSingleton', [this.singleton.name, singleton]).then(function(data) {

                if (data.result) {

                    App.ui.notify("Saving successful", "success");
                    $this.singleton = data.result;
                    $this.update();

                } else {

                    App.ui.notify("Saving failed.", "danger");
                }
            });
        }

        toggleview(e) {
            this.view = e.target.parentElement.getAttribute('data-view');
        }

    </script>
</div>
