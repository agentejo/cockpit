<field-date>

    <input name="input" class="uk-width-1-1" type="text">

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/datepicker.js'], function() {

                UIkit.datepicker(this.input, opts);

            }.bind(this));
        });

    </script>

</field-date>
