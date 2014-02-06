(function(g,d,$){

    var CockpitApi = {
        token  : '{{ $token }}',
        apiurl : '@route('/rest/api')',
        request: function(route, params, callback, type){
            return $.post.([this.apiurl, route, '?token='+this.token].join(''), params, callback, type);
        }
    };


    // AMD support
    if (typeof define === 'function' && define.amd) {
        define(function () { return CockpitApi; });
    }

    g.CockpitApi = CockpitApi;

})(window, document, jQuery);