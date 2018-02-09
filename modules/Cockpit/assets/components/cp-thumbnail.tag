<cp-thumbnail>

    <div class="uk-position-relative">
        <i ref="spinner" class="uk-icon-spinner uk-icon-spin uk-position-center"></i>
        <canvas ref="canvas" width="{ this.width || ''}" height="{ this.height || ''}" style="background-size:contain;background-position:50% 50%;background-repeat:no-repeat;visibility:hidden;"></canvas>
    </div>

    <script>

        var $this = this, src;

        this.inView = false;
        this.width  = opts.width;
        this.height = opts.height;

        this.on('mount', function() {

            if (!('IntersectionObserver' in window)) {
                this.load();
                return;
            }

            var observer = new IntersectionObserver(function(entries, observer) {

                if (!entries[0].intersectionRatio) return;

                if (opts.src || opts.riotSrc || opts['riot-src']) {
                    $this.inView = true;
                    $this.load();
                    observer.unobserve($this.refs.canvas);
                }

            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            setTimeout(function() {
                observer.observe($this.refs.canvas);
            }, 50);
        });

        this.on('update', function() {
            if (this.inView) {
                this.load();
            }
        })

        this.load = function() {

            var _src = opts.src || opts.riotSrc || opts['riot-src'];
            var mode = opts.mode || 'bestFit';

            if (!_src || src === _src) {
                return;
            }

            $this.refs.spinner.style.display = '';

            requestAnimationFrame(function() {

                if (_src.match(/^(http\:|https\:|\/\/)/)) {

                    src = _src;

                    setTimeout(function() {
                        $this.updateCanvasDim(_src)
                    }, 50);

                    return;
                }

                App.request('/cockpit/utils/thumb_url', {src:_src,w:opts.width,h:opts.height,m:mode}, 'text').then(function(url){

                    if (_src.match(/\.svg$/i)) {
                        url = _src;
                    }

                    src = _src;

                    setTimeout(function() {
                        $this.updateCanvasDim(url)
                    }, 50);

                }).catch(function(e){});
            });
        };

        this.updateCanvasDim = function(url) {

            if (!App.$($this.root).closest('body').length) return;

            var img = new Image();

            img.src = url

            setTimeout(function() {

                $this.width = img.width;
                $this.height = img.height;

                App.$($this.refs.canvas).css({
                    backgroundImage: 'url('+url+')',
                    visibility: 'visible'
                });

                $this.refs.spinner.style.display = 'none';
                $this.update();

            }, 50);

            $this.refs.canvas
        }

    </script>

</cp-thumbnail>
