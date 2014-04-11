(function($, global){

    function autocomplete(cm) {
        var doc = cm.getDoc(),
            cur = cm.getCursor(),
            toc = cm.getTokenAt(cur),
            mode = CodeMirror.innerMode(cm.getMode(), toc.state).mode.name;

        if(!toc.string.trim()) return;

        if (mode == 'xml') { //html depends on xml

            if(toc.string.charAt(0) == "<" || toc.type == "attribute") {
                CodeMirror.showHint(cm, CodeMirror.hint.html, {completeSingle:false});
            }

        } else if (mode == 'javascript') {
            CodeMirror.showHint(cm, CodeMirror.hint.javascript, {completeSingle:false});
        } else if (mode == 'css' || mode == 'less') {
            CodeMirror.showHint(cm, CodeMirror.hint.css, {completeSingle:false});
        } else {
            CodeMirror.showHint(cm, CodeMirror.hint.anyword, {completeSingle:false});
        }
    };

    var Editor = {

        init: function($scope) {

            if (this.element) {
                return;
            }

            var $this = this;

            this.scope   = $scope;

            this.element = $("#mm-editor");
            this.toolbar = this.element.find("nav");
            this.code    = CodeMirror.fromTextArea(this.element.find("textarea")[0], {
                               lineNumbers: true,
                               styleActiveLine: true,
                               matchBrackets: true,
                               autoCloseBrackets: true,
                               autoCloseTags: true,
                               mode: 'text',
                               theme: 'pastel-on-dark'
                           });

            this.filename = this.element.find(".filename");

            this.code.on("inputRead", $.UIkit.Utils.debounce(function(){
              autocomplete($this.code);
            }, 200));

            this.resize();

            $(window).on("resize", $.UIkit.Utils.debounce(function(){
                $this.resize();
            }, 150));


            this.element.on("click", "[data-editor-action]", function(){

                switch($(this).data("editorAction")) {
                    case "close":
                        $this.close();
                        break;
                    case "save":
                        $this.save();
                        break;
                }
            });

            // key mappings

            this.code.addKeyMap({
                'Ctrl-S': function(){ Editor.save(); },
                'Cmd-S': function(){ Editor.save(); },
                'Esc': function(){ Editor.close(); }
            });
        },

        resize: function(){

            if(!this.element.is(":visible")) {
                return;
            }

            var wrap = this.code.getWrapperElement();

            wrap.style.height = (this.element.height() - this.toolbar.height())+"px";
            this.code.refresh();
        },

        save: function(){

            if(!this.file) {
                return;
            }

            if(!this.file.is_writable) {
                App.notify(App.i18n.get("This file is not writable!"), "danger");
                return;
            }

            this.scope.saveFile(this.file, this.code.getValue());
        },

        show: function(file, content){

            var ext  = file.name.split('.').pop().toLowerCase(),
                mode = "text";

            switch(ext) {
                case 'css':
                case 'sql':
                case 'xml':
                case 'markdown':
                    mode = ext;
                    break;
                case 'less':
                case 'scss':
                    mode = 'css';
                    break;
                case 'js':
                case 'json':
                    mode = 'javascript';
                    break;
                case 'md':
                    mode = 'markdown';
                    break;
                case 'php':
                    mode = 'application/x-httpd-php';
                    break;
                case "txt":
                    mode = 'text';
                    break;
            }

            Editor.code.setOption("mode", mode);

            this.filename.text(file.name);

            this.code.setValue(content);
            this.code.getDoc().clearHistory();

            this.element.show();
            this.resize();

            setTimeout(function(){
                Editor.code.focus();
            }, 50);

            this.file = file;
        },

        close: function(){
            this.file = null;
            this.element.hide();
        }
    };

    global.MMEditor = Editor;

})(jQuery, this);