<field-text>

    <style>

        [ref="input"][type=text] {
            padding-right: 30px !important;
        }

        .field-text-container span {
            position: absolute;
            top: 50%;
            right: 0;
            font-family: monospace;
            transform: translateY(-50%) scale(.9);
        }
    </style>

    <div class="uk-position-relative field-text-container">
        <input ref="input" class="uk-width-1-1" bind="{opts.bind}" type="{ opts.type || 'text' }" oninput="{updateLengthIndicator}" placeholder="{ opts.placeholder }">
        <span class="uk-text-muted" ref="lengthIndicator" show="{type=='text'}" hide="{opts.showCount === false}"></span>
    </div>

    <div class="uk-text-muted uk-text-small uk-margin-small-top" if="{opts.slug}" title="Slug">
        { slug }
    </div>

    <script>

        var $this = this;

        this.on('mount', function() {

            this.type = opts.type || 'text';

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
            }

            if (opts.required) {
                this.refs.input.setAttribute('required', 'required');
            }

            if (opts.slug) {
                this.slug = this.$getValue(opts.bind+'_slug') || '';
            }

            (['maxlength', 'minlength', 'step', 'placeholder', 'pattern', 'size', 'min', 'max']).forEach( function(key) {
                if (opts[key]) $this.refs.input.setAttribute(key, opts[key]);
            });

            this.updateLengthIndicator();

            this.update();
        });

        this.$updateValue = function(value) {

            if (opts.slug) {
                this.slug = App.Utils.sluggify(value || '');
                this.$setValue(this.slug, false, opts.bind+'_slug');
                this.update();
            }

            this.updateLengthIndicator();

        }.bind(this);

        this.updateLengthIndicator = function() {

            if (this.type != 'text' || opts.showCount === false) {
                return;
            }

            this.refs.lengthIndicator.innerText = Math.abs((opts.maxlength || 0) - this.refs.input.value.length);
        }

    </script>

</field-text>
