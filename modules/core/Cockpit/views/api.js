(function(w, d){

    var Cockpit = {
        token  : '{{ $token }}',
        apiurl : '@route('/api')',
        pathToUrl: function(path) {
            return String(path).replace("site:", "{{ $app->pathToUrl('site:') }}");
        },
        request: function(route, params, type){

            type   = type || 'auto';
            params = params || {};

            var xhr    = new XMLHttpRequest(),
                status = status,
                ret    = {
                    "_s": [],
                    "_f": [],
                    "success": function(fn){
                        if (!status) {
                            this._s.push(fn);
                        } else {
                            if (status===1) fn(ret._r, ret._req);
                        }

                        return ret;
                    },
                    "fail": function(fn){
                        if (!status) {
                            this._f.push(fn)
                        } else {
                            if (status===-1) fn(ret._r, ret._req);
                        }
                        return ret;
                    }
                };

            xhr.onloadend = function(){

                ret._r   = xhr.responseText;
                ret._req = xhr;

                if (this.status == 200) {

                    status = 1;

                    if (type=="auto" && String(ret._r).match(/^(\{(.*)\}|\[(.*)\])$/g)) {
                        type = "json";
                    }

                    if (type=="json") {
                        try { ret._r = JSON.parse(ret._r); } catch(e){ ret._r = null; }
                    }

                } else {
                    status = -1;
                }

                for (var stat=((status===1) ? '_s':'_f'),  i = ret[stat].length - 1; i >= 0; i--) {
                    ret[stat][i](ret._r, ret._req);
                }
            };

            xhr.open('POST', [this.apiurl, route, '?token='+this.token].join(''), true);

            if (typeof(params) === 'object' && params instanceof HTMLFormElement) {
                params = new FormData(params);
            } else if (typeof(params) === 'object' && params instanceof FormData) {
                // do nothing
            } else if (typeof(params) === 'object') {
                xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
                params = JSON.stringify(params || {});
            } else if (typeof(params) === 'string') {
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            }

            xhr.send(params);

            return ret;
        }
    };

    // AMD support
    if (typeof define === 'function' && define.amd) {
        define(function() { return Cockpit; });
    }

    w.Cockpit = Cockpit;

})(window, document);
