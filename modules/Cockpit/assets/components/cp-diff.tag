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
        <pre><code ref="canvas"></code></pre>
    </div>

    <script>

        var $this = this;

        this.on('mount', function() {
            this.update();
        });

        this.on('update', function() {
            this.diff(opts.oldtxt, opts.newtxt)
        });

        diff(oldtxt, newtxt) {

            if (typeof(oldtxt) !== 'string') oldtxt = JSON.stringify(oldtxt, null, 2);
            if (typeof(newtxt) !== 'string') newtxt = JSON.stringify(newtxt, null, 2);

            this.refs.canvas.textContent = oldtxt;
        }

    </script>

</cp-diff>
