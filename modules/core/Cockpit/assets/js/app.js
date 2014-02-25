(function(global, $, $win, $doc){

    var html = $('html'),

        App  = {

            "base_route" : html.data("route"),
            "base_url"   : html.data("base"),
            "modules"    : [],
            "_events"    : {},

            base: function(url) {
                return this.base_url+url.replace(/^\//g, '');
            },

            route: function(url) {
                return this.base_route+url.replace(/^\//g, '');
            },

            request: function(url, data, fn, type) {
                return $.post(this.route(url), data, fn, type);
            },

            notify: function(note, type){

                $.UIkit.notify(note, {"status":(type || 'primary')});
            },

            Ui: {

                block: function(message) {

                },

                unblock: function(){

                },

                dialog: function(content, options) {
                    $.UIkit.modal.dialog(content, options);
                },

                alert: function(content, options) {
                    $.UIkit.modal.alert(content, options);
                },

                confirm: function(content, onconfirm, options){
                    $.UIkit.modal.confirm(content, onconfirm, options);
                }
            },

            on: function(name, fn){
                if(!this._events[name]) this._events[name] = [];

                this._events[name].push(fn);
            },

            off: function(name, fn){
                if(!this._events[name]) return;

                if (!fn) {
                   this._events[name] = [];
                } else {

                    for(var i=0; i < this._events[name].length; i++) {
                        if(this._events[name][i]===fn) {
                            this._events[name].splice(i, 1);
                            break;
                        }
                    }
                }
            },

            trigger: function(name, params) {
                if(!this._events[name]) return;

                var event = {"name":name, "params": params};

                for(var i=0; i < this._events[name].length; i++) {
                    this._events[name][i].apply(App, [event]);
                }
            }
        };

    App.assets = {

        _ress: {},

        require: function(ress, callback, failcallback) {

            var req  = [],
                ress = $.isArray(ress) ? ress:[ress];

            for (var i=0, len=ress.length; i<len; i++) {

                if(!ress[i]) continue;

                if (!this._ress[ress[i]]) {
                   if (ress[i].match(/\.js$/)) {
                    this._ress[ress[i]] = this.getScript(ress[i]);
                   } else {
                    this._ress[ress[i]] = this.getCss(ress[i]);
                   }
                }
                req.push(this._ress[ress[i]]);
            }

            return $.when.apply($, req).done(callback).fail(function(){
                failcallback ? failcallback() : $.error("Require failed: \n"+ress.join(",\n"));
            });
        },

        getScript: function(url, callback) {

            var d = $.Deferred(), script = document.createElement('script');

            script.async = true;

            script.onload = function() {
                d.resolve();
                if(callback) { callback(script); }
            };

            script.onerror = function() {
                d.reject(url);
            };

            script.src = url.match(/^http/) ? url : App.base(url);

            document.getElementsByTagName('head')[0].appendChild(script);

            return d.promise();
        },

        getCss: function(url, callback){
            var d         = $.Deferred(),
                link      = document.createElement('link');
                link.type = 'text/css';
                link.rel  = 'stylesheet';
                link.href = url.match(/^http/) ? url : App.base(url);

            document.getElementsByTagName('head')[0].appendChild(link);

            var img = document.createElement('img');
                img.onerror = function(){
                    d.resolve();
                    if(callback) callback(link);
                };
                img.src = link.href;

            return d.promise();
        }
    };

    App.session = Storage.select("cockpit", "session");
    App.storage = Storage.select("cockpit", "local");
    App.memory  = Storage.select("cockpit", "memory");
    App.i18n    = window.i18n;

    global.App = App;


    $(function(){

        App.modules.push("cockpit");

        angular.bootstrap(document, App.modules);
        $doc.trigger("app-init");
    });

})(this, jQuery, jQuery(window), jQuery(document));