<cp-gravatar>

    <canvas name="image" class="uk-responsive-width uk-border-circle" width="{ size }" height="{ size }"></canvas>

    <script>

        this.url = '';

        this.on('update', function() {

            this.size  = opts.size || 100;
            this.email = opts.email || '';

            var img = new Image(), url;

            url = '//www.gravatar.com/avatar/'+md5(this.email)+'?d=404&s='+this.size;

            img.onload = function() {
                this.image.getContext("2d").drawImage(img,0,0);
            }.bind(this);

            img.onerror = function() {
                img.src = App.Utils.letterAvatar(opts.alt || '', this.size);
                this.image.getContext("2d").drawImage(img,0,0);
            }.bind(this);

            img.src = url;

        });

    </script>

</cp-gravatar>
