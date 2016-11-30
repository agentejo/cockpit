<field-date>

    <input ref="input" class="uk-width-1-1" bind="{ opts.bind }" type="text" placeholder="{ opts.placeholder }">

    <script>

        var $this = this;

        if (opts.cls) {
            App.$(this.refs.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.refs.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/datepicker.js', '/assets/lib/uikit/js/components/form-select.js'], function() {

                UIkit.datepicker(this.refs.input, opts).element.on('change', function() {
                    $this.refs.input.$setValue($this.refs.input.value);
                });

            }.bind(this));
        });

    </script>

</field-date>
