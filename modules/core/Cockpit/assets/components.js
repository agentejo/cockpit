riot.tag2('codemirror', '', '', '', function(opts) {

        var $this = this,
            root  = this.root,
            $root = App.$(root),
            $textarea, editor, options;

        this.on('mount', function(){

            App.assets.require([
                '/assets/lib/codemirror/lib/codemirror.js'
            ], function() {

                $textarea = App.$('<textarea style="visibility:hidden;"></textarea>');

                $root.append($textarea);

                editor = CodeMirror.fromTextArea($textarea[0], App.$.extend({
                    lineNumbers: true,
                    theme: 'light',
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

                init();

                if (opts.syntax) {
                    editor.setOption("mode", CodeMirror.findModeByName(opts.syntax).mode || 'text');
                }

                this.trigger('ready');

            }.bind(this));

        });

        function init() {

            CodeMirror.modeInfo = [
                {name: "APL", mime: "text/apl", mode: "apl", ext: ["dyalog", "apl"]},
                {name: "PGP", mimes: ["application/pgp", "application/pgp-keys", "application/pgp-signature"], mode: "asciiarmor", ext: ["pgp"]},
                {name: "Asterisk", mime: "text/x-asterisk", mode: "asterisk", file: /^extensions\.conf$/i},
                {name: "C", mime: "text/x-csrc", mode: "clike", ext: ["c", "h"]},
                {name: "C++", mime: "text/x-c++src", mode: "clike", ext: ["cpp", "c++", "cc", "cxx", "hpp", "h++", "hh", "hxx"], alias: ["cpp"]},
                {name: "Cobol", mime: "text/x-cobol", mode: "cobol", ext: ["cob", "cpy"]},
                {name: "C#", mime: "text/x-csharp", mode: "clike", ext: ["cs"], alias: ["csharp"]},
                {name: "Clojure", mime: "text/x-clojure", mode: "clojure", ext: ["clj"]},
                {name: "CMake", mime: "text/x-cmake", mode: "cmake", ext: ["cmake", "cmake.in"], file: /^CMakeLists.txt$/},
                {name: "CoffeeScript", mime: "text/x-coffeescript", mode: "coffeescript", ext: ["coffee"], alias: ["coffee", "coffee-script"]},
                {name: "Common Lisp", mime: "text/x-common-lisp", mode: "commonlisp", ext: ["cl", "lisp", "el"], alias: ["lisp"]},
                {name: "Cypher", mime: "application/x-cypher-query", mode: "cypher", ext: ["cyp", "cypher"]},
                {name: "Cython", mime: "text/x-cython", mode: "python", ext: ["pyx", "pxd", "pxi"]},
                {name: "CSS", mime: "text/css", mode: "css", ext: ["css"]},
                {name: "CQL", mime: "text/x-cassandra", mode: "sql", ext: ["cql"]},
                {name: "D", mime: "text/x-d", mode: "d", ext: ["d"]},
                {name: "Dart", mimes: ["application/dart", "text/x-dart"], mode: "dart", ext: ["dart"]},
                {name: "diff", mime: "text/x-diff", mode: "diff", ext: ["diff", "patch"]},
                {name: "Django", mime: "text/x-django", mode: "django"},
                {name: "Dockerfile", mime: "text/x-dockerfile", mode: "dockerfile", file: /^Dockerfile$/},
                {name: "DTD", mime: "application/xml-dtd", mode: "dtd", ext: ["dtd"]},
                {name: "Dylan", mime: "text/x-dylan", mode: "dylan", ext: ["dylan", "dyl", "intr"]},
                {name: "EBNF", mime: "text/x-ebnf", mode: "ebnf"},
                {name: "ECL", mime: "text/x-ecl", mode: "ecl", ext: ["ecl"]},
                {name: "Eiffel", mime: "text/x-eiffel", mode: "eiffel", ext: ["e"]},
                {name: "Embedded Javascript", mime: "application/x-ejs", mode: "htmlembedded", ext: ["ejs"]},
                {name: "Embedded Ruby", mime: "application/x-erb", mode: "htmlembedded", ext: ["erb"]},
                {name: "Erlang", mime: "text/x-erlang", mode: "erlang", ext: ["erl"]},
                {name: "Forth", mime: "text/x-forth", mode: "forth", ext: ["forth", "fth", "4th"]},
                {name: "Fortran", mime: "text/x-fortran", mode: "fortran", ext: ["f", "for", "f77", "f90"]},
                {name: "F#", mime: "text/x-fsharp", mode: "mllike", ext: ["fs"], alias: ["fsharp"]},
                {name: "Gas", mime: "text/x-gas", mode: "gas", ext: ["s"]},
                {name: "Gherkin", mime: "text/x-feature", mode: "gherkin", ext: ["feature"]},
                {name: "GitHub Flavored Markdown", mime: "text/x-gfm", mode: "gfm", file: /^(readme|contributing|history).md$/i},
                {name: "Go", mime: "text/x-go", mode: "go", ext: ["go"]},
                {name: "Groovy", mime: "text/x-groovy", mode: "groovy", ext: ["groovy"]},
                {name: "HAML", mime: "text/x-haml", mode: "haml", ext: ["haml"]},
                {name: "Haskell", mime: "text/x-haskell", mode: "haskell", ext: ["hs"]},
                {name: "Haxe", mime: "text/x-haxe", mode: "haxe", ext: ["hx"]},
                {name: "HXML", mime: "text/x-hxml", mode: "haxe", ext: ["hxml"]},
                {name: "ASP.NET", mime: "application/x-aspx", mode: "htmlembedded", ext: ["aspx"], alias: ["asp", "aspx"]},
                {name: "HTML", mime: "text/html", mode: "htmlmixed", ext: ["html", "htm"], alias: ["xhtml"]},
                {name: "HTTP", mime: "message/http", mode: "http"},
                {name: "IDL", mime: "text/x-idl", mode: "idl", ext: ["pro"]},
                {name: "Jade", mime: "text/x-jade", mode: "jade", ext: ["jade"]},
                {name: "Java", mime: "text/x-java", mode: "clike", ext: ["java"]},
                {name: "Java Server Pages", mime: "application/x-jsp", mode: "htmlembedded", ext: ["jsp"], alias: ["jsp"]},
                {name: "JavaScript", mimes: ["text/javascript", "text/ecmascript", "application/javascript", "application/x-javascript", "application/ecmascript"],
                 mode: "javascript", ext: ["js"], alias: ["ecmascript", "js", "node"]},
                {name: "JSON", mimes: ["application/json", "application/x-json"], mode: "javascript", ext: ["json", "map"], alias: ["json5"]},
                {name: "JSON-LD", mime: "application/ld+json", mode: "javascript", ext: ["jsonld"], alias: ["jsonld"]},
                {name: "Jinja2", mime: "null", mode: "jinja2"},
                {name: "Julia", mime: "text/x-julia", mode: "julia", ext: ["jl"]},
                {name: "Kotlin", mime: "text/x-kotlin", mode: "kotlin", ext: ["kt"]},
                {name: "LESS", mime: "text/x-less", mode: "css", ext: ["less"]},
                {name: "LiveScript", mime: "text/x-livescript", mode: "livescript", ext: ["ls"], alias: ["ls"]},
                {name: "Lua", mime: "text/x-lua", mode: "lua", ext: ["lua"]},
                {name: "Markdown", mime: "text/x-markdown", mode: "markdown", ext: ["markdown", "md", "mkd"]},
                {name: "mIRC", mime: "text/mirc", mode: "mirc"},
                {name: "MariaDB SQL", mime: "text/x-mariadb", mode: "sql"},
                {name: "Modelica", mime: "text/x-modelica", mode: "modelica", ext: ["mo"]},
                {name: "MUMPS", mime: "text/x-mumps", mode: "mumps"},
                {name: "MS SQL", mime: "text/x-mssql", mode: "sql"},
                {name: "MySQL", mime: "text/x-mysql", mode: "sql"},
                {name: "Nginx", mime: "text/x-nginx-conf", mode: "nginx", file: /nginx.*\.conf$/i},
                {name: "NTriples", mime: "text/n-triples", mode: "ntriples", ext: ["nt"]},
                {name: "Objective C", mime: "text/x-objectivec", mode: "clike", ext: ["m", "mm"]},
                {name: "OCaml", mime: "text/x-ocaml", mode: "mllike", ext: ["ml", "mli", "mll", "mly"]},
                {name: "Octave", mime: "text/x-octave", mode: "octave", ext: ["m"]},
                {name: "Pascal", mime: "text/x-pascal", mode: "pascal", ext: ["p", "pas"]},
                {name: "PEG.js", mime: "null", mode: "pegjs", ext: ["jsonld"]},
                {name: "Perl", mime: "text/x-perl", mode: "perl", ext: ["pl", "pm"]},
                {name: "PHP", mime: "application/x-httpd-php", mode: "php", ext: ["php", "php3", "php4", "php5", "phtml"]},
                {name: "Pig", mime: "text/x-pig", mode: "pig", ext: ["pig"]},
                {name: "Plain Text", mime: "text/plain", mode: "null", ext: ["txt", "text", "conf", "def", "list", "log"]},
                {name: "PLSQL", mime: "text/x-plsql", mode: "sql", ext: ["pls"]},
                {name: "Properties files", mime: "text/x-properties", mode: "properties", ext: ["properties", "ini", "in"], alias: ["ini", "properties"]},
                {name: "Python", mime: "text/x-python", mode: "python", ext: ["py", "pyw"]},
                {name: "Puppet", mime: "text/x-puppet", mode: "puppet", ext: ["pp"]},
                {name: "Q", mime: "text/x-q", mode: "q", ext: ["q"]},
                {name: "R", mime: "text/x-rsrc", mode: "r", ext: ["r"], alias: ["rscript"]},
                {name: "reStructuredText", mime: "text/x-rst", mode: "rst", ext: ["rst"], alias: ["rst"]},
                {name: "RPM Changes", mime: "text/x-rpm-changes", mode: "rpm"},
                {name: "RPM Spec", mime: "text/x-rpm-spec", mode: "rpm", ext: ["spec"]},
                {name: "Ruby", mime: "text/x-ruby", mode: "ruby", ext: ["rb"], alias: ["jruby", "macruby", "rake", "rb", "rbx"]},
                {name: "Rust", mime: "text/x-rustsrc", mode: "rust", ext: ["rs"]},
                {name: "Sass", mime: "text/x-sass", mode: "sass", ext: ["sass"]},
                {name: "Scala", mime: "text/x-scala", mode: "clike", ext: ["scala"]},
                {name: "Scheme", mime: "text/x-scheme", mode: "scheme", ext: ["scm", "ss"]},
                {name: "SCSS", mime: "text/x-scss", mode: "css", ext: ["scss"]},
                {name: "Shell", mime: "text/x-sh", mode: "shell", ext: ["sh", "ksh", "bash"], alias: ["bash", "sh", "zsh"]},
                {name: "Sieve", mime: "application/sieve", mode: "sieve", ext: ["siv", "sieve"]},
                {name: "Slim", mimes: ["text/x-slim", "application/x-slim"], mode: "slim", ext: ["slim"]},
                {name: "Smalltalk", mime: "text/x-stsrc", mode: "smalltalk", ext: ["st"]},
                {name: "Smarty", mime: "text/x-smarty", mode: "smarty", ext: ["tpl"]},
                {name: "Solr", mime: "text/x-solr", mode: "solr"},
                {name: "Soy", mime: "text/x-soy", mode: "soy", ext: ["soy"], alias: ["closure template"]},
                {name: "SPARQL", mime: "application/sparql-query", mode: "sparql", ext: ["rq", "sparql"], alias: ["sparul"]},
                {name: "Spreadsheet", mime: "text/x-spreadsheet", mode: "spreadsheet", alias: ["excel", "formula"]},
                {name: "SQL", mime: "text/x-sql", mode: "sql", ext: ["sql"]},
                {name: "MariaDB", mime: "text/x-mariadb", mode: "sql"},
                {name: "sTeX", mime: "text/x-stex", mode: "stex"},
                {name: "LaTeX", mime: "text/x-latex", mode: "stex", ext: ["text", "ltx"], alias: ["tex"]},
                {name: "SystemVerilog", mime: "text/x-systemverilog", mode: "verilog", ext: ["v"]},
                {name: "Tcl", mime: "text/x-tcl", mode: "tcl", ext: ["tcl"]},
                {name: "Textile", mime: "text/x-textile", mode: "textile", ext: ["textile"]},
                {name: "TiddlyWiki ", mime: "text/x-tiddlywiki", mode: "tiddlywiki"},
                {name: "Tiki wiki", mime: "text/tiki", mode: "tiki"},
                {name: "TOML", mime: "text/x-toml", mode: "toml", ext: ["toml"]},
                {name: "Tornado", mime: "text/x-tornado", mode: "tornado"},
                {name: "troff", mime: "troff", mode: "troff", ext: ["1", "2", "3", "4", "5", "6", "7", "8", "9"]},
                {name: "Turtle", mime: "text/turtle", mode: "turtle", ext: ["ttl"]},
                {name: "TypeScript", mime: "application/typescript", mode: "javascript", ext: ["ts"], alias: ["ts"]},
                {name: "VB.NET", mime: "text/x-vb", mode: "vb", ext: ["vb"]},
                {name: "VBScript", mime: "text/vbscript", mode: "vbscript", ext: ["vbs"]},
                {name: "Velocity", mime: "text/velocity", mode: "velocity", ext: ["vtl"]},
                {name: "Verilog", mime: "text/x-verilog", mode: "verilog", ext: ["v"]},
                {name: "XML", mimes: ["application/xml", "text/xml"], mode: "xml", ext: ["xml", "xsl", "xsd"], alias: ["rss", "wsdl", "xsd"]},
                {name: "XQuery", mime: "application/xquery", mode: "xquery", ext: ["xy", "xquery"]},
                {name: "YAML", mime: "text/x-yaml", mode: "yaml", ext: ["yaml", "yml"], alias: ["yml"]},
                {name: "Z80", mime: "text/x-z80", mode: "z80", ext: ["z80"]}
              ];

              for (var i = 0; i < CodeMirror.modeInfo.length; i++) {
                var info = CodeMirror.modeInfo[i];
                if (info.mimes) info.mime = info.mimes[0];
              }

              CodeMirror.findModeByMIME = function(mime) {
                mime = mime.toLowerCase();
                for (var i = 0; i < CodeMirror.modeInfo.length; i++) {
                  var info = CodeMirror.modeInfo[i];
                  if (info.mime == mime) return info;
                  if (info.mimes) for (var j = 0; j < info.mimes.length; j++)
                    if (info.mimes[j] == mime) return info;
                }
              };

              CodeMirror.findModeByExtension = function(ext) {
                for (var i = 0; i < CodeMirror.modeInfo.length; i++) {
                  var info = CodeMirror.modeInfo[i];
                  if (info.ext) for (var j = 0; j < info.ext.length; j++)
                    if (info.ext[j] == ext) return info;
                }
              };

              CodeMirror.findModeByFileName = function(filename) {
                for (var i = 0; i < CodeMirror.modeInfo.length; i++) {
                  var info = CodeMirror.modeInfo[i];
                  if (info.file && info.file.test(filename)) return info;
                }
                var dot = filename.lastIndexOf(".");
                var ext = dot > -1 && filename.substring(dot + 1, filename.length);
                if (ext) return CodeMirror.findModeByExtension(ext);
              };

              CodeMirror.findModeByName = function(name) {
                name = name.toLowerCase();
                for (var i = 0; i < CodeMirror.modeInfo.length; i++) {
                  var info = CodeMirror.modeInfo[i];
                  if (info.name.toLowerCase() == name) return info;
                  if (info.alias) for (var j = 0; j < info.alias.length; j++)
                    if (info.alias[j].toLowerCase() == name) return info;
                }
              };
        }

});

riot.tag2('cp-field', '', '', '', function(opts) {

        this._field = null;

        this.on('update', function() {

            if (opts.bind && opts.bind != this._field) {

                App.$(this.root).children('div').remove();

                var container = App.$('<div name="fieldcontainer" type="{ field.type }"></div>').appendTo(this.root);

                var field   = typeof(opts.field) == 'string' ? {type:opts.field} : ( opts.field || {}),
                    type    = field.type || 'text',
                    options = field.options || {},
                    fc      = 'field-'+type;

                if (!riot.tags[fc]) {
                    fc = 'field-text';
                }

                if (opts.cls) {
                    container[0].setAttribute('cls', opts.cls);
                }

                if (opts.bind) {
                    container[0].setAttribute('bind', opts.bind);
                }

                riot.mount(container[0], fc, options);

                this._field = opts.bind;
            }
        })

});

riot.tag2('cp-fieldsmanager', '<div name="fieldscontainer" class="uk-sortable uk-grid uk-grid-small uk-grid-gutter uk-form"> <div class="uk-grid-margin uk-width-{field.width}" data-idx="{idx}" each="{field,idx in fields}"> <div class="uk-panel uk-panel-box uk-panel-card"> <div class="uk-grid uk-grid-small"> <div class="uk-flex-item-1 uk-flex"> <input class="uk-flex-item-1 uk-form-small uk-form-blank" type="text" bind="fields[{idx}].name" placeholder="name" required> </div> <div class="uk-width-1-4"> <div class="uk-form-select" data-uk-form-select> <div class="uk-form-icon"> <i class="uk-icon-arrows-h"></i> <input class="uk-width-1-1 uk-form-small uk-form-blank" value="{field.width}"> </div> <select bind="fields[{idx}].width"> <option value="1-1">1-1</option> <option value="1-2">1-2</option> <option value="1-3">1-3</option> <option value="2-3">2-3</option> <option value="1-4">1-4</option> <option value="3-4">3-4</option> </select> </div> </div> <div class="uk-text-right"> <ul class="uk-subnav"> <li> <a class="uk-text-{field.lst ? \'success\':\'muted\'}" onclick="{parent.togglelist}" title="{App.i18n.get(\'Show field on list view\')}"> <i class="uk-icon-list"></i> </a> </li> <li> <a onclick="UIkit.modal(\'#field-{idx}\').show()"><i class="uk-icon-cog uk-text-primary"></i></a> </li> <li> <a class="uk-text-danger" onclick="{parent.removefield}"> <i class="uk-icon-trash"></i> </a> </li> </ul> </div> </div> </div> <div class="uk-modal" id="field-{idx}"> <div class="uk-modal-dialog"> <div class="uk-form-row uk-text-bold"> {field.name || \'Field\'} </div> <div class="uk-form-row"> <div class="uk-form-select uk-width-1-1"> <div class="uk-form-icon uk-width-1-1"> <i class="uk-icon-tag"></i> <input class="uk-width-1-1 uk-form-small uk-form-blank" value="{field.type.toUpperCase()}"> </div> <select class="uk-width-1-1" bind="fields[{idx}].type"> <option each="{type,typeidx in parent.fieldtypes}" value="{type.value}">{type.name}</option> </select> </div> </div> <div class="uk-form-row"> <input class="uk-width-1-1" type="text" bind="fields[{idx}].label" placeholder="{App.i18n.get(\'label\')}"> </div> <div class="uk-form-row"> <input class="uk-width-1-1" type="text" bind="fields[{idx}].info" placeholder="{App.i18n.get(\'info\')}"> </div> <div class="uk-form-row"> <div class="uk-text-small uk-text-bold">{App.i18n.get(\'Options\')} <span class="uk-text-muted">JSON</span></div> <field-object cls="uk-width-1-1" bind="fields[{idx}].options" rows="6" allowtabs="2"></field-object> </div> <div class="uk-form-row"> <input type="checkbox" bind="fields[{idx}].required"> {App.i18n.get(\'Required\')} </div> <div class="uk-form-row"> <input type="checkbox" bind="fields[{idx}].localize"> {App.i18n.get(\'Localize\')} </div> <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{App.i18n.get(\'Close\')}</button></div> </div> </div> </div> <div class="uk-margin-top" show="{fields.length}"> <a class="uk-button uk-button-link" onclick="{addfield}"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add field\')}</a> </div> </div> <div class="uk-width-medium-1-3 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle" if="{!fields.length && !reorder}"> <div class="uk-animation-fade"> <p class="uk-text-xlarge"> <i class="uk-icon-list-alt"></i> </p> <hr> {App.i18n.get(\'No fields added yet\')}. <a onclick="{addfield}">{App.i18n.get(\'Add field\')}.</a> </div> </div>', '', '', function(opts) {

        this.mixin(RiotBindMixin);

        var $this = this;

        this.fields  = [];
        this.reorder = false;

        this.fieldtypes = [];

        for (var tag in riot.tags) {

            if(tag.indexOf('field-')==0) {

                f = tag.replace('field-', '');

                this.fieldtypes.push({name:f.toUpperCase(), value:f});
            }
        }

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this.fields !== value) {
                this.fields = value;
                this.update();
            }

        }.bind(this);

        this.on('bindingupdated', function(){
            $this.$setValue(this.fields);
        });

        this.one('mount', function(){

            UIkit.sortable(this.fieldscontainer, {

                dragCustomClass:'uk-form'

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                if (App.$(e.target).is(':input')) {
                    return;
                }

                ele = App.$(ele);

                var fields = $this.fields,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                fields.splice(cidx, 0, fields.splice(oidx, 1)[0]);

                App.$($this.fieldscontainer).css('height', App.$($this.fieldscontainer).height());

                $this.fields = [];
                $this.reorder = true;
                $this.update();

                setTimeout(function() {
                    $this.reorder = false;
                    $this.fields = fields;
                    $this.update();
                    $this.$setValue(fields);

                    setTimeout(function(){
                        $this.fieldscontainer.style.height = '';
                    }, 30)
                }, 0);

            });

        });

        this.addfield = function() {

            this.fields.push({
                'name'    : '',
                'label'   : '',
                'type'    : 'text',
                'default' : '',
                'info'    : '',
                'localize': false,
                'options' : {},
                'width'   : '1-1',
                'lst'     : true
            });

            $this.$setValue(this.fields);
        }.bind(this)

        this.removefield = function(e) {
            this.fields.splice(e.item.idx, 1);
            $this.$setValue(this.fields);
        }.bind(this)

        this.togglelist = function(e) {
            e.item.field.lst = !e.item.field.lst;
        }.bind(this)

}, '{ }');

riot.tag2('cp-finder', '<div show="{data}"> <div class="uk-clearfix" data-uk-margin> <div class="uk-float-left"> <span class="uk-button uk-button-primary uk-margin-small-right uk-form-file"> <input class="js-upload-select" type="file" multiple="true" title=""> <i class="uk-icon-upload"></i> </span> <span class="uk-button-group uk-margin-small-right"> <span class="uk-position-relative uk-button" data-uk-dropdown="\\{mode:\'click\'\\}"> <i class="uk-icon-magic"></i> <div class="uk-dropdown uk-text-left"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header">Create</li> <li><a onclick="{createfolder}"><i class="uk-icon-folder-o uk-icon-justify"></i> Folder</a></li> <li><a onclick="{createfile}"><i class="uk-icon-file-o uk-icon-justify"></i> File</a></li> </ul> </div> </span> <button class="uk-button" onclick="{refresh}"> <i class="uk-icon-refresh"></i> </button> </span> <span class="uk-button" if="{selected.count}" data-uk-dropdown="\\{mode:\'click\'\\}"> <strong>Batch:</strong> {selected.count} selected &nbsp;<i class="uk-icon-caret-down"></i> <div class="uk-dropdown uk-text-left"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header">Batch action</li> <li><a onclick="{removeSelected}">Delete</a></li> </ul> </div> </span> </div> <div class="uk-float-right"> <div class="uk-form uk-form-icon uk-width-1-1"> <i class="uk-icon-filter"></i> <input name="filter" type="text" onkeyup="{updatefilter}"> </div> </div> </div> <div class="uk-grid uk-grid-divider uk-margin-large-top" data-uk-grid-margin> <div class="uk-width-medium-1-4"> <div class="uk-panel"> <ul class="uk-nav uk-nav-side"> <li class="uk-nav-header">Display</li> <li class="{!typefilter ? \'uk-active\':\'\'}"><a data-type="" onclick="{settypefilter}"><i class="uk-icon-circle-o uk-icon-justify"></i> All</a></li> <li class="{typefilter==\'image\' ? \'uk-active\':\'\'}"><a data-type="image" onclick="{settypefilter}"><i class="uk-icon-image uk-icon-justify"></i> Images</a></li> <li class="{typefilter==\'video\' ? \'uk-active\':\'\'}"><a data-type="video" onclick="{settypefilter}"><i class="uk-icon-video-camera uk-icon-justify"></i> Video</a></li> <li class="{typefilter==\'audio\' ? \'uk-active\':\'\'}"><a data-type="audio" onclick="{settypefilter}"><i class="uk-icon-volume-up uk-icon-justify"></i> Audio</a></li> <li class="{typefilter==\'document\' ? \'uk-active\':\'\'}"><a data-type="document" onclick="{settypefilter}"><i class="uk-icon-paper-plane uk-icon-justify"></i> Documents</a></li> <li class="{typefilter==\'archive\' ? \'uk-active\':\'\'}"><a data-type="archive" onclick="{settypefilter}"><i class="uk-icon-archive uk-icon-justify"></i> Archives</a></li> </ul> </div> </div> <div class="uk-width-medium-3-4"> <div class="uk-panel"> <ul class="uk-breadcrumb"> <li onclick="{changedir}"><a title="Change dir to root"><i class="uk-icon-home"></i></a></li> <li each="{folder, idx in breadcrumbs}"><a onclick="{parent.changedir}" title="Change dir to @@ folder.name @@">{folder.name}</a></li> </ul> </div> <div name="uploadprogress" class="uk-margin uk-hidden"> <div class="uk-progress"> <div name="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div> </div> </div> <div class="uk-alert uk-text-center uk-margin" if="{(this.typefilter || this.filter.value) && (data.folders.length || data.files.length)}"> Filter is active </div> <div class="uk-alert uk-text-center uk-margin" if="{(!data.folders.length && !data.files.length)}"> This is an empty folder </div> <div class="{modal ? \'uk-overflow-container\':\'\'}"> <div class="uk-margin-top" if="{data.folders.length}"> <strong class="uk-text-small uk-text-muted" if="{!(this.filter.value)}"><i class="uk-icon-folder-o uk-margin-small-right"></i> {data.folders.length} Folders</strong> <ul class="uk-grid uk-grid-small uk-grid-match uk-grid-width-1-2 uk-grid-width-medium-1-4"> <li class="uk-grid-margin" each="{folder, idx in data.folders}" onclick="{parent.select}" if="{parent.infilter(folder)}"> <div class="uk-panel uk-panel-box finder-folder {folder.selected ? \'uk-selected\':\'\'}"> <div class="uk-flex"> <div> <span class="uk-margin-small-right" data-uk-dropdown="\\{mode:\'click\'\\}"> <i class="uk-icon-folder-o uk-text-muted js-no-item-select"></i> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header uk-text-truncate">{folder.name}</li> <li><a onclick="{parent.rename}">Rename</a></li> <li><a onclick="{parent.remove}">Delete</a></li> </ul> </div> </span> </div> <div class="uk-flex-item-1 uk-text-truncate"> <a class="uk-link-muted" onclick="{parent.changedir}"><strong>{folder.name}</strong></a> </div> </div> </div> </li> </ul> </div> <div class="uk-margin-top" if="{data.files.length}"> <strong class="uk-text-small uk-text-muted" if="{!(this.typefilter || this.filter.value)}"><i class="uk-icon-file-o uk-margin-small-right"></i> {data.files.length} Files</strong> <ul class="uk-grid uk-grid-small uk-grid-match uk-grid-width-1-2 uk-grid-width-medium-1-4"> <li class="uk-grid-margin" each="{file, idx in data.files}" onclick="{parent.select}" if="{parent.infilter(file)}"> <div class="uk-panel uk-panel-box finder-file {file.selected ? \'uk-selected\':\'\'}"> <div class="uk-panel-teaser uk-cover-background uk-position-relative"> <div class="uk-position-cover uk-position-z-index"> <div class="uk-panel uk-panel-box uk-panel-box-trans"> <span class="uk-margin-small-right" data-uk-dropdown="\\{mode:\'click\'\\}"> <a><i class="uk-icon-{parent.getIconCls(file)} js-no-item-select"></i> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header uk-text-truncate">{file.name}</li> <li> <a class="uk-link-muted js-no-item-select" onclick="{parent.open}">Open</a></li> <li><a onclick="{parent.rename}">Rename</a></li> <li if="{file.ext == \'zip\'}"><a onclick="{parent.unzip}">Unzip</a></li> <li class="uk-nav-divider"></li> <li><a onclick="{parent.remove}">Delete</a></li> </ul> </div> </span> </div> </div> <canvas class="uk-responsive-width uk-display-block" width="400" height="300" if="{parent.getIconCls(file) != \'image\'}"></canvas> <cp-thumbnail riot-src="{file.url}" width="400" height="300" if="{parent.getIconCls(file) == \'image\'}"></cp-thumbnail> </div> <div class="uk-flex-item-1 uk-text-truncate"> <a class="uk-link-muted js-no-item-select" onclick="{parent.open}">{file.name}</a> <div class="uk-margin-small-top uk-text-small uk-text-muted"> {file.size} </div> </div> </div> </li> </ul> </div> </div> </div> </div> <div name="editor" class="uk-offcanvas"> <div class="uk-offcanvas-bar uk-width-3-4"> <picoedit></picoedit> </div> </div> </div>', '.uk-offcanvas[name=editor] .CodeMirror { height: auto; } .uk-offcanvas[name=editor] .picoedit-toolbar { padding-left: 15px; padding-right: 15px; } .uk-modal .uk-panel-box.finder-folder, .uk-modal .uk-panel-box.finder-file { border: 1px rgba(0,0,0,0.1) solid; }', '', function(opts) {

        var $this = this,
            typefilters = {
                'image'    : /\.(jpg|jpeg|png|gif|svg)$/i,
                'video'    : /\.(mp4|mov|ogv|webv|flv|avi)$/i,
                'audio'    : /\.(mp3|weba|ogg|wav|flac)$/i,
                'archive'  : /\.(zip|rar|7zip|gz)$/i,
                'document' : /\.(htm|html|pdf|md)$/i,
                'text'     : /\.(txt|htm|html|php|css|less|js|json|md|markdown|yaml|xml|htaccess)$/i
            };

        opts.root = opts.root || '/';

        this.currentpath = opts.path || App.session.get('app.finder.path', opts.root);

        this.data;
        this.breadcrumbs = [];
        this.selected    = {count:0, paths:{}};
        this.bookmarks   = {"folders":[], "files":[]};

        this.typefilter = opts.typefilter || '';
        this.namefilter = '';

        this.mode       = 'table';
        this.dirlist    = false;
        this.selected   = {};

        App.$(this.editor).on('click', function(e){

            if (e.target.classList.contains('uk-offcanvas-bar')) {
                $this.tags.picoedit.codemirror.editor.focus();
            }
        });

        this.on('mount', function(){

            this.modal = App.$(this.root).closest('.uk-modal').length ? UIkit.modal(App.$(this.root).closest('.uk-modal')):false;

            this.loadPath()

            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                var uploadSettings = {

                        action: App.route('/media/api'),
                        params: {"cmd":"upload"},
                        type: 'json',
                        before: function(options) {
                            options.params.path = $this.currentpath;
                        },
                        loadstart: function() {
                            $this.uploadprogress.classList.remove('uk-hidden');
                        },
                        progress: function(percent) {

                            percent = Math.ceil(percent) + '%';

                            $this.progressbar.innerHTML   = '<span>'+percent+'</span>';
                            $this.progressbar.style.width = percent;
                        },
                        allcomplete: function(response) {

                            $this.uploadprogress.classList.add('uk-hidden');

                            if (response && response.failed && response.failed.length) {
                                App.ui.notify("File(s) failed to uploaded.", "danger");
                            }

                            if (response && response.uploaded && response.uploaded.length) {
                                App.ui.notify("File(s) uploaded.", "success");
                                $this.loadPath();
                            }

                            if (!response) {
                                App.ui.notify("Something went wrong.", "danger");
                            }

                        }
                },

                uploadselect = UIkit.uploadSelect(App.$('.js-upload-select', $this.root)[0], uploadSettings),
                uploaddrop   = UIkit.uploadDrop($this.root, uploadSettings);

                UIkit.init(this.root);
            });
        });

        this.changedir = function(e, path) {

            if (e && e.item) {
                e.stopPropagation();
                path = e.item.folder.path;
            } else {
                path = opts.root;
            }

            this.loadPath(path);
        }.bind(this)

        this.open = function(evt) {

            if (opts.previewfiles === false) {
                this.select(evt, true);
                return;
            }

            var file = evt.item.file,
                name = file.name.toLowerCase();

            if (name.match(typefilters.image)) {

                UIkit.lightbox.create([
                    {'source': file.url, 'type':'image'}
                ]).show();

            } else if(name.match(typefilters.video)) {

                UIkit.lightbox.create([
                    {'source': file.url, 'type':'video'}
                ]).show();

            } else if(name.match(typefilters.text)) {

                UIkit.offcanvas.show(this.editor);
                this.tags.picoedit.open(file.path);

            } else {
                App.ui.notify("Filetype not supported");
            }
        }.bind(this)

        this.refresh = function() {
            this.loadPath().then(function(){
                App.ui.notify('Folder reloaded');
            });
        }.bind(this)

        this.select = function(e, force) {

            if (e && e.item && force || !e.target.classList.contains('js-no-item-select') && !App.$(e.target).parents('.js-no-item-select').length) {

                try {
                    window.getSelection().empty()
                } catch(err) {
                    try {
                        window.getSelection().removeAllRanges()
                    } catch(err){}
                }

                var item = e.item.folder || e.item.file, idx = e.item.idx;

                if (e.shiftKey) {

                    var prev, items = this.data[item.is_file ? 'files' : 'folders'];

                    for (var i=idx;i>=0;i--) {
                        if (items[i].selected) break;

                        items[i].selected = true;
                        this.selected.paths[items[i].path] = items[i];
                    }

                    this.selected.count = Object.keys(this.selected.paths).length;

                    return;
                }

                if (!(e.metaKey || e.ctrlKey)) {

                    Object.keys(this.selected.paths).forEach(function(path) {
                        if (path != item.path) {
                            $this.selected.paths[path].selected = false;
                            delete $this.selected.paths[path];
                        }
                    });
                }

                item.selected = !item.selected;

                if (!item.selected && this.selected.paths[item.path]) {
                    delete this.selected.paths[item.path];
                }

                if (item.selected && !this.selected.paths[item.path]) {
                    this.selected.paths[item.path] = item;
                }

                this.selected.count = Object.keys(this.selected.paths).length;

                App.$(this.root).trigger('selectionchange', [this.selected]);
            }
        }.bind(this)

        this.rename = function(e, item) {

            e.stopPropagation();

            item = e.item.folder || e.item.file;

            App.ui.prompt("Please enter a name:", item.name, function(name){

                if (name!=item.name && name.trim()) {

                    requestapi({"cmd":"rename", "path": item.path, "name":name});
                    item.path = item.path.replace(item.name, name);
                    item.name = name;

                    $this.update();
                }

            });
        }.bind(this)

        this.unzip = function(e, item) {

            e.stopPropagation();

            item = e.item.file;

            requestapi({"cmd": "unzip", "path": $this.currentpath, "zip": item.path}, function(resp){

                if (resp) {

                    if (resp.success) {
                        App.ui.notify("Archive extracted!", "success");
                    } else {
                        App.ui.notify("Extracting archive failed!", "error");
                    }
                }

                $this.loadPath();

            });
        }.bind(this)

        this.remove = function(e, item, index) {

            e.stopPropagation();

            item = e.item.folder || e.item.file;

            App.ui.confirm("Are you sure?", function() {

                requestapi({"cmd":"removefiles", "paths": item.path}, function(){

                    index = $this.data[item.is_file ? "files":"folders"].indexOf(item);

                    $this.data[item.is_file ? "files":"folders"].splice(index, 1);

                    App.ui.notify("Item(s) deleted", "success");

                    $this.update();
                });
            });
        }.bind(this)

        this.removeSelected = function() {

            var paths = Object.keys(this.selected.paths);

            if (paths.length) {

                App.ui.confirm("Are you sure?", function() {

                    requestapi({"cmd":"removefiles", "paths": paths}, function(){
                        $this.loadPath();
                        App.ui.notify("File(s) deleted", "success");
                    });
                });
            }
        }.bind(this)

        this.createfolder = function() {

            App.ui.prompt("Please enter a folder name:", "", function(name){

                if (name.trim()) {
                    requestapi({"cmd":"createfolder", "path": $this.currentpath, "name":name}, function(){
                        $this.loadPath();
                    });
                }
            });
        }.bind(this)

        this.createfile = function() {

            App.ui.prompt("Please enter a file name:", "", function(name){

                if (name.trim()) {
                    requestapi({"cmd":"createfile", "path": $this.currentpath, "name":name}, function(){
                        $this.loadPath();
                    });
                }
            });
        }.bind(this)

        this.loadPath = function(path, defer) {

            path  = path || $this.currentpath;
            defer = App.deferred();

            requestapi({"cmd":"ls", "path": path}, function(data){

                $this.currentpath = path;
                $this.breadcrumbs = [];
                $this.selected    = {};
                $this.selectAll   = false;

                if ($this.currentpath && $this.currentpath != opts.root && $this.currentpath != '.'){
                    var parts   = $this.currentpath.split('/'),
                        tmppath = [],
                        crumbs  = [];

                    for(var i=0;i<parts.length;i++){
                        if(!parts[i]) continue;
                        tmppath.push(parts[i]);
                        crumbs.push({'name':parts[i],'path':tmppath.join("/")});
                    }

                    $this.breadcrumbs = crumbs;
                }

                App.session.set('app.finder.path', path);

                defer.resolve(data);

                $this.data = data;

                $this.resetselected();
                $this.update();

                if ($this.modal) {
                    setTimeout(function(){
                        $this.modal.resize();
                    }, 100);
                }
            });

            return defer;
        }.bind(this)

        this.settypefilter = function(evt) {
            this.typefilter = evt.target.dataset.type;
            this.resetselected();
        }.bind(this)

        this.updatefilter = function(evt) {
            this.resetselected();
        }.bind(this)

        this.infilter = function(item) {

            var name = item.name.toLowerCase();

            if (this.typefilter && item.is_file && typefilters[this.typefilter]) {

                if (!name.match(typefilters[this.typefilter])) {
                    return false;
                }
            }

            return (!this.filter.value || (name && name.indexOf(this.filter.value.toLowerCase()) !== -1));
        }.bind(this)

        this.resetselected = function() {

            if (this.selected.paths) {
                Object.keys(this.selected.paths).forEach(function(path) {
                    $this.selected.paths[path].selected = false;
                });
            }

            this.selected  = {count:0, paths:{}};

            if (opts.onChangeSelect) {
                opts.onChangeSelect(this.selected);
            }
        }.bind(this)

        this.getIconCls = function(file) {

            var name = file.name.toLowerCase();

            if (name.match(typefilters.image)) {

                return 'image';

            } else if(name.match(typefilters.video)) {

                return 'video';

            } else if(name.match(typefilters.text)) {

                return 'pencil';

            } else if(name.match(typefilters.archive)) {

                return 'archive';

            } else {
                return 'file-o';
            }
        }.bind(this)

        function requestapi(data, fn, type) {

            data = Object.assign({"cmd":""}, data);

            App.request('/media/api', data).then(fn);
        }

}, '{ }');

