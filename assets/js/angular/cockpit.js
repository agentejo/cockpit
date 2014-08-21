(function(global, $){

    angular.module('cockpit.config', []);
    angular.module('cockpit.filters', ['cockpit.config']);
    angular.module('cockpit.services', ['cockpit.config']);
    angular.module('cockpit.directives', ['cockpit.config']);
    angular.module('cockpit.fields', ['cockpit.config']);
    angular.module('cockpit', ['cockpit.filters', 'cockpit.directives', 'cockpit.services', 'cockpit.config', 'cockpit.fields']);

    angular.module('cockpit').config(function ($compileProvider) {
        angular.module('cockpit').compileProvider = $compileProvider;
    });

})(this, jQuery);