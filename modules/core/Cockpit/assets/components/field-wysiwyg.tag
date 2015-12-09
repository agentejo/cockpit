<field-wysiwyg>

    <textarea name="input" class="uk-width-1-1" rows="5" style="height:350px;visibility:hidden;"></textarea>

    <script>

        var $this     = this,
            lang      = document.documentElement.getAttribute('lang') || 'en',
            languages = ['ar','az','ba','bg','by','ca','cs','da','de','el','eo','es_ar','es','fa','fi','fr','ge','he','hr','hu','id','it','ja','ko','lt','lv','mk','nl','no_NB','pl','pt_br','pt_pt','ro','ru','sl','sq','sr-cir','sr-lat','sv','th','tr','ua','vi','zh_cn','zh_tw'],
            editor;

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.rows) {
            this.input.setAttribute('rows', opts.rows);
        }

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

            if (!this.input.id) {
                this.input.id = 'wysiwyg-'+parseInt(Math.random()*10000000, 10);
            }

            var assets = [
                '/assets/lib/tinymce/tinymce.min.js'
            ];

            var plugins = [];

            App.assets.require(assets, function() {

                App.assets.require(plugins, function() {

                    initPlugins();

                    this.input.value = this.value;

                    tinymce.init(App.$.extend(true, {
                        resize: true,
                        height: 350,
                        menubar: 'edit insert view format table tools',
                        plugins: [
                            "link image lists preview hr anchor",
                            "code fullscreen media mediapath",
                            "table contextmenu paste"
                        ],
                        relative_urls: false
                    },opts.editor || {}, {

                      selector: '#'+this.input.id,
                      setup: function (ed) {


                          // Update model on button click
                          ed.on('ExecCommand', function (e) {
                             ed.save();
                             $this.$setValue($this.input.value, true);
                          });
                          // Update model on keypress
                          ed.on('KeyUp', function (e) {
                             ed.save();
                             $this.$setValue($this.input.value, true);
                          });

                          editor = ed;

                          App.$(document).trigger('init-wysiwyg-editor', [editor]);
                      }

                    }));

                }.bind(this));

            }.bind(this)).catch(function(){

                this.input.value = this.value;

                App.$(this.input).css('visibility','').on('change', function() {
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
                        }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });
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
