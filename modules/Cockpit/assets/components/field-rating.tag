<field-rating>

    <ul class="uk-grid uk-grid-small">
        <li class="{(!hoverValue && Math.ceil(value) >= n) || (hoverValue && Math.ceil(hoverValue) >= n) ? 'uk-text-primary' : ''}" each="{n,idx in ratingRange}" onmousemove="{hoverRating}" onmouseleave="{leaveHoverRating}" onclick="{setRating}" style="cursor:pointer;"><i class="uk-icon-{opts.icon ? opts.icon : 'star'}" title="{ (idx+1) }" data-uk-tooltip></i></li>
        <li show="{value}"><span class="uk-badge">{!hoverValue && value || hoverValue}</span></li>
        <li show="{value}"><a class="uk-text-danger" onclick="{removeRating}"><i class="uk-icon-trash-o"></i></a></li>
    </ul>

    <script>

        // Code based on work of https://github.com/attitude

        this.value = null;
        this.hoverValue = null;
        this.ratingRange = [];

        this.on('mount', function() {

            this.mininmum  = opts.mininmum  || 0;
            this.maximum   = opts.maximum   || 5;
            this.precision = opts.precision || 0;

            if (this.precision < 0 || this.precision > 0.5) {
                this.precision = this.precision - Math.floor(this.precision);

                if (this.precision > 0.5) {
                    this.precision = this.precision - 0.5;
                }
            }

            for (var j = this.mininmum + 1; j <= this.maximum; j = j +1) {
                this.ratingRange.push(j);
            }

            this.update();
        });

        setRating(e) {
            this.$setValue(this.getValue(e));
        }

        getValue(e) {

            var element = App.$(e.target).closest('li')[0];

            if (!element) return;

            if (this.precision === 0) {
                return e.item.n;
            }

            return Math.floor(((e.item.n - 1) + (Math.floor(e.layerX/element.clientWidth / this.precision) + 1) * this.precision) * 1000) / 1000;
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
                value = this.mininmum;
            }

            if (value !== null) {

                if (value < this.mininmum) {
                    value = this.mininmum;
                }

                if (value > this.maximum) {
                    value = this.maximum;
                }
            }

            if (this.value != value) {
                this.value = value;
                this.update();
            }

        }.bind(this);

    </script>

</field-rating>
