<cp-thumbnail>

    <div class="uk-position-relative">
        <i ref="spinner" class="uk-icon-spinner uk-icon-spin uk-position-center"></i>
        <canvas ref="canvas" width="{ this.width || ''}" height="{ this.height || ''}" style="background-size:contain;background-position:50% 50%;background-repeat:no-repeat;visibility:hidden;"></canvas>
    </div>

    <script>

        var $this = this, src, cache = {};

        this.inView = false;
        this.width  = opts.width;
        this.height = opts.height;

        this.on('mount', function() {

            if (!('IntersectionObserver' in window)) {
                this.inView = true;
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
            if (this.inView) this.load();
        })

        this.load = function() {

            var _src = opts.src || opts.riotSrc || opts['riot-src'], img, mode = opts.mode || 'bestFit';

            if (!_src || src === _src) {
                return;
            }

            this.refs.spinner.style.display = '';

            this.getUrl(_src, mode).then(function(url) {

                img = new Image();
                img.onload = function() {    
                    
                    setTimeout(function() {
                        $this.updateCanvasDim(img)
                    }, 0);
                }
                
                img.onerror = function() {}

                img.src = url;
            });
        };

        this.updateCanvasDim = function(img) {

            if (!App.$(this.root).closest('body').length) return;

            setTimeout(function() {

                this.width = img.width;
                this.height = img.height;

                this.refs.canvas.width = img.width;
                this.refs.canvas.height = img.height;

                App.$(this.refs.canvas).css({
                    backgroundImage: 'url('+img.src+')',
                    visibility: 'visible'
                });

                if (!$this.refs.spinner.style) {
                    return;
                }

                this.refs.spinner.style.display = 'none';
                //this.update();

            }.bind(this), 0);

        }

        getUrl(url, mode) {

            var key = `${url}:${mode}`;

            if (!cache[key]) {

                cache[key] = new Promise(function(resolve) {
                    
                    if (url.match(/^(http\:|https\:|\/\/)/) && !(url.includes(ASSETS_URL) || url.includes(SITE_URL))) {
                        resolve(url);
                        return;
                    }
                    
                    if (!url.match(/\.(svg|ico)$/i)) {
                        url = App.route(`/cockpit/utils/thumb_url?src=${url}&w=${opts.width}&h=${opts.height}&m=${mode}&re=1`);
                    }
                    
                    resolve(url);
                });
            }

            return cache[key];
        }

    </script>

</cp-thumbnail>