riot.tag2('cp-gravatar', '<canvas name="image" class="uk-responsive-width uk-border-circle" width="{size}" height="{size}"></canvas>', '', '', function(opts) {

        this.url = '';

        this.on('update', function() {

            this.size  = opts.size || 100;
            this.email = opts.email || '';

            var img = new Image(), url;

            url = '//www.gravatar.com/avatar/'+md5(this.email)+'?d=404&s='+this.size;

            img.onload = function() {
                this.image.getContext("2d").drawImage(img,0,0);
            }.bind(this);

            img.onerror = function() {
                img.src = App.Utils.letterAvatar(opts.alt || '', this.size);
                this.image.getContext("2d").drawImage(img,0,0);
            }.bind(this);

            img.src = url;

        });

}, '{ }');

riot.tag2('cp-search', '<div name="autocomplete" class="uk-autocomplete uk-form uk-form-icon app-search"> <i class="uk-icon-search"></i> <input class="uk-width-1-1 uk-form-blank" type="text" placeholder="{App.i18n.get(\'Search...\')}"> </div>', 'cp-search .uk-dropdown { min-width: 25vw; }', '', function(opts) {

        this.on('mount', function(){

            var txtSearch = App.$("input[type='text']", this.autocomplete);

            UIkit.autocomplete(this.autocomplete, {
                source: App.route('/cockpit/search'),
                template: '<ul class="uk-nav uk-nav-autocomplete uk-autocomplete-results">{{~items}}<li data-value="" data-url="{{$item.url}}"><a><i class="uk-icon-{{ ($item.icon || "cube") }}"></i> {{$item.title}}</a></li>{{/items}}</ul>'
            });

            UIkit.$doc.on("keydown", function(e) {

                if (e.ctrlKey || e.altKey || e.metaKey) return;

                if (e.target.tagName && e.target.tagName.toLowerCase()=='body' && (e.keyCode>=65 && e.keyCode<=90)) {
                    txtSearch.focus();
                }
            });

            Mousetrap.bindGlobal(['alt+f'], function(e) {

                if (e.preventDefault) {
                    e.preventDefault();
                } else {
                    e.returnValue = false;
                }
                txtSearch.focus();
                return false;
            });

        });

        App.$(this.root).on("selectitem.uk.autocomplete", function(e, data) {

            if (data.url) {
                location.href = data.url;
            }
        });

}, '{ }');

