<field-set>

    <div>

        <div class="uk-alert" if="{!fields.length}">
            { App.i18n.get('Fields definition is missing') }
        </div>

        <div class="uk-margin" each="{field,idx in fields}">
            <label><span class="uk-badge">{ field.label || field.name || ''}</span></label>
            <cockpit-field class="uk-width-1-1" field="{field}" bind="{parent.bind}.{field.name}"></cockpit-field>
        </div>

    </div>

    <script>

        this.set    = opts.multiple ? []:{};
        this.fields = opts.fields || [];

        this.bind = opts.bind || '';

        if (opts.bind) {
            this.root.removeAttribute('bind');
        }

    </script>

</field-set>