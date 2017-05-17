(function(w, d){

    var Cockpit = {
        token  : '{{ $token }}',
        apiurl : '{{ $apiurl }}',
        pathToUrl: function(path) {
            return String(path).replace('site:', '{{ $app->pathToUrl("site:") }}')
                               .replace('#root:', '{{ $app->pathToUrl("#root:") }}')
                               .replace('#uploads:', '{{ $app->pathToUrl("#uploads:") }}')
        },
        request: function(route, params, type) {

            type   = type || 'auto';
            params = params || {};

            var promise = new Promise(function(resolve, reject) {

                 var xhr = new XMLHttpRequest();

                 xhr.onloadend = function() {

                    var data  = xhr.responseText;

                    if (this.status == 200) {
                        if (type=='auto' && String(data).match(/^(\{(.*)\}|\[(.*)\])$/g)) {
                            type = 'json';
                        }

                        if (type == 'json') {
                            try { data = JSON.parse(data); } catch(e){ data = null; }
                        }

                        resolve(data, xhr);

                    } else {
                        reject(xhr);
                    }
                };

                xhr.open('POST', [Cockpit.apiurl, route, '?token='+Cockpit.token].join(''), true);

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
            });

            return promise;
        }
    };

    <?php $app->trigger('cockpit.api.js') ?>

    // AMD support
    if (typeof define === 'function' && define.amd) {
        define(function() { return Cockpit; });
    }

    w.Cockpit = Cockpit;

})(window, document);