riot.tag2('cp-thumbnail', '<span class="uk-position-relative"> <i name="spinner" class="uk-icon-spinner uk-icon-spin uk-position-absolute"></i> <canvas name="canvas" class="uk-responsive-width" width="{opts.width || \'\'}" height="{opts.height || \'\'}"></canvas> </span>', '', '', function(opts) {

        var $this = this;

        this.on('mount', function(){

            opts.src = opts.src || opts['riot-src'] || opts['riotSrc'];

            App.request('/cockpit/utils/thumb_url', {src:opts.src,w:opts.width,h:opts.height}, 'text').then(function(url){

                var img = new Image();

                img.onload = function() {

                    $this.canvas.getContext("2d").drawImage(img,0,0);
                    $this.spinner.classList.add('uk-hidden');
                };

                img.src = url;
            });
        });

}, '{ }');

riot.tag2('field-boolean', '<button type="button" name="button" class="uk-button uk-button-{value ? \'success\':\'default\'}" onclick="{toggle}"> <span show="{value}">{opts.label || \'On\'}</span> <span show="{!value}">{opts.label || \'Off\'}</span> </button>', '', '', function(opts) {

        if (opts.cls) {
            App.$(this.button).addClass(opts.cls.replace(/uk\-form\-/g, 'uk-button-'));
        }

        this.value = opts.default || false;

        this.$updateValue = function(value) {

            if (this.value != value) {

                this.value = value;
                this.update();
            }

        }.bind(this);

        this.toggle = function() {
            this.$setValue(!this.value);
        }.bind(this)

}, '{ }');

