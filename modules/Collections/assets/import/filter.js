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
            this.resolve(value == '1' || !!value);
        },

        date: function(value) {

            var date = new Date(value);

            if (isNaN(date.getTime()) || !date.toISOString().match(/(.+)\T/)[1]) {
                value = null;
            }

            this.resolve(value);
        },

        collectionlink: function(value, field, extra) {

            if (field.options && field.options.link && extra && value) {
                var $this = this;

                if (Array.isArray(value)) {
                    var options = {};
                    value = _.map(value, function(item){
                        return _.isPlainObject(item) && extra ? item[extra] : item;
                    });
                    options.filter = {};
                    options.filter["$or"] = _.map(value, function(item){
                        var filter = {};
                        filter[extra] = item;
                        return filter;
                    });

                    App.callmodule('collections:find', [field.options.link, options]).then(function(data) {
                        if (data.result && data.result.length) {
                            if (field.options.multiple) {

                                var entries = _.map(data.result, function(item){
                                    return {
                                        _id: item._id,
                                        display: item[field.options.display] || item[Filter.collections[field.options.link].fields[0].name] || 'n/a'
                                    };
                                });

                                $this.resolve(entries);
                            } else {
                                var entry = {
                                    _id:data.result[0]._id,
                                    display: data.result[0][field.options.display] || data.result[0][Filter.collections[field.options.link].fields[0].name] || 'n/a'
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

        App.callmodule('collections:collections', true).then(function(data) {
            var collections = _.keyBy(data.result, 'name');
            resolve(collections);
        });
    });

    Filter._getCollections.then(function(collections) {
        Filter.collections = collections;
    })

    g.ImportFilter = Filter;

})(this);
