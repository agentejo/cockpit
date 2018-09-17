<cp-diff>

    <style>

        pre {
            background:none;
            margin:0;
            width
            overflow: auto;
            word-wrap: normal;
            white-space: pre;
        }
    </style>


    <div class="uk-overflow-container">
        <div ref="canvas"></div>
    </div>

    <script>

        var $this = this;

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function() {
            this.refs.canvas.innerHTML = '';
            this.diff(opts.oldtxt, opts.newtxt)
        });

        diff(oldtxt, newtxt) {

            if (['string', 'number', 'boolean'].indexOf(typeof(oldtxt)) !== -1) {
                this.refs.canvas.innerHTML = '<pre><code>'+JSON.stringify(oldtxt)+'</code></pre>';
            } else {
                App.assets.require([

                    '/assets/lib/jsoneditor/jsoneditor.min.css',
                    '/assets/lib/jsoneditor/jsoneditor.min.js'

                ], function() {

                    editor = new JSONEditor(this.refs.canvas, {
                        modes: ['tree'],
                        mode: 'tree',
                        navigationBar: false,
                        onEditable: function() {
                            return false;
                        }
                    });

                    editor.set(oldtxt);

                }.bind(this));
            }

        }

    </script>

</cp-diff>
