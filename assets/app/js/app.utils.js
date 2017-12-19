
// md5() function
!function(a){"use strict";function b(a,b){var c=(65535&a)+(65535&b),d=(a>>16)+(b>>16)+(c>>16);return d<<16|65535&c}function c(a,b){return a<<b|a>>>32-b}function d(a,d,e,f,g,h){return b(c(b(b(d,a),b(f,h)),g),e)}function e(a,b,c,e,f,g,h){return d(b&c|~b&e,a,b,f,g,h)}function f(a,b,c,e,f,g,h){return d(b&e|c&~e,a,b,f,g,h)}function g(a,b,c,e,f,g,h){return d(b^c^e,a,b,f,g,h)}function h(a,b,c,e,f,g,h){return d(c^(b|~e),a,b,f,g,h)}function i(a,c){a[c>>5]|=128<<c%32,a[(c+64>>>9<<4)+14]=c;var d,i,j,k,l,m=1732584193,n=-271733879,o=-1732584194,p=271733878;for(d=0;d<a.length;d+=16)i=m,j=n,k=o,l=p,m=e(m,n,o,p,a[d],7,-680876936),p=e(p,m,n,o,a[d+1],12,-389564586),o=e(o,p,m,n,a[d+2],17,606105819),n=e(n,o,p,m,a[d+3],22,-1044525330),m=e(m,n,o,p,a[d+4],7,-176418897),p=e(p,m,n,o,a[d+5],12,1200080426),o=e(o,p,m,n,a[d+6],17,-1473231341),n=e(n,o,p,m,a[d+7],22,-45705983),m=e(m,n,o,p,a[d+8],7,1770035416),p=e(p,m,n,o,a[d+9],12,-1958414417),o=e(o,p,m,n,a[d+10],17,-42063),n=e(n,o,p,m,a[d+11],22,-1990404162),m=e(m,n,o,p,a[d+12],7,1804603682),p=e(p,m,n,o,a[d+13],12,-40341101),o=e(o,p,m,n,a[d+14],17,-1502002290),n=e(n,o,p,m,a[d+15],22,1236535329),m=f(m,n,o,p,a[d+1],5,-165796510),p=f(p,m,n,o,a[d+6],9,-1069501632),o=f(o,p,m,n,a[d+11],14,643717713),n=f(n,o,p,m,a[d],20,-373897302),m=f(m,n,o,p,a[d+5],5,-701558691),p=f(p,m,n,o,a[d+10],9,38016083),o=f(o,p,m,n,a[d+15],14,-660478335),n=f(n,o,p,m,a[d+4],20,-405537848),m=f(m,n,o,p,a[d+9],5,568446438),p=f(p,m,n,o,a[d+14],9,-1019803690),o=f(o,p,m,n,a[d+3],14,-187363961),n=f(n,o,p,m,a[d+8],20,1163531501),m=f(m,n,o,p,a[d+13],5,-1444681467),p=f(p,m,n,o,a[d+2],9,-51403784),o=f(o,p,m,n,a[d+7],14,1735328473),n=f(n,o,p,m,a[d+12],20,-1926607734),m=g(m,n,o,p,a[d+5],4,-378558),p=g(p,m,n,o,a[d+8],11,-2022574463),o=g(o,p,m,n,a[d+11],16,1839030562),n=g(n,o,p,m,a[d+14],23,-35309556),m=g(m,n,o,p,a[d+1],4,-1530992060),p=g(p,m,n,o,a[d+4],11,1272893353),o=g(o,p,m,n,a[d+7],16,-155497632),n=g(n,o,p,m,a[d+10],23,-1094730640),m=g(m,n,o,p,a[d+13],4,681279174),p=g(p,m,n,o,a[d],11,-358537222),o=g(o,p,m,n,a[d+3],16,-722521979),n=g(n,o,p,m,a[d+6],23,76029189),m=g(m,n,o,p,a[d+9],4,-640364487),p=g(p,m,n,o,a[d+12],11,-421815835),o=g(o,p,m,n,a[d+15],16,530742520),n=g(n,o,p,m,a[d+2],23,-995338651),m=h(m,n,o,p,a[d],6,-198630844),p=h(p,m,n,o,a[d+7],10,1126891415),o=h(o,p,m,n,a[d+14],15,-1416354905),n=h(n,o,p,m,a[d+5],21,-57434055),m=h(m,n,o,p,a[d+12],6,1700485571),p=h(p,m,n,o,a[d+3],10,-1894986606),o=h(o,p,m,n,a[d+10],15,-1051523),n=h(n,o,p,m,a[d+1],21,-2054922799),m=h(m,n,o,p,a[d+8],6,1873313359),p=h(p,m,n,o,a[d+15],10,-30611744),o=h(o,p,m,n,a[d+6],15,-1560198380),n=h(n,o,p,m,a[d+13],21,1309151649),m=h(m,n,o,p,a[d+4],6,-145523070),p=h(p,m,n,o,a[d+11],10,-1120210379),o=h(o,p,m,n,a[d+2],15,718787259),n=h(n,o,p,m,a[d+9],21,-343485551),m=b(m,i),n=b(n,j),o=b(o,k),p=b(p,l);return[m,n,o,p]}function j(a){var b,c="";for(b=0;b<32*a.length;b+=8)c+=String.fromCharCode(a[b>>5]>>>b%32&255);return c}function k(a){var b,c=[];for(c[(a.length>>2)-1]=void 0,b=0;b<c.length;b+=1)c[b]=0;for(b=0;b<8*a.length;b+=8)c[b>>5]|=(255&a.charCodeAt(b/8))<<b%32;return c}function l(a){return j(i(k(a),8*a.length))}function m(a,b){var c,d,e=k(a),f=[],g=[];for(f[15]=g[15]=void 0,e.length>16&&(e=i(e,8*a.length)),c=0;16>c;c+=1)f[c]=909522486^e[c],g[c]=1549556828^e[c];return d=i(f.concat(k(b)),512+8*b.length),j(i(g.concat(d),640))}function n(a){var b,c,d="0123456789abcdef",e="";for(c=0;c<a.length;c+=1)b=a.charCodeAt(c),e+=d.charAt(b>>>4&15)+d.charAt(15&b);return e}function o(a){return unescape(encodeURIComponent(a))}function p(a){return l(o(a))}function q(a){return n(p(a))}function r(a,b){return m(o(a),o(b))}function s(a,b){return n(r(a,b))}function t(a,b,c){return b?c?r(b,a):s(b,a):c?p(a):q(a)}"function"==typeof define&&define.amd?define(function(){return t}):a.md5=t}(this);


