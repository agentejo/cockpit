<cp-gravatar>

    <img name="image" class="uk-border-circle" riot-src="//www.gravatar.com/avatar/{ md5(email) }?d=mm&s={ size }" width="{ size }" height="{ size }" alt="avatar">

    <script>

        this.on('update', function() {
            this.size  = opts.size || 100;
            this.email = opts.email || '';
        });

    </script>

</cp-gravatar>
