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
                    var mode = CodeMirror.findModeByName(opts.syntax) || {mode:'text'};
                    editor.setOption("mode", mode.mode);
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

riot.tag2('cp-assets', '<div class="uk-form" ref="list" show="{mode==\'list\'}"> <div class="uk-grid uk-grid-width-1-2"> <div> <div class="uk-grid uk-grid-small uk-flex-middle"> <div> <div class="uk-form-select"> <span class="uk-button uk-button-large {getRefValue(\'filtertype\') && \'uk-button-primary\'} uk-text-capitalize"><i class="uk-icon-eye uk-margin-small-right"></i> {getRefValue(\'filtertype\') || App.i18n.get(\'All\')}</span> <select ref="filtertype" onchange="{updateFilter}"> <option value="">All</option> <option value="image">Image</option> <option value="video">Video</option> <option value="audio">Audio</option> <option value="document">Document</option> <option value="archive">Archive</option> <option value="code">Code</option> </select> </div> </div> <div class="uk-flex-item-1"> <div class="uk-form-icon uk-display-block uk-width-1-1"> <i class="uk-icon-search"></i> <input class="uk-width-1-1 uk-form-large" type="text" ref="filtertitle" onchange="{updateFilter}"> </div> </div> </div> </div> <div class="uk-text-right"> <button class="uk-button uk-button-large uk-button-danger" type="button" onclick="{removeSelected}" show="{selected.length}"> {App.i18n.get(\'Delete\')} <span class="uk-badge uk-badge-contrast uk-margin-small-left">{selected.length}</span> </button> <span class="uk-button-group uk-button-large"> <button class="uk-button uk-button-large {listmode==\'list\' && \'uk-button-primary\'}" type="button" onclick="{toggleListMode}"><i class="uk-icon-list"></i></button> <button class="uk-button uk-button-large {listmode==\'grid\' && \'uk-button-primary\'}" type="button" onclick="{toggleListMode}"><i class="uk-icon-th"></i></button> </span> <span class="uk-button uk-button-large uk-button-primary uk-margin-small-right uk-form-file"> <input class="js-upload-select" type="file" multiple="true"> <i class="uk-icon-upload"></i> </span> </div> </div> <div ref="uploadprogress" class="uk-margin uk-hidden"> <div class="uk-progress"> <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div> </div> </div> <div class="uk-margin-large-top uk-panel-space uk-text-center" show="{!loading && !assets.length}"> <span class="uk-text-muted uk-h2">{App.i18n.get(\'No Assets found\')}</span> </div> <div class="uk-text-center uk-text-muted uk-h2 uk-margin-large-top" show="{loading}"> <i class="uk-icon-spinner uk-icon-spin"></i> </div> <div class="uk-margin-large-top {modal ? \'uk-overflow-container\':\'\'}" if="{!loading && assets.length}"> <div class="uk-grid uk-grid-small uk-grid-width-medium-1-5" if="{listmode==\'grid\'}"> <div class="uk-grid-margin" each="{asset,idx in assets}" each="{asset,idx in assets}" onclick="{select}"> <div class="uk-panel uk-panel-box uk-panel-card {selected.length && selected.indexOf(asset) != -1 ? \'uk-selected\':\'\'}"> <div class="uk-overlay uk-display-block uk-position-relative"> <canvas class="uk-responsive-width" width="200" height="150"></canvas> <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle"> <div class="uk-width-1-1 uk-text-center"> <span if="{asset.mime.match(/^image\\//) == null}"><i class="uk-h1 uk-text-muted uk-icon-{parent.getIconCls(asset.path)}"></i></span> <a href="{ASSETS_URL+asset.path}" if="{asset.mime.match(/^image\\//)}" data-uk-lightbox="type:\'image\'" title="{asset.width && [asset.width, asset.height].join(\'x\')}"> <cp-thumbnail riot-src="{ASSETS_URL+asset.path}" width="100" height="75"></cp-thumbnail> </a> </div> </div> </div> <div class="uk-margin-small-top uk-text-truncate"><a onclick="{parent.edit}">{asset.title}</a></div> <div class="uk-text-small uk-text-muted"> <strong>{asset.mime}</strong> {App.Utils.formatSize(asset.size)} </div> </div> </div> </div> <table class="uk-table uk-panel-card" if="{listmode==\'list\'}"> <thead> <tr> <td width="30"></td> <th>{App.i18n.get(\'Title\')}</th> <th width="20%">{App.i18n.get(\'Type\')}</th> <th width="10%">{App.i18n.get(\'Size\')}</th> <th width="10%">{App.i18n.get(\'Updated\')}</th> <th width="30"></th> </tr> </thead> <tbody> <tr class="{selected.length && selected.indexOf(asset) != -1 ? \'uk-selected\':\'\'}" each="{asset,idx in assets}" onclick="{select}"> <td class="uk-text-center"> <span if="{asset.mime.match(/^image\\//) == null}"><i class="uk-text-muted uk-icon-{parent.getIconCls(asset.path)}"></i></span> <a href="{ASSETS_URL+asset.path}" if="{asset.mime.match(/^image\\//)}" data-uk-lightbox="type:\'image\'" title="{asset.width && [asset.width, asset.height].join(\'x\')}"> <cp-thumbnail riot-src="{ASSETS_URL+asset.path}" width="20" height="20"></cp-thumbnail> </a> </td> <td><a onclick="{parent.edit}">{asset.title}</a></td> <td class="uk-text-small">{asset.mime}</td> <td class="uk-text-small">{App.Utils.formatSize(asset.size)}</td> <td class="uk-text-small">{App.Utils.dateformat( new Date( 1000 * asset.modified ))}</td> <td> <span class="uk-float-right" data-uk-dropdown="mode:\'click\'"> <a class="uk-icon-bars"></a> <div class="uk-dropdown uk-dropdown-flip"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header">{App.i18n.get(\'Actions\')}</li> <li><a class="uk-dropdown-close" onclick="{parent.edit}">{App.i18n.get(\'Edit\')}</a></li> <li><a class="uk-dropdown-close" onclick="{parent.remove}">{App.i18n.get(\'Delete\')}</a></li> </ul> </div> </span> </td> </tr> </tbody> </table> <div class="uk-margin-top" if="{count > limit}"> <span class="uk-button-group uk-margin-small-right"> <a class="uk-button uk-button-large" onclick="{loadPage}" data-page="{(page - 1)}" if="{page > 1}">{App.i18n.get(\'Previous\')}</a> <a class="uk-button uk-button-large" onclick="{loadPage}" data-page="{(page + 1)}" if="{(page*limit) < count}">{App.i18n.get(\'Next\')}</a> </span> <span class="uk-text-small uk-text-muted">{page}/{Math.ceil(count/limit)}</span> </div> </div> </div> <div class="uk-form" show="{asset && mode==\'edit\'}"> <form onsubmit="{updateAsset}"> <div class="uk-grid"> <div class="uk-width-2-3"> <div class="uk-form-row"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Title\')}</label> <input class="uk-width-1-1" type="text" ref="assettitle" required> </div> <div class="uk-form-row"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Description\')}</label> <textarea class="uk-width-1-1" ref="assetdescription"></textarea> </div> <div class="uk-margin uk-panel uk-panel-box uk-panel-card uk-panel-space uk-text-center"> <span class="uk-h1" if="{asset && asset.mime.match(/^image\\//) == null}"><i class="uk-icon-{getIconCls(asset.path)}"></i></span> <cp-thumbnail riot-src="{asset && ASSETS_URL+asset.path}" width="400" height="250" if="{asset && asset.mime.match(/^image\\//)}"></cp-thumbnail> </div> </div> <div class="uk-width-1-3" if="{asset}"> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Id\')}</label> <div class="uk-margin-small-top uk-text-muted">{asset._id}</div> </div> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Type\')}</label> <div class="uk-margin-small-top uk-text-muted">{asset.mime}</div> </div> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Size\')}</label> <div class="uk-margin-small-top uk-text-muted">{App.Utils.formatSize(asset.size)}</div> </div> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Created\')}</label> <div class="uk-margin-small-top uk-text-muted">{App.Utils.dateformat( new Date( 1000 * asset.modified ))}</div> </div> <div class="uk-margin"> <label class="uk-text-small uk-text-bold">{App.i18n.get(\'Url\')}</label> <div class="uk-margin-small-top uk-text-truncate uk-text-muted"><a href="{ASSETS_URL+asset.path}" target="_blank">{ASSETS_URL+asset.path}</a></div> </div> </div> </div> <div class="uk-margin-large-top"> <button type="submit" class="uk-button uk-button-large uk-button-primary uk-margin-right">{App.i18n.get(\'Save\')}</button> <a onclick="{cancelEdit}">{App.i18n.get(\'Cancel\')}</a> </div> </form> </div>', '', '', function(opts) {

        var $this = this, typefilters = {
            'image'    : /\.(jpg|jpeg|png|gif|svg)$/i,
            'video'    : /\.(mp4|mov|ogv|webv|wmv|flv|avi)$/i,
            'audio'    : /\.(mp3|weba|ogg|wav|flac)$/i,
            'archive'  : /\.(zip|rar|7zip|gz)$/i,
            'document' : /\.(txt|pdf|md)$/i,
            'code'     : /\.(htm|html|php|css|less|js|json|yaml|xml|htaccess)$/i
        };

        this.mode     = 'list';
        this.listmode = App.session.get('app.assets.listmode', 'list');
        this.loading  = false;
        this.assets   = [];
        this.selected = [];

        this.count    = 0;
        this.page     = 1;
        this.limit    = opts.limit || 30;

        this.on('mount', function() {

            this.modal = App.$(this.root).closest('.uk-modal').length ? UIkit.modal(App.$(this.root).closest('.uk-modal')):false;

            this.listAssets(1);

            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                var uploadSettings = {

                        action: App.route('/assetsmanager/upload'),
                        type: 'json',
                        before: function(options) {

                        },
                        loadstart: function() {
                            $this.refs.uploadprogress.classList.remove('uk-hidden');
                        },
                        progress: function(percent) {

                            percent = Math.ceil(percent) + '%';

                            $this.refs.progressbar.innerHTML   = '<span>'+percent+'</span>';
                            $this.refs.progressbar.style.width = percent;
                        },
                        allcomplete: function(response) {

                            $this.refs.uploadprogress.classList.add('uk-hidden');

                            if (response && response.failed && response.failed.length) {
                                App.ui.notify("File(s) failed to uploaded.", "danger");
                            }

                            if (response && Array.isArray(response.assets) && response.assets.length) {

                                if (!Array.isArray($this.assets)) {
                                    $this.assets = [];
                                }

                                App.ui.notify("File(s) uploaded.", "success");

                                response.assets.forEach(function(asset){
                                    $this.assets.unshift(asset);
                                });

                                $this.listAssets(1);
                            }

                            if (!response) {
                                App.ui.notify("Something went wrong.", "danger");
                            }

                        }
                },

                uploadselect = UIkit.uploadSelect(App.$('.js-upload-select', $this.root)[0], uploadSettings),
                uploaddrop   = UIkit.uploadDrop($this.refs.list, uploadSettings);

                UIkit.init(this.root);
            });

        });

        this.toggleListMode = function() {
            this.listmode = this.listmode=='list' ? 'grid':'list';
            App.session.set('app.assets.listmode', this.listmode);
        }.bind(this)

        this.listAssets = function(page) {

            this.page    = page || 1;
            this.loading = true;

            var options = {
                filter : this.filter || null,
                limit  : this.limit,
                skip   : (this.page-1) * this.limit,
                sort   : {created:-1}
            };

            App.request('/assetsmanager/listAssets', options).then(function(response){

                $this.assets   = Array.isArray(response.assets) ? response.assets:[];
                $this.count    = response.count || 0;
                $this.loading  = false;
                $this.selected = [];
                $this.update();
            });

        }.bind(this)

        this.updateFilter = function() {

            this.filter = null;

            if (this.refs.filtertitle.value || this.refs.filtertype.value) {
                this.filter = {};
            }

            if (this.refs.filtertitle.value) {
                this.filter.title = {'$regex':this.refs.filtertitle.value};
            }

            if (this.refs.filtertype.value) {
                this.filter[this.refs.filtertype.value] = true;
            }

            this.listAssets(1);
        }.bind(this)

        this.loadPage = function(e) {

            var page = parseInt(e.target.getAttribute('data-page'), 10);

            this.listAssets(page || 1);
        }.bind(this)

        this.remove = function(e) {
            var asset = e.item.asset,
                idx   = e.item.idx;

            App.ui.confirm("Are you sure?", function() {

                App.request('/assetsmanager/removeAssets', {assets:[asset]}).then(function(data) {

                    App.ui.notify("Asset removed", "success");

                    $this.assets.splice(idx, 1);

                    $this.update();
                });
            });

        }.bind(this)

        this.removeSelected = function() {

            App.ui.confirm("Are you sure?", function() {

                App.request('/assetsmanager/removeAssets', {assets:$this.selected}).then(function(data) {

                    $this.selected.forEach(function(asset){
                        $this.assets.splice($this.assets.indexOf(asset), 1);
                    });

                    App.ui.notify("Assets removed", "success");
                    $this.selected = [];
                    $this.update();
                });
            });

        }.bind(this)

        this.edit = function(e) {
            this.asset = e.item.asset;
            this.mode  = 'edit';
            this.refs.assettitle.value = this.asset.title;
            this.refs.assetdescription.value = this.asset.description;
        }.bind(this)

        this.cancelEdit = function() {
            this.asset = null;
            this.mode  = 'list';
        }.bind(this)

        this.updateAsset = function(e) {

            e.preventDefault();

            this.asset.title = this.refs.assettitle.value;
            this.asset.description = this.refs.assetdescription.value;

            App.request('/assetsmanager/updateAsset', {asset:$this.asset}).then(function(asset) {

                $this.asset = asset;

                App.ui.notify("Asset updated", "success");

                $this.update();
            });

            return false;
        }.bind(this)

        this.select = function(e) {

            if (App.$(e.target).is('a') || App.$(e.target).parents('a').length) return;

            var idx = this.selected.indexOf(e.item.asset);

            if (idx == -1) {
                this.selected.push(e.item.asset);
            } else {
                this.selected.splice(idx, 1);
            }

            App.$(this.root).trigger('selectionchange', [this.selected]);
        }.bind(this)

        this.getIconCls = function(path) {

            var name = path.toLowerCase();

            if (name.match(typefilters.image)) {

                return 'image';

            } else if(name.match(typefilters.video)) {

                return 'video-camera';

            } else if(name.match(typefilters.audio)) {

                return 'music';

            } else if(name.match(typefilters.document)) {

                return 'file-text-o';

            } else if(name.match(typefilters.code)) {

                return 'code';

            } else if(name.match(typefilters.archive)) {

                return 'archive';

            } else {
                return 'paperclip';
            }
        }.bind(this)

        this.getRefValue = function(name) {
            return this.refs[name] && this.refs[name].value;
        }.bind(this)

});

