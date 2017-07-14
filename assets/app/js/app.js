(function(g, d, $) {

    var html = $('html'), App = {

        version   : html.attr("data-version") || '1.0',
        base_route : (html.attr("data-route") || '').replace(/\/$/, ''),
        base_url   : (html.attr("data-base") || '').replace(/\/$/, ''),

        $: $,

        _events : {},

        base: function(url) {
            return this.base_url+url;
        },

        route: function(url) {
            return this.base_route+url;
        },

        reroute: function(url){
            location.href = url.match(/^http/) ? url : this.route(url);
        },

        request: function(url, data, type) {

            url  = this.route(url);
            type = type || 'json';

            return new Promise(function (fulfill, reject){

                var xhr = new XMLHttpRequest();

                xhr.open('post', url, true);

                url += (url.indexOf('?') !== -1 ? '&':'?') + 'nc=' + Math.random().toString(36).substr(2);

                if (data) {

                    if (typeof(data) === 'object' && data instanceof HTMLFormElement) {
                        data = new FormData(data);
                    } else if (typeof(data) === 'object' && data instanceof FormData) {
                        // do nothing
                    } else if (typeof(data) === 'object') {

                        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
                        data = JSON.stringify(data || {});
                    }
                }

                xhr.onloadend = function () {

                    var resdata = xhr.responseText;

                    if (type == 'json') {
                        try {
                            resdata = JSON.parse(xhr.responseText);
                        } catch(e) {
                            resdata = null;
                        }
                    }

                    if (this.status == 200) {
                        fulfill(resdata, xhr);
                    } else {
                        reject(resdata, xhr);
                    }
                };

                // send the collected data as JSON
                xhr.send(data);
            });
        },

        on: function(name, fn){
            if (!this._events[name]) this._events[name] = [];
            this._events[name].push(fn);
        },

        off: function(name, fn){
            if (!this._events[name]) return;

            if (!fn) {
               this._events[name] = [];
            } else {

                for (var i=0; i < this._events[name].length; i++) {
                    if (this._events[name][i]===fn) {
                        this._events[name].splice(i, 1);
                        break;
                    }
                }
            }
        },

        trigger: function(name, params) {

            if (!this._events[name]) return;

            var event = {"name":name, "params": params};

            for (var i=0; i < this._events[name].length; i++) {
                this._events[name][i].apply(App, [event]);
            }
        },

        deferred: function() {

            var resolve, fail;

            var d = new Promise(function(fullfill, reject) {
                resolve = fullfill;
                fail    = reject;
            });

            d.resolve = function(data) {
                resolve(data);
            };

            d.reject = function(data) {
                fail(data);
            };

            return d;
        }
    };

    App.ui = {

        notify: function(note, type, pos){

            pos = pos || 'top-center';

            if (typeof(note) !== 'string') {
                note = JSON.stringify(note);
            }

            UIkit.notify(App.i18n.get(note), {"status":(type || 'primary'), "pos": pos, "timeout": 2000});
        },

        block: function(content) {
            this._blockmodal = UIkit.modal.blockUI(content);
        },

        unblock: function(){
            if (this._blockmodal) {
                this._blockmodal.hide();
                this._blockmodal = null;
            }
        },

        dialog: function(content, options) {
            UIkit.modal.dialog(App.i18n.get(content), options);
        },

        alert: function(content, options) {
            UIkit.modal.alert(App.i18n.get(content), options);
        },

        confirm: function(content, onconfirm, options){
            UIkit.modal.confirm(App.i18n.get(content), onconfirm, options);
        },

        prompt: function(text, value, clb, options){
            UIkit.modal.prompt(App.i18n.get(text), value, clb, options);
        }
    };

    App.assets = {

        _ress: {},

        require: function(ress, onSuccess, onError) {

            onSuccess = onSuccess || function(){};
            onError = onError ||  function(){};

            var req  = [],
                ress = Array.isArray(ress) ? ress:[ress];

            for (var i=0, len=ress.length; i<len; i++) {

                if (!ress[i]) continue;

                if (!this._ress[ress[i]]) {

                    if (ress[i].match(/\.js$/i)) {
                        this._ress[ress[i]] = this.getScript(ress[i]);
                    } else if(ress[i].match(/\.(jpg|jpeg|gif|png)$/i)) {
                        this._ress[ress[i]] = this.getImage(ress[i]);
                    } else if(ress[i].match(/\.css$/i)) {
                        this._ress[ress[i]] = this.getCss(ress[i]);
                    } else {
                        continue;
                    }
                }

                req.push(this._ress[ress[i]]);
            }

            return Promise.all(req).then(onSuccess).catch(function(e){
                onError.apply(self, [e]);
            });
        },

        getScript: function(url) {

            return new Promise(function(resolve, reject) {

                var script = document.createElement('script');

                script.async = true;

                script.onload = function() {
                    resolve(url);
                };

                script.onerror = function() {
                    reject(url);
                };

                script.src = (url.match(/^(\/\/|http)/) ? url : App.base(url))+'?v='+App.version;

                document.getElementsByTagName('head')[0].appendChild(script);

            });
        },

        getCss: function(url){

          return new Promise(function(resolve, reject) {

              var link      = document.createElement('link');
                  link.type = 'text/css';
                  link.rel  = 'stylesheet';
                  link.href = (url.match(/^(\/\/|http)/) ? url : App.base(url))+'?v='+App.version;

              document.getElementsByTagName('head')[0].appendChild(link);

              var img = document.createElement('img');
                  img.onerror = function(){
                      resolve(url);
                  };
                  img.src = link.href+'?v='+App.version;
            });
        },

        getImage: function(url){

            return new Promise(function(resolve, reject) {

                var img = document.createElement('img');

                img.onload  = function(){ resolve(url); };
                img.onerror = function(){ reject(url); };

                img.src = (url.match(/^(\/\/|http)/) ? url : App.base(url))+'?v='+App.version;
            });
        }
    };

    // general services
    App.session = g.JSONStorage ? g.JSONStorage.select("app", "session") : null;
    App.storage = g.JSONStorage ? g.JSONStorage.select("app", "local") : null;
    App.memory  = g.JSONStorage ? g.JSONStorage.select("app", "memory") : null;
    App.i18n    = g.i18n || null;

    g.App = App;

    $(function() {
        App.trigger("app-init");
    });

})(this, document, jQuery);