riot.tag2('field-code', '<codemirror name="codemirror" syntx="{opts.syntax || \'text\'}"></codemirror>', 'field-code .CodeMirror { height: auto; }', '', function(opts) {

        var $this = this, editor;

        this.value  = null;
        this._field = null;

        this.ready = new Promise(function(resolve){

            $this.tags.codemirror.on('ready', function(){
                editor = $this.codemirror.editor;
                $this.isReady = true;
                resolve();
            });
        });

        this.$updateValue = function(value, field) {

            if (this.value != value) {

                this.value = value;

                if (editor && field != this._field) {
                    editor.setValue($this.value || '', true);
                }
            }

            this._field = field;

        }.bind(this);

        this.on('mount', function(){

            this.ready.then(function() {
                editor.setValue($this.value || '');
            });

            this.codemirror.on('input', function() {
                $this.$setValue($this.codemirror.editor.getValue(), true);
            });
        });

}, '{ }');

riot.tag2('field-color', '<input name="input" class="uk-width-1-1" bind="{opts.bind}" type="{opts.type || \'text\'}" placeholder="{opts.placeholder}">', '', '', function(opts) {

        var $this = this;

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.$updateValue = function(value, field) {

            if (value && this.input.value !== value) {
                this.input.value = value;
                this.update();
            }

        }.bind(this);

        this.on('mount', function(){

            App.assets.require(['/assets/lib/tinycolorpicker/colors.js'], function(){

                App.assets.require(['/assets/lib/tinycolorpicker/jqColorPicker.js'], function(){

                    $this.input.id = $this.input.id || 'colorpicker'+Math.ceil(Math.random()*10000);

                    $this.input.onfocus = function(){
                        $('#'+$this.input.id).colorPicker({
                            renderCallback: function($elm, toggled) {
                                if (toggled === false) {
                                    $this.$setValue($elm.val());
                                }
                            }
                        });
                    };

                    $this.input.onkeyup = function(){
                        $this.$setValue($this.input.value);
                    };
                });
            });
        });

}, '{ }');

