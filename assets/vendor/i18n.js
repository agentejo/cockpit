/**
 * Translation tool
 */
(function(global){

        function extend(destination, source) {
            
            if (!destination || !source) return destination;

            for (var field in source) {
                if (destination[field] === source[field]) continue;
                destination[field] = source[field];
            }

            return destination;
        }


        var i18n = {
                
                __data : {},

                register: function(key, data){
                    
                    switch(arguments.length) {
                        case 1:
                            extend(this.__data, key);
                            break;
                        case 2:
                            this.__data[key] = data;
                            break;
                    }
                },
                get: function(key){

                    if (!this.__data[key]) {
                        return key;
                    }

                    var args = arguments.length ==1 ? [] : Array.prototype.slice.call(arguments, 1),
                        ret  = String(this.__data[key]);

                    return this.printf(ret, args);
                },

                /*
                 * printf()
                 * C-printf like function, which substitutes %s with parameters
                 * given in list. %%s is used to escape %s.
                 * src: https://github.com/recurser/jquery-i18n/blob/master/jquery.i18n.js
                 *
                 * @param string S : string to perform printf on.
                 * @param string L : Array of arguments for printf()
                 */
                printf: function(str, args) {
                    if (!args) return str;

                    var result = '',
                        search = /%(\d+)\$s/g;

                    // Replace %n1$ where n is a number.
                    
                    var matches = search.exec(str), index;

                    while (matches) {
                        index   = parseInt(matches[1], 10) - 1;
                        str     = str.replace('%' + matches[1] + '\$s', (args[index]));
                        matches = search.exec(str);
                    }

                    var parts = str.split('%s');

                    if (parts.length > 1) {
                        for(var i = 0; i < args.length; i++) {
                            // If the part ends with a '%' chatacter, we've encountered a literal
                            // '%%s', which we should output as a '%s'. To achieve this, add an
                            // 's' on the end and merge it with the next part.
                            if (parts[i].length > 0 && parts[i].lastIndexOf('%') == (parts[i].length - 1)) {
                                parts[i] += 's' + parts.splice(i + 1, 1)[0];
                            }

                            // Append the part and the substitution to the result.
                            result += parts[i] + args[i];
                        }
                    }

                      return result + parts[parts.length - 1];
                }

        };


        if (global.i18nDict) {
            i18n.register(global.i18nDict);
        }

        // AMD support
        if (typeof define === 'function' && define.amd) {
            define(function () { return i18n; });

        // CommonJS and Node.js module support.
        } else if (typeof exports !== 'undefined') {
            // Support Node.js specific `module.exports` (which can be a function)
            if (typeof module != 'undefined' && module.exports) {
             exports = module.exports = i18n;
            }
            // But always support CommonJS module 1.1.1 spec (`exports` cannot be a function)
            exports.i18n = i18n;
        } else {
            // browser client
            window.i18n = i18n;
        }

})(this);