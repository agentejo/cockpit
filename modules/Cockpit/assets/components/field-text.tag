<field-text>

    <input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="{ opts.type || 'text' }" placeholder="{ opts.placeholder }">
    <div class="uk-text-muted uk-text-small uk-margin-small-top" if="{opts.slug}" title="Slug">
        { slug }
    </div>

    <script>

        var $this = this;

        this.on('mount', function() {

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            if (opts.slug) {
                this.slug = this.$getValue(opts.bind+'_slug') || '';
            }

            (['maxlength', 'minlength', 'step', 'placeholder', 'pattern', 'size']).forEach( function(key) {
                if (opts[key]) $this.refs.input.setAttribute(key, opts[key]);
            });

            this.update();
        });

        this.$updateValue = function(value) {

            if (opts.slug) {
                this.slug = App.Utils.sluggify(value || '');
                this.$setValue(this.slug, false, opts.bind+'_slug');
                this.update();
            }

        }.bind(this);

    </script>

</field-text>