riot.tag2('field-date', '<input name="input" class="uk-width-1-1" bind="{opts.bind}" type="text" placeholder="{opts.placeholder}">', '', '', function(opts) {

        var $this = this;

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/datepicker.js', '/assets/lib/uikit/js/components/form-select.js'], function() {

                UIkit.datepicker(this.input, opts).element.on('change', function() {
                    $this.input.$setValue($this.input.value);
                });

            }.bind(this));
        });

}, '{ }');

riot.tag2('field-file', '<div class="uk-flex"> <input class="uk-flex-item-1 uk-margin-small-right" type="text" name="input" bind="{opts.bind}" placeholder="{opts.placeholder || App.i18n.get(\'No file selected...\')}" disabled> <button type="button" class="uk-button" name="picker"><i class="uk-icon-paperclip"></i></button> </div>', '', '', function(opts) {

        var $this = this, $input = App.$(this.input);

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
            App.$(this.picker).addClass(opts.cls);
        }

        App.$(this.picker).on('click', function() {

            App.media.select(function(selected) {
                $this.input.$setValue(selected[0]);
            });
        });

}, '{ }');

riot.tag2('field-gallery', '<div name="panel" class="uk-panel"> <div name="imagescontainer" class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-grid-gutter uk-grid-width-medium-1-4" show="{images && images.length}"> <div class="uk-grid-margin" data-idx="{idx}" each="{img,idx in images}"> <div class="uk-panel uk-panel-box uk-panel-card"> <figure class="uk-display-block uk-overlay uk-overlay-hover"> <div class="uk-flex uk-flex-middle uk-flex-center" style="min-height:120px;"> <div class="uk-width-1-1"> <img class="uk-display-inline-block uk-responsive-width" riot-src="{(SITE_URL+\'/\'+img.path)}"> </div> </div> <figcaption class="uk-overlay-panel uk-overlay-background"> <ul class="uk-subnav"> <li><a onclick="{parent.title}" title="{App.i18n.get(\'Set title\')}" data-uk-tooltip><i class="uk-icon-tag"></i></a></li> <li><a onclick="{parent.remove}" title="{App.i18n.get(\'Remove image\')}" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li> </ul> <p class="uk-text-small uk-text-truncate">{img.title}</p> </figcaption> </figure> </div> </div> </div> <div class="{images && images.length ? \'uk-margin-top\':\'\'}"> <div class="uk-alert" if="{images && !images.length}">{App.i18n.get(\'Gallery is empty\')}.</div> <a class="uk-button uk-button-link" onclick="{selectimages}"> <i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add images\')} </a> </div> </div>', '', '', function(opts) {

        var $this = this;

        this.images = [];
        this._field = null;

        this.on('mount', function() {

            UIkit.sortable(this.imagescontainer, {

                animation: false

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                ele = App.$(ele);

                var images = $this.images,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                images.splice(cidx, 0, images.splice(oidx, 1)[0]);

                App.$($this.panel).css('height', App.$($this.panel).height());

                $this.images = [];
                $this.update();

                setTimeout(function() {
                    $this.images = images;
                    $this.$setValue(images);
                    $this.update();

                    setTimeout(function(){
                        $this.panel.style.height = '';
                    }, 30)
                }, 10);

            });

        });

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this.images !== value) {
                this.images = value;
                this.update();
            }

        }.bind(this);

        this.selectimages = function() {

            App.media.select(function(selected) {

                var images = [];

                selected.forEach(function(path){
                    images.push({title:'', path:path});
                });

                $this.$setValue($this.images.concat(images));

            }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });
        }.bind(this)

        this.remove = function(e) {
            this.images.splice(e.item.idx, 1);
            this.$setValue(this.images);
        }.bind(this)

        this.title = function(e) {

            App.ui.prompt('Title', this.images[e.item.idx].title, function(value) {
                $this.images[e.item.idx].title = value;
                $this.$setValue($this.images);
                $this.update();
            });
        }.bind(this)

}, '{ }');

