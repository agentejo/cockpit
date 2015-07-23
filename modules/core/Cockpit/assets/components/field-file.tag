<field-file>

    <div class="uk-flex">
        <input class="uk-flex-item-1 uk-margin-small-right" type="text" name="input" bind="{ opts.bind }" placeholder="{ App.i18n.get('No file selected...') }" disabled>
        <button type="button" class="uk-button" name="picker"><i class="uk-icon-paperclip"></i></button>
    </div>

    <script>

        var $this = this, $input = App.$(this.input);

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
            App.$(this.picker).addClass(opts.cls);
        }

        App.$(this.picker).on('click', function() {

            App.media.select(function(selected) {
                $this.input.$setValue(selected[0]);
            });
        });

    </script>

</field-file>
