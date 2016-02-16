(function(){

    /**
     * source: http://codepen.io/tmrDevelops/pen/MavRdZ
     */

    function Stars(c, color) {

        color = color || '#222';

        var $ = c.getContext("2d");

        var w = c.width = window.innerWidth;
        var h = c.height = window.innerHeight;

        var arr = [];
        var u = 0;
        var dep = w;
        var dp = 0.70;
        var ms = {
            x: 0,
            y: 0
        };
        var msd = {
            x: 0,
            y: 0
        };

        function Obj(x, y, z) {
            this.set(x, y, z);
        }

        Obj.prototype = {
            set: function(x, y, z) {
                this.x = x || 0;
                this.y = y || 0;
                this.z = z || 0;
            },
            rotX: function(ang) {
                var y = this.y;
                var z = this.z;
                this.y = y * Math.cos(ang) - z * Math.sin(ang);
                this.z = z * Math.cos(ang) + y * Math.sin(ang);
            },
            rotY: function(ang) {
                var x = this.x;
                var z = this.z;
                this.x = x * Math.cos(ang) - z * Math.sin(ang);
                this.z = z * Math.cos(ang) + x * Math.sin(ang);
            },
            rotZ: function(ang) {
                var x = this.x;
                var y = this.y;
                this.x = x * Math.cos(ang) - y * Math.sin(ang);
                this.y = y * Math.cos(ang) + x * Math.sin(ang);
            }
        };

        function Part(x, y, z) {
            this.op = new Obj(x, y, z);
            this.rp = new Obj(x, y, z);
            this.rot = new Obj();
            this.vel = new Obj();
            this.col = 'hsla(216,95%,85%,'+rnd(0.5, 1)+')';
        }

        function upd(rot) {
            var pos;
            var rot;
            var vel;
            var op;
            var rp;
            var col;
            var size;
            for (var i in arr) {
                op = arr[i].op;
                rp = arr[i].rp;
                rot = arr[i].rot;
                vel = arr[i].vel;
                col = arr[i].col;
                vel.x += msd.x * 0.15;
                vel.y += msd.y * 0.15;
                rp.set(op.x, op.y, op.z);

                rot.x += vel.x;
                rot.y += vel.y;
                rot.z += vel.z;

                rot.x = rot.x > Math.PI * 2 ? 0 : rot.x;
                rot.y = rot.y > Math.PI * 2 ? 0 : rot.y;

                rp.rotY(rot.y);
                rp.rotX(rot.x);

                vel.set(
                  vel.x * dp,
                  vel.y * dp,
                  0
                );
            }
            msd.x = 0.0005;
            msd.y = 0.0005;
        }

        function draw() {

            var p, dth;
            for (var i in arr) {
                p = arr[i];
                dth = ((p.rp.z / dep) + 1);
                $.fillStyle = p.col;
                $.fillRect(w + p.rp.x, h + p.rp.y, rnd(dth/0.8, dth/2),  dth/0.9);
            }
        }

        function rnd(min, max) {
            return Math.random() * (max - min) + min;
        }

        function go() {
            for (var i = 0; i < 6800; i++) {
            var d = new Part(
              rnd(-w, h),
              rnd(-w, h),
              rnd(-dep, dep)
            );
            d.vel.set(0, 0, 0);
            arr.push(d);
            }
        }

        window.addEventListener('mousemove', function(e) {
            msd.x = (e.clientY - ms.y) / w;
            msd.y = (e.clientX - ms.x) / h;
            ms.x = e.clientX;
            ms.y = e.clientY;
        }, false);

        window.addEventListener('touchmove', function(e) {
            e.preventDefault();
            msd.x = (e.touches[0].clientY - ms.y) / w;
            msd.y = (e.touches[0].clientX - ms.x) / h;
            ms.x = e.touches[0].clientX;
            ms.y = e.touches[0].clientY;
        }, false);

        window.addEventListener('resize', function(e) {
            c.width = w = window.innerWidth;
            c.height = h = window.innerHeight;
        }, false);

        function run() {
            $.clearRect(0, 0, w, h);
            var g_ = $.createLinearGradient(c.width + c.width,
            c.height + c.height * 1.5,
            c.width + c.width, 1);
            g_.addColorStop(1, color);
            $.globalCompositeOperation = 'normal';
            $.fillStyle = g_;
            $.fillRect(0, 0, w, h);
            $.globalCompositeOperation = 'lighter';
            upd();
            draw();
            window.requestAnimationFrame(run);
        }

        go();
        run();

    }

    window.SKY = function(ele, color) {

        if (!ele) return;

        color = color || '#222';

        var canvas = $('<canvas class="uk-position-absolute uk-position-top uk-display-block"></canvas>').prependTo(ele);

        var c = canvas[0];

        c.width  = $(ele).width();
        c.height = $(ele).height();

        var sky = new Stars(c, color);

        return canvas;
    };

})();
