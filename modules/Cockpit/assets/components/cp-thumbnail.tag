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

            var _src = opts.src || opts.riotSrc || opts['riot-src'];
            var mode = opts.mode || 'bestFit';

            if (!_src || src === _src) {
                return;
            }

            this.refs.spinner.style.display = '';
            
            src = _src;

            requestAnimationFrame(function() {

                if (_src.match(/^(http\:|https\:|\/\/)/) && !(_src.includes(ASSETS_URL) || _src.includes(SITE_URL))) {

                    src = _src;

                    setTimeout(function() {
                        $this.updateCanvasDim(_src)
                    }, 50);

                    return;
                }
                
                var url;
                
                if (_src.match(/\.(svg|ico)$/i)) {
                    url = _src;
                } else {
                    url = App.route(`/cockpit/utils/thumb_url?src=${_src}&w=${opts.width}&h=${opts.height}&m=${mode}&o=1`);
                }
                
                var img = new Image();
                
                img.onload = function() {    
                    
                    setTimeout(function() {
                        $this.updateCanvasDim(img)
                    }, 0);
                }
                
                img.onerror = function() {
                    //console.log(`error ${url}`)
                }
                
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

    </script>

</cp-thumbnail>