riot.tag2('cp-field', '<div ref="field" data-is="{\'field-\'+opts.type}" bind="{opts.bind}" cls="{opts.cls}"></div>', '', '', function(opts) {

        this.on('mount', function() {
            this.trigger('update');
        });

        this.on('update', function() {

            this.refs.field.opts.bind = opts.bind;
            this.refs.field.opts.bind = opts.opts || {};

            if (opts.opts) {
                App.$.extend(this.refs.field.opts, opts.opts);
            }

            this.refs.field.update();
        });

});

riot.tag2('cp-fieldsmanager', '<div ref="fieldscontainer" class="uk-sortable uk-grid uk-grid-small uk-grid-gutter uk-form"> <div riot-class="uk-width-{field.width}" data-idx="{idx}" each="{field,idx in fields}"> <div class="uk-panel uk-panel-box uk-panel-card"> <div class="uk-grid uk-grid-small"> <div class="uk-flex-item-1 uk-flex"> <input class="uk-flex-item-1 uk-form-small uk-form-blank" type="text" fields-bind="fields[{idx}].name" placeholder="name" required> </div> <div class="uk-width-1-4"> <div class="uk-form-select" data-uk-form-select> <div class="uk-form-icon"> <i class="uk-icon-arrows-h"></i> <input class="uk-width-1-1 uk-form-small uk-form-blank" riot-value="{field.width}"> </div> <select fields-bind="fields[{idx}].width"> <option value="1-1">1-1</option> <option value="1-2">1-2</option> <option value="1-3">1-3</option> <option value="2-3">2-3</option> <option value="1-4">1-4</option> <option value="3-4">3-4</option> </select> </div> </div> <div class="uk-text-right"> <ul class="uk-subnav"> <li show="{parent.opts.listoption}"> <a class="uk-text-{field.lst ? \'success\':\'muted\'}" onclick="{parent.togglelist}" title="{App.i18n.get(\'Show field on list view\')}"> <i class="uk-icon-list"></i> </a> </li> <li> <a onclick="UIkit.modal(\'#field-{idx}\').show()"><i class="uk-icon-cog uk-text-primary"></i></a> </li> <li> <a class="uk-text-danger" onclick="{parent.removefield}"> <i class="uk-icon-trash"></i> </a> </li> </ul> </div> </div> </div> <div class="uk-modal uk-sortable-nodrag" id="field-{idx}"> <div class="uk-modal-dialog"> <div class="uk-form-row uk-text-bold"> {field.name || \'Field\'} </div> <div class="uk-form-row"> <label class="uk-text-muted uk-text-small">{App.i18n.get(\'Field Type\')}:</label> <div class="uk-form-select uk-width-1-1 uk-margin-small-top"> <a class="uk-text-capitalize">{field.type}</a> <select class="uk-width-1-1 uk-text-capitalize" fields-bind="fields[{idx}].type"> <option each="{type,typeidx in parent.fieldtypes}" riot-value="{type.value}">{type.name}</option> </select> </div> </div> <div class="uk-form-row"> <label class="uk-text-muted uk-text-small">{App.i18n.get(\'Field Label\')}:</label> <input class="uk-width-1-1 uk-margin-small-top" type="text" fields-bind="fields[{idx}].label" placeholder="{App.i18n.get(\'Label\')}"> </div> <div class="uk-form-row"> <label class="uk-text-muted uk-text-small">{App.i18n.get(\'Field Info\')}:</label> <input class="uk-width-1-1 uk-margin-small-top" type="text" fields-bind="fields[{idx}].info" placeholder="{App.i18n.get(\'Info\')}"> </div> <div class="uk-form-row"> <label class="uk-text-muted uk-text-small">{App.i18n.get(\'Field Group\')}:</label> <input class="uk-width-1-1 uk-margin-small-top" type="text" fields-bind="fields[{idx}].group" placeholder="{App.i18n.get(\'Group name\')}"> </div> <div class="uk-form-row"> <label class="uk-text-small uk-text-bold uk-margin-small-bottom">{App.i18n.get(\'Options\')} <span class="uk-text-muted">JSON</span></label> <field-object cls="uk-width-1-1" fields-bind="fields[{idx}].options" rows="6" allowtabs="2"></field-object> </div> <div class="uk-form-row"> <field-boolean fields-bind="fields[{idx}].required" label="{App.i18n.get(\'Required\')}"></field-boolean> </div> <div class="uk-form-row"> <field-boolean fields-bind="fields[{idx}].localize" label="{App.i18n.get(\'Localize\')}"></field-boolean> </div> <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{App.i18n.get(\'Close\')}</button></div> </div> </div> </div> </div> <div class="uk-margin-top" show="{fields.length}"> <a class="uk-button uk-button-link" onclick="{addfield}"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add field\')}</a> </div> <div class="uk-width-medium-1-3 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle" if="{!fields.length && !reorder}"> <div class="uk-animation-fade"> <p class="uk-text-xlarge"> <img riot-src="{App.base(\'/assets/app/media/icons/form-editor.svg\')}" width="100" height="100"> </p> <hr> {App.i18n.get(\'No fields added yet\')}. <span data-uk-dropdown="pos:\'bottom-center\'"> <a onclick="{addfield}">{App.i18n.get(\'Add field\')}.</a> <div class="uk-dropdown uk-dropdown-scrollable uk-text-left" if="{opts.templates && opts.templates.length}"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header">{App.i18n.get(\'Choose from template\')}</li> <li each="{template in opts.templates}"> <a onclick="{parent.fromTemplate.bind(parent, template)}"><i class="uk-icon-sliders uk-margin-small-right"></i> {template.label || template.name}</a> </li> </ul> </div> <span> </div> </div>', '', '', function(opts) {

        riot.util.bind(this, 'fields');

        var $this = this;

        this.fields  = [];
        this.reorder = false;

        this.fieldtypes = [];

        for (var tag in riot.tags) {

            if(tag.indexOf('field-')==0) {

                f = tag.replace('field-', '');

                this.fieldtypes.push({name:f, value:f});
            }
        }

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this.fields !== value) {

                this.fields = value;

                this.fields.forEach(function(field) {
                    if (Array.isArray(field.options)) {
                        field.options = {};
                    }
                });

                this.update();
            }

        }.bind(this);

        this.on('bindingupdated', function(){
            $this.$setValue(this.fields);
        });

        this.one('mount', function(){

            UIkit.sortable(this.refs.fieldscontainer, {

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

                App.$($this.refs.fieldscontainer).css('height', App.$($this.refs.fieldscontainer).height());

                $this.fields = [];
                $this.reorder = true;
                $this.update();

                setTimeout(function() {
                    $this.reorder = false;
                    $this.fields = fields;
                    $this.update();
                    $this.$setValue(fields);

                    setTimeout(function(){
                        $this.refs.fieldscontainer.style.height = '';
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
                'group'   : '',
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

        this.fromTemplate = function(template) {

            if (template && Array.isArray(template.fields) && template.fields.length) {
                this.fields = template.fields;
                $this.$setValue(this.fields);
            }
        }.bind(this)

});

riot.tag2('cp-finder', '<div show="{data}"> <div class="uk-clearfix" data-uk-margin> <div class="uk-float-left"> <span class="uk-button uk-button-primary uk-margin-small-right uk-form-file"> <input class="js-upload-select" type="file" multiple="true" title=""> <i class="uk-icon-upload"></i> </span> <span class="uk-button-group uk-margin-small-right"> <span class="uk-position-relative uk-button" data-uk-dropdown="mode:\'click\'"> <i class="uk-icon-magic"></i> <div class="uk-dropdown uk-text-left"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header">Create</li> <li><a onclick="{createfolder}"><i class="uk-icon-folder-o uk-icon-justify"></i> Folder</a></li> <li><a onclick="{createfile}"><i class="uk-icon-file-o uk-icon-justify"></i> File</a></li> </ul> </div> </span> <button class="uk-button" onclick="{refresh}"> <i class="uk-icon-refresh"></i> </button> </span> <span if="{selected.count}" data-uk-dropdown="mode:\'click\'"> <span class="uk-button"><strong>Batch:</strong> {selected.count} selected &nbsp;<i class="uk-icon-caret-down"></i></span> <div class="uk-dropdown uk-margin-top uk-text-left"> <ul class="uk-nav uk-nav-dropdown"> <li class="uk-nav-header">Batch action</li> <li><a onclick="{removeSelected}">Delete</a></li> </ul> </div> </span> </div> <div class="uk-float-right"> <div class="uk-form uk-form-icon uk-width-1-1"> <i class="uk-icon-filter"></i> <input ref="filter" type="text" onkeyup="{updatefilter}"> </div> </div> </div> <div class="uk-grid uk-grid-divider uk-margin-large-top" data-uk-grid-margin> <div class="uk-width-medium-1-4"> <div class="uk-panel"> <ul class="uk-nav uk-nav-side"> <li class="uk-nav-header">Display</li> <li riot-class="{!typefilter ? \'uk-active\':\'\'}"><a data-type="" onclick="{settypefilter}"><i class="uk-icon-circle-o uk-icon-justify"></i> All</a></li> <li riot-class="{typefilter==\'image\' ? \'uk-active\':\'\'}"><a data-type="image" onclick="{settypefilter}"><i class="uk-icon-image uk-icon-justify"></i> Images</a></li> <li riot-class="{typefilter==\'video\' ? \'uk-active\':\'\'}"><a data-type="video" onclick="{settypefilter}"><i class="uk-icon-video-camera uk-icon-justify"></i> Video</a></li> <li riot-class="{typefilter==\'audio\' ? \'uk-active\':\'\'}"><a data-type="audio" onclick="{settypefilter}"><i class="uk-icon-volume-up uk-icon-justify"></i> Audio</a></li> <li riot-class="{typefilter==\'document\' ? \'uk-active\':\'\'}"><a data-type="document" onclick="{settypefilter}"><i class="uk-icon-paper-plane uk-icon-justify"></i> Documents</a></li> <li riot-class="{typefilter==\'archive\' ? \'uk-active\':\'\'}"><a data-type="archive" onclick="{settypefilter}"><i class="uk-icon-archive uk-icon-justify"></i> Archives</a></li> </ul> </div> </div> <div class="uk-width-medium-3-4"> <div class="uk-panel"> <ul class="uk-breadcrumb"> <li onclick="{changedir}"><a title="Change dir to root"><i class="uk-icon-home"></i></a></li> <li each="{folder, idx in breadcrumbs}"><a onclick="{parent.changedir}" title="Change dir to {folder.name}">{folder.name}</a></li> </ul> </div> <div ref="uploadprogress" class="uk-margin uk-hidden"> <div class="uk-progress"> <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div> </div> </div> <div class="uk-alert uk-text-center uk-margin" if="{data && (this.typefilter || this.refs.filter.value) && (data.folders.length || data.files.length)}"> Filter is active </div> <div class="uk-alert uk-text-center uk-margin" if="{data && (!data.folders.length && !data.files.length)}"> This is an empty folder </div> <div riot-class="{modal ? \'uk-overflow-container\':\'\'}"> <div class="uk-margin-top" if="{data && data.folders.length}"> <strong class="uk-text-small uk-text-muted" if="{!(this.refs.filter.value)}"><i class="uk-icon-folder-o uk-margin-small-right"></i> {data.folders.length} Folders</strong> <ul class="uk-grid uk-grid-small uk-grid-match uk-grid-width-1-2 uk-grid-width-medium-1-4"> <li class="uk-grid-margin" each="{folder, idx in data.folders}" onclick="{select}" if="{infilter(folder)}"> <div riot-class="uk-panel uk-panel-box finder-folder {folder.selected ? \'uk-selected\':\'\'}"> <div class="uk-flex"> <div> <span class="uk-margin-small-right" data-uk-dropdown="mode:\'click\'"> <i class="uk-icon-folder-o uk-text-muted js-no-item-select"></i> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header uk-text-truncate">{folder.name}</li> <li><a onclick="{parent.rename}">Rename</a></li> <li><a onclick="{parent.remove}">Delete</a></li> </ul> </div> </span> </div> <div class="uk-flex-item-1 uk-text-truncate"> <a class="uk-link-muted" onclick="{parent.changedir}"><strong>{folder.name}</strong></a> </div> </div> </div> </li> </ul> </div> <div class="uk-margin-top" if="{data && data.files.length}"> <strong class="uk-text-small uk-text-muted" if="{!(this.typefilter || this.refs.filter.value)}"><i class="uk-icon-file-o uk-margin-small-right"></i> {data.files.length} Files</strong> <ul class="uk-grid uk-grid-small uk-grid-match uk-grid-width-1-2 uk-grid-width-medium-1-4"> <li class="uk-grid-margin" each="{file, idx in data.files}" onclick="{select}" if="{infilter(file)}"> <div riot-class="uk-panel uk-panel-box finder-file {file.selected ? \'uk-selected\':\'\'}"> <div class="uk-panel-teaser uk-cover-background uk-position-relative"> <div class="uk-position-cover uk-position-z-index"> <div class="uk-panel uk-panel-box uk-panel-box-trans"> <span class="uk-margin-small-right" data-uk-dropdown="mode:\'click\'"> <a><i class="uk-icon-{parent.getIconCls(file)} js-no-item-select"></i> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown uk-dropdown-close"> <li class="uk-nav-header uk-text-truncate">{file.name}</li> <li> <a class="uk-link-muted js-no-item-select" onclick="{parent.open}">Open</a></li> <li><a onclick="{parent.rename}">Rename</a></li> <li if="{file.ext == \'zip\'}"><a onclick="{parent.unzip}">Unzip</a></li> <li class="uk-nav-divider"></li> <li><a onclick="{parent.remove}">Delete</a></li> </ul> </div> </span> </div> </div> <canvas class="uk-responsive-width uk-display-block" width="400" height="300" if="{parent.getIconCls(file) != \'image\'}"></canvas> <cp-thumbnail riot-src="{file.url}" width="400" height="300" if="{parent.getIconCls(file) == \'image\'}"></cp-thumbnail> </div> <div class="uk-flex-item-1 uk-text-truncate"> <a class="uk-link-muted js-no-item-select" onclick="{parent.open}">{file.name}</a> <div class="uk-margin-small-top uk-text-small uk-text-muted"> {file.size} </div> </div> </div> </li> </ul> </div> </div> </div> </div> <div ref="editor" class="uk-offcanvas"> <div class="uk-offcanvas-bar uk-width-3-4"> <picoedit></picoedit> </div> </div> </div>', 'cp-finder .uk-offcanvas[ref=editor] .CodeMirror,[data-is="cp-finder"] .uk-offcanvas[ref=editor] .CodeMirror{ height: auto; } cp-finder .uk-offcanvas[ref=editor] .picoedit-toolbar,[data-is="cp-finder"] .uk-offcanvas[ref=editor] .picoedit-toolbar{ padding-left: 15px; padding-right: 15px; } cp-finder .uk-modal .uk-panel-box.finder-folder,[data-is="cp-finder"] .uk-modal .uk-panel-box.finder-folder,cp-finder .uk-modal .uk-panel-box.finder-file,[data-is="cp-finder"] .uk-modal .uk-panel-box.finder-file{ border: 1px rgba(0,0,0,0.1) solid; }', '', function(opts) {

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

        App.$(this.refs.editor).on('click', function(e){

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
                e.preventDefault();
                e.stopPropagation();
                path = e.item.folder.path;
            } else {
                path = opts.root;
            }

            this.loadPath(path);
        }.bind(this)

        this.open = function(e) {

            e.preventDefault();

            if (opts.previewfiles === false) {
                this.select(e, true);
                return;
            }

            var file = e.item.file,
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

                UIkit.offcanvas.show(this.refs.editor);
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

        this.settypefilter = function(e) {
            e.preventDefault();

            this.typefilter = e.target.dataset.type;
            this.resetselected();
        }.bind(this)

        this.updatefilter = function(e) {
            this.resetselected();
        }.bind(this)

        this.infilter = function(item) {

            var name = item.name.toLowerCase();

            if (this.typefilter && item.is_file && typefilters[this.typefilter]) {

                if (!name.match(typefilters[this.typefilter])) {
                    return false;
                }
            }

            return (!this.refs.filter.value || (name && name.indexOf(this.refs.filter.value.toLowerCase()) !== -1));
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

                return 'video-camera';

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

});

riot.tag2('cp-gravatar', '<canvas ref="image" class="uk-responsive-width uk-border-circle" width="{size}" height="{size}"></canvas>', '', '', function(opts) {

        this.url = '';
        this.size  = opts.size || 100;

        this.on('mount', function(){
            this.trigger('update');
        });

        this.on('update', function() {

            this.size  = opts.size || 100;
            this.email = opts.email || '';

            var img = new Image(), url, release = function() {
                setTimeout(function() {
                    this.refs.image.getContext("2d").drawImage(img,0,0);
                    sessionStorage[url] = this.refs.image.toDataURL();
                }.bind(this), 10);
            }.bind(this);

            url = '//www.gravatar.com/avatar/'+md5(this.email)+'?d=404&s='+this.size;

            img.crossOrigin = 'Anonymous';

            img.onload = function() {
                release();
            }.bind(this);

            img.onerror = function() {
                img.src = App.Utils.letterAvatar(opts.alt || '', this.size);
                release();
            }.bind(this);

            img.src = sessionStorage[url] || url;

        });

});

riot.tag2('cp-search', '<div ref="autocomplete" class="uk-autocomplete uk-form uk-form-icon app-search"> <i class="uk-icon-search"></i> <input class="uk-width-1-1 uk-form-blank" type="text" placeholder="{App.i18n.get(\'Search for anything...\')}"> </div>', 'cp-search .uk-dropdown { min-width: 25vw; }', '', function(opts) {

        this.on('mount', function(){

            var txtSearch = App.$("input[type='text']", this.refs.autocomplete);

            UIkit.autocomplete(this.refs.autocomplete, {
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

                e.preventDefault();
                txtSearch.focus();
                return false;
            });

        });

        App.$(this.root).on("selectitem.uk.autocomplete", function(e, data) {

            if (data.url) {
                location.href = data.url;
            }
        });

});

riot.tag2('cp-thumbnail', '<span class="uk-position-relative"> <i ref="spinner" class="uk-icon-spinner uk-icon-spin uk-position-absolute"></i> <canvas ref="canvas" class="uk-responsive-width" width="{opts.width || \'\'}" height="{opts.height || \'\'}"></canvas> </span>', '', '', function(opts) {

        var $this = this, src;

        this.on('mount', function() {
            this.trigger('update');
        })

        this.on('update', function(){

            opts.src = opts.src || opts['riot-src'] || opts['riotSrc'];

            if (!opts.src || src == opts.src) {
                return;
            }

            $this.refs.spinner.classList.remove('uk-hidden');

            $this.refs.canvas.getContext("2d").clearRect(0, 0, $this.refs.canvas.width, $this.refs.canvas.height);

            App.request('/cockpit/utils/thumb_url', {src:opts.src,w:opts.width,h:opts.height}, 'text').then(function(url){

                var img = new Image();

                img.onload = function() {
                    $this.refs.canvas.getContext("2d").drawImage(img,0,0);
                    $this.refs.spinner.classList.add('uk-hidden');
                };

                img.src = url;
                src = opts.src;
            }).catch(function(e){

            });
        });

});

riot.tag2('field-asset', '<div class="uk-placeholder uk-text-center" if="{!asset}"> {App.i18n.get(\'No asset selected\')}. <a onclick="{selectAsset}">{App.i18n.get(\'Select one\')}</a> </div> <div class="uk-panel uk-panel-box uk-panel-card uk-display-inline-block" if="{asset}"> <div class="uk-overlay uk-display-block uk-position-relative"> <canvas class="uk-responsive-width" width="200" height="150"></canvas> <div class="uk-position-absolute uk-position-cover uk-flex uk-flex-middle"> <div class="uk-width-1-1 uk-text-center"> <span if="{asset.mime.match(/^image\\//) == null}"><i class="uk-h1 uk-text-muted uk-icon-{getIconCls(asset.path)}"></i></span> <a href="{ASSETS_URL+asset.path}" if="{asset.mime.match(/^image\\//)}" data-uk-lightbox="type:\'image\'" title="{asset.width && [asset.width, asset.height].join(\'x\')}"> <cp-thumbnail riot-src="{asset && ASSETS_URL+asset.path}" width="100" height="75"></cp-thumbnail> </a> </div> </div> </div> <div class="uk-margin-small-top uk-text-truncate"><a href="{ASSETS_URL+asset.path}" target="_blank">{asset.title}</a></div> <div class="uk-text-small uk-text-muted"> <strong>{asset.mime}</strong> {App.Utils.formatSize(asset.size)} </div> <hr> <div class="uk-text-small"> <a class="uk-margin-small-right" onclick="{selectAsset}">{App.i18n.get(\'Replace\')}</a> <a onclick="{reset}"><i class="uk-icon-trash-o"></i></a> </div> </div>', '', '', function(opts) {

        var $this = this, typefilters = {
            'image'    : /\.(jpg|jpeg|png|gif|svg)$/i,
            'video'    : /\.(mp4|mov|ogv|webv|wmv|flv|avi)$/i,
            'audio'    : /\.(mp3|weba|ogg|wav|flac)$/i,
            'archive'  : /\.(zip|rar|7zip|gz)$/i,
            'document' : /\.(txt|pdf|md)$/i,
            'code'     : /\.(htm|html|php|css|less|js|json|yaml|xml|htaccess)$/i
        };

        this.asset = opts.default || false;

        this.$updateValue = function(value) {

            if (JSON.stringify(this.asset) != JSON.stringify(value)) {

                this.asset = value;
                this.update();
            }

        }.bind(this);

        this.selectAsset = function() {

            Cockpit.assets.select(function(assets){
                if (Array.isArray(assets)) {
                    $this.$setValue(assets[0]);
                }
            });
        }.bind(this)

        this.reset = function() {
            $this.asset = null;
            $this.$setValue($this.asset);
        }.bind(this)

        this.getIconCls = function(path) {

            var name = path.toLowerCase();

            if (name.match(typefilters.image)) {

                return 'image';

            } else if(name.match(typefilters.video)) {

                return 'video-camera';

            } else if(name.match(typefilters.audio)) {

                return 'music';

            } else if(name.match(typefilters.document)) {

                return 'file-text-o';

            } else if(name.match(typefilters.code)) {

                return 'code';

            } else if(name.match(typefilters.archive)) {

                return 'archive';

            } else {
                return 'paperclip';
            }
        }.bind(this)

});

riot.tag2('field-boolean', '<div ref="container" class="uk-display-inline-block" onclick="{toggle}" style="cursor:pointer;"> <div class="uk-form-switch"> <input ref="check" type="checkbox" id="{id}"> <label for="{id}"></label> </div> <span show="{value && (opts.label !== \'false\' && opts.label !== false)}">{opts.label || \'On\'}</span> <span class="uk-text-muted" show="{!value && (opts.label !== \'false\' && opts.label !== false)}">{opts.label || \'Off\'}</span> </div>', '', '', function(opts) {

        this.id = 'switch'+Math.ceil(Math.random()*10000000);

        if (opts.cls) {
            App.$(this.refs.container).addClass(opts.cls);
        }

        this.value = opts.default || false;

        this.$updateValue = function(value) {

            if (this.value != value) {
                this.value = value;
                this.update();
            }

            document.getElementById(this.id).checked = Boolean(this.value);

        }.bind(this);

        this.toggle = function(e) {
            e.preventDefault();
            this.$setValue(!this.value);
        }.bind(this)

});

riot.tag2('field-code', '<codemirror ref="codemirror" syntax="{opts.syntax || \'text\'}"></codemirror>', 'field-code .CodeMirror { height: auto; }', '', function(opts) {

        var $this = this, editor;

        this.value  = null;
        this._field = null;

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

            this.refs.codemirror.on('ready', function(){
                editor = $this.refs.codemirror.editor;

                editor.setValue($this.value || '');

                editor.on('change', function() {
                    $this.$setValue(editor.getValue(), true);
                });

                $this.isReady = true;
                $this.update();
            });
        });

});

riot.tag2('field-color', '<input ref="input" class="uk-width-1-1" type="text">', '', '', function(opts) {

        this.on('mount', function() { this.trigger('update'); });
        this.on('update', function() { if (opts.opts) App.$.extend(opts, opts.opts); });

        var $this = this;

        this.$updateValue = function(value, field) {

            if (value && this.refs.input.value !== value) {
                this.refs.input.value = value;
                this.update();
            }

            if (App.$.fn.spectrum) {
                App.$($this.refs.input).spectrum("set", $this.root.$value);
            }

        }.bind(this);

        this.on('mount', function(){

            App.assets.require([
                '/assets/lib/spectrum/spectrum.js',
                '/assets/lib/spectrum/spectrum.css'
            ], function(){

                $this.refs.input.value = $this.root.$value || '';

                App.$($this.refs.input).spectrum(App.$.extend({
                    preferredFormat: 'rgb',
                    allowEmpty:true,
                    showInitial: true,
                    showInput: true,
                    showButtons: false,
                    showAlpha: true,
                    showSelectionPalette: true,
                    palette: [ ],
                    change: function() {
                        $this.$setValue($this.refs.input.value);
                    }
                }, opts.spectrum));

            });
        });

});

riot.tag2('field-colortag', '<div class="uk-display-inline-block" data-uk-dropdown="pos:\'right-center\'"> <a riot-style="font-size:{size};color:{value || \'#ccc\'}"><i class="uk-icon-circle"></i></a> <div class="uk-dropdown uk-text-center"> <strong class="uk-text-small">{App.i18n.get(\'Choose\')}</strong> <div class="uk-grid uk-grid-small uk-margin-small-top uk-grid-width-1-4"> <div class="uk-grid-margin" each="{color in colors}"> <a onclick="{parent.select}" riot-style="color:{color};"><i class="uk-icon-circle"></i></a> </div> </div> <div class="uk-margin-top uk-text-small"> <a onclick="{reset}">{App.i18n.get(\'Reset\')}</a> </div> </div> </div>', '', '', function(opts) {

        this.value  = '';
        this.size   = opts.size || 'inherit';
        this.colors = opts.colors || ['#D8334A','#FFCE54','#A0D468','#48CFAD','#4FC1E9','#5D9CEC','#AC92EC','#EC87C0','#BAA286','#8E8271','#3C3B3D'];

        this.$updateValue = function(value, field) {

            if (this.value !== value) {
                this.value = value;
                this.update();
            }

        }.bind(this);

        this.select = function(e) {
            this.$setValue(e.item.color);
        }.bind(this)

        this.reset = function() {
            this.$setValue('');
        }.bind(this)

});

riot.tag2('field-date', '<input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="text" placeholder="{opts.placeholder}">', '', '', function(opts) {

        var $this = this;

        if (opts.cls) {
            App.$(this.refs.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.refs.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/datepicker.js', '/assets/lib/uikit/js/components/form-select.js'], function() {

                UIkit.datepicker(this.refs.input, opts).element.on('change', function() {
                    $this.refs.input.$setValue($this.refs.input.value);
                });

            }.bind(this));
        });

});

riot.tag2('field-file', '<div class="uk-panel uk-panel-box uk-panel-card"> <button type="button" class="uk-button uk-margin-small-right" ref="picker" title="{App.i18n.get(\'Pick file\')}"><i class="uk-icon-paperclip"></i></button> <input class="uk-form-blank" type="text" ref="input" bind="{opts.bind}" placeholder="{opts.placeholder || App.i18n.get(\'No file selected...\')}"> </div>', '', '', function(opts) {

        this.on('mount', function() {

            var $this = this, $input = App.$(this.refs.input);

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
                App.$(this.refs.picker).addClass(opts.cls);
            }

            App.$(this.refs.picker).on('click', function() {

                App.media.select(function(selected) {
                    $this.refs.input.$setValue(selected[0]);
                });
            });
        });

});

riot.tag2('field-gallery', '<div ref="panel"> <div ref="imagescontainer" class="uk-sortable uk-grid uk-grid-match uk-grid-small uk-grid-gutter uk-grid-width-medium-1-4" show="{images && images.length}"> <div data-idx="{idx}" each="{img,idx in images}"> <div class="uk-panel uk-panel-box uk-panel-thumbnail uk-panel-card"> <figure class="uk-display-block uk-overlay uk-overlay-hover"> <div class="uk-flex uk-flex-middle uk-flex-center" style="min-height:120px;"> <div class="uk-width-1-1 uk-text-center"> <img class="uk-display-inline-block uk-responsive-width" riot-src="{(SITE_URL+\'/\'+img.path)}"> </div> </div> <figcaption class="uk-overlay-panel uk-overlay-background uk-flex uk-flex-middle uk-flex-center"> <div> <ul class="uk-subnav"> <li><a onclick="{parent.showMeta}" title="{App.i18n.get(\'Edit meta data\')}" data-uk-tooltip><i class="uk-icon-cog"></i></a></li> <li><a href="{(SITE_URL+\'/\'+img.path)}" data-uk-lightbox="type:\'image\'" title="{App.i18n.get(\'Full size\')}" data-uk-tooltip><i class="uk-icon-eye"></i></a></li> <li><a onclick="{parent.remove}" title="{App.i18n.get(\'Remove image\')}" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li> </ul> <p class="uk-text-small uk-text-truncate">{img.title}</p> </div> </figcaption> </figure> </div> </div> </div> <div riot-class="{images && images.length ? \'uk-margin-top\':\'\'}"> <div class="uk-alert" if="{images && !images.length}">{App.i18n.get(\'Gallery is empty\')}.</div> <a class="uk-button uk-button-link" onclick="{selectimages}"> <i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add images\')} </a> </div> <div class="uk-modal uk-sortable-nodrag" ref="modalmeta"> <div class="uk-modal-dialog"> <div class="uk-modal-header"><h3>{App.i18n.get(\'Image Meta\')}</h3></div> <div class="uk-grid uk-grid-match uk-grid-gutter" if="{image}"> <div riot-class="uk-grid-margin uk-width-medium-{field.width}" each="{field,name in meta}" no-reorder> <div class="uk-panel"> <label class="uk-text-bold"> {field.label || name} </label> <div class="uk-margin uk-text-small uk-text-muted"> {field.info || \' \'} </div> <div class="uk-margin"> <cp-field type="{field.type || \'text\'}" bind="image.meta[\'{name}\']" opts="{field.options || {}}"></cp-field> </div> </div> </div> </div> <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{App.i18n.get(\'Close\')}</button></div> </div> </div> </div>', '', '', function(opts) {

        riot.util.bind(this);

        var $this = this;

        this.images = [];
        this._field = null;

        this.meta   = App.$.extend(opts.meta || {}, {
            title: {
                type: 'text',
                label: 'Title'
            }
        });

        this.on('mount', function() {

            UIkit.sortable(this.refs.imagescontainer, {

                animation: false

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                ele = App.$(ele);

                var images = $this.images,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                images.splice(cidx, 0, images.splice(oidx, 1)[0]);

                App.$($this.refs.panel).css('height', App.$($this.refs.panel).height());

                $this.images = [];
                $this.update();

                setTimeout(function() {
                    $this.images = images;
                    $this.$setValue(images);
                    $this.update();

                    setTimeout(function(){
                        $this.refs.panel.style.height = '';
                        $this.update();
                    }, 30)
                }, 10);

            });

        });

        this.$updateValue = function(value, field) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.images) !== JSON.stringify(value)) {
                this.images = value;
                this.update();
            }

        }.bind(this);

        this.$initBind = function() {
            this.root.$value = this.images;
        };

        this.on('bindingupdated', function() {
            $this.$setValue(this.images);
        });

        this.showMeta = function(e) {

            this.image = this.images[e.item.idx];

            setTimeout(function() {
                UIkit.modal($this.refs.modalmeta).show().on('close.uk.modal', function(){
                    $this.image = null;
                });
            }, 50)
        }.bind(this)

        this.selectimages = function() {

            App.media.select(function(selected) {

                var images = [];

                selected.forEach(function(path){
                    images.push({meta:{title:''}, path:path});
                });

                $this.$setValue($this.images.concat(images));

            }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });
        }.bind(this)

        this.remove = function(e) {
            this.images.splice(e.item.idx, 1);
            this.$setValue(this.images);
        }.bind(this)

});

