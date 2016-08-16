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
            this.keys.master = buildToken(120);
        }

        save() {

            App.request('/restadmin/save', {data:this.keys}).then(function(){
                App.ui.notify("Data saved", "success");
            });
        }

        function buildToken(bits, base) {
            if (!base) base = 16;
            if (bits === undefined) bits = 128;
            if (bits <= 0) return '0';
            var digits = Math.log(Math.pow(2, bits)) / Math.log(base);
            for (var i = 2; digits === Infinity; i *= 2) {
                digits = Math.log(Math.pow(2, bits / i)) / Math.log(base) * i;
            }
            var rem = digits - Math.floor(digits), res = '';
            for (var i = 0; i < Math.floor(digits); i++) {
                var x = Math.floor(Math.random() * base).toString(base);
                res = x + res;
            }
            if (rem) {
                var b = Math.pow(base, rem);
                var x = Math.floor(Math.random() * b).toString(base);
                res = x + res;
            }
            var parsed = parseInt(res, base);
            if (parsed !== Infinity && parsed >= Math.pow(2, bits)) {
                return hat(bits, base)
            }
            else return res;
        };


    </script>

</div>
