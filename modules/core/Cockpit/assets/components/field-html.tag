<field-html>

    <textarea name="input" class="uk-visibility-hidden"></textarea>

    <script>

        var $this = this;

        this.value = null;

        this.$updateValue = function(value) {

            if (this.value != value) {

                this.value = value;
            }

        }.bind(this);


        this.on('mount', function(){

            App.assets.require([

                '/assets/lib/marked.js',
                '/assets/lib/codemirror/lib/codemirror.js',
                '/assets/lib/uikit/js/components/htmleditor.js'

            ], function() {

                $this.input.value = $this.value;

                var editor = UIkit.htmleditor(this.input, opts);

                editor.on('input', function() {
                    $this.$setValue(editor.editor.getValue());
                });

            }.bind(this));
        });

    </script>

</field-html>
