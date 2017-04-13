<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('API Access')</span></li>
    </ul>
</div>


<div class="uk-margin-large-top uk-form" riot-view>

    <h3>@lang('Full access API-key') <span class="uk-badge">@lang('Share with caution')</span></h3>

    <div class="uk-grid">
        <div class="uk-width-1-2">

            <div class="uk-grid">
                <div class="uk-flex-item-1">
                    <input class="uk-width-1-1 uk-form-large uk-text-primary" type="text" placeholder="@lang('No key generated')" bind="keys.master" name="fullaccesskey" readonly>
                </div>
                <div>
                    <button class="uk-button uk-button-large" type="button" onclick="{ generate }">@lang('Generate')</button>
                </div>
            </div>

            <button class="uk-button uk-button-primary uk-button-large uk-margin-top" type="button" name="button" onclick="{ save }" show="{ keys.master }">@lang('Save')</button>

        </div>
        
        <div class="uk-width-1-2"></div>
    </div>



    <script type="view/script">

        this.mixin(RiotBindMixin);

        var $this = this;

        this.keys = {{ json_encode($keys) }};

        generate() {
            this.keys.master = App.Utils.generateToken(120);
        }

        save() {

            App.request('/restadmin/save', {data:this.keys}).then(function(){
                App.ui.notify("Data saved", "success");
            });
        }

    </script>

</div>