riot.tag2('field-html', '<textarea ref="input" class="uk-visibility-hidden"></textarea>', '', '', function(opts) {

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

                    }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });

                });

                App.$(document).trigger('init-html-editor', [editor]);

            }.bind(this));
        });

});

riot.tag2('field-image', '<figure class="uk-display-block uk-panel uk-panel-box uk-panel-card uk-overlay uk-overlay-hover"> <div class="uk-flex uk-flex-middle uk-flex-center uk-text-muted"> <div class="uk-width-1-1" show="{image.path}" riot-style="min-height:160px;background-size:contain;background-repeat:no-repeat;background-position:50% 50%;{image.path ? \'background-image: url(\'+encodeURI(SITE_URL+\'/\'+image.path)+\')\':\'\'}"></div> <div class="uk-width-1-1 uk-text-large" show="{!image.path}"><i class="uk-icon-image"></i></div> </div> <figcaption class="uk-overlay-panel uk-overlay-background"> <ul class="uk-subnav"> <li><a onclick="{selectimage}" title="{App.i18n.get(\'Select image\')}" data-uk-tooltip><i class="uk-icon-image"></i></a></li> <li><a onclick="{showMeta}" title="{App.i18n.get(\'Edit meta data\')}" data-uk-tooltip><i class="uk-icon-cog"></i></a></li> <li><a onclick="{remove}" title="{App.i18n.get(\'Reset\')}" data-uk-tooltip><i class="uk-icon-trash-o"></i></a></li> </ul> <p class="uk-text-small uk-text-truncate">{image.title}</p> </figcaption> </figure> <div class="uk-modal uk-sortable-nodrag" ref="modalmeta"> <div class="uk-modal-dialog"> <div class="uk-modal-header"><h3>{App.i18n.get(\'Image Meta\')}</h3></div> <div class="uk-grid uk-grid-match uk-grid-gutter" if="{_meta}"> <div riot-class="uk-grid-margin uk-width-medium-{field.width}" each="{field, name in meta}" no-reorder> <div class="uk-panel"> <label class="uk-text-bold"> {field.label || name} </label> <div class="uk-margin uk-text-small uk-text-muted"> {field.info || \' \'} </div> <div class="uk-margin"> <cp-field type="{field.type || \'text\'}" bind="image.meta[\'{name}\']" opts="{field.options || {}}"></cp-field> </div> </div> </div> </div> <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{App.i18n.get(\'Close\')}</button></div> </div> </div>', '', '', function(opts) {

        this.on('mount', function() { this.trigger('update'); });
        this.on('update', function() { if (opts.opts) App.$.extend(opts, opts.opts); });

        riot.util.bind(this);

        var $this = this;

        this.image = {path:'', meta:{title:''}};

        this.on('mount', function() {

            this.meta  = App.$.extend(opts.meta || {}, {
                title: {
                    type: 'text',
                    label: 'Title'
                }
            });
        });

        this.$updateValue = function(value, field) {

            if (value && JSON.stringify(this.image) !== JSON.stringify(value)) {
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
            this.$setValue({path:'', title:'', meta:{title:''}});
        }.bind(this)

        this.showMeta = function() {

            this._meta = this.image.meta;

            setTimeout(function() {
                UIkit.modal($this.refs.modalmeta).show().on('close.uk.modal', function(){
                    $this._meta = null;
                });
            }, 50)
        }.bind(this)

});

riot.tag2('field-location', '<div class="uk-alert" if="{!apiready}"> Loading maps api... </div> <div show="{apiready}"> <div class="uk-form uk-position-relative uk-margin-small-bottom uk-width-1-1" style="z-index:1001"> <input ref="autocomplete" class="uk-width-1-1" placeholder="{latlng.address || [latlng.lat, latlng.lng].join(\', \')}"> </div> <div ref="map" style="min-height:300px;"> Loading map... </div> </div>', '', '', function(opts) {

        var map, marker;

        var locale = document.documentElement.lang.toUpperCase();

        var loadApi = App.assets.require([
            'https://cdn.jsdelivr.net/leaflet/1.0.0/leaflet.css',
            'https://cdn.jsdelivr.net/places.js/1/places.min.js',
            'https://cdn.jsdelivr.net/leaflet/1.0.0/leaflet.js'
        ]);

        var $this = this, defaultpos = {lat:53.55909862554551, lng:9.998652343749995};

        this.latlng = defaultpos;

        this.$updateValue = function(value) {

            if (!value) {
                value = defaultpos;
            }

            if (this.latlng != value) {
                this.latlng = value;

                if (marker) {
                    marker.setLatLng([this.latlng.lat, this.latlng.lng]).update();
                    map.panTo(marker.getLatLng());
                }

                this.update();
            }

        }.bind(this);

        this.on('mount', function() {

            loadApi.then(function() {

                $this.apiready = true;

                setTimeout(function(){

                    var map = L.map($this.refs.map).setView([$this.latlng.lat, $this.latlng.lng], opts.zoomlevel || 13);

                    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    var marker = new L.marker([$this.latlng.lat, $this.latlng.lng], {draggable:'true'});

                    marker.on('dragend', function(e) {
                        $this.$setValue(marker.getLatLng());
                    });

                    map.addLayer(marker);

                    var pla = places({
                        container: $this.refs.autocomplete
                    }).on('change', function(e) {
                        e.suggestion.latlng.address = e.suggestion.value;
                        $this.$setValue(e.suggestion.latlng);
                        marker.setLatLng(e.suggestion.latlng).update();
                        map.panTo(marker.getLatLng());
                        pla.close();
                        pla.setVal('');
                    });

                }, 50);

                $this.update();
            });

        });

});

riot.tag2('field-markdown', '<field-html ref="input" markdown="true" bind="{opts.bind}" height="{opts.height}"></field-html>', '', '', function(opts) {
});

riot.tag2('field-multipleselect', '<div riot-class="{options.length > 10 ? \'uk-scrollable-box\':\'\'}"> <div class="uk-margin-small-top" each="{option in options}"> <a data-value="{option}" riot-class="{parent.selected.indexOf(option)!==-1 ? \'uk-text-primary\':\'uk-text-muted\'}" onclick="{parent.toggle}" title="{option}"> <i riot-class="uk-icon-{parent.selected.indexOf(option)!==-1 ? \'circle\':\'circle-o\'} uk-margin-small-right"></i> {option} </a> </div> </div> <span class="uk-text-small uk-text-muted" if="{options.length > 10}">{selected.length} {App.i18n.get(\'selected\')}</span>', '', '', function(opts) {

        var $this = this;

        this.selected = [];
        this.options  = [];

        this.on('mount', function() {
            this.trigger('update');
        });

        this.on('update', function() {

            this.options = opts.options || [];

            if (typeof(this.options) === 'string') {

                var options = [];

                this.options.split(',').forEach(function(option) {
                    options.push(option.trim());
                });

                this.options = options;
            }
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

});

riot.tag2('field-object', '<div ref="input" riot-style="height: {opts.height || \'300px\'}"></div>', '', '', function(opts) {

        var $this = this, editor;

        this.value = {};

        this.on('mount', function(){

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }
            App.assets.require([

                '/assets/lib/jsoneditor/jsoneditor.min.css',
                '/assets/lib/jsoneditor/jsoneditor.min.js'

            ], function() {

                editor = new JSONEditor(this.refs.input, {
                    modes: ['tree', 'code'],
                    mode: 'code',
                    onChange: function(){
                        $this.value = editor.get() || {};
                        $this.$setValue($this.value, true);
                    }
                });

                editor.set(this.value);

            }.bind(this));

        });

        this.$updateValue = function(value) {

            if (typeof(value) != 'object') {
                value = {};
            }

            if (JSON.stringify(this.value) != JSON.stringify(value)) {
                this.value = value || {};
                if (editor)  {
                    editor.set(this.value);
                }
            }

        }.bind(this);

});

riot.tag2('field-password', '<div class="uk-form-password uk-width-1-1"> <input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="password"> <a href="" class="uk-form-password-toggle" data-uk-form-password>Show</a> </div>', '', '', function(opts) {

        this.on('mount', function(){

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            App.assets.require(['/assets/lib/uikit/js/components/form-password.js'], function() {

                UIkit.init(this.root);

            }.bind(this));
        });

});

riot.tag2('field-rating', '<ul class="uk-grid uk-grid-small"> <li show="{value}"><a onclick="{removeRating}"><i class="uk-icon-trash-o"></i></a></li> <li riot-class="{(!hoverValue && Math.ceil(value) >= n) || (hoverValue && Math.ceil(hoverValue) >= n) ? \'uk-text-primary\' : \'\'}" each="{n,idx in ratingRange}" onmousemove="{hoverRating}" onmouseleave="{leaveHoverRating}" onclick="{setRating}"><i class="uk-icon-{opts.icon ? opts.icon : \'star\'}" title="{(idx+1)}" data-uk-tooltip></i></li> <li show="{value}"><span class="uk-badge">{!hoverValue && value || hoverValue}</span></li> </ul>', 'field-rating .uk-grid > *,[data-is="field-rating"] .uk-grid > *{ cursor: pointer; }', '', function(opts) {


        this.on('mount', function() {

            this.mininmum  = opts.mininmum  || 0;
            this.maximum   = opts.maximum   || 5;
            this.precision = opts.this.precision || 0;

            if (this.precision < 0 || this.precision > 0.5) {
                this.precision = this.precision - Math.floor(this.precision);

                if (this.precision > 0.5) {
                    this.precision = this.precision - 0.5;
                }
            }

            this.value = null;
            this.hoverValue = null;

            this.ratingRange = [];

            for (var j = this.mininmum + 1; j <= this.maximum; j = j +1) {
                this.ratingRange.push(j);
            }
        });

        this.setRating = function(e) {
            this.$setValue(this.getValue(e));
        }.bind(this)

        this.getValue = function(e) {

            var element = App.$(e.target).closest('li')[0];

            if (!element) return;

            if (this.precision === 0) {
                return e.item.n;
            }

            return Math.floor(((e.item.n - 1) + (Math.floor(e.layerX/element.clientWidth / this.precision) + 1) * this.precision) * 1000) / 1000;
        }.bind(this)

        this.hoverRating = function(e) {
            this.hoverValue = this.getValue(e);
        }.bind(this)

        this.leaveHoverRating = function() {
            this.hoverValue = null;
        }.bind(this)

        this.removeRating = function() {
            this.$setValue(null);
        }.bind(this)

        this.$updateValue = function(value) {

            if (value === null && !opts.remove) {
                value = this.mininmum;
            }

            if (value !== null) {

                if (value < this.mininmum) {
                    value = this.mininmum;
                }

                if (value > this.maximum) {
                    value = this.maximum;
                }
            }

            if (this.value != value) {
                this.value = value;
                this.update();
            }

        }.bind(this);

});

riot.tag2('field-repeater', '<div class="uk-alert" show="{!items.length}"> {App.i18n.get(\'No items\')}. </div> <div show="{mode==\'edit\' && items.length}"> <div class="uk-margin uk-panel-box uk-panel-card" each="{item,idx in items}" data-idx="{idx}"> <div class="uk-text-small uk-margin"> <span class="uk-text-primary uk-badge uk-badget-outline">{App.Utils.ucfirst(typeof(item.field) == \'string\' ? item.field : (item.field.label || item.field.type))}</span> </div> <cp-field type="{item.field.type || \'text\'}" bind="items[{idx}].value" opts="{item.field.options || {}}"></cp-field> <div class="uk-panel-box-footer uk-bg-light"> <a onclick="{parent.remove}"><i class="uk-icon-trash-o"></i></a> </div> </div> </div> <div ref="itemscontainer" class="uk-sortable" show="{mode==\'reorder\' && items.length}"> <div class="uk-margin uk-panel-box uk-panel-card" each="{item,idx in items}" data-idx="{idx}"> <div class="uk-grid uk-grid-small"> <div class="uk-flex-item-1"><i class="uk-icon-bars uk-margin-small-right"></i> {App.Utils.ucfirst(typeof(item.field) == \'string\' ? item.field : (item.field.label || item.field.type))}</div> <div class="uk-text-muted uk-text-small">Item {(idx+1)}</div> </div> </div> </div> <div class="uk-margin"> <a class="uk-button" onclick="{add}" show="{mode==\'edit\'}" if="{!fields}"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add item\')}</a> <span show="{mode==\'edit\'}" if="{fields}" data-uk-dropdown="mode:\'click\'"> <a class="uk-button"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Add item\')}</a> <div class="uk-dropdown"> <ul class="uk-nav uk-nav-dropdown"> <li each="{field in fields}"><a class="uk-dropdown-close" onclick="{parent.add}">{field.label && field.label || App.Utils.ucfirst(typeof(field) == \'string\' ? field:field.type)}</a></li> </ul> </div> </span> <a class="uk-button" onclick="{updateorder}" show="{mode==\'reorder\'}"><i class="uk-icon-plus-circle"></i> {App.i18n.get(\'Update order\')}</a> <a class="uk-button" onclick="{switchreorder}" show="{items.length > 1}"> <span show="{mode==\'edit\'}"><i class="uk-icon-arrows"></i> {App.i18n.get(\'Reorder\')}</span> <span show="{mode==\'reorder\'}">{App.i18n.get(\'Cancel\')}</span> </a> </div>', '', '', function(opts) {

        var $this = this;

        riot.util.bind(this);

        this.items  = [];
        this.field  = {type:'text'};
        this.fields = false;
        this.mode   = 'edit';

        this.on('mount', function() {

            UIkit.sortable(this.refs.itemscontainer, {
                animation: false
            });

            this.trigger('update');
        });

        this.on('update', function() {
            this.field  = opts.field || {type:'text'};
            this.fields = opts.fields && Array.isArray(opts.fields) && opts.fields  || false;
        })

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
            $this.$setValue(this.items);
        });

        this.add = function(e) {

            if (opts.limit && this.items.length >= opts.limit) {
                App.ui.notify('Maximum amount of items reached');
                return;
            }

            if (this.fields) {
                this.items.push({field:e.item.field, value:null});
            } else {
                this.items.push({field:this.field, value:null});
            }
        }.bind(this)

        this.remove = function(e) {
            this.items.splice(e.item.idx, 1);
        }.bind(this)

        this.switchreorder = function() {
            $this.mode = $this.mode == 'edit' ? 'reorder':'edit';
        }.bind(this)

        this.updateorder = function() {

            var items = [];

            App.$(this.refs.itemscontainer).children().each(function(){
                items.push($this.items[Number(this.getAttribute('data-idx'))]);
            });

            $this.items = [];
            $this.update();

            setTimeout(function() {
                $this.mode = 'edit';
                $this.items = items;
                $this.$setValue(items);

                setTimeout(function(){
                    $this.update();
                }, 50)
            }, 50);
        }.bind(this)

});

