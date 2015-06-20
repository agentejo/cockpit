<field-set>

    <div>

        <div class="uk-alert" if="{!fields.length}">
            { App.i18n.get('Fields definition is missing') }
        </div>

        <div class="uk-margin" each="{field,idx in fields}">
            <label><span class="uk-badge">{ field.label || field.name || ''}</span></label>
            <cp-field class="uk-width-1-1" field="{field}" bind="{opts.bind}.{field.name}"></cp-field>
        </div>

    </div>

    <script>

        this.set    = opts.multiple ? []:{};
        this.fields = opts.fields || [];

    </script>

</field-set>
