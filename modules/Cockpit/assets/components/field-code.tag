<field-code>

    <style>

        field-code .CodeMirror {
            height: auto;
        }

    </style>

    <codemirror ref="codemirror" syntax="{ opts.syntax || 'text' }" height="{ opts.height || 200 }"></codemirror>

    <script>

        var $this = this, editor, idle;

        this.value  = null;
        this._field = null;

        this.$updateValue = function(value, field) {

            if (this.value != value) {

                this.value = value;

                if (editor && field != this._field) {
                    editor.setValue($this.value || '', true);
                }
            }

            this._field = field;

        }.bind(this);

        this.on('mount', function(){

            this.refs.codemirror.on('ready', function(){
                editor = $this.refs.codemirror.editor;

                editor.setValue($this.value || '');

                editor.on('change', function() {
                    $this.$setValue(editor.getValue(), true);
                });

                $this.isReady = true;
                $this.update();

                idle = setInterval(function() {

                    if (App.$($this.root).is(':visible')) {
                        if(!editor.hasFocus()) editor.refresh();
                    } else {
                        if (!App.$($this.root).closest('body').length) clearInterval(idle);
                    }
                }, 500)

            });
        });

    </script>

</field-code>
