<field-textarea>
    
    <style>
        [ref="lengthIndicator"] {
            font-family: monospace;
        }
    </style>


    <textarea ref="input" class="uk-width-1-1 {opts.cls}" bind="{opts.bind}" bind-event="input" riot-rows="{opts.rows || 10}" riot-placeholder="{ opts.placeholder || 'Text...' }"></textarea>
    <div class="uk-text-right uk-text-small uk-text-muted" ref="lengthIndicator" hide="{opts.showCount === false}"></div>
    
    
    <script>

        var $this = this;

        this.on('mount', function() {

            if (opts.allowtabs) {

                this.refs.input.onkeydown = function(e) {
                    if (e.keyCode === 9) {
                        var val = this.value, start = this.selectionStart, end = this.selectionEnd;
                        this.value = val.substring(0, start) + '\t' + val.substring(end);
                        this.selectionStart = this.selectionEnd = start + 1;
                        return false;
                    }
                };

                this.refs.input.style.tabSize = opts.allowtabs;
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            (['maxlength', 'minlength', 'placeholder', 'cols', 'rows']).forEach( function(key) {
                if (opts[key]) $this.refs.input.setAttribute(key, opts[key]);
            });
            
            this.updateLengthIndicator();

            this.update();
        });
        
        this.$updateValue = function(value) {
            this.updateLengthIndicator();
        }.bind(this);
        
        
        this.updateLengthIndicator = function() {
            
            if (opts.showCount === false) {
                return;
            }
            
            this.refs.lengthIndicator.innerText = Math.abs((opts.maxlength || 0) - this.refs.input.value.length);
        }

    </script>

</field-textarea>
