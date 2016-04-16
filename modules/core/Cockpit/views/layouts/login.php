<!doctype html>
<html class="uk-height-1-1" lang="en" data-base="@base('/')" data-route="@route('/')">
<head>
    <meta charset="UTF-8">
    <title>@lang('Authenticate Please!')</title>
    <link rel="icon" href="@base('/favicon.ico')" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    {{ $app->assets($app['app.assets.base'], $app['cockpit/version']) }}
    {{ $app->assets(['assets:lib/uikit/js/components/form-password.min.js', 'assets:lib/sky.js'], $app['cockpit/version']) }}

    <style>

        .login-container {
            width: 360px;
            max-width: 90%;
        }

        html {
            background: #222228;
        }

        .uk-panel-box-header {
            border-bottom: none;
        }

        .gooeys {
            position: fixed;
            top:0;
            left: 0;
            z-index: -1;
            margin: 0 auto;
        	-webkit-filter: url('#filter');
        	filter: url('#filter');
        }

    </style>

</head>
<body class="login-page uk-height-viewport uk-flex uk-flex-middle uk-flex-center">

    <div class="uk-position-relative login-container" riot-view>

        <form class="uk-form" method="post" action="@route('/auth/check')" onsubmit="{ submit }">

            <div class="uk-panel-box uk-panel-space uk-panel-card uk-nbfc">

                <div name="header" class="uk-panel-box-header uk-text-bold uk-text-center">

                    <h2 class="uk-text-bold uk-text-truncate"><span>{{ $app['app.name'] }}</span></h2>

                    <div class="uk-animation-shake uk-margin-top" if="{ error }">
                        <strong>{ error }</strong>
                    </div>
                </div>

                <div class="uk-form-row">
                    <input name="user" class="uk-form-large uk-width-1-1" type="text" placeholder="@lang('Username')" required>
                </div>

                <div class="uk-form-row">
                    <div class="uk-form-password uk-width-1-1">
                        <input name="password" class="uk-form-large uk-width-1-1" type="password" placeholder="@lang('Password')" required>
                        <a href="#" class="uk-form-password-toggle" data-uk-form-password>@lang('Show')</a>
                    </div>
                </div>

                <div class="uk-margin-large-top">
                    <button class="uk-button uk-button-large uk-button-primary uk-width-1-1">@lang('Authenticate')</button>
                </div>
            </div>

        </form>

        <canvas  class="gooeys" id="canvas"></canvas>
        <svg>
            <defs>
                <filter id="filter">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="5" result="blur"/>
                    <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="filter"/>
                    <feComposite in="SourceGraphic" in2="filter" operator="atop"/>
                </filter>
            </defs>
        </svg>

        <script type="view/script">

            this.error = false;

            submit() {

                this.error = false;

                App.request('/auth/check', {"auth":{"user":this.user.value, "password":this.password.value}}).then(function(data){

                    if (data && data.success) {

                        App.reroute('/');

                    } else {

                        this.error = 'Login failed';

                        App.$(this.header).addClass('uk-bg-danger uk-contrast');
                    }

                    this.update();

                }.bind(this));

                return false;
            }

            this.on('mount', function(){
                this.animation();
            })

            animation() {

                // based on http://codepen.io/Gthibaud/pen/YqGZdj

                var utils = {
                    norm: function(value, min, max) {
                        return (value - min) / (max - min);
                    },

                    lerp: function(norm, min, max) {
                        return (max - min) * norm + min;
                    },

                    map: function(value, sourceMin, sourceMax, destMin, destMax) {
                        return utils.lerp(utils.norm(value, sourceMin, sourceMax), destMin, destMax);
                    },

                    clamp: function(value, min, max) {
                        return Math.min(Math.max(value, Math.min(min, max)), Math.max(min, max));
                    },

                    distance: function(p0, p1) {
                        var dx = p1.x - p0.x,
                            dy = p1.y - p0.y;
                        return Math.sqrt(dx * dx + dy * dy);
                    },

                    distanceXY: function(x0, y0, x1, y1) {
                        var dx = x1 - x0,
                            dy = y1 - y0;
                        return Math.sqrt(dx * dx + dy * dy);
                    },

                    circleCollision: function(c0, c1) {
                        return utils.distance(c0, c1) <= c0.radius + c1.radius;
                    },

                    circlePointCollision: function(x, y, circle) {
                        return utils.distanceXY(x, y, circle.x, circle.y) < circle.radius;
                    },

                    pointInRect: function(x, y, rect) {
                        return utils.inRange(x, rect.x, rect.x + rect.radius) &&
                            utils.inRange(y, rect.y, rect.y + rect.radius);
                    },

                    inRange: function(value, min, max) {
                        return value >= Math.min(min, max) && value <= Math.max(min, max);
                    },

                    rangeIntersect: function(min0, max0, min1, max1) {
                        return Math.max(min0, max0) >= Math.min(min1, max1) &&
                            Math.min(min0, max0) <= Math.max(min1, max1);
                    },

                    rectIntersect: function(r0, r1) {
                        return utils.rangeIntersect(r0.x, r0.x + r0.width, r1.x, r1.x + r1.width) &&
                            utils.rangeIntersect(r0.y, r0.y + r0.height, r1.y, r1.y + r1.height);
                    },

                    degreesToRads: function(degrees) {
                        return degrees / 180 * Math.PI;
                    },

                    radsToDegrees: function(radians) {
                        return radians * 180 / Math.PI;
                    },

                    randomRange: function(min, max) {
                        return min + Math.random() * (max - min);
                    },

                    randomInt: function(min, max) {
                        return min + Math.random() * (max - min + 1);
                    },

                    getmiddle: function(p0, p1) {
                        var x = p0.x,
                            x2 = p1.x;
                        middlex = (x + x2) / 2;
                        var y = p0.y,
                            y2 = p1.y;
                        middley = (y + y2) / 2;
                        pos = [middlex, middley];

                        return pos;
                    },

                    getAngle: function(p0, p1) {
                        var deltaX = p1.x - p0.x;
                        var deltaY = p1.y - p0.y;
                        var rad = Math.atan2(deltaY, deltaX);
                        return rad;
                    },
                    inpercentW: function(size) {
                        return (size * W) / 100;
                    },

                    inpercentH: function(size) {
                        return (size * H) / 100;
                    },

                }

                // basic setup  :)
                var fps = 50;

                canvas = document.getElementById("canvas");
                var ctx = canvas.getContext('2d');
                W = canvas.width = window.innerWidth;
                H = canvas.height = window.innerHeight;

                function generator(x, y, w, h, number) {
                    // particle will spawn in this aera
                    this.x = x;
                    this.y = y;
                    this.w = w;
                    this.h = h;
                    this.number = number;
                    this.particles = [];
                }

                generator.prototype.draw = function() {

                    if (this.particles.length < this.number) {
                        this.particles.push(new particle(utils.clamp(utils.randomInt(this.x, this.w + this.x), this.x, this.w + this.x), utils.clamp(utils.randomInt(this.y, this.h + this.y), this.y, this.h + this.y), this.text));
                    }

                    for (var i = 0; i < this.particles.length; i++) {
                        p = this.particles[i];
                        p.update();
                        if (p.radius < 1) {
                            //a new particle replacing the dead one
                            this.particles[i] = new particle(utils.clamp(utils.randomInt(this.x, this.w + this.x), this.x, this.w + this.x),

                                utils.clamp(utils.randomInt(this.y, this.h + this.y), this.y, this.h + this.y), this.text);
                        }
                    }

                }
                colors = [
                    '#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5',
                    '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4CAF50',
                    '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800',
                    '#FF5722'
                ];


                function particle(x, y, type) {
                    this.radius = utils.randomInt(1, 35);
                    this.rebond = utils.randomInt(1, 5);
                    this.x = x;
                    this.y = y;
                    this.vx = 0;
                    this.vy = 0;
                    this.type = type;
                    this.friction = .99;
                    this.gravity = -0;
                    this.color = colors[Math.floor(Math.random() * colors.length)];


                    this.getSpeed = function() {
                        return Math.sqrt(this.vx * this.vx + this.vy * this.vy);
                    };

                    this.setSpeed = function(speed) {
                        var heading = this.getHeading();
                        this.vx = Math.cos(heading) * speed;
                        this.vy = Math.sin(heading) * speed;
                    };

                    this.getHeading = function() {
                        return Math.atan2(this.vy, this.vx);
                    };

                    this.setHeading = function(heading) {
                        var speed = this.getSpeed();
                        this.vx = Math.cos(heading) * speed;
                        this.vy = Math.sin(heading) * speed;
                    };

                    this.angleTo = function(p2) {
                        return Math.atan2(p2.y - this.y, p2.x - this.x);

                    };

                    this.update = function(heading) {
                        this.x += this.vx;
                        this.y += this.vy;
                        this.vy += this.gravity;

                        this.vx *= this.friction;
                        this.vy *= this.friction;
                        this.radius -= .4;

                        if (this.y > H - this.radius) {
                            this.y = H - this.radius;
                            //this.setHeading(-this.getHeading());
                            this.vy *= -this.rebond / 10;
                            this.vx *= .8;

                        }

                        if (this.x > W || this.x < 0) {
                            this.vx *= -1;
                        }
                        ctx.globalCompositeOperation = "destination-over";



                        ctx.beginPath();

                        ctx.shadowBlur = 25;
                        ctx.shadowOffsetX = 0;
                        ctx.shadowOffsetY = 0;
                        ctx.shadowColor = this.color;

                        ctx.fillStyle = this.color;
                        ctx.arc(this.x, this.y, this.radius, Math.PI * 2, false);
                        ctx.fill();


                        ctx.closePath();

                    };
                    this.setSpeed(5);
                    this.setHeading(utils.randomInt(utils.degreesToRads(0), utils.degreesToRads(360)));

                }

                generator1 = new generator(W / 2 - 35, H / 2 - 35, 120, 80, 200);

                update();

                function update() {
                    setTimeout(function() {
                        ctx.clearRect(0, 0, W, H);
                        generator1.draw();
                        requestAnimationFrame(update);
                    }, 1000 / fps);
                }

                function resize_canvas() {

                    console.log("resize");

                    W = canvas.width = window.innerWidth;
                    H = canvas.height = window.innerHeight;
                    generator1.x = W / 2 - 35;
                    generator1.y = H / 2 - 35;

                }

                $(window).on('resize', resize_canvas);

            }

        </script>

    </div>

</body>
</html>
