(function(g){

    var Parser = {

        parse: function(file) {

            var p = new Promise(function(resolve, reject) {

                var reader = new FileReader();

                reader.onload = function(e) {
                    Parser[file.type=='application/json' ? 'json':'csv'](e.target.result, resolve, reject);
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

            var data, headers = [];

            try {
                var data = JSON.parse(content);
            } catch(e) { return reject(e.message) }

            if (!Array.isArray(data)) {
                return reject('JSON needs to be an array of items!');
            }

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
        }
    };

    g.ImportParser = Parser;

})(this);