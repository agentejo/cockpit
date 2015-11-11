<cp-thumbnail>

    <span class="uk-position-relative">
        <i name="spinner" class="uk-icon-spinner uk-icon-spin uk-position-absolute"></i>
        <canvas name="canvas" class="uk-responsive-width" width="{ opts.width || ''}" height="{ opts.height || ''}"></canvas>
    </span>

    <script>

        var $this = this;

        this.on('mount', function(){

            opts.src = opts.src || opts['riot-src'] || opts['riotSrc'];

            App.request('/cockpit/utils/thumb_url', {src:opts.src,w:opts.width,h:opts.height}, 'text').then(function(url){

                var img = new Image();

                img.onload = function() {
                    //App.$($this.canvas).replaceWith('<img src="'+url+'">');
                    $this.canvas.getContext("2d").drawImage(img,0,0);
                    $this.spinner.classList.add('uk-hidden');
                };

                img.src = url;
            });
        });

    </script>

</cp-thumbnail>
