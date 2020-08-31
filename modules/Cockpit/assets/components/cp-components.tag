<cp-field>

    <div ref="field" data-is="{ 'field-'+opts.type }" bind="{ opts.bind }"></div>

    <script>

        this.on('mount', function() {

            var o = opts.opts || {};

            if (this.root.$value == undefined && o.default !== undefined) {
                this.$setValue(o.default);
            }

            if (this.root.$value == undefined) {
                this.$setValue(null);
            }

            if (o.disabled) {
                this.root.classList.add('uk-disabled');
            }

            this.parent.update();
        });

        this.on('update', function() {

            this.refs.field.opts.bind = opts.bind;

            if (opts.required) this.refs.field.opts.required = opts.required;

            if (opts.opts) {
                App.$.extend(this.refs.field.opts, opts.opts);
            }

            this.refs.field.update();
        });

    </script>
</cp-field>

<cp-preloader>

    <div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
    </div>

    <style media="screen">

        cp-preloader {
            display: block;
            position: relative;
            width: 40px;
            height: 40px;
        }

        cp-preloader[size="large"] {
            width: 80px;
            height: 80px;
        }

        cp-preloader[size="small"] {
            width: 20px;
            height: 20px;
        }

        cp-preloader > div {
            position: absolute;
            width: 100%;
            height: 100%;
            animation: preloader-rotate-elements 8000ms infinite linear;
        }

        cp-preloader div div {
            border-radius: 50%;
            transform: scale(0.1);
            opacity: 0.1;
        }

        cp-preloader div div:nth-child(1) {
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 50%;
            background: #03A9F4;
            animation: preloader-pulse-elements 1000ms infinite ease alternate;
            animation-delay: 0;
        }

        cp-preloader div div:nth-child(2) {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 50%;
            background: #F44336;
            animation: preloader-pulse-elements 1000ms infinite ease alternate;
            animation-delay: 250ms;
        }

        cp-preloader div div:nth-child(3) {
            position: absolute;
            top: 50%;
            left: 0;
            width: 50%;
            height: 50%;
            background: #8BC34A;
            animation: preloader-pulse-elements 1000ms infinite ease alternate;
            animation-delay: 500ms;
        }

        cp-preloader div div:nth-child(4) {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 50%;
            height: 50%;
            background: #FFC107;
            animation: preloader-pulse-elements 1000ms infinite ease alternate;
            animation-delay: 750ms;
        }

        @keyframes preloader-rotate-elements {
            from { transform: rotate(-180deg); }
            to { transform: rotate(180deg); }
        }

        @keyframes preloader-pulse-elements {
            from {
                top: -50%;
                left: -50%;
                transform: scale(1.0);
                opacity: 0;
            }

            to {
                transform: scale(0.2);
                opacity: 0.8;
            }
        }

    </style>

</cp-preloader>

<cp-preloader-fullscreen>

    <style>

        cp-preloader-fullscreen {
            position: fixed;
            display: flex;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.5);
            z-index: 1000000000000000;
        }

        cp-preloader { display: inline-block; }
    </style>

    <div class="uk-text-center">
        <cp-preloader></cp-preloader>
        <div class="uk-margin-top uk-text-large uk-text-bold" if="{opts.message}">
            { opts.message }
        </div>
    </div>
</cp-preloader-fullscreen>

<cp-inspectobject>

    <style>

        .header {
            padding: 20px;
        }

        pre {
            background: #1C1D21;
            color: #eee;
            border-radius: 0;
            padding: 15px;
            max-width: 100%;
            margin: 0;
            overflow: auto;
        }

        .string { color: #4FB4D7; }
        .number { color: #fff; }
        .boolean { color: #E7CE56;}
        .null {color: #808080;}
        .key {color: #888;}

    </style>

    <div class="uk-offcanvas" ref="offcanvas">

        <div class="uk-offcanvas-bar uk-offcanvas-bar-flip uk-width-3-4 uk-flex uk-flex-column">
            <div class="uk-flex uk-flex-middle header">
                <span class="uk-badge">{opts.title || 'JSON' }</span>
                <a class="uk-margin-left" onclick="{ copyJSON }"><i class="uk-icon-clone"></i></a>
                <div class="uk-flex-item-1 uk-text-right">
                    <a class="uk-offcanvas-close uk-link-muted uk-icon-close"></a>
                </div>
            </div>
            <pre class="uk-text-small uk-flex-item-1" ref="code"></pre>
        </div>

    </div>

    <script>

        this.data = null;

        this.on('mount', function() {

        });

        this.show = function(data) {
            this.data = null;
            this.refs.code.innerHTML = '';

            if (data) {
                this.data = data;
                this.refs.code.innerHTML = this.syntaxHighlight(data);
            } else {
                this.refs.code.innerHTML = 'n/a';
            }

            UIkit.offcanvas.show(this.refs.offcanvas);

            setTimeout(this.update, 100);
        }

        this.syntaxHighlight = function(json) {

            if (typeof json != 'string') {
                json = JSON.stringify(json, undefined, 2);
            }

            var cls;

            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {

                cls = 'number';

                if (/^"/.test(match)) {
                    cls = /:$/.test(match) ? 'key' : 'string';
                } else if (/true|false/.test(match)) {
                    cls = 'boolean';
                } else if (/null/.test(match)) {
                    cls = 'null';
                }

                return '<span class="'+cls+'">'+match+'</span>';
            });
        }

        this.copyJSON = function() {

            App.Utils.copyText(this.refs.code.innerText, function() {
                App.ui.notify("Copied!", "success");
            });
        }

    </script>

</cp-inspectobject>
