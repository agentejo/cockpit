(function($){

    angular.module('cockpit.fields').run(['Contentfields', function(Contentfields) {

        Contentfields.register('link-collection', {
            label: 'Collection link',
            template: function(model, options) {
                return '<div link-collection="'+options.collection+'" ng-model="'+model+'" data-multiple="'+(options.multiple ? 'true':'false')+'">Linking '+options.collection+'</div>';
            }
        });

    }]);


    angular.module('cockpit.fields').directive("linkCollection", ['$timeout', '$http', function($timeout, $http) {

        var collections = false, cache = {}, cacheItems = {}, loaded, Field, Picker;

        Field = function($element, options, model, scope) {

            this.element    = $element;
            this.options    = $.extend({multiple: false, collection: null}, options);
            this.model      = model;
            this.value      = model.$viewValue;
            this.collection = this.options.collection;

            this.init();
        };

        Field.prototype = {

            init: function() {

                var $this = this, itemsloaded;

                this.element.on('click', '.js-pick', function(e){
                    e.preventDefault();
                    $this.select($(this).data('index'));
                });

                this.element.on('click', '.js-remove', function(e){
                    e.preventDefault();
                    $this.remove($(this).data('index'));
                });
            },

            render: function() {

                var $this = this;
                    getItem = function($item, index) {

                        var item = [], main;

                        index = index || 0;

                        if (!$item) {

                            main = '<div class="uk-alert uk-alert-danger">'+App.i18n.get('Linked item doesn\'t exist.')+'</div>';

                        } else {

                            $this.collection.fields.forEach(function(field){
                                if (field.lst && $item[field.name]) {
                                    item.push('<div class="uk-grid"><div class="uk-width-medium-1-5"><strong>'+field.name+'</strong></div><div class="uk-width-medium-4-5">'+$item[field.name]+'</div></div>');
                                }
                            });

                            main = item.join('');
                        }

                        return [
                            '<div class="uk-panel uk-panel-divider uk-text-small">',
                                '<div class="uk-margin">',
                                    main,
                                '</div>',
                                '<span class="uk-button-group">',
                                    '<button data-index="'+index+'" type="button" class="uk-button uk-button-small uk-button-primary js-pick"><i class="uk-icon-link"></i></button>',
                                    '<button data-index="'+index+'" type="button" class="uk-button uk-button-small uk-button-danger js-remove"><i class="uk-icon-trash-o"></i></button>',
                                '</span>',
                            '</div>'
                        ].join('');
                    }


                if (!this.value) {

                    this.element.html([
                        '<div class="uk-placeholder uk-text-center">',
                            '<strong class="uk-text-small">'+this.collection.name+'</strong>',
                            '<p class="uk-text-muted">'+[App.i18n.get('No item selected.')].join(' ')+'</p>',
                            '<button type="button" class="uk-button uk-button-primary js-pick"><i class="uk-icon-link"></i></button>',
                        '</div>'
                    ].join(''));

                } else {



                    (new Promise(function(resolve){

                        var items = $this.options.multiple ? $this.value : [$this.value], max = items.length;

                        items.forEach(function(item){

                            // hide previous slide
                            if (cacheItems[item]) {
                                max = max-1;
                                if (!max) resolve();
                            } else {

                                App.request("/api/collections/entries", {
                                    "collection": angular.copy($this.collection),
                                    "filter": JSON.stringify({'_id': item})
                                }, function(data){
                                    cacheItems[item] = data && data[0] ? data[0]:false;
                                    max = max-1;
                                    if (!max) resolve();
                                }, 'json');
                            }
                        });

                    })).then(function() {

                        var output;

                        if ($this.options.multiple) {

                            output = [];

                            $this.value.forEach(function(value, index) {
                                output.push(getItem(cacheItems[value], index));
                            });

                            output = output.join('');

                        } else {
                            output = getItem(cacheItems[$this.value]);
                        }

                        if ($this.options.multiple) {
                            output += '<hr><div><button class="uk-button uk-button-small js-pick" type="button"><i class="uk-icon-plus"></i></button></div>';
                        }

                        $this.element.html('<div class="uk-margin-top">'+output+'</div>');

                    });
                }

            },

            remove: function(index) {

                var $this = this;

                if (this.options.multiple && this.value[index]) {

                    this.value.splice(index, 1);

                    if (!this.value.length) {
                        this.value = null;
                    }

                } else {
                    this.value = null;

                }

                this.model.$setViewValue(this.value);

                $timeout(function(){
                    $this.render();
                });
            },

            select: function(index) {

                var $this = this;

                index = isNaN(index) ? -1 : Number(index);

                Picker.show($this.collection, function(idx) {

                    if ($this.options.multiple) {

                        if (index > -1 && $this.value && $this.value[index]  ) {
                            $this.value[index] = cache[$this.collection._id][idx]._id;
                        } else {

                            if (!$this.value) {
                                $this.value = [];
                            }

                            $this.value.push(cache[$this.collection._id][idx]._id);
                        }

                    } else {
                        $this.value = cache[$this.collection._id][idx]._id;
                    }

                    $this.model.$setViewValue($this.value);

                    $timeout(function(){
                        $this.render();
                    });
                });
            }
        };

        var Picker = (function(){

            var modal = $([
                    '<div class="uk-modal collection-item-picker">',
                        '<div class="uk-modal-dialog uk-modal-dialog-large">',
                            '<button type="button" class="uk-modal-close uk-close"></button>',
                            '<h4><i class="uk-icon-list"></i> <span class="js-collection-name"></span></h4>',
                            '<div class="uk-overflow-container uk-margin-top">',
                                '<div class="js-items"></div>',
                            '</div>',
                            '<div class="uk-modal-buttons"><button class="media-select uk-button uk-button-large uk-button-primary uk-hidden" type="button">'+App.i18n.get('Select')+'</button> <button class="uk-button uk-button-large uk-modal-close" type="button">Cancel</button></div>',
                        '</div>',
                    '</div>'
                ].join('')).appendTo('body'),

                container   = modal.find('.js-items'),
                picker      = UIkit.modal(modal),
                itemsloaded = {};

            container.on('click', '.js-select', function(e){
                e.preventDefault();
                picker.hide();
                Picker.handler($(this).data('index'));
            });

            function renderItems(collection, items) {

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

                    rows.push([
                        '<tr>',
                            '<td>'+tpl.join('')+'</td><td class="uk-width-1-10 uk-text-right"><a data-index="'+index+'" class="js-select"><i class="uk-icon-link"></i></a></td>',
                        '</tr>'
                    ].join(''));
                });

                table.find('tbody').html(rows.join(''));

                container.html(table);
            };

            return {

                show: function(collection, handler) {

                    modal.find('.js-collection-name').html(collection.name);
                    container.html('<div class="uk-text-center uk-text-large uk-margin"><i class="uk-icon-spinner uk-icon-spin"></i></div>');

                    if (!itemsloaded[collection._id]) {

                        itemsloaded[collection._id] = new Promise(function(resolve){

                            if (!cache[collection._id]) {

                                $http.post(App.route("/api/collections/entries"), {
                                    "collection": angular.copy(collection)
                                }, {responseType:"json"}).success(function(data){


                                    cache[collection._id] = data;
                                    resolve();
                                }).error(App.module.callbacks.error.http);

                            } else {
                               resolve();
                            }

                        });
                    }

                    itemsloaded[collection._id].then(function() {

                        if (!cache[collection._id].length) {
                            container.html('<div class="uk-text-center uk-text-large uk-margin">'+App.i18n.get('No items.')+'</div>');
                        } else {
                            Picker.handler = handler;
                            renderItems(collections[collection._id], cache[collection._id]);
                        }
                    });

                    picker.show();
                },

                handler: function() {}
            };

        })();


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
                        collectionId = attrs.linkCollection;

                    loaded.then(function() {

                        if (collections[collectionId]) {


                            $timeout(function(){

                                var options = {
                                    multiple   : attrs.multiple==='true',
                                    collection : collections[collectionId],
                                    model      : ngModel
                                },

                                field = new Field($element, options, ngModel, scope);

                                ngModel.$render = function() {
                                    field.render();
                                };

                                ngModel.$render();
                            });

                        } else {
                            $element.html('<div class="uk-alert uk-alert-danger">'+App.i18n.get('Linked collection doesn\'t exist.')+'</div>');
                        }
                    });
                };
            }
        };

    }]);

})(jQuery);
