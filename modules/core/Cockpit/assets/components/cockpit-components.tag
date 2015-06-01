<cockpit-field>

    <div name="fieldcontainer" type="{ field.type }"></div>

    <script>

        var field   = opts.field || {},
            type    = field.type || 'text',
            options = field.options || {},
            fc      = 'field-'+type;

        if (!riot.tags[fc]) {
            fc = 'field-text';
        }

        if (opts.bind) {
            this.fieldcontainer.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

        if (opts.cls) {
            this.fieldcontainer.setAttribute('cls', opts.cls);
        }

        riot.mount(this.fieldcontainer, fc, options);

    </script>

</cockpit-field>


<field-text>

    <input name="input" class="uk-width-1-1" type="{ opts.type || 'text' }">

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

    </script>

</field-text>


<field-password>

    <div class="uk-form-password uk-width-1-1">
        <input name="input" class="uk-width-1-1" type="password">
        <a href="" class="uk-form-password-toggle" data-uk-form-password>Show</a>
    </div>

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/form-password.js'], function() {

                UIkit.init(this.root);

            }.bind(this));
        });

    </script>

</field-password>


<field-html>

    <textarea name="input" class="uk-visibility-hidden"></textarea>

    <script>

        var $this = this;

        this.value = null;

        this.$updateValue = function(value) {

            if (this.value != value) {

                this.value = value;
            }

        }.bind(this);


        this.on('mount', function(){

            App.assets.require([

                '/assets/lib/marked.js',
                '/assets/lib/codemirror/lib/codemirror.js',
                '/assets/lib/uikit/js/components/htmleditor.js'

            ], function() {

                $this.input.value = $this.value;

                var editor = UIkit.htmleditor(this.input, opts);

                editor.on('input', function() {
                    $this.$setValue(editor.editor.getValue());
                });

            }.bind(this));
        });

    </script>

</field-html>

<field-markdown>

    <field-html name="input" markdown="true"></field-html>

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

    </script>

</field-markdown>


<field-date>

    <input name="input" class="uk-width-1-1" type="text">

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/datepicker.js'], function() {

                UIkit.datepicker(this.input, opts);

            }.bind(this));
        });

    </script>

</field-date>

<field-time>

    <input name="input" class="uk-width-1-1" type="text">

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/timepicker.js'], function() {

                UIkit.timepicker(this.input, opts);

            }.bind(this));
        });

    </script>

</field-time>


<field-boolean>

    <button type="button" name="button" class="uk-button uk-button-{ checked ? 'success':'default'}" onclick="{ toggle }">
        <i if="{parent.checked}" class="uk-icon-check"></i>
        <i if="{!parent.checked}" class="uk-icon-times"></i>
    </button>

    <script>

        if (opts.cls) {
            App.$(this.button).addClass(opts.cls.replace(/uk\-form\-/g, 'uk-button-'));
        }

        this.button.innerHTML = opts.label || '<i class="uk-icon-check"></i>';

        this.$updateValue = function(value) {

            if (this.checked != value) {

                this.checked = value;
                this.update();
            }

        }.bind(this);

        toggle() {
            this.$setValue(!this.checked);
        }

    </script>

</field-boolean>

<field-longtext>

    <textarea name="input" class="uk-width-1-1"></textarea>

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

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

    </script>

</field-longtext>

<field-object>

    <textarea name="input" class="uk-width-1-1" onchange="{ change }"></textarea>

    <script>

        this.value = {};

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        this.input.setAttribute('rows', opts.rows || 5);
        this.input.setAttribute('style', 'font-family: monospace;tab-size:2;');

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.input.onkeydown = function(e) {

            if (e.keyCode === 9) {
                var val = this.value, start = this.selectionStart, end = this.selectionEnd;
                this.value = val.substring(0, start) + '\t' + val.substring(end);
                this.selectionStart = this.selectionEnd = start + 1;
                return false;
            }
        };

        this.$updateValue = function(value) {

            if (JSON.stringify(this.value) != JSON.stringify(value)) {

                this.value = value;
                this.update();
            }

        }.bind(this);

        change() {
            this.$setValue(App.Utils.str2json(this.input.value) || this.value);
        }

        this.on('update', function() {
            this.input.value = JSON.stringify(this.value, null, 2);
        });


    </script>

</field-object>

<field-select>

    <select name="input" class="uk-width-1-1">
        <option value=""></option>
        <option each="{ option,idx in options }" value="{ option }">{ option }</option>
    </select>

    <script>

        this.options = opts.options || []

        if (typeof(this.options) === 'string') {

            var options = [];

            this.options.split(',').forEach(function(option) {
                options.push(option.trim());
            });

            this.options = options;
        }

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.required) {
            this.fieldcontainer.setAttribute('required', 'required');
        }

    </script>

</field-select>

<field-file>

    <div class="uk-flex">
        <input class="uk-flex-item-1 uk-margin-small-right" type="text" name="input">
        <button type="button" class="uk-button" name="picker"><i class="uk-icon-hand-o-up"></i></button>
    </div>

    <script>

        var $this = this, $input = App.$(this.input);

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
            App.$(this.picker).addClass(opts.cls);
        }

        App.$([this.picker, this.input]).on('click', function() {

            App.media.select(function(selected) {
                $input.val(selected[0]).trigger('change');
            });
        });

    </script>

</field-file>
