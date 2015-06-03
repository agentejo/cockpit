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

        if (opts.rows) {
            this.input.setAttribute('rows', opts.rows);
        }

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        if (opts.allowtabs) {

            this.input.onkeydown = function(e) {
                if (e.keyCode === 9) {
                    var val = this.value, start = this.selectionStart, end = this.selectionEnd;
                    this.value = val.substring(0, start) + '\t' + val.substring(end);
                    this.selectionStart = this.selectionEnd = start + 1;
                    return false;
                }
            };

            this.input.style.tabSize = opts.allowtabs;
        }

    </script>

</field-longtext>
