<field-text>

    <input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="{ opts.type || 'text' }" placeholder="{ opts.placeholder }" bind-event="change">
    <div class="uk-text-muted uk-text-small uk-margin-small-top" if="{opts.slug}" title="Slug">
        { slug }
    </div>

    <script>

        this.on('mount', function() {

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }
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
