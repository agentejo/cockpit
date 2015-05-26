<cockpit-field>

    <div name="fieldcontainer" type="{ field.type }"></div>

    <script>

        var field = opts.field || {},
            type  = field.type || 'text',
            meta  = field.meta || {},
            fc    = 'field-'+type;

        this.field = field;
        this.meta  = meta;

        if (!riot.tags[fc]) {
            fc = 'field-text';
        }

        if (opts.bind) {
            this.fieldcontainer.setAttribute('bind', opts.bind);
        }

        riot.mount(this.fieldcontainer, fc, meta);

    </script>

</cockpit-field>


<field-text>

    <input name="input" class="uk-width-1-1 uk-form-large" type="{ opts.type || 'text' }">

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
        }

        if (opts.required) {
            this.fieldcontainer.setAttribute('required', 'required');
        }

    </script>

</field-text>


<field-password>

    <div class="uk-form-password uk-width-1-1">
        <input name="input" class="uk-width-1-1 uk-form-large" type="password">
        <a href="" class="uk-form-password-toggle" data-uk-form-password>Show</a>
    </div>

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
        }

        this.on('mount', function(){

            App.assets.require(['/assets/lib/uikit/js/components/form-password.js'], function() {

                UIkit.init(this.root);

            }.bind(this));
        });

    </script>

</field-password>

<field-boolean>

    <input type="checkbox" name="input" class="uk-width-1-1">

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
        }

    </script>

</field-boolean>

<field-longtext>

    <textarea name="input" class="uk-width-1-1 uk-form-large"></textarea>

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
        }

        if (opts.required) {
            this.fieldcontainer.setAttribute('required', 'required');
        }

    </script>

</field-longtext>

<field-select>

    <select name="input" class="uk-width-1-1 uk-form-large">
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
        }

        if (opts.required) {
            this.fieldcontainer.setAttribute('required', 'required');
        }

    </script>

</field-select>

<field-file>

    <div class="uk-flex">
        <input class="uk-flex-item-1 uk-form-large uk-margin-small-right" type="text" name="input">
        <button type="button" class="uk-button uk-button-large" name="picker"><i class="uk-icon-hand-o-up"></i></button>
    </div>

    <script>

        var $this = this, $input = App.$(this.input);

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
        }

        App.$([this.picker, this.input]).on('click', function() {

            App.media.select(function(selected) {
                $input.val(selected[0]).trigger('change');
            });
        });

    </script>

</field-file>
