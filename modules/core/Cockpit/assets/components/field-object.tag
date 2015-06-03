<field-object>

    <textarea name="input" class="uk-width-1-1" onchange="{ change }"></textarea>

    <script>

        this.value = {};

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        this.input.setAttribute('rows', opts.rows || 5);
        this.input.setAttribute('style', 'font-family: monospace;tab-size:2;');

        if (opts.required) {
            this.input.setAttribute('required', 'required');
        }

        this.input.onkeydown = function(e) {

            if (e.keyCode === 9) {
                var val = this.value, start = this.selectionStart, end = this.selectionEnd;
                this.value = val.substring(0, start) + '\t' + val.substring(end);
                this.selectionStart = this.selectionEnd = start + 1;
                return false;
            }
        };

        this.$updateValue = function(value) {

            if (JSON.stringify(this.value) != JSON.stringify(value)) {

                this.value = value;
                this.input.value = JSON.stringify(this.value, null, 2);
            }

        }.bind(this);

        change() {
            this.$setValue(App.Utils.str2json(this.input.value) || this.value);
        }

    </script>

</field-object>
