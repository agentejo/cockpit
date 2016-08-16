<field-file>

    <div class="uk-panel uk-panel-box uk-panel-card">
        { input.$value }
        <button type="button" class="uk-button uk-margin-small-right" name="picker" title="{ App.i18n.get('Pick file') }"><i class="uk-icon-paperclip"></i></button>
        <input class="uk-form-blank" type="text" name="input" bind="{ opts.bind }" placeholder="{ opts.placeholder || App.i18n.get('No file selected...') }">
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
