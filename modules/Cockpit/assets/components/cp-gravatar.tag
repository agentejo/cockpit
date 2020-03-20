<cp-gravatar>

    <canvas ref="image" class="uk-responsive-width" width="{ size }" height="{ size }"></canvas>

    <script>

        var $this = this;

        this.size  = opts.size || 100;

        this.on('mount', function(){
            this.update();
        });

        this.on('update', function() {

            this.size = opts.size || 100;

            var img = new Image();

            img.onload = function() {
                $this.refs.image.getContext('2d').drawImage(img,0,0);
            };

            img.src = App.Utils.letterAvatar(opts.alt || '', this.size);
        });

    </script>

</cp-gravatar>
