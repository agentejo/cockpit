<cp-field>

    <div ref="field" data-is="{ 'field-'+opts.type }" bind="{ opts.bind }" cls="{ opts.cls }"></div>

    <script>

        this.on('mount', function() {
            this.parent.update();
        });

        this.on('update', function() {

            this.refs.field.opts.bind = opts.bind;
            this.refs.field.opts.bind = opts.opts || {};

            if (opts.opts) {
                App.$.extend(this.refs.field.opts, opts.opts);
            }

            this.refs.field.update();
        });

    </script>
</cp-field>
