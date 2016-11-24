<field-markdown>

    <field-html ref="input" markdown="true" bind="{ opts.bind }" height="{opts.height}"></field-html>

    <script>
        
        this.on('mount', function() { this.trigger('update'); });
        this.on('update', function() { if (opts.opts) App.$.extend(opts, opts.opts); });

    </script>

</field-markdown>
