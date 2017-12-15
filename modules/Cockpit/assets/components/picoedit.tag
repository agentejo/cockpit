<picoedit>

    <style>

        .picoedit-toolbar {
            padding-top: 15px;
            padding-bottom: 15px;
        }

    </style>

    <div class="picoedit" show="{isReady}">

        <div class="picoedit-toolbar uk-flex" if="{path}">
            <div class="uk-flex-item-1 uk-text-truncate">
                <strong class="uk-text-small"><i class="uk-icon-pencil uk-margin-small-right"></i> { path }</strong>
            </div>
            <div>
                <button type="button" class="uk-button uk-button-primary" onclick="{ save }"><i class="uk-icon-save"></i></button>
            </div>
        </div>

        <codemirror ref="codemirror" height="{opts.height || 400}"></codemirror>
    </div>

    <script>

        var root  = this.root,
            $this = this,
            editor;

        this.isReady = false;
        root.picoedit = this;

        this.path = null;

        this.on('mount', function() {

            this.ready = new Promise(function(resolve){

                $this.tags.codemirror.on('ready', function(){

                    editor = $this.refs.codemirror.editor;

                    editor.addKeyMap({
                        'Ctrl-S': function(){ $this.save(); },
                        'Cmd-S': function(){ $this.save(); }
                    });

                    resolve();
                });
            });

            if (opts.path) {
                this.open(opts.path);
            }
        });

        open(path) {

            this.ready.then(function(){

                this.path = path;

                editor.setValue('');
                editor.clearHistory();

                requestapi({"cmd":"readfile", "path":path}, function(content){
                    editor.setOption("mode", getMode(path));
                    editor.focus();
                    $this.isReady = true;

                    this.update();

                    editor.setValue(content);
                    editor.refresh();

                }.bind(this), "text");

            }.bind(this));
        }

        save() {

            if (!this.path) return;

            requestapi({"cmd":"writefile", "path": this.path, "content":editor.getValue()}, function(status){

                App.ui.notify("File saved", "success");

            }, "text");
        }

        function requestapi(data, fn, type) {

            data = Object.assign({"cmd":""}, data);

            return App.request('/media/api', data, type).then(fn);
        }

        function getMode(path) {

            var def = CodeMirror.findModeByFileName(path) || {},
                mode = def.mode || 'text';

            if (mode == 'php') {
                mode = 'application/x-httpd-php';
            }

            return mode;
        }

    </script>

</picoedit>
