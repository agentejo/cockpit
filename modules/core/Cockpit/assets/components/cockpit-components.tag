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