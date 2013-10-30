(function(global, $){

    angular.module('cockpit.config', []);
    angular.module('cockpit.filters', ['cockpit.config']);
    angular.module('cockpit.services', ['cockpit.config']);
    angular.module('cockpit.directives', ['cockpit.config']);
    angular.module('cockpit', ['cockpit.filters', 'cockpit.directives', 'cockpit.services', 'cockpit.config']);


})(this, jQuery);