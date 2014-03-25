/**
 * Tags field.
 */

(function($){

    var tpl = [
        '<div>',
            '<input type="text" class="uk-width-1-1 uk-form-large" placeholder="'+App.i18n.get('Add tag...')+'">',
            '<div class="uk-margin-small-top uk-clearfix"></div>',
        '</div>'
    ].join('');


    angular.module('cockpit.directives').directive("tags", function($timeout){

      return {

        require: 'ngModel',
        restrict: 'E',

        link: function (scope, elm, attrs, ngModel) {

            var $tags      = $(tpl),
                $input     = $tags.find('input'),
                $container = $tags.find('div'),
                tags;

            $input.on("keydown", function(e) {

                if (e.which && e.which == 13) {

                    var tag = $input.val().trim();

                    if(tags.indexOf(tag) === -1 ) {
                        tags.push(tag);
                        updateSope();
                        renderTags();
                    }

                    e.preventDefault();
                    $input.val("");
                }

            });

            $container.on("click", ".js-remove", function(){

                var ele   = $(this),
                    item  = ele.closest('div[data-tag]'),
                    index = $container.children().index(item);

                tags.splice(index, 1);
                item.fadeOut(function(){ item.remove(); });

                updateSope();
            });

            ngModel.$render = function() {

                if(!tags) {
                    tags = ngModel.$viewValue || [];
                }

                setTimeout(function(){
                    renderTags();
                }, 10);
            };

            function updateSope() {

                ngModel.$setViewValue(tags);

                if (!scope.$root.$$phase) {
                    scope.$apply();
                }
            }

            function renderTags() {

                $container.empty();

                if (tags && tags.length) {
                    tags.forEach(function(tag, index){

                        $container.append([
                            '<div class="uk-margin-small-top uk-margin-small-right uk-float-left uk-badge" data-tag="'+tag+'" >',
                                '<i class="uk-icon-tag"></i> '+tag,
                                '&nbsp;<span class="js-remove" style="cursor:pointer;"><i class="uk-icon-times"></i></span>',
                            '</div>'
                        ].join(''));
                    });
                } else {
                    $container.append('<div class="uk-width-1-1 uk-grid-margin"><p class="uk-text-muted uk-text-small">'+App.i18n.get('No tags')+'</p></div>');
                }
            }

            elm.replaceWith($tags);
        }
      };

    });

})(jQuery);