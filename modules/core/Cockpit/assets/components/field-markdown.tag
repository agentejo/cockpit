<field-markdown>

    <field-html name="input" markdown="true"></field-html>

    <script>

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

    </script>

</field-markdown>
