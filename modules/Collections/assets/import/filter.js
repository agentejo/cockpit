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

            if (field.options && field.options.link && extra) {

                var $this = this, filter = {};
                filter[extra] = value;

                App.callmodule('collections:findOne', [field.options.link, filter]).then(function(data) {
                    
                    if (data.result && data.result._id) {

                        var entry = {_id:data.result._id, display: data.result[field.options.display] || data.result[Filter.collections[field.options.link].fields[0].name] || 'n/a'};
                        $this.resolve(field.options.multiple ? [entry]:entry);

                    } else {
                        console.log("Couldn't find a collection reference for "+value);
                        $this.resolve(null);
                    }
                });

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