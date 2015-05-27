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
            this.fieldcontainer.setAttribute('required', 'required');
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

<field-boolean>

    <button type="button" name="button" class="uk-button uk-button-{ checked ? 'success':'default'}" onclick="{ toggle }"><i class="uk-icon-check"></i></button>

    <script>

        if (opts.cls) {
            App.$(this.button).addClass(opts.cls.replace(/uk\-form\-/g, 'uk-button-'));
        }

        this.button.innerHTML = opts.label || '<i class="uk-icon-check"></i>';

        this.root.$updateValue = function(value) {

            if (this.checked != value) {

                this.checked = value;
                this.update();
            }

        }.bind(this);

        toggle() {

            if (this.root.$setValue) {
                this.root.$setValue(!this.checked);
            }
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

        if (opts.required) {
            this.fieldcontainer.setAttribute('required', 'required');
        }

    </script>

</field-longtext>

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

        App.$([this.picker, this.input]).on('click', function() {

            App.media.select(function(selected) {
                $input.val(selected[0]).trigger('change');
            });
        });

    </script>

</field-file>
