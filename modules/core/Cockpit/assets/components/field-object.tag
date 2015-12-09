<field-object>

    <textarea name="input" class="uk-width-1-1" onchange="{ change }" placeholder="{ opts.placeholder }">{}</textarea>

    <script>

        var $this = this, editor;

        this.value = {};

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        this.input.setAttribute('rows', opts.rows || 5);
        this.input.setAttribute('style', 'font-family: monospace;tab-size:2;');

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require([

                '/assets/lib/behave.js'

            ], function() {

                editor = new Behave({
                    textarea: $this.input,
                    replaceTab: true,
                    softTabs: true,
                    tabSize: 2,
                    autoOpen: true,
                    overwrite: true,
                    autoStrip: true,
                    autoIndent: true,
                    fence: false
                });

            }.bind(this));

        });


        this.$updateValue = function(value) {

            if (typeof(value) != 'object') {
                value = {};
            }

            if (JSON.stringify(this.value) != JSON.stringify(value)) {
                this.value = value || {};
                this.input.value = JSON.stringify(this.value, null, 2);
            }

        }.bind(this);

        change() {
            this.$setValue(App.Utils.str2json(this.input.value) || this.value);
        }

    </script>

</field-object>
