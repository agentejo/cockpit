<field-object>

    <div ref="input" style="height: {opts.height || '300px'}"></div>

    <script>

        var $this = this, editor;

        this.value = {};

        if (opts.cls) {
            App.$(this.refs.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.refs.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require([

                '/assets/lib/jsoneditor/jsoneditor.min.css',
                '/assets/lib/jsoneditor/jsoneditor.min.js'

            ], function() {

                editor = new JSONEditor(this.refs.input, {
                    modes: ['tree', 'code'],
                    mode: 'code',
                    onChange: function(){
                        $this.value = editor.get() || {};
                        $this.$setValue($this.value, true);
                    }
                });

                editor.set(this.value);

            }.bind(this));

        });


        this.$updateValue = function(value) {

            if (typeof(value) != 'object') {
                value = {};
            }

            if (JSON.stringify(this.value) != JSON.stringify(value)) {
                this.value = value || {};
                if (editor)  {
                    editor.set(this.value);
                }
            }

        }.bind(this);

    </script>

</field-object>
