(function(g){

    var Filter = {

        filter: function(field, value, extra){
            var _resolve, _reject, p = new Promise(function(resolve, reject) {
                _resolve = resolve;
                _reject = reject;
            });

            p.resolve = _resolve;
            p.reject = _reject;


            if (this[field.type]) {
                this[field.type].apply(p, [value, field, extra]);
            } else {
                p.resolve(value);
            }

            return p;
        },

        text: function(value) {
            this.resolve(value.toString());
        },

        boolean: function(value) {

            if (value == 'true' || value == '1') value = true;
            if (value == 'false' || value == '0') value = false;

            this.resolve(!!value);
        },

        date: function(value) {

            var date = new Date(value);

            if (isNaN(date.getTime()) || !date.toISOString().match(/(.+)\T/)[1]) {
                value = null;
            }

            this.resolve(value);
        },

        tags: function(value) {

            if (typeof(value) == 'string') {
                value = value.split(',').map(function(tag) { return tag.trim()});
            }

            this.resolve(Array.isArray(value) ? value : []);
        },

        collectionlink: function(value, field, extra) {

            if (field.options && field.options.link && extra && value) {

                var $this = this;

                if (Array.isArray(value)) {

                    var options = {};

                    var value = _.map(value, function(item){

                        if (!_.isPlainObject(item)) {
                            return item;
                        }

                        if (item[extra]) {
                            return item[extra];
                        }

                        if (item.display) {
                            return item.display;
                        }

                        return null;
                    });

                    options.filter = {};

                    options.filter.$or = _.map(value, function(item){
                        var filter = {};
                        filter[extra] = item;
                        return filter;
                    });

                    App.request('/collections/_find', {collection:field.options.link, options:options}).then(function(data) {

                        if (data && data.length) {

                            if (field.options.multiple) {

                                var entries = _.map(data, function(item){
                                    return {
                                        _id: item._id,
                                        display: item[field.options.display] || item[Filter.collections[field.options.link].fields[0].name] || 'n/a'
                                    };
                                });

                                $this.resolve(entries);

                            } else {

                                var entry = {
                                    _id:data.result[0]._id,
                                    display: data[0][field.options.display] || data[0][Filter.collections[field.options.link].fields[0].name] || 'n/a'
                                };

                                $this.resolve(entry);
                            }
                        } else {
                            console.log("Couldn't find a collection reference for "+value.join(", "));
                            $this.resolve(null);
                        }
                    });

                } else {

                    if (_.isPlainObject(value) && extra) {
                        value = value[extra];
                    }

                    var filter = {};

                    filter[extra] = value;

                    App.callmodule('collections:findOne', [field.options.link, filter]).then(function(data) {

                        if (data.result && data.result._id) {
                            //TODO add support for multiple imports
                            var entry = {_id:data.result._id, display: data.result[field.options.display] || data.result[Filter.collections[field.options.link].fields[0].name] || 'n/a'};
                            $this.resolve(field.options.multiple ? [entry]:entry);

                        } else {
                            console.log("Couldn't find a collection reference for "+value);
                            $this.resolve(null);
                        }
                    });

                }
            } else {

                this.resolve(null);
            }
        }
    };

    // Utils

    Filter._getCollections = new Promise(function(resolve){

        App.request('/collections/_collections', {nc: Math.random()}).then(function(collections) {
            var collections = _.keyBy(collections, 'name');
            resolve(collections);
        });
    });

    Filter._getCollections.then(function(collections) {
        Filter.collections = collections;
    })

    g.ImportFilter = Filter;

})(this);
