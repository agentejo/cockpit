<cp-thumbnail>

    <div class="uk-position-relative">
        <i ref="spinner" class="uk-icon-spinner uk-icon-spin uk-position-center"></i>
        <canvas ref="canvas" width="{ opts.width || ''}" height="{ opts.height || ''}"></canvas>
    </div>

    <script>

        var $this = this, src;

        this.on('mount', function() {
            this.trigger('update');
        })

        this.on('update', function(){

            opts.src = opts.src || opts['riot-src'] || opts['riotSrc'];

            var mode = opts.mode || 'bestFit';

            if (!opts.src || src == opts.src) {
                return;
            }

            $this.refs.spinner.classList.remove('uk-hidden');
            $this.refs.canvas.getContext("2d").clearRect(0, 0, $this.refs.canvas.width, $this.refs.canvas.height);

            App.request('/cockpit/utils/thumb_url', {src:opts.src,w:opts.width,h:opts.height,m:mode}, 'text').then(function(url){

                App.$($this.refs.canvas).css({
                    background: '50% 50% url('+url+') no-repeat',
                    backgroundSize: 'contain'  
                });

                $this.refs.spinner.classList.add('uk-hidden');

                src = opts.src;
            }).catch(function(e){

            });
        });

    </script>

</cp-thumbnail>