(function(App, riot) {


    App.Utils = App.Utils || {};

    App.Utils.md5 = md5;
    App.Utils.str2json = UIkit.Utils.str2json;
    App.Utils.debounce = UIkit.Utils.debounce;

    App.Utils.isString    = function(val){ return "string"===typeof val; };
    App.Utils.isNumber    = function(val){ return "number"===typeof val; };
    App.Utils.isFunction  = function(val){ return "function"===typeof val; };
    App.Utils.isUndefined = function(val){ return "undefined"===typeof val; };
    App.Utils.isDefined   = function(val){ return "undefined"!==typeof val; };
    App.Utils.isObject    = function(val){ return null!==val && "object"===typeof val; };

    App.Utils.ucfirst = function(string) {
        return string[0].toUpperCase() + string.slice(1);
    };

    App.Utils.dirname = function(path) {
        return path.replace(/\\/g, '/').replace(/\/[^\/]*\/?$/, '');
    };

    App.Utils.basename = function(path, suffix) {

        var b = path;
        var lastChar = b.charAt(b.length - 1);

        if (lastChar === '/' || lastChar === '\\') {
            b = b.slice(0, -1);
        }

        b = b.replace(/^.*[\/\\]/g, '');

        if (typeof suffix === 'string' && b.substr(b.length - suffix.length) == suffix) {
            b = b.substr(0, b.length - suffix.length);
        }

        return b;
    };

    App.Utils.dateformat  = function(date, format) {

        var str;

        if (window.moment) {
            return window.moment(date).format(format || 'll');
        }

        if (window.Intl && Intl.DateTimeFormat) {
            str = (new Intl.DateTimeFormat()).format(date);
        } else {
            str = date.toDateString();
        }

        return str;
    };

    App.Utils.copyText = function (text, cb) {
        var inp = document.createElement('textarea');
        document.body.appendChild(inp)
        inp.value = text
        inp.select();
        try{ document.execCommand('copy',false); }catch(e){}
        inp.remove();
        if (cb) cb();
    };

    App.Utils.count = function(value) {

        if (!value) {
            return 0;
        }

        if (App.Utils.isObject(value)) {
            return Object.keys(value).length;
        }

        if (App.Utils.isString(value) || Array.isArray(value)) {
            return value.length;
        }

        return 0;
    };

    // Unix filename pattern matching *.jpg
    App.Utils.fnmatch = function(pattern, path) {

        path = path.split('/').pop();

        var parsedPattern = '^' + pattern.replace(/\//g, '\\/').
            replace(/\*\*/g, '(\\/[^\\/]+)*').
            replace(/\*/g, '[^\\/]+').
            replace(/((?!\\))\?/g, '$1.') + '$';

        parsedPattern = '^' + parsedPattern + '$';

        return (path.match(new RegExp(parsedPattern, 'i')) !== null);
    }

    App.Utils.sluggify = (function(){

        var defaults = {
                'delimiter': '-',
                'limit': undefined,
                'lowercase': true,
                'replacements': {},
                'transliterate': (typeof(XRegExp) === 'undefined') ? true : false
            },
            char_map = {
                'À': 'A', 'Á': 'A', 'Â': 'A', 'Ã': 'A', 'Ä': 'A', 'Å': 'A', 'Æ': 'AE', 'Ç': 'C', 'È': 'E', 'É': 'E', 'Ê': 'E', 'Ë': 'E', 'Ì': 'I', 'Í': 'I', 'Î': 'I', 'Ï': 'I', 'Ð': 'D', 'Ñ': 'N', 'Ò': 'O', 'Ó': 'O', 'Ô': 'O', 'Õ': 'O', 'Ö': 'O', 'Ő': 'O', 'Ø': 'O', 'Ù': 'U', 'Ú': 'U', 'Û': 'U', 'Ü': 'U', 'Ű': 'U', 'Ý': 'Y', 'Þ': 'TH', 'ß': 'ss', 'à': 'a', 'á': 'a', 'â': 'a', 'ã': 'a', 'ä': 'a', 'å': 'a', 'æ': 'ae', 'ç': 'c', 'è': 'e', 'é': 'e', 'ê': 'e', 'ë': 'e', 'ì': 'i', 'í': 'i', 'î': 'i', 'ï': 'i', 'ð': 'd', 'ñ': 'n', 'ò': 'o', 'ó': 'o', 'ô': 'o', 'õ': 'o', 'ö': 'o', 'ő': 'o', 'ø': 'o', 'ù': 'u', 'ú': 'u', 'û': 'u', 'ü': 'u', 'ű': 'u', 'ý': 'y', 'þ': 'th', 'ÿ': 'y', '©': '(c)', 'Α': 'A', 'Β': 'B', 'Γ': 'G', 'Δ': 'D', 'Ε': 'E', 'Ζ': 'Z', 'Η': 'H', 'Θ': '8', 'Ι': 'I', 'Κ': 'K', 'Λ': 'L', 'Μ': 'M', 'Ν': 'N', 'Ξ': '3', 'Ο': 'O', 'Π': 'P', 'Ρ': 'R', 'Σ': 'S', 'Τ': 'T', 'Υ': 'Y', 'Φ': 'F', 'Χ': 'X', 'Ψ': 'PS', 'Ω': 'W', 'Ά': 'A', 'Έ': 'E', 'Ί': 'I', 'Ό': 'O', 'Ύ': 'Y', 'Ή': 'H', 'Ώ': 'W', 'Ϊ': 'I', 'Ϋ': 'Y', 'α': 'a', 'β': 'b', 'γ': 'g', 'δ': 'd', 'ε': 'e', 'ζ': 'z', 'η': 'h', 'θ': '8', 'ι': 'i', 'κ': 'k', 'λ': 'l', 'μ': 'm', 'ν': 'n', 'ξ': '3', 'ο': 'o', 'π': 'p', 'ρ': 'r', 'σ': 's', 'τ': 't', 'υ': 'y', 'φ': 'f', 'χ': 'x', 'ψ': 'ps', 'ω': 'w', 'ά': 'a', 'έ': 'e', 'ί': 'i', 'ό': 'o', 'ύ': 'y', 'ή': 'h', 'ώ': 'w', 'ς': 's', 'ϊ': 'i', 'ΰ': 'y', 'ϋ': 'y', 'ΐ': 'i', 'Ş': 'S', 'İ': 'I', 'Ç': 'C', 'Ü': 'U', 'Ö': 'O', 'Ğ': 'G', 'ş': 's', 'ı': 'i', 'ç': 'c', 'ü': 'u', 'ö': 'o', 'ğ': 'g', 'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo', 'Ж': 'Zh', 'З': 'Z', 'И': 'I', 'Й': 'J', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N', 'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'C', 'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sh', 'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu', 'Я': 'Ya', 'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya', 'Є': 'Ye', 'І': 'I', 'Ї': 'Yi', 'Ґ': 'G', 'є': 'ye', 'і': 'i', 'ї': 'yi', 'ґ': 'g', 'Č': 'C', 'Ď': 'D', 'Ě': 'E', 'Ň': 'N', 'Ř': 'R', 'Š': 'S', 'Ť': 'T', 'Ů': 'U', 'Ž': 'Z', 'č': 'c', 'ď': 'd', 'ě': 'e', 'ň': 'n', 'ř': 'r', 'š': 's', 'ť': 't', 'ů': 'u', 'ž': 'z', 'Ą': 'A', 'Ć': 'C', 'Ę': 'e', 'Ł': 'L', 'Ń': 'N', 'Ó': 'o', 'Ś': 'S', 'Ź': 'Z', 'Ż': 'Z', 'ą': 'a', 'ć': 'c', 'ę': 'e', 'ł': 'l', 'ń': 'n', 'ó': 'o', 'ś': 's', 'ź': 'z', 'ż': 'z', 'Ā': 'A', 'Č': 'C', 'Ē': 'E', 'Ģ': 'G', 'Ī': 'i', 'Ķ': 'k', 'Ļ': 'L', 'Ņ': 'N', 'Š': 'S', 'Ū': 'u', 'Ž': 'Z', 'ā': 'a', 'č': 'c', 'ē': 'e', 'ģ': 'g', 'ī': 'i', 'ķ': 'k', 'ļ': 'l', 'ņ': 'n', 'š': 's', 'ū': 'u', 'ž': 'z'
            },
            alnum = RegExp('[^a-z0-9]+', 'ig');

        return function _slugify(s, options) {

            s       = String(s, options);
            options = Object(options);

            for (var k in defaults) {
                if (!options.hasOwnProperty(k)) options[k] = defaults[k];
            }

            for (var k in options.replacements) s = s.replace(RegExp(k, 'g'), options.replacements[k]);
            for (var k in char_map) s = s.replace(RegExp(k, 'g'), char_map[k]);

            s = s.replace(alnum, options.delimiter);
            s = s.replace(RegExp('[' + options.delimiter + ']{2,}', 'g'), options.delimiter);
            s = s.substring(0, options.limit);
            s = s.replace(RegExp('(^' + options.delimiter + '|' + options.delimiter + '$)', 'g'), '');
            return options.lowercase ? s.toLowerCase() : s;
        }

    })();

    App.Utils.letterAvatar = function(name, size) {
        name  = name || '';
        size  = size || 60;

        var colours = [
                "#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50",
                "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#95a5a6", "#f39c12", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d"
            ],

            nameSplit = String(name).toUpperCase().split(' '),
            initials, charIndex, colourIndex, canvas, context, dataURI;


        if (nameSplit.length == 1) {
            initials = nameSplit[0] ? nameSplit[0].charAt(0):'?';
        } else {
            initials = nameSplit[0].charAt(0) + nameSplit[1].charAt(0);
        }

        if (window.devicePixelRatio) {
            //size = (size * window.devicePixelRatio);
        }

        charIndex     = (initials == '?' ? 72 : initials.charCodeAt(0)) - 64;
        colourIndex   = charIndex % 20;
        canvas        = document.createElement('canvas');
        canvas.width  = size;
        canvas.height = size;
        context       = canvas.getContext("2d");

        context.fillStyle = colours[colourIndex - 1];
        context.fillRect (0, 0, canvas.width, canvas.height);
        context.font = Math.round(canvas.width/2)+"px Arial";
        context.textAlign = "center";
        context.fillStyle = "#FFF";
        context.fillText(initials, size / 2, size / 1.5);

        dataURI = canvas.toDataURL();
        canvas  = null;

        return dataURI;
    };

    App.Utils.worker = function(fn) {

        var worker = new Worker(URL.createObjectURL(new Blob(['(', fn.toString(), ')()'], { type: 'application/javascript' })));

        return worker;
    };

    App.Utils.worker.execute = function(fn, data) {

        var canceled = false, p = new Promise(function(r, f) {

           var w = new Worker(URL.createObjectURL(new Blob([
               [
                   'self.onmessage=function(e){',
                        'var release = function(result) { self.postMessage(result) }',
                        'var result = ('+fn.toString()+')(e.data, e)',
                        'if(result!==undefined) release(result);',
                    '}'

               ].join("\n")
           ], { type: 'application/javascript' })));

            w.onmessage = function(e) {
                if(!canceled) r(e.data || null, e);
                w = null;
            };

            w.onerror = function(e) {
                if(!canceled) f(e);
                w = null;
            };

            w.postMessage(data);
        });

        p.cancel = function() {
        	 canceled = true;
        };

        return p;
    };

    App.Utils.formatSize = function humanFileSize(size) {
        var i = Math.floor( Math.log(size) / Math.log(1024) );
        return Number(size) ? ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i] : 'n/a';
    };

    // custom renderer
    App.Utils.renderer = {};

    App.Utils.renderer.default = function(v) {
        v = String(v === undefined ? '': (['number','string'].indexOf(typeof(v)) > -1) ? v:JSON.stringify(v));
        return v.length > 30 ? v.substr(0,30)+'...':v;
    };

    App.Utils.renderer.location = function(v) {
        return v && v.address ? v.address : App.Utils.renderer.default(v);
    };

    App.Utils.renderer.image = function(v) {
        return v && v.path ? '<a href="'+(v.path.match(/^(http\:|https\:|\/\/)/) ? v.path:encodeURI(SITE_URL+'/'+v.path))+'" data-uk-lightbox title="'+App.i18n.get('Preview')+'"><i class="uk-icon-image"></i></a>' : App.Utils.renderer.default(v);
    };

    App.Utils.renderer.asset = function(v) {

        if (v && v.mime) {
            if (v.mime.match(/^image\//)) {

                var id = 'img'+Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);;

                App.request('/cockpit/utils/thumb_url', {src:ASSETS_URL+v.path,w:30,h:30}, 'text').then(function(url){

                    App.$('#'+id).attr('src', url);

                }).catch(function(e){
                    // todo
                });

                return '<img id="'+id+'" width="20" height="20">';
            }

            return '<span class="uk-badge">'+v.mime+'</span>';
        }

        return App.Utils.renderer.default(v);
    };

    App.Utils.renderer.gallery = function(v) {
        return Array.isArray(v) ? '<span class="uk-badge">'+(v.length+' '+App.i18n.get(v.length == 1 ? 'Image':'Images'))+'</span>' : App.Utils.renderer.default(v);
    };

    App.Utils.renderer.boolean = function(v) {
        return '<i class="uk-icon-circle uk-text-'+(v ? 'success':'danger')+'"></i>';
    };

    App.Utils.renderer.color = function(v) {
        return '<i class="uk-icon-circle" style="color:'+(v ? v:'transparent')+'"></i>';
    };

    App.Utils.renderer.colortag = App.Utils.renderer.color;

    App.Utils.renderer.file = function(v) {
        return v ? '<a title="'+v+'" dta-uk-tooltip><i class="uk-icon-paperclip></i></a>':null;
    };

    App.Utils.renderer.rating = function(v) {
        return (v===0 || v) ? '<span class="uk-badge">'+v+'</span>':null;
    };

    App.Utils.renderer.markdown = function(v) {
        return v ? '<i class="uk-icon-file-text-o" title="Markdown..." data-uk-tooltip></i>':null;
    };

    App.Utils.renderer.html = App.Utils.renderer.code = function(v) {
        return v ? '<i class="uk-icon-code" title="Code..." data-uk-tooltip></i>':null;
    };

    App.Utils.renderer.repeater = function(v) {
        var cnt = Array.isArray(v) ? v.length : 0;
        return '<span class="uk-badge">'+(cnt+(cnt ==1 ? ' Item' : ' Items'))+'</span>';
    };

    App.Utils.renderer.tags = App.Utils.renderer.multipleselect = function(v) {

        if (Array.isArray(v) && v.length > 1) {
            return '<span class="uk-badge" title="'+v.join(', ')+'" data-uk-tooltip>'+v.length+'</span>';
        }

        return Array.isArray(v) ? v.join(', ') : App.Utils.renderer.default(v);
    };

    App.Utils.renderer.layout = function(v) {
        return Array.isArray(v) ? '<span class="uk-badge">'+v.length+(v.length==1 ? ' Component':' Components')+'</span>' : App.Utils.renderer.default(v);
    };


    App.Utils.renderValue = function(renderer, v) {
        return (this.renderer[renderer] || this.renderer.default)(v);
    };

    // riot enhancments
    (function(riot){

        if (!riot) return;

        /**
         * override tag method to know which tags exist
         */

        riot.tag = riot.tag2 = (function(_tag) {

            riot.tags = {};

            return function() {

                riot.tags[arguments[0]] = {tpl:arguments[1],script:arguments[2]};

                return _tag.apply(riot, arguments);
            };

        })(riot.tag);

    })(riot);

    App.Utils.multiline = (function(){

        var stripIndent = function (str) {
        	var match = str.match(/^[ \t]*(?=\S)/gm);

        	if (!match) return str;

        	var indent = Math.min.apply(Math, match.map(function (el) {
        		return el.length;
        	}));

        	var re = new RegExp('^[ \\t]{' + indent + '}', 'gm');

        	return indent > 0 ? str.replace(re, '') : str;
        };

        // start matching after: comment start block => ! or @preserve => optional whitespace => newline
        // stop matching before: last newline => optional whitespace => comment end block
        var reCommentContents = /\/\*!?(?:\@preserve)?[ \t]*(?:\r\n|\n)([\s\S]*?)(?:\r\n|\n)[ \t]*\*\//;

        var multiline = function (fn) {
        	if (typeof fn !== 'function') {
        		throw new TypeError('Expected a function');
        	}

        	var match = reCommentContents.exec(fn.toString());

        	if (!match) {
        		throw new TypeError('Multiline comment missing.');
        	}

        	return match[1];
        };

        multiline.stripIndent = function (fn) {
        	return stripIndent(multiline(fn));
        };

        return multiline;

    })();

    App.Utils.generateToken = function(bits, base) {

        if (!base) base = 16;
        if (bits === undefined) bits = 128;
        if (bits <= 0) return '0';
        var digits = Math.log(Math.pow(2, bits)) / Math.log(base);
        for (var i = 2; digits === Infinity; i *= 2) {
            digits = Math.log(Math.pow(2, bits / i)) / Math.log(base) * i;
        }
        var rem = digits - Math.floor(digits), res = '';
        for (var i = 0; i < Math.floor(digits); i++) {
            var x = Math.floor(Math.random() * base).toString(base);
            res = x + res;
        }
        if (rem) {
            var b = Math.pow(base, rem);
            var x = Math.floor(Math.random() * b).toString(base);
            res = x + res;
        }
        var parsed = parseInt(res, base);
        if (parsed !== Infinity && parsed >= Math.pow(2, bits)) {
            return hat(bits, base)
        }
        else return res;
    };

})(App, riot);
