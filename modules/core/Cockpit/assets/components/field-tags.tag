<field-tags>

    <div>

        <div name="autocomplete" class="uk-autocomplete uk-form-icon uk-form">
            <i class="uk-icon-tag"></i>
            <input name="input" class="uk-width-1-1 uk-form-blank" type="text" placeholder="{ App.i18n.get('Add Tag...') }">
        </div>

        <div class="uk-margin uk-panel uk-panel-box" if="{ tags && tags.length }">
            <span class="uk-margin-small-right uk-margin-small-top" each="{tag,idx in tags}">
                <a onclick="{ parent.remove }"><i class="uk-icon-close"></i></a>
                {{ tag }}
            </span>
        </div>

    </div>

    <script>

        var $this = this;

        this.tags = [];

        this.on('mount', function(){

            if (opts.autocomplete) {
                UIkit.autocomplete(this.autocomplete, {source: opts.autocomplete});
            }

            App.$(this.input).on('keydown', function(e) {

                if (e.keyCode == 13 && $this.input.value.trim()) {

                    e.stopImmediatePropagation();
                    e.stopPropagation();

                    $this.tags.push($this.input.value);
                    $this.input.value = "";
                    $this.$setValue($this.tags)
                    $this.update();

                    return false;
                }
            });
        });

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this.tags !== value) {
                this.tags = value;
                this.update();
            }

        }.bind(this);

        remove(e) {
            this.tags.splice(e.item.idx, 1);
            this.$setValue(this.tags);
        }

    </script>

</field-tags>