riot.tag2('field-select', '<select ref="input" riot-class="uk-width-1-1 {opts.cls}" bind="{opts.bind}"> <option value=""></option> <option each="{option,idx in options}" riot-value="{option}">{option}</option> </select>', '', '', function(opts) {

        this.on('mount', function() { this.trigger('update'); });

        this.on('update', function() {

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            this.options = opts.options || []

            if (typeof(this.options) === 'string') {

                var options = [];

                this.options.split(',').forEach(function(option) {
                    options.push(option.trim());
                });

                this.options = options;
            }

            this.refs.input.value = this.root.$value;
        });

});

riot.tag2('field-set', '<div> <div class="uk-alert" if="{fields && !fields.length}"> {App.i18n.get(\'Fields definition is missing\')} </div> <div class="uk-margin" each="{field,idx in fields}"> <label class="uk-display-block uk-margin-small"><span class="uk-badge uk-badge-outline uk-badge-primary">{field.label || field.name || \'\'}</span></label> <cp-field type="{field.type || \'text\'}" bind="value.{field.name}" opts="{field.options || {}}"></cp-field> </div> </div>', '', '', function(opts) {

        var $this = this;

        this._field = null;
        this.set    = {};
        this.value  = {};
        this.fields = [];

        riot.util.bind(this);

        this.on('mount', function() {
            this.trigger('update');
        });

        this.on('update', function() {
            this.fields = opts.fields || [];
        });

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
            $this.$setValue(this.value);
        });

});

