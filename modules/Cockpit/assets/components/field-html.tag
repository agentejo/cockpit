<field-html>

    <textarea ref="input" class="uk-visibility-hidden" hidden></textarea>

    <script>

        var $this = this, editor;

        this.value = '';

        this._field = null;

        this.$updateValue = function(value, field) {

            if (this.value != value) {

                this.value = value;

                if (editor && this._field != field) {
                    editor.editor.setValue(value || '', true);
                }
            }

            this._field = field;

        }.bind(this);


        this.on('mount', function(){

            codemirror().then(function() {

                App.assets.require([
                    '/assets/lib/marked.js',
                    '/assets/lib/uikit/js/components/htmleditor.js'
                ], function() {

                    $this.refs.input.value = $this.value;

                    editor = UIkit.htmleditor(this.refs.input, opts);
                    editor.editor.on('change', function() {
                        $this.$setValue(editor.editor.getValue());
                    });

                    editor.off('action.image').on('action.image', function() {

                        App.media.select(function(selected) {

                            if (editor.getCursorMode() == 'markdown') {
                                editor['replaceSelection']('![title]('+SITE_URL+'/'+selected[0]+')');
                            } else {
                                editor['replaceSelection']('<img src="'+SITE_URL+'/'+selected[0]+'">');
                            }

                        }, { typefilter:'image', pattern: '*.jpg|*.jpeg|*.png|*.gif|*.svg' });

                    });

                    App.$(document).trigger('init-html-editor', [editor]);

                }.bind($this));

            });

        });

    </script>

</field-html>
