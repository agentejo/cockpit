<cp-field>

    <script>

        this._field = null;

        this.on('update', function() {

            if (opts.bind != this._field) {

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

    </script>

</cp-field>
