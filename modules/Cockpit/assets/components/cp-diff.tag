<cp-diff>

    <style>

        pre {
            background:none;
            margin:0;
            width:100%;
            overflow: auto;
            word-wrap: normal;
            white-space: pre;
        }
        
        del {
            text-decoration: none;
            background: #A52A2A;
            color: #fff;
        }

        ins {
            text-decoration: none;
            background: #008000;
            color: #fff;
        }
    </style>


    <div class="uk-overflow-container">
        <div><pre ref="canvas" style="background:none;margin:0;"></pre></div>
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
            
            App.assets.require(['/assets/lib/diff.js'], function() {
                
                if (typeof(oldtxt) !== 'string') oldtxt = JSON.stringify(oldtxt, null, 2);
                if (typeof(newtxt) !== 'string') newtxt = JSON.stringify(newtxt, null, 2);

                //var diff = JsDiff.diffWords(oldtxt || '', newtxt || '');
                //var diff = JsDiff.diffChars(oldtxt || '', newtxt || '');
                var diff = JsDiff.diffLines(oldtxt || '', newtxt || '');
                var html = '';

                for (var i=0; i < diff.length; i++) {

                    if (diff[i].added && diff[i + 1] && diff[i + 1].removed) {
                        var swap = diff[i];
                        diff[i] = diff[i + 1];
                        diff[i + 1] = swap;
                    }

                    if (diff[i].removed) {
                        html += '<del>'+diff[i].value+'</del>';
                    } else if (diff[i].added) {
                        html += '<ins>'+diff[i].value+'</ins>';
                    } else {
                        html += diff[i].value;
                    }
                }

                this.refs.canvas.textContent = '';
                this.refs.canvas.innerHTML = html;

            }.bind(this));
        }

    </script>

</cp-diff>
