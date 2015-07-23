<field-html>

    <textarea name="input" class="uk-visibility-hidden"></textarea>

    <script>

        var $this = this, editor;

        this.value = '';

        this._field = null;

        this.$updateValue = function(value, field) {

            if (this.value != value) {

                this.value = value;

                if (editor && this._field != field) {
                    editor.editor.setValue(value || '');
                }
            }

            this._field = field;

        }.bind(this);


        this.on('mount', function(){

            App.assets.require([

                '/assets/lib/marked.js',
                '/assets/lib/codemirror/lib/codemirror.js',
                '/assets/lib/uikit/js/components/htmleditor.js'

            ], function() {

                $this.input.value = $this.value;

                editor = UIkit.htmleditor(this.input, opts);
                editor.on('input', function() {
                    $this.$setValue(editor.editor.getValue());
                });

            }.bind(this));
        });

    </script>

</field-html>
