<cockpit-field>

    <div name="fieldcontainer" type="{ opts.type }"></div>

    <script>

        var type  = opts.type || 'text',
            field = 'field-'+type;

        if (!riot.tags[field]) {
            field = 'field-text';
        }

        if (opts.bind) {
            this.fieldcontainer.setAttribute('bind', opts.bind);
        }

        riot.mount(this.fieldcontainer, field);

    </script>

</cockpit-field>


<field-text>

    <input name="input" class="uk-width-1-1 uk-form-large" type="{ opts.type || 'text' }">

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
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

    </script>

</field-longtext>
