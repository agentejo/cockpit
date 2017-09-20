<field-file>

    <div class="uk-panel uk-panel-box uk-panel-card">
        <button type="button" class="uk-button uk-margin-small-right" ref="picker" title="{ App.i18n.get('Pick file') }"><i class="uk-icon-paperclip"></i></button>
        <input class="uk-form-blank" type="text" ref="input" bind="{ opts.bind }" placeholder="{ opts.placeholder || App.i18n.get('No file selected...') }">
    </div>

    <script>

        this.on('mount', function() {

            var $this = this, $input = App.$(this.refs.input);

            if (opts.cls) {
                App.$(this.refs.input).addClass(opts.cls);
                App.$(this.refs.picker).addClass(opts.cls);
            }

            App.$(this.refs.picker).on('click', function() {

                App.media.select(function(selected) {
                    $this.refs.input.$setValue(selected[0]);
                });
            });
        });

    </script>

</field-file>
