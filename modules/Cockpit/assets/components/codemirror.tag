<codemirror>

    <script>

        var $this = this,
            root  = this.root,
            $root = App.$(root),
            $textarea, editor, options;

        this.on('mount', function(){

            codemirror().then(function() {

                $textarea = App.$('<textarea style="visibility:hidden;"></textarea>');

                $root.append($textarea);

                editor = CodeMirror.fromTextArea($textarea[0], App.$.extend({
                    lineNumbers: true,
                    indentUnit: 2,
                    indentWithTabs: false,
                    smartIndent: false,
                    tabSize: 2,
                    autoCloseBrackets: true,
                    extraKeys: {
                        Tab: function(cm) {
                            var spaces = Array(cm.getOption("indentUnit") + 1).join(" ");
                            cm.replaceSelection(spaces);
                        }
                    }
                }, opts || {}));

                root.editor = editor;
                this.editor = editor;

                if (opts.syntax) {
                    var mode = CodeMirror.findModeByName(opts.syntax) || {mode:'text'};
                    editor.setOption("mode", mode.mode);
                }

                if (opts.theme) {
                    App.assets.require(['/assets/lib/codemirror/theme/'+opts.theme+'.css'], function() {
                        editor.setOption("theme", opts.theme);
                    });
                }

                if (opts.height) {

                    if (opts.height == "auto") {
                        editor.setOption("height", "auto");
                        editor.setOption("viewportMargin", Infinity);
                    } else {
                        editor.setSize(opts.width || '100%', opts.height);
                    }
                }

                this.trigger('ready');

            }.bind(this));

        });


    </script>

</codemirror>
