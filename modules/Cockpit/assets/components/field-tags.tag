<field-tags>

    <style>

        .field-tag {
            display: inline-block;
            border: 1px currentColor solid;
            padding: .4em .5em;
            font-size: .9em;
            border-radius: 3px;
            line-height: 1;
        }

    </style>

    <div class="uk-grid uk-grid-small uk-flex-middle" data-uk-grid-margin="observe:true">

        <div class="uk-text-primary" each="{ _tag,idx in _tags }">
            <span class="field-tag"><i class="uk-icon-tag"></i> { _tag } <a onclick="{ parent.remove }"><i class="uk-icon-close"></i></a></span>
        </div>

        <div>
            <div ref="autocomplete" class="uk-autocomplete uk-form-icon uk-form">
                <i class="uk-icon-tag"></i>
                <input ref="input" class="uk-width-1-1 uk-form-blank" type="text" placeholder="{ App.i18n.get(opts.placeholder || 'Add Tag...') }">
            </div>
        </div>

    </div>

    <script>

        var $this = this;

        this._tags = [];

        this.on('mount', function(){

            if (opts.autocomplete) {

                UIkit.autocomplete(this.refs.autocomplete, {source: opts.autocomplete});
            }

            App.$(this.root).on({

                'selectitem.uk.autocomplete keydown': function(e, data) {

                    var value = e.type=='keydown' ? $this.refs.input.value : data.value;

                    if (e.type=='keydown' && e.keyCode != 13) {
                        return;
                    }

                    if (value.trim()) {

                        $this.refs.input.value = value;

                        e.stopImmediatePropagation();
                        e.stopPropagation();
                        e.preventDefault();
                        $this._tags.push($this.refs.input.value);
                        $this.refs.input.value = "";
                        $this.$setValue(_.uniq($this._tags));
                        $this.update();

                        return false;
                    }
                }
            });
        });

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (this._tags !== value) {
                this._tags = value;
                this.update();
            }

        }.bind(this);

        remove(e) {
            this._tags.splice(e.item.idx, 1);
            this.$setValue(this._tags);
        }

    </script>

</field-tags>
