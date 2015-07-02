<cp-gravatar>

    <img name="image" class="uk-border-circle" src="{ url }" width="{ size }" height="{ size }" alt="{ opts.alt }">

    <script>

        this.url = '';

        this.on('update', function() {

            this.size  = opts.size || 100;
            this.email = opts.email || '';

            var img = new Image(), url;

            url = '//www.gravatar.com/avatar/'+md5(this.email)+'?d=404&s='+this.size;

            img.onload = function() {
                this.image.src = url;
                this.image.style.visibility = '';
            }.bind(this);

            img.onerror = function() {
                this.image.src = App.Utils.letterAvatar(opts.alt || '', this.size);
                this.image.style.visibility = '';
            }.bind(this);

            this.image.style.visibility = 'hidden';
            img.src = url;

        });

    </script>

</cp-gravatar>
