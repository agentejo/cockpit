(function(g){

    var Filter = {

        filter: function(field, value){

            var _resolve, _reject, p = new Promise(function(resolve, reject) {
                _resolve = resolve;
                _reject = reject;
            });

            p.resolve = _resolve;
            p.reject = _reject;


            if (this[field.type]) {
                this[field.type].apply(p, [value, field]);
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
        }
    };

    g.ImportFilter = Filter;

})(this);