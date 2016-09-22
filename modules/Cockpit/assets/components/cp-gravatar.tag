<cp-gravatar>

    <canvas name="image" class="uk-responsive-width uk-border-circle" width="{ size }" height="{ size }"></canvas>

    <script>

        this.url = '';

        this.on('update', function() {

            this.size  = opts.size || 100;
            this.email = opts.email || '';

            var img = new Image(), url, release = function() {
                setTimeout(function() {
                    this.image.getContext("2d").drawImage(img,0,0);
                    sessionStorage[url] = this.image.toDataURL();
                }.bind(this), 10);
            }.bind(this);
            
            url = '//www.gravatar.com/avatar/'+md5(this.email)+'?d=404&s='+this.size;

            img.crossOrigin = 'Anonymous';

            img.onload = function() {
                release();
            }.bind(this);

            img.onerror = function() {
                img.src = App.Utils.letterAvatar(opts.alt || '', this.size);
                release();
            }.bind(this);

            img.src = sessionStorage[url] || url;

        });

    </script>

</cp-gravatar>