riot.tag2('field-html', '<textarea name="input" class="uk-visibility-hidden"></textarea>', '', '', function(opts) {

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

            App.assets.require([

                '/assets/lib/marked.js',
                '/assets/lib/codemirror/lib/codemirror.js',
                '/assets/lib/uikit/js/components/htmleditor.js'

            ], function() {

                $this.input.value = $this.value;

                editor = UIkit.htmleditor(this.input, opts);
                editor.on('input', function() {
                    $this.$setValue(editor.editor.getValue());
                });

                editor.off('action.image').on('action.image', function() {

                    App.media.select(function(selected) {

                        if (editor.getCursorMode() == 'markdown') {
                            editor['replaceSelection']('![title]('+SITE_URL+'/'+selected[0]+')');
                        } else {
                            editor['replaceSelection']('<img src="'+SITE_URL+'/'+selected[0]+'">');
                        }

                    }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });

                });

                App.$(document).trigger('init-html-editor', [editor]);

            }.bind(this));
        });

});

riot.tag2('field-image', '<figure class="uk-display-block uk-overlay uk-overlay-hover"> <div class="uk-placeholder uk-flex uk-flex-middle uk-flex-center uk-text-muted"> <div class="uk-width-1-1" show="{image.path}" riot-style="min-height:160px;background-size:contain;background-repeat:no-repeat;background-position:50% 50%;{image.path ? \'background-image: url(\'+encodeURI(SITE_URL+\'/\'+image.path)+\')\':\'\'}"></div> <div class="uk-width-1-1" show="{!image.path}"><i class="uk-icon-image"></i></div> </div> <figcaption class="uk-overlay-panel uk-overlay-background"> <ul class="uk-subnav"> <li><a onclick="{selectimage}" title="{App.i18n.get(\'Select image\')}" data-uk-tooltip><i class="uk-icon-image"></i></a></li> <li><a onclick="{title}" title="{App.i18n.get(\'Set title\')}" data-uk-tooltip><i class="uk-icon-tag"></i></a></li> <li><a onclick="{remove}" title="{App.i18n.get(\'Reset\')}" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li> </ul> <p class="uk-text-small uk-text-truncate">{image.title}</p> </figcaption> </figure>', '', '', function(opts) {

        var $this = this;

        this.image = {path:'', title:''};

        this.$updateValue = function(value, field) {

            if (value && this.image !== value) {
                this.image = value;
                this.update();
            }

        }.bind(this);

        this.selectimage = function() {

            App.media.select(function(selected) {

                $this.image.path = selected[0];
                $this.$setValue($this.image);
                $this.update();

            }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });
        }.bind(this)

        this.remove = function() {
            this.$setValue({path:'', title:''});
        }.bind(this)

        this.title = function() {

            App.ui.prompt('Title', this.image.title, function(value) {
                $this.image.title = value;
                $this.$setValue($this.image);
                $this.update();
            });
        }.bind(this)

}, '{ }');

