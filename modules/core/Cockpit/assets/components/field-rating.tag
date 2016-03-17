<field-rating>

    <ul class="uk-grid uk-grid-small">
        <li show="{value}"><a onclick="{removeRating}"><i class="uk-icon-trash-o"></i></a></li>
        <li class="{(!hoverValue && Math.ceil(value) >= n) || (hoverValue && Math.ceil(hoverValue) >= n) ? 'uk-text-primary' : ''}" each={n,idx in ratingRange} onmousemove={hoverRating} onmouseleave={leaveHoverRating} onclick={setRating}><i class="uk-icon-{opts.icon ? opts.icon : 'star'}" title="{ (idx+1) }" data-uk-tooltip></i></li>
        <li show="{value}"><span class="uk-badge">{!hoverValue && value || hoverValue}</span></li>
    </ul>

    <style scoped>
        .uk-grid > * { cursor: pointer; }
    </style>

    <script>

        // Code based on work of https://github.com/attitude

        var mininmum  = opts.mininmum  || 0,
            maximum   = opts.maximum   || 5,
            precision = opts.precision || 0,
            j;

        if (precision < 0 || precision > 0.5) {
            precision = precision - Math.floor(precision);

            if (precision > 0.5) {
                precision = precision - 0.5;
            }
        }

        this.value = null;
        this.hoverValue = null;

        this.ratingRange = [];

        for (j = mininmum + 1; j <= maximum; j = j +1) {
            this.ratingRange.push(j);
        }

        setRating(e) {
            this.$setValue(this.getValue(e));
        }

        getValue(e) {

            var element = App.$(e.target).closest('li')[0];

            if (!element) return;

            if (precision === 0) {
                return e.item.n;
            }

            return Math.floor(((e.item.n - 1) + (Math.floor(e.layerX/element.clientWidth / precision) + 1) * precision) * 1000) / 1000;
        }

        hoverRating(e) {
            this.hoverValue = this.getValue(e);
        }

        leaveHoverRating() {
            this.hoverValue = null;
        }

        removeRating() {
            this.$setValue(null);
        }

        this.$updateValue = function(value) {
            if (value === null && !opts.remove) {
                value = mininmum;
            }

            if (value !== null) {
                if (value < mininmum) {
                    value = mininmum;
                }

                if (value > maximum) {
                    value = maximum;
                }
            }

            if (this.value != value) {

                this.value = value;
                this.update();
            }

        }.bind(this);

    </script>

</field-rating>
