<field-wysiwyg>

    <textarea ref="input" class="uk-width-1-1" rows="5" style="height:350px;visibility:hidden;"></textarea>

    <script>

        var $this     = this,
            lang      = document.documentElement.getAttribute('lang') || 'en',
            languages = ['ar','az','ba','bg','by','ca','cs','da','de','el','eo','es_ar','es','fa','fi','fr','ge','he','hr','hu','id','it','ja','ko','lt','lv','mk','nl','no_NB','pl','pt_br','pt_pt','ro','ru','sl','sq','sr-cir','sr-lat','sv','th','tr','ua','vi','zh_cn','zh_tw'],
            editor;

        this.value = null;
        this._field = null;

        this.$updateValue = function(value, field) {

            if (this.value != value) {

                this.value = value;

                if (editor && this._field != field) {
                    editor.setContent(this.value || '');
                }
            }

            this._field = field;

        }.bind(this);


        this.on('mount', function(){

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.rows) {
                this.refs.input.setAttribute('rows', opts.rows);
            }

            if (!this.refs.input.id) {
                this.refs.input.id = 'wysiwyg-'+parseInt(Math.random()*10000000, 10);
            }

            var assets = [
                '/assets/lib/tinymce/tinymce.min.js'
            ];

            var plugins = [];

            App.assets.require(assets, function() {

                App.assets.require(plugins, function() {

                    initPlugins();

                    setTimeout(function(){

                        if (!App.$('#'+this.refs.input.id).length) return;

                        tinymce.init(App.$.extend(true, {
                            branding: false,
                            resize: true,
                            height: 350,
                            menubar: 'edit insert view format table tools',
                            plugins: [
                                "link image lists preview hr anchor",
                                "code fullscreen media mediapath",
                                "table contextmenu paste"
                            ],
                            relative_urls: false
                        }, opts.editor || {}, {

                          selector: '#'+this.refs.input.id,
                          setup: function (ed) {

                              $this.refs.input.value = $this.value;

                              var clbChange = function(e){
                                ed.save();
                                $this.$setValue($this.refs.input.value, true);
                              };

                              ed.on('ExecCommand', clbChange);
                              ed.on('KeyUp', clbChange);
                              ed.on('Change', clbChange);

                              var clbSave = function(){
                                var form = App.$($this.root).closest('form');

                                if (form.length) {
                                    form.trigger('submit', [ed]);
                                }
                              };

                              ed.addShortcut('ctrl+s','Save', clbSave, ed);
                              ed.addShortcut('meta+s','Save', clbSave, ed);

                              editor = ed;

                              App.$(document).trigger('init-wysiwyg-editor', [editor]);
                          }

                        }));


                    }.bind(this), 10);

                }.bind(this));

            }.bind(this)).catch(function(){

                this.refs.input.value = this.value;

                App.$(this.refs.input).css('visibility','').on('change', function() {
                    $this.$setValue(this.value);
                });

            }.bind(this));
        });


        function initPlugins() {

            if (initPlugins.done) return;

            tinymce.PluginManager.add('mediapath', function(editor) {

                editor.addMenuItem('mediapath', {
                    icon: 'image',
                    text: 'Insert image (Finder)',
                    onclick: function(){

                        App.media.select(function(selected) {
                            editor.insertContent('<img src="' + SITE_URL+'/'+selected + '" alt="">');
                        }, { typefilter:'image', pattern: '*.jpg|*.jpeg|*.png|*.gif|*.svg' });
                    },
                    context: 'insert',
                    prependToContext: true
                });

                editor.addMenuItem('assetpath', {
                    icon: 'image',
                    text: 'Insert Asset (Assets)',
                    onclick: function(){

                        App.assets.select(function(assets){

                            if (Array.isArray(assets) && assets[0]) {

                                var asset = assets[0], content;

                                if (asset.mime.match(/^image\//)) {
                                    content = '<img src="' + ASSETS_URL+asset.path + '" alt="">';
                                } else {
                                    content = '<a href="' + ASSETS_URL+asset.path + '">'+asset.title+'<a>';
                                }

                                editor.insertContent(content);
                            }
                        });

                    },
                    context: 'insert',
                    prependToContext: true
                });

            });

            initPlugins.done = true;
        }

        initPlugins.done = false;

    </script>

</field-wysiwyg>
