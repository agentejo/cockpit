<field-code>

    <style>

        field-code .CodeMirror {
            height: auto;
        }

    </style>

    <codemirror name="codemirror" syntx="{ opts.syntax || 'text' }"></codemirror>

    <script>

        var $this = this, editor;

        this.value  = null;
        this._field = null;

        this.ready = new Promise(function(resolve){

            $this.tags.codemirror.on('ready', function(){
                editor = $this.codemirror.editor;
                $this.isReady = true;
                resolve();
            });
        });

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

            this.ready.then(function() {
                editor.setValue($this.value || '');
            });

            this.codemirror.on('input', function() {
                $this.$setValue($this.codemirror.editor.getValue(), true);
            });
        });

    </script>

</field-code>