riot.tag2('field-location', '<div> <div class="uk-form uk-form-icon uk-margin-small-bottom uk-width-1-1"> <i class="uk-icon-search"></i><input name="autocomplete" class="uk-width-1-1" value="{latlng.address}"> </div> <div name="map" style="min-height:300px;"> Loading map... </div> <div class="uk-text-small uk-margin-small-top"> LAT: <span class="uk-text-muted">{latlng.lat}</span> LNG: <span class="uk-text-muted">{latlng.lng}</span> </div> </div>', '', '', function(opts) {

        var map, marker;

        var locale = document.documentElement.lang.toUpperCase();

        var loadApi = (function(){

            var p, fn = function(){

                if (!p) {

                    p = new Promise(function(resolve){

                        var script = document.createElement('script');

                        script.async = true;

                        script.onload = function() {

                            google.load("maps", "3", {other_params: 'libraries=places&language=' + locale, callback: function(){
                              if (google && google.maps.places) resolve();
                            }});
                        };

                        script.onerror = function() {
                            alert('Failed loading google maps api.');
                        };

                        script.src = 'https://www.google.com/jsapi';

                        document.getElementsByTagName('head')[0].appendChild(script);
                    });
                }

                return p;
            };

            return fn;
        })();

        var $this = this;

        this.latlng = {lat:53.55909862554551, lng:9.998652343749995};

        this.$updateValue = function(value) {

            if (!value) {
                value = {lat:53.55909862554551, lng:9.998652343749995};
            }

            if (this.latlng != value) {
                this.latlng = value;

                if (marker) {
                    marker.setPosition(new google.maps.LatLng(this.latlng.lat, this.latlng.lng));
                }

                this.update();
            }

        }.bind(this);

        this.on('mount', function(){

            loadApi().then(function(){

                var point = new google.maps.LatLng($this.latlng.lat, $this.latlng.lng), input, autocomplete;

                map = new google.maps.Map($this.map, {
                    zoom   : 6,
                    center : point
                });

                marker = new google.maps.Marker({
                    position  : point,
                    map       : map,
                    draggable : true
                });

                google.maps.event.addListener(marker, 'dragend', function() {
                    var point = marker.getPosition();

                    input.value = '';
                    $this.$setValue({lat: point.lat(), lng: point.lng(), address: input.value});
                });

                App.$(window).on('resize', function(){
                    google.maps.event.trigger(map,'resize');
                    map.setCenter(marker.getPosition());
                });

                input = $this.autocomplete;

                autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.bindTo('bounds', map);

                google.maps.event.addListener(autocomplete, 'place_changed', function(e) {

                    var place = autocomplete.getPlace();

                    if (!place.geometry) {
                      return;
                    }

                    if (place.geometry.viewport) {
                      map.fitBounds(place.geometry.viewport);
                    } else {
                      map.setCenter(place.geometry.location);
                    }

                    marker.setPosition(place.geometry.location);

                    var point = marker.getPosition();
                    $this.$setValue({lat: point.lat(), lng: point.lng(), address: input.value});
                });

                google.maps.event.addDomListener(input, 'keydown', function(e) {
                    if (e.keyCode == 13) {
                        e.preventDefault();
                    }
                });

            });

        });

}, '{ }');

riot.tag2('field-markdown', '<field-html name="input" markdown="true" bind="{opts.bind}"></field-html>', '', '', function(opts) {
}, '{ }');

riot.tag2('field-multipleselect', '<div class="uk-grid-gutter"> <div name="container" class="uk-grid uk-grid-match uk-grid-width-medium-1-6"> <div class="uk-grid-margin" each="{option in options}"> <a data-value="{option}" class="{parent.selected.indexOf(option)!==-1 ? \'uk-link-muted\':\'uk-text-muted\'}" onclick="{toggle}" title="{option}"> <i class="uk-icon-{parent.selected.indexOf(option)!==-1 ? \'circle\':\'circle-o\'}"></i> {option} </a> </div> </div> </div>', '', '', function(opts) {

        var $this = this;

        this.selected   = [];
        this.options = opts.options || []

        if (typeof(this.options) === 'string') {

            var options = [];

            this.options.split(',').forEach(function(option) {
                options.push(option.trim());
            });

            this.options = options;
        }

        this.on('mount', function() {

        });

        this.$initBind = function() {
            this.root.$value = this.selected;
        };

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.selected) != JSON.stringify(value)) {
                this.selected = value;
                this.update();
            }

        }.bind(this);

        this.toggle = function(e) {

            var option = e.item.option,
                index  = this.selected.indexOf(option);

            if (index == -1) {
                this.selected.push(option);
            } else {
                this.selected.splice(index, 1);
            }

            this.$setValue(this.selected);
        }.bind(this)

}, '{ }');

riot.tag2('field-object', '<textarea name="input" class="uk-width-1-1" onchange="{change}" placeholder="{opts.placeholder}">{}</textarea>', '', '', function(opts) {

        var $this = this, editor;

        this.value = {};

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        this.input.setAttribute('rows', opts.rows || 5);
        this.input.setAttribute('style', 'font-family: monospace;tab-size:2;');

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require([

                '/assets/lib/behave.js'

            ], function() {

                editor = new Behave({
                    textarea: $this.input,
                    replaceTab: true,
                    softTabs: true,
                    tabSize: 2,
                    autoOpen: true,
                    overwrite: true,
                    autoStrip: true,
                    autoIndent: true,
                    fence: false
                });

            }.bind(this));

        });

        this.$updateValue = function(value) {

            if (typeof(value) != 'object') {
                value = {};
            }

            if (JSON.stringify(this.value) != JSON.stringify(value)) {
                this.value = value || {};
                this.input.value = JSON.stringify(this.value, null, 2);
            }

        }.bind(this);

        this.change = function() {
            this.$setValue(App.Utils.str2json(this.input.value) || this.value);
        }.bind(this)

}, '{ }');

riot.tag2('field-password', '<div class="uk-form-password uk-width-1-1"> <input name="input" class="uk-width-1-1" bind="{opts.bind}" type="password"> <a href="" class="uk-form-password-toggle" data-uk-form-password>Show</a> </div>', '', '', function(opts) {

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/form-password.js'], function() {

                UIkit.init(this.root);

            }.bind(this));
        });

}, '{ }');

riot.tag2('field-repeater', '<div class="uk-alert" show="{!items.length}"> {App.i18n.get(\'No items\')}. </div> <div show="{mode==\'edit\' && items.length}"> <div class="uk-margin uk-panel-box uk-panel-card" each="{item,idx in items}" data-idx="{idx}"> <cp-field class="uk-width-1-1" field="{parent.field}" options="{opts.options}" bind="items[{idx}].value"></cp-field> <div class="uk-panel-box-footer uk-bg-light"> <a onclick="{parent.remove}"><i class="uk-icon-trash-o"></i></a> </div> </div> </div> <div name="itemscontainer" class="uk-sortable" show="{mode==\'reorder\' && items.length}"> <div class="uk-margin uk-panel-box uk-panel-card" each="{item,idx in items}" data-idx="{idx}"> <i class="uk-icon-bars"></i> Item {(idx+1)} </div> </div> <div class="uk-margin"> <a class="uk-button" onclick="{add}" show="{mode==\'edit\'}"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add item\')}</a> <a class="uk-button" onclick="{updateorder}" show="{mode==\'reorder\'}"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Update order\')}</a> <a class="uk-button" onclick="{switchreorder}" show="{items.length > 1}"> <span show="{mode==\'edit\'}"><i class="uk-icon-arrows"></i> {App.i18n.get(\'Reorder\')}</span> <span show="{mode==\'reorder\'}">{App.i18n.get(\'Cancel\')}</span> </a> </div>', '', '', function(opts) {

        var $this = this;

        riot.util.bind(this);

        this.items = [];
        this.field = opts.field || {type:'text'};
        this.mode  = 'edit';

        this.$initBind = function() {
            this.root.$value = this.items;
        };

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.items) != JSON.stringify(value)) {
                this.items = value;
                this.update();
            }

        }.bind(this);

        this.on('bindingupdated', function() {
            this.$setValue(this.items);
        });

        this.on('mount', function() {

            UIkit.sortable(this.itemscontainer, {
                animation: false
            });

        });

        this.add = function() {
            this.items.push({type:this.field.type, value:''});
        }.bind(this)

        this.remove = function(e) {
            this.items.splice(e.item.idx, 1);
        }.bind(this)

        this.switchreorder = function() {
            $this.mode = $this.mode == 'edit' ? 'reorder':'edit';
        }.bind(this)

        this.updateorder = function() {

            var items = [];

            App.$($this.root).css('height', App.$($this.root).height());

            App.$(this.itemscontainer).children().each(function(){
                items.push($this.items[Number(this.getAttribute('data-idx'))]);
            });

            $this.items = [];
            $this.update();

            setTimeout(function() {
                $this.mode = 'edit'
                $this.items = items;
                $this.$setValue(items);
                $this.update();

                setTimeout(function(){
                    $this.root.style.height = '';
                }, 30)
            }, 10);
        }.bind(this)

}, '{ }');

