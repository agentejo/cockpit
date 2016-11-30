<field-colortag>


    <div class="uk-display-inline-block" data-uk-dropdown="pos:'right-center'">
        <a riot-style="font-size:{size};color:{value || '#ccc'}"><i class="uk-icon-circle"></i></a>

        <div class="uk-dropdown uk-text-center">

            <strong class="uk-text-small">{ App.i18n.get('Choose') }</strong>

            <div class="uk-grid uk-grid-small uk-margin-small-top uk-grid-width-1-4">
                <div class="uk-grid-margin" each="{color in colors}">
                    <a onclick="{parent.select}" style="color:{color};"><i class="uk-icon-circle"></i></a>
                </div>
            </div>

            <div class="uk-margin-top uk-text-small">
                <a onclick="{reset}">{ App.i18n.get('Reset') }</a>
            </div>

        </div>
    </div>


    <script>

        this.value  = '';
        this.size   = opts.size || 'inherit';
        this.colors = opts.colors || ['#D8334A','#FFCE54','#A0D468','#48CFAD','#4FC1E9','#5D9CEC','#AC92EC','#EC87C0','#BAA286','#8E8271','#3C3B3D'];

        this.$updateValue = function(value, field) {

            if (this.value !== value) {
                this.value = value;
                this.update();
            }

        }.bind(this);

        select(e) {
            this.$setValue(e.item.color);
        }

        reset() {
            this.$setValue('');
        }

    </script>

</field-colortag>