riot.tag2('field-tags', '<div class="uk-grid uk-grid-small uk-flex-middle" data-uk-grid-margin="observe:true"> <div class="uk-text-primary" each="{_tag,idx in _tags}"> <span class="field-tag"><i class="uk-icon-tag"></i> {_tag} <a onclick="{parent.remove}"><i class="uk-icon-close"></i></a></span> </div> <div> <div ref="autocomplete" class="uk-autocomplete uk-form-icon uk-form"> <i class="uk-icon-tag"></i> <input ref="input" class="uk-width-1-1 uk-form-blank" type="text" placeholder="{App.i18n.get(opts.placeholder || \'Add Tag...\')}"> </div> </div> </div>', 'field-tags .field-tag,[data-is="field-tags"] .field-tag{ display: inline-block; border: 1px currentColor solid; padding: .1em .5em; font-size: .9em; border-radius: 3px; }', '', function(opts) {

        var $this = this;

        this._tags = [];

        this.on('mount', function(){

            if (opts.autocomplete) {

                UIkit.autocomplete(this.refs.autocomplete, {source: opts.autocomplete});
            }

            App.$(this.root).on({

                'selectitem.uk.autocomplete keydown': function(e, data) {

                    var value = e.type=='keydown' ? $this.refs.input.value : data.value;

                    if (e.type=='keydown' && e.keyCode != 13) {
                        return;
                    }

                    if (value.trim()) {

                        $this.refs.input.value = value;

                        e.stopImmediatePropagation();
                        e.stopPropagation();
                        e.preventDefault();
                        $this._tags.push($this.refs.input.value);
                        $this.refs.input.value = "";
                        $this.$setValue(_.uniq($this._tags));
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

            if (this._tags !== value) {
                this._tags = value;
                this.update();
            }

        }.bind(this);

        this.remove = function(e) {
            this._tags.splice(e.item.idx, 1);
            this.$setValue(this._tags);
        }.bind(this)

});

riot.tag2('field-text', '<input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="{opts.type || \'text\'}" placeholder="{opts.placeholder}" bind-event="change"> <div class="uk-text-muted uk-text-small uk-margin-small-top" if="{opts.slug}" title="Slug"> {slug} </div>', '', '', function(opts) {

        this.on('mount', function() {

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }
        });

        this.$updateValue = function(value) {

            if (opts.slug) {
                this.slug = App.Utils.sluggify(value || '');
                this.$setValue(this.slug, false, opts.bind+'_slug');
                this.update();
            }

        }.bind(this);

});

riot.tag2('field-textarea', '<textarea ref="input" riot-class="uk-width-1-1 {opts.cls}" bind="{opts.bind}" riot-rows="{opts.rows || 10}" riot-placeholder="{opts.placeholder}" bind-event="change"></textarea>', '', '', function(opts) {

        this.on('mount', function() {

            if (opts.allowtabs) {

                this.refs.input.onkeydown = function(e) {
                    if (e.keyCode === 9) {
                        var val = this.value, start = this.selectionStart, end = this.selectionEnd;
                        this.value = val.substring(0, start) + '\t' + val.substring(end);
                        this.selectionStart = this.selectionEnd = start + 1;
                        return false;
                    }
                };

                this.refs.input.style.tabSize = opts.allowtabs;
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            this.trigger('update');
        });

});

riot.tag2('field-time', '<input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="text">', '', '', function(opts) {

        var $this = this;

        this.on('mount', function(){

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            App.assets.require(['/assets/lib/uikit/js/components/timepicker.js'], function() {

                UIkit.timepicker(this.refs.input, opts).element.on('change', function() {
                    $this.refs.input.$setValue($this.refs.input.value);
                });

            }.bind(this));
        });

});

riot.tag2('field-wysiwyg', '<textarea ref="input" class="uk-width-1-1" rows="5" style="height:350px;visibility:hidden;"></textarea>', '', '', function(opts) {

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
                                    form.trigger('submit');
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
                        }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });
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

});

riot.tag2('picoedit', '<div class="picoedit" show="{isReady}"> <div class="picoedit-toolbar uk-flex" if="{path}"> <div class="uk-flex-item-1 uk-text-truncate"> <strong class="uk-text-small"><i class="uk-icon-pencil uk-margin-small-right"></i> {path}</strong> </div> <div> <button type="button" class="uk-button uk-button-primary" onclick="{save}"><i class="uk-icon-save"></i></button> </div> </div> <codemirror ref="codemirror"></codemirror> </div>', 'picoedit .picoedit-toolbar,[data-is="picoedit"] .picoedit-toolbar{ padding-top: 15px; padding-bottom: 15px; }', '', function(opts) {

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

        this.open = function(path) {

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

        function getMode(path) {
            var mode = CodeMirror.findModeByFileName(path).mode || 'text';

            if (mode == 'php') {
                mode = 'application/x-httpd-php';
            }

            return mode;
        }

});

riot.tag2('raw', '<span></span>', '', '', function(opts) {

        var cache = null;

        this.on('mount', function() {
            this.trigger('update');
        });

        this.on('update', function(){

            if (cache==opts.content) return;

            this.root.innerHTML = opts.content;
            cache = opts.content;
        });

});