riot.tag2('field-select', '<select name="input" class="uk-width-1-1" bind="{opts.bind}"> <option value=""></option> <option each="{option,idx in options}" value="{option}">{option}</option> </select>', '', '', function(opts) {

        this.options = opts.options || []

        if (typeof(this.options) === 'string') {

            var options = [];

            this.options.split(',').forEach(function(option) {
                options.push(option.trim());
            });

            this.options = options;
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

}, '{ }');

riot.tag2('field-set', '<div> <div class="uk-alert" if="{!fields.length}"> {App.i18n.get(\'Fields definition is missing\')} </div> <div class="uk-margin" each="{field,idx in fields}"> <label><span class="uk-badge">{field.label || field.name || \'\'}</span></label> <cp-field class="uk-width-1-1" field="{field}" bind="value.{field.name}"></cp-field> </div> </div>', '', '', function(opts) {

        var $this = this;

        this._field = null;

        riot.util.bind(this);

        this.set    = opts.multiple ? []:{};
        this.fields = opts.fields || [];
        this.value  = {};

        this.bind = opts.bind || '';

        this.$initBind = function() {
            this.root.$value = this.value;
        };

        this.$updateValue = function(value, field) {

            if (!App.Utils.isObject(value) || Array.isArray(value)) {

                value = {};

                this.fields.forEach(function(field){
                    value[field.name] = null;
                });
            }

            if (JSON.stringify(this.value) != JSON.stringify(value)) {
                this.value = value;
                this.update();
            }

            this._field = field;

        }.bind(this);

        this.on('bindingupdated', function() {
            this.$setValue(this.value);
        });

}, '{ }');

riot.tag2('field-tags', '<div> <div name="autocomplete" class="uk-autocomplete uk-form-icon uk-form"> <i class="uk-icon-tag"></i> <input name="input" class="uk-width-1-1 uk-form-blank" type="text" placeholder="{App.i18n.get(opts.placeholder || \'Add Tag...\')}"> </div> <div class="uk-margin uk-panel uk-panel-box" show="{tags && tags.length}"> <div class="uk-margin-small-right uk-margin-small-top" each="{tag,idx in tags}"> <a onclick="{parent.remove}"><i class="uk-icon-close"></i></a> {tag} </div> </div> </div>', '', '', function(opts) {

        var $this = this;

        this.tags = [];

        this.on('mount', function(){

            if (opts.autocomplete) {

                UIkit.autocomplete(this.autocomplete, {source: opts.autocomplete});
            }

            App.$(this.root).on({

                'selectitem.uk.autocomplete keydown': function(e, data) {

                    var value = e.type=='keydown' ? $this.input.value : data.value;

                    if (e.type=='keydown' && e.keyCode != 13) {
                        return;
                    }

                    if (value.trim()) {

                        $this.input.value = value;

                        e.stopImmediatePropagation();
                        e.stopPropagation();
                        e.preventDefault();
                        $this.tags.push($this.input.value);
                        $this.input.value = "";
                        $this.$setValue(_.uniq($this.tags));
                        $this.update();

                        return false;
                    }
                }
            });
        });

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this.tags !== value) {
                this.tags = value;
                this.update();
            }

        }.bind(this);

        this.remove = function(e) {
            this.tags.splice(e.item.idx, 1);
            this.$setValue(this.tags);
        }.bind(this)

}, '{ }');

riot.tag2('field-text', '<input name="input" class="uk-width-1-1" bind="{opts.bind}" type="{opts.type || \'text\'}" placeholder="{opts.placeholder}">', '', '', function(opts) {

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

}, '{ }');

riot.tag2('field-textarea', '<textarea name="input" class="uk-width-1-1" bind="{opts.bind}" placeholder="{opts.placeholder}"></textarea>', '', '', function(opts) {

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.rows) {
            this.input.setAttribute('rows', opts.rows);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        if (opts.allowtabs) {

            this.input.onkeydown = function(e) {
                if (e.keyCode === 9) {
                    var val = this.value, start = this.selectionStart, end = this.selectionEnd;
                    this.value = val.substring(0, start) + '\t' + val.substring(end);
                    this.selectionStart = this.selectionEnd = start + 1;
                    return false;
                }
            };

            this.input.style.tabSize = opts.allowtabs;
        }

}, '{ }');

riot.tag2('field-time', '<input name="input" class="uk-width-1-1" bind="{opts.bind}" type="text">', '', '', function(opts) {

        var $this = this;

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/timepicker.js'], function() {

                UIkit.timepicker(this.input, opts).element.on('change', function() {
                    $this.input.$setValue($this.input.value);
                });

            }.bind(this));
        });

}, '{ }');

riot.tag2('field-wysiwyg', '<textarea name="input" class="uk-width-1-1" rows="5" style="height:350px;visibility:hidden;"></textarea>', '', '', function(opts) {

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

                    setTimeout(function(){

                        if (!App.$('#'+this.input.id).length) return;

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
                        }, opts.editor || {}, {

                          selector: '#'+this.input.id,
                          setup: function (ed) {

                              $this.input.value = $this.value;

                              ed.on('ExecCommand', function (e) {
                                 ed.save();
                                 $this.$setValue($this.input.value, true);
                              });

                              ed.on('KeyUp', function (e) {
                                 ed.save();
                                 $this.$setValue($this.input.value, true);
                              });

                              editor = ed;

                              App.$(document).trigger('init-wysiwyg-editor', [editor]);
                          }

                        }));

                    }.bind(this), 10);

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

});

riot.tag2('picoedit', '<div class="picoedit"> <div class="picoedit-toolbar uk-flex" if="{path}"> <div class="uk-flex-item-1 uk-text-truncate"> <strong class="uk-text-small"><i class="uk-icon-pencil uk-margin-small-right"></i> {path}</strong> </div> <div> <button type="button" class="uk-button uk-button-primary" onclick="{save}"><i class="uk-icon-save"></i></button> </div> </div> <codemirror name="codemirror"></codemirror> </div>', '.picoedit-toolbar { padding-top: 15px; padding-bottom: 15px; }', '', function(opts) {

        var root  = this.root,
            $this = this,
            editor;

        this.isReady = false;

        this.ready = new Promise(function(resolve){

            $this.tags.codemirror.on('ready', function(){
                editor = $this.codemirror.editor;

                editor.addKeyMap({
                    'Ctrl-S': function(){ $this.save(); },
                    'Cmd-S': function(){ $this.save(); }
                });

                $this.isReady = true;

                resolve();
            });
        });

        root.picoedit = this;

        this.path = null;

        this.on('mount', function() {

            if (opts.path) {

                this.open(opts.path);
            }
        });

        this.open = function(path) {

            this.ready.then(function(){

                this.path = path;

                editor.setValue('');
                editor.clearHistory();

                requestapi({"cmd":"readfile", "path":path}, function(content){

                    editor.setOption("mode", CodeMirror.findModeByFileName(path).mode || 'text');
                    editor.setValue(content);
                    editor.focus();
                    editor.refresh();

                    this.update();

                }.bind(this), "text");

            }.bind(this));
        }.bind(this)

        this.save = function() {

            if (!this.path) return;

            requestapi({"cmd":"writefile", "path": this.path, "content":editor.getValue()}, function(status){

                App.ui.notify("File saved", "success");

            }, "text");
        }.bind(this)

        function requestapi(data, fn, type) {

            data = Object.assign({"cmd":""}, data);

            return App.request('/media/api', data, type).then(fn);
        }

}, '{ }');
