<field-html>

    <textarea ref="input" class="uk-visibility-hidden" hidden></textarea>

    <script>

        var $this = this, editor;

        this.value = '';
        this._field = null;
        this.evtSrc = false;

        this.$updateValue = function(value, field, force) {

            if (this.value != value) {

                if (typeof(value) != 'string') {
                    value = '';
                }

                this.value = value;

                if (editor && (!this.evtSrc || force)) {
                    editor.editor.setValue(value, true);
                }
            }

            this.evtSrc = false;

        }.bind(this);


        this.on('mount', function(){

            codemirror().then(function() {

                App.assets.require([
                    '/assets/lib/marked.js',
                    '/assets/lib/uikit/js/components/htmleditor.js'
                ], function() {

                    $this.refs.input.value = $this.value || '';

                    editor = UIkit.htmleditor(this.refs.input, opts);

                    editor.editor.on('change', function() {
                        $this.evtSrc = true;
                        $this.$setValue(editor.editor.getValue());
                    });

                    editor.editor.on('focus', function() {
                        editor.editor.refresh();
                    });

                    var buttons = {};

                    if (App.$data.acl.finder) {

                        buttons.cpfinder = {
                            title : 'Finder',
                            label : '<i class="uk-icon-folder-open"></i>'
                        };
                    }

                    buttons.cpasset = {
                        title : 'Asset',
                        label : '<i class="uk-icon-cloud"></i>'
                    };

                    editor.addButtons(buttons);

                    editor.on('action.cpfinder', function() {
                        App.media.select(function(selected) {

                            if (editor.getCursorMode() == 'markdown') {
                                editor['replaceSelection']('[title]('+SITE_URL+'/'+selected[0]+')');
                            } else {
                                editor['replaceSelection']('<a src="'+SITE_URL+'/'+selected[0]+'">'+selected[0]+'</a>');
                            }

                        }, { });
                    });

                    editor.on('action.cpasset', function() {

                        App.assets.select(function(assets){

                            if (Array.isArray(assets) && assets.length) {

                                var asset = assets[0], isImage = asset.mime.match(/^image\//);

                                if (editor.getCursorMode() == 'markdown') {
                                    editor['replaceSelection'](isImage ? '!['+asset.title+']('+ASSETS_URL+asset.path+')' : '['+asset.title+']('+ASSETS_URL+asset.path+')');
                                } else {
                                    editor['replaceSelection'](isImage ? '<img src="'+ASSETS_URL+asset.path+'" alt="'+asset.title+'">' : '<a href="'+ASSETS_URL+asset.path+'">'+asset.title+'</a>');
                                }
                            }
                        });
                    });

                    editor.options.toolbar = editor.options.toolbar.concat(['cpfinder', 'cpasset']);

                    App.$(document).trigger('init-html-editor', [editor]);

                }.bind($this));

            });

        });

    </script>

</field-html>
