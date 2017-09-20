<cp-diff>

    <style>
        cp-diff del {
            text-decoration: none;
            background: rgba(199, 8, 55, .12);
        }

        cp-diff ins {
            text-decoration: none;
            background: rgba(10, 233, 6, .12);
        }
    </style>


    <div><pre ref="canvas" style="background:none;margin:0;"></pre></div>

    <script>

        var $this = this;

        this.on('mount', function() {

            App.assets.require([
                '/assets/lib/diff.js'
            ], function() {
                
                $this.diff(opts.oldtxt, opts.newtxt)

                $this.on('update', function() {
                    $this.diff(opts.oldtxt, opts.newtxt)
                });
            });
        });

        diff(oldtxt, newtxt) {

            if (typeof(oldtxt) !== 'string') oldtxt = JSON.stringify(oldtxt, null, 2);
            if (typeof(newtxt) !== 'string') newtxt = JSON.stringify(newtxt, null, 2);

            //var diff = JsDiff.diffWords(oldtxt || '', newtxt || '');
            var diff = JsDiff.diffChars(oldtxt || '', newtxt || '');
            var fragment = document.createDocumentFragment();

            for (var i=0; i < diff.length; i++) {

                if (diff[i].added && diff[i + 1] && diff[i + 1].removed) {
                    var swap = diff[i];
                    diff[i] = diff[i + 1];
                    diff[i + 1] = swap;
                }

                var node;
                if (diff[i].removed) {
                    node = document.createElement('del');
                    node.appendChild(document.createTextNode(diff[i].value));
                } else if (diff[i].added) {
                    node = document.createElement('ins');
                    node.appendChild(document.createTextNode(diff[i].value));
                } else {
                    node = document.createTextNode(diff[i].value);
                }
                fragment.appendChild(node);
            }

            this.refs.canvas.textContent = '';
            this.refs.canvas.appendChild(fragment);
        }

    </script>

</cp-diff>