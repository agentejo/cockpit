<field-file>

    <div class="uk-flex">
        <input class="uk-flex-item-1 uk-margin-small-right" type="text" name="input">
        <button type="button" class="uk-button" name="picker"><i class="uk-icon-hand-o-up"></i></button>
    </div>

    <script>

        var $this = this, $input = App.$(this.input);

        if (opts.bind) {
            this.input.setAttribute('bind', opts.bind);
            this.root.removeAttribute('bind');
        }

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
            App.$(this.picker).addClass(opts.cls);
        }

        App.$([this.picker, this.input]).on('click', function() {

            App.media.select(function(selected) {
                $input.val(selected[0]).trigger('change');
            });
        });

    </script>

</field-file>
