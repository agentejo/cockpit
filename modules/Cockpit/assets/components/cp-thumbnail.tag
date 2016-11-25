<cp-thumbnail>

    <span class="uk-position-relative">
        <i ref="spinner" class="uk-icon-spinner uk-icon-spin uk-position-absolute"></i>
        <canvas ref="canvas" class="uk-responsive-width" width="{ opts.width || ''}" height="{ opts.height || ''}"></canvas>
    </span>

    <script>

        var $this = this, src;

        this.on('mount', function() {
            this.trigger('update');
        })

        this.on('update', function(){

            opts.src = opts.src || opts['riot-src'] || opts['riotSrc'];

            if (!opts.src || src == opts.src) {
                return;
            }

            $this.refs.spinner.classList.remove('uk-hidden');

            $this.refs.canvas.getContext("2d").clearRect(0, 0, $this.refs.canvas.width, $this.refs.canvas.height);

            App.request('/cockpit/utils/thumb_url', {src:opts.src,w:opts.width,h:opts.height}, 'text').then(function(url){

                var img = new Image();

                img.onload = function() {
                    $this.refs.canvas.getContext("2d").drawImage(img,0,0);
                    $this.refs.spinner.classList.add('uk-hidden');
                };

                img.src = url;
                src = opts.src;
            }).catch(function(e){

            });
        });

    </script>

</cp-thumbnail>
