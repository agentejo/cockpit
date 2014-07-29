(function(g,d){

    var Cockpit = {
        token  : '{{ $token }}',
        apiurl : '@route('/rest/api')',
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

            xhr.onload = function(){

                ret._r   = this.responseText;
                ret._req = this;

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
            xhr.setRequestHeader('Content-type', typeof(params)=='object' ? 'application/json':'application/x-www-form-urlencoded');
            xhr.send(typeof(params)=='object' ? JSON.stringify(params):params);

            return ret;
        },
        registry: {{ $registry }}
    };


    // AMD support
    if (typeof define === 'function' && define.amd) {
        define(function() { return Cockpit; });
    }

    g.Cockpit = Cockpit;

})(window, document);