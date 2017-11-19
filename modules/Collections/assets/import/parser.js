(function(g){

    var Parser = {

        parse: function(file) {

            var p = new Promise(function(resolve, reject) {

                var reader = new FileReader();

                reader.onload = function(e) {
                    Parser[(file.type=='application/json' || file._type=='application/json') ? 'json':'csv'](e.target.result, resolve, reject);
                };

                reader.readAsText(file);
            });

            return p;
        },

        csv: function(content, resolve, reject) {

            // require parsing lib
            App.assets.require('/assets/lib/papaparse.js').then(function() {

                Papa.parse(content, {
                    header: true,
                    complete: function(result) {

                        var data, headers = [];

                        data = result.data;

                        if (!data.length) {
                            return reject('List is empty!');
                        }

                        Object.keys(data[0]).forEach(function(key) {

                            if (['_id', '_created', '_modified'].indexOf(key) != -1) return;

                            headers.push(key);
                        });

                        resolve({
                            headers: headers,
                            rows: data
                        });

                    },
                    error: function() {
                        reject('Error parsing CSV file');
                    }
                });
            });
        },

        json: function(content, resolve, reject) {

            var data;

            try {
                var data = JSON.parse(content);
            } catch(e) { return reject(e.message) }

            if (!Array.isArray(data)) {
                if (_.isPlainObject(data)) {
                    data = _.transform(_.keys(data).sort(), function(result, val){
                        result.push(data[val]);
                    }, []);
                } else {
                    return reject('JSON needs to be a collection of items!');
                }
            }

            if (!data.length) {
                return reject('List is empty!');
            }

            var headers = _.reduce(data, function(result, item){
                return _.difference(_.union(_.keys(item), result), ['_id', '_uid', '_created', '_modified']);
            }, []);

            resolve({
                headers: headers,
                rows: data
            });
        }
    };

    g.ImportParser = Parser;

})(this);
