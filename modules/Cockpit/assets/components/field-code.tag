<field-code>

    <style>

        field-code .CodeMirror {
            height: auto;
        }

    </style>

    <codemirror name="codemirror" syntax="{ opts.syntax || 'text' }"></codemirror>

    <script>

        this.on('mount', function() { this.trigger('update'); });
        this.on('update', function() { if (opts.opts) App.$.extend(opts, opts.opts); });

        var $this = this, editor;

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

            this.ready = new Promise(function(resolve){

                $this.tags.codemirror.on('ready', function(){
                    editor = $this.codemirror.editor;
                    $this.isReady = true;
                    resolve();
                });
            });

            this.ready.then(function() {

                editor.setValue($this.value || '');

                editor.on('change', function() {
                    $this.$setValue(editor.getValue(), true);
                });
            });
        });

    </script>

</field-code>
