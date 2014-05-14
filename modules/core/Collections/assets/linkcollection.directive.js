(function($){

    var collections = false, cache = {}, loaded, modal, container, picker, handler;

    function render($element, $data, $item) {

        if (!$data.value) {

            $element.html([
                '<div class="uk-placeholder uk-text-center">',
                    '<strong class="uk-text-small">'+$data.collection.name+'</strong>',
                    '<p class="uk-text-muted">'+[App.i18n.get('No item selected.')].join(' ')+'</p>',
                    '<button type="button" class="uk-button uk-button-primary js-pick"><i class="uk-icon-link"></i></button>',
                '</div>'
            ].join(''));

        } else {

            var item = [], main;

            (new Promise(function(resolve){

                // hide previous slide
                if ($item) {
                    resolve($item);
                } else {

                    App.request("/api/collections/entries", {
                        "collection": angular.copy($data.collection),
                        "filter": JSON.stringify({'_id': $data.value})
                    }, function(data){
                        resolve(data && data[0] ? data[0]:false);
                    }, 'json');
                }

            })).then(function($item) {

                if (!$item) {

                    main = '<div class="uk-alert uk-alert-danger">'+App.i18n.get('Linked item doesn\'t exist.')+'</div>';

                } else {

                    $data.collection.fields.forEach(function(field){
                        if (field.lst && $item[field.name]) {
                            item.push('<div class="uk-grid"><div class="uk-width-medium-1-5"><strong>'+field.name+'</strong></div><div class="uk-width-medium-4-5">'+$item[field.name]+'</div></div>');
                        }
                    });

                    main = item.join('');
                }

                $element.html([
                    '<div class="uk-margin-top uk-text-small">',
                        '<div class="uk-margin">',
                            main,
                        '</div>',
                        '<span class="uk-button-group">',
                            '<button type="button" class="uk-button uk-button-small uk-button-primary js-pick"><i class="uk-icon-link"></i></button>',
                            '<button type="button" class="uk-button uk-button-small uk-button-danger js-remove"><i class="uk-icon-trash-o"></i></button>',
                        '</span>',
                    '</div>'
                ].join(''));
            });
        }
    }

    function renderItems(collection, items, $element) {

        var table = $('<table class="uk-table uk-table-striped"><tbody></tbody></table>'),
            rows  = [],
            tpl;

        items.forEach(function(item, index){

            tpl = [];

            collection.fields.forEach(function(field){
                if (field.lst && item[field.name]) {
                    tpl.push('<div class="uk-grid"><div class="uk-width-medium-1-5"><strong>'+field.name+'</strong></div><div class="uk-width-medium-4-5">'+item[field.name]+'</div></div>');
                }
            });

            rows.push('<tr><td>'+tpl.join('')+'</td><td class="uk-width-1-10 uk-text-right"><a data-index="'+index+'" class="js-select"><i class="uk-icon-link"></i></a></td></tr>');
        });

        table.find('tbody').html(rows.join(''));

        container.html(table);
    }

    modal = $([
        '<div class="uk-modal collection-item-picker">',
            '<div class="uk-modal-dialog uk-modal-dialog-large">',
                '<button type="button" class="uk-modal-close uk-close"></button>',
                '<h4><i class="uk-icon-list"></i> <span class="js-collection-name"></span></h4>',
                '<div class="uk-overflow-container uk-margin-top">',
                    '<div class="js-items"></div>',
                '</div>',
                '<div class="uk-modal-buttons"><button class="media-select uk-button uk-button-large uk-button-primary uk-hidden" type="button">Select</button> <button class="uk-button uk-button-large uk-modal-close" type="button">Cancel</button></div>',
            '</div>',
        '</div>'
    ].join('')).appendTo('body');

    container = modal.find('.js-items');
    picker    = $.UIkit.modal(modal);

    container.on('click', '.js-select', function(e){
        e.preventDefault();
        picker.hide();
        handler($(this).data('index'));
    });


    angular.module('cockpit.directives').directive("linkCollection", function($timeout, $http){

        loaded = $http.post(App.route("/api/collections/find"), {}).success(function(data){

            collections = {};

            data.forEach(function(collection){
                collections[collection._id] = collection;
            });
        });

        return {
            require: '?ngModel',
            restrict: 'A',

            compile: function(element, attrs) {

                return function link(scope, elm, attrs, ngModel) {

                    var $element     = $(elm).html('<i class="uk-icon-spinner uk-icon-spin"></i>'),
                        collectionId = attrs.linkCollection,
                        data         = {},
                        itemsloaded;

                    loaded.then(function() {

                        $element.on('click', '.js-pick', function(e){
                            e.preventDefault();

                            modal.find('.js-collection-name').html(data.collection.name);

                            container.html('<div class="uk-text-center uk-text-large uk-margin"><i class="uk-icon-spinner uk-icon-spin"></i></div>');

                            if (!cache[collectionId]) {

                                itemsloaded = $http.post(App.route("/api/collections/entries"), {
                                    "collection": angular.copy(data.collection)
                                }, {responseType:"json"}).success(function(data){
                                    cache[collectionId] = data;
                                }).error(App.module.callbacks.error.http);
                            }

                            itemsloaded.then(function() {

                                if (!cache[collectionId].length) {
                                    container.html('<div class="uk-text-center uk-text-large uk-margin">'+App.i18n.get('No items.')+'</div>');
                                } else {

                                    handler = function(index) {

                                        data.value = cache[collectionId][index]._id;
                                        ngModel.$setViewValue(data.value);
                                        render($element, data, cache[collectionId][index]);
                                    };

                                    renderItems(collections[collectionId], cache[collectionId], $element)
                                }
                            });

                            picker.show();
                        });

                        $element.on('click', '.js-remove', function(e){

                            data.value = null;
                            ngModel.$setViewValue(data.value);
                            render($element, data);
                        });

                        ngModel.$render = function() {

                            if (collections[collectionId]) {

                                data.value = ngModel.$viewValue || null;
                                data.collection = collections[collectionId];
                                render($element, data);

                            } else {
                                $element.html('<div class="uk-alert uk-alert-danger">'+App.i18n.get('Linked collection doesn\'t exist.')+'</div>');
                            }
                        };

                        ngModel.$render();
                    });
                };
            }
        };

    });

})(jQuery);