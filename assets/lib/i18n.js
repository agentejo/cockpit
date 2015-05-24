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

                    var args = arguments.length ==1 ? [] : Array.prototype.slice.call(arguments, 1);

                    if (!this.__data[key]) {
                        return this.printf(key, args);
                    }

                    return this.printf(String(this.__data[key]), args);
                },

                key: function(key) {
                  return this.__data[key] ? this.__data[key] : null;
                },

                printf: function() {
                    // From: http://phpjs.org/functions
                      // +   original by: Ash Searle (http://hexmen.com/blog/)
                      // + namespaced by: Michael White (http://getsprink.com)
                      // +    tweaked by: Jack
                      // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
                      // +      input by: Paulo Freitas
                      // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
                      // +      input by: Brett Zamir (http://brett-zamir.me)
                      // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
                      // +   improved by: Dj
                      // +   improved by: Allidylls
                      // *     example 1: sprintf("%01.2f", 123.1);
                      // *     returns 1: 123.10
                      // *     example 2: sprintf("[%10s]", 'monkey');
                      // *     returns 2: '[    monkey]'
                      // *     example 3: sprintf("[%'#10s]", 'monkey');
                      // *     returns 3: '[####monkey]'
                      // *     example 4: sprintf("%d", 123456789012345);
                      // *     returns 4: '123456789012345'
                      var regex = /%%|%(\d+\$)?([-+\'#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuideEfFgG])/g;
                      var a = arguments,
                        i = 0,
                        format = a[i++];

                      // pad()
                      var pad = function (str, len, chr, leftJustify) {
                        if (!chr) {
                          chr = ' ';
                        }
                        var padding = (str.length >= len) ? '' : Array(1 + len - str.length >>> 0).join(chr);
                        return leftJustify ? str + padding : padding + str;
                      };

                      // justify()
                      var justify = function (value, prefix, leftJustify, minWidth, zeroPad, customPadChar) {
                        var diff = minWidth - value.length;
                        if (diff > 0) {
                          if (leftJustify || !zeroPad) {
                            value = pad(value, minWidth, customPadChar, leftJustify);
                          } else {
                            value = value.slice(0, prefix.length) + pad('', diff, '0', true) + value.slice(prefix.length);
                          }
                        }
                        return value;
                      };

                      // formatBaseX()
                      var formatBaseX = function (value, base, prefix, leftJustify, minWidth, precision, zeroPad) {
                        // Note: casts negative numbers to positive ones
                        var number = value >>> 0;
                        prefix = prefix && number && {
                          '2': '0b',
                          '8': '0',
                          '16': '0x'
                        }[base] || '';
                        value = prefix + pad(number.toString(base), precision || 0, '0', false);
                        return justify(value, prefix, leftJustify, minWidth, zeroPad);
                      };

                      // formatString()
                      var formatString = function (value, leftJustify, minWidth, precision, zeroPad, customPadChar) {
                        if (precision != null) {
                          value = value.slice(0, precision);
                        }
                        return justify(value, '', leftJustify, minWidth, zeroPad, customPadChar);
                      };

                      // doFormat()
                      var doFormat = function (substring, valueIndex, flags, minWidth, _, precision, type) {
                        var number;
                        var prefix;
                        var method;
                        var textTransform;
                        var value;

                        if (substring === '%%') {
                          return '%';
                        }

                        // parse flags
                        var leftJustify = false,
                          positivePrefix = '',
                          zeroPad = false,
                          prefixBaseX = false,
                          customPadChar = ' ';
                        var flagsl = flags.length;
                        for (var j = 0; flags && j < flagsl; j++) {
                          switch (flags.charAt(j)) {
                          case ' ':
                            positivePrefix = ' ';
                            break;
                          case '+':
                            positivePrefix = '+';
                            break;
                          case '-':
                            leftJustify = true;
                            break;
                          case "'":
                            customPadChar = flags.charAt(j + 1);
                            break;
                          case '0':
                            zeroPad = true;
                            break;
                          case '#':
                            prefixBaseX = true;
                            break;
                          }
                        }

                        // parameters may be null, undefined, empty-string or real valued
                        // we want to ignore null, undefined and empty-string values
                        if (!minWidth) {
                          minWidth = 0;
                        } else if (minWidth === '*') {
                          minWidth = +a[i++];
                        } else if (minWidth.charAt(0) == '*') {
                          minWidth = +a[minWidth.slice(1, -1)];
                        } else {
                          minWidth = +minWidth;
                        }

                        // Note: undocumented perl feature:
                        if (minWidth < 0) {
                          minWidth = -minWidth;
                          leftJustify = true;
                        }

                        if (!isFinite(minWidth)) {
                          throw new Error('sprintf: (minimum-)width must be finite');
                        }

                        if (!precision) {
                          precision = 'fFeE'.indexOf(type) > -1 ? 6 : (type === 'd') ? 0 : undefined;
                        } else if (precision === '*') {
                          precision = +a[i++];
                        } else if (precision.charAt(0) == '*') {
                          precision = +a[precision.slice(1, -1)];
                        } else {
                          precision = +precision;
                        }

                        // grab value using valueIndex if required?
                        value = valueIndex ? a[valueIndex.slice(0, -1)] : a[i++];

                        switch (type) {
                        case 's':
                          return formatString(String(value), leftJustify, minWidth, precision, zeroPad, customPadChar);
                        case 'c':
                          return formatString(String.fromCharCode(+value), leftJustify, minWidth, precision, zeroPad);
                        case 'b':
                          return formatBaseX(value, 2, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
                        case 'o':
                          return formatBaseX(value, 8, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
                        case 'x':
                          return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
                        case 'X':
                          return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad).toUpperCase();
                        case 'u':
                          return formatBaseX(value, 10, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
                        case 'i':
                        case 'd':
                          number = +value || 0;
                          number = Math.round(number - number % 1); // Plain Math.round doesn't just truncate
                          prefix = number < 0 ? '-' : positivePrefix;
                          value = prefix + pad(String(Math.abs(number)), precision, '0', false);
                          return justify(value, prefix, leftJustify, minWidth, zeroPad);
                        case 'e':
                        case 'E':
                        case 'f': // Should handle locales (as per setlocale)
                        case 'F':
                        case 'g':
                        case 'G':
                          number = +value;
                          prefix = number < 0 ? '-' : positivePrefix;
                          method = ['toExponential', 'toFixed', 'toPrecision']['efg'.indexOf(type.toLowerCase())];
                          textTransform = ['toString', 'toUpperCase']['eEfFgG'.indexOf(type) % 2];
                          value = prefix + Math.abs(number)[method](precision);
                          return justify(value, prefix, leftJustify, minWidth, zeroPad)[textTransform]();
                        default:
                          return substring;
                        }
                      };

                      return format.replace(regex, doFormat);
                }

        };

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