(function(global){

    "use strict";

    var extend = function (defaults, options) {
        var extended = {}, prop;

        for (prop in defaults) {
            if (Object.prototype.hasOwnProperty.call(defaults, prop)) {
                extended[prop] = defaults[prop];
            }
        }
        for (prop in options) {
            if (Object.prototype.hasOwnProperty.call(options, prop)) {
                extended[prop] = options[prop];
            }
        }
        return extended;
    };

    var Channel = function(options) {

        var $this = this;

        this.options = extend({
            'window' : parent ? window.parent : window,
            'origin' : '*',
            'scope'  : 'global',
            'actions': {}
        }, options);

        this.uid = 0;
        this.promises = [];

        window.addEventListener("message", function(e) {

            if (e.data.scope !== $this.options.scope) {
                return;
            }

            if (e.data.response && $this.promises[e.data.response]) {
                return $this.promises[e.data.response](e.data.data);
            }

            if (e.data.action && $this.options.actions[e.data.action]) {

                var req = e.data, resp = function(data) {

                    e.source.postMessage({
                        'response' : e.data.uid,
                        'data'     : data,
                        'scope'    : $this.options.scope
                    }, e.origin);
                };

                $this.options.actions[e.data.action].apply($this, [req, resp]);
            }

        }, false);

        if (window.parent) {

            document.addEventListener("DOMContentLoaded", function(event) {

                $this.call('channel-frame-ready');

                var h = -1;

                setInterval(function() {

                    if (h != document.body.offsetHeight) {

                        h = document.body.offsetHeight;

                        $this.call('channel-frame-resize', {
                            height : document.body.offsetHeight,
                            width  : document.body.scrollWidth
                        });
                    }

                }, 50);
            });

            window.addEventListener("load", function(event) {
                $this.call('channel-frame-load');
            });
        }

    };

    Channel.prototype = {

        call: function(action, data, callback) {

            var uid = this.options.scope+'-'+this.uid;

            this.options.window.postMessage({
                'uid'    : uid,
                'action' : action,
                'data'   : data || {},
                'scope'  : this.options.scope
            }, this.options.origin);

            var $this = this, promise = new Promise(function(resolve){

                $this.promises[uid] = resolve;
            });

            if (callback) {
                promise.then(function(data) {
                    callback(data);
                });
            }

            return promise;
        }
    };

    global.FrameChannel = Channel;

})(this);
