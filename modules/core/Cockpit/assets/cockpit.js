(function($){


    var Cockpit = {

        callmodule: function (module, method, args, acl) {

            if (module.indexOf(':') !== -1) {

                var parts = module.split(':');

                args   = method;
                acl    = args;

                module = parts[0];
                method = parts[1];
            }

            args = args || [];
            acl  = acl || 'manage.'+module;

            if (!Array.isArray(args)) args = [args];

            var req = App.request('/cockpit/call/'+module+'/'+method, {args:args, acl:acl});

            // catch any error
            req.catch(function(){

            });

            return req;
        }
    };

    App.$.extend(true, App, Cockpit);

    window.Cockpit = Cockpit;

})(jQuery);
