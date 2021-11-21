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

    <div if="{loading}"><i class="uk-icon-spinner uk-icon-spin"></i></div>
    <div show="{!loading}" class="uk-grid uk-grid-small uk-flex-middle" data-uk-grid-margin="observe:true">

        <div class="uk-text-primary" each="{ _tag,idx in _tags }">
            <span class="field-tag"><i class="uk-icon-tag"></i> { _tag } <a onclick="{ parent.remove }"><i class="uk-icon-close"></i></a></span>
        </div>

        <div show="{allowInput}">
            <div ref="autocomplete" class="uk-autocomplete uk-form-icon uk-form">
                <i class="uk-icon-tag"></i>
                <input ref="input" class="uk-width-1-1 uk-form-blank" type="text" placeholder="{ App.i18n.get(opts.placeholder || 'Add Tag...') }">
            </div>
        </div>

    </div>

    <script>

        var $this = this;

        this._tags = [];
        this.allowInput = true;
        this.autocompleteOptions = [];
        this.loading = 0;

        this.on('mount', function(){
            var _source = [];
            if (Array.isArray(opts.autocomplete) && opts.autocomplete.length && !opts.autocomplete[0].value) {
                opts.autocomplete.forEach(function(value) {
                    $this.autocompleteOptions.push({value: value});
                });
            } else if(opts.autocomplete) {
                _source = opts.autocomplete;
            }

            $this.autocomplete = UIkit.autocomplete(this.refs.autocomplete, {
                source: _source,
                delay: typeof opts.delay === "number" ? opts.delay : undefined,
                minLength: typeof opts.minLength === "number" ? opts.minLength : 1
            });

            var sources = [];
            if (Array.isArray(opts.src)) {
                sources = opts.src;
            } else if (opts.src && opts.src.url && opts.src.value) {
                sources.push(opts.src);
            }

            sources.forEach(function (src) {
                if (src && src.url && src.value) {

                    $this.loading++;

                    var url = src.url;
                    var fieldVal = src.value;

                    if (url.match('^collection=')) {
                        url = '/collections/find?' + url;
                    }

                    App.request(url).then(function (data) {

                        $this.loading--;

                        if (url.match('^\/collections\/find\?')) {
                            data = data.entries;
                        }

                        if (!Array.isArray(data)) {
                            $this.update();
                            return;
                        }

                        data.forEach(function (item) {
                            var value = _.get(item, fieldVal);
                            if (Array.isArray(value)) {
                                value.forEach(function (val) {
                                    $this.autocompleteOptions.push({value: val});
                                });
                            } else if (typeof value == "string") {
                                $this.autocompleteOptions.push({value: value});
                            }
                        });

                        $this.update();
                    })
                }

            });

            if (opts.minLength === 0) {
                this.refs.input.onfocus = function(e) {
                    $this.autocomplete.handle();
                    $this.autocomplete.show();
                };
            }

            this.update();
        });

        this.on('update', function(){
            if ($this.autocomplete && $this.autocompleteOptions.length > 0) {
                $this.autocomplete.options.source = _.sortBy($this.autocompleteOptions, ["value"]);
                $this.autocomplete.options.source = _.uniqBy($this.autocomplete.options.source, function (e) {return e.value});
                $this.autocomplete.options.source = _.filter($this.autocomplete.options.source, function (e) { return $this._tags.indexOf(e.value) === -1;  });
            }

            if ($this.opts.limit) {
                $this.allowInput = $this._tags.length < $this.opts.limit;
            }

            App.$(this.root).on({

                'selectitem.uk.autocomplete': function() {
                    setTimeout(function(){
                        $this.refs.input.value = '';
                    }, 0)
                },

                'selectitem.uk.autocomplete keydown': function(e, data) {
                    var value = e.type === 'keydown' ? $this.refs.input.value : data.value;

                    if (e.type === 'keydown' && e.keyCode !== 13 && e.key !== ",") {
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
