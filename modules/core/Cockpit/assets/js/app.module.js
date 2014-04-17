(function(global, $, $win, $doc, App){

    var module = angular.module('app', []);

    module.config(function($sceProvider) {

      // Completely disable SCE
      $sceProvider.enabled(false);

    });

    module.run(function($rootScope, $http){

        $rootScope.app = {
            "notifications": []
        };
    });

    module.directive("appClock", function(){

        return {
            restrict: 'A',

            link: function (scope, elm, attrs) {

                var timer = setInterval((function(){

                    var fn = function(){
                        elm.text(i18n_date(attrs.appClock || "H:i"));
                    };

                    fn();

                    return fn;

                })(), 60000);
            }
        };
    });

    module.directive("multipleSelect", function(){

        return {
            restrict: 'A',

            link: function (scope, elm, attrs) {

                var options        = $.extend({}, MultipleSelect.defaults, scope.$eval(attrs.multipleSelect))
                    multipleSelect = new MultipleSelect(elm, options);

                if (scope && scope.$parent) {
                    elm.on('multiple-select', function(e, items){
                        scope.$parent.$broadcast('multiple-select', {"items":items});
                    });
                }
            }
        };
    });

    module.filter('fmtdate', function() {
        return function(input, format) {

            if(!input) return input;
            if(!String(input).match(/^\d+$/)) input = strtotime(input);
            if(!format) format = "Y-m-d H:i";

            return i18n_date(format, input);
        }
    });

    module.filter('md5hash', function() {
        return function(input) {
            return md5(input);
        }
    });

    module.filter('base64', function() {
        return function(input) {
            return btoa(input);
        }
    });

    module.directive('appInplaceEdit', function() {
      return {
        restrict: 'A',
        require: 'ngModel',
        link: function ($scope, element, attrs, ngModel) {

            var attributes = false;

            if(attrs.appInplaceEdit) {
                try { attributes = (new Function("", "var json = " + attrs.appInplaceEdit + "; return JSON.parse(JSON.stringify(json));"))(); } catch (e) {}
            }

            ngModel.$render = function() {

                element.on("dblclick", function(){

                    if (element.css('display')=='block' && !(attributes && attributes["type"])) {
                        var input = $('<textarea>'+ngModel.$viewValue+'</textarea>').css("min-height", element.height());
                    } else {
                        var input = $('<input type="text" value="'+ngModel.$viewValue+'">');
                    }

                    if(attributes) input.attr(attributes);

                    input.on("change", function(){

                        if(input.is(":invalid")) return;

                        ngModel.$setViewValue(input.val());

                        $scope.$apply();
                    })

                    element.html(input.on("blur", function(){
                        if(input.is(":invalid")) return;
                        element.html(ngModel.$viewValue);
                    }));

                    setTimeout(function(){ input.focus(); }, 50);
                });
            };
        }
      };
    });

    module.directive("appConfirmLink", function(){

        return {
            restrict: 'A',

            link: function (scope, elm, attrs) {

                if(elm.is("a")){

                    var msg = attrs.appConfirmLink || App.i18n.get("Are you sure?");

                    elm.on("click", function(e){
                        e.preventDefault();

                        App.Ui.confirm(msg, function() {
                            location.href = elm.attr("href");
                        });
                    });
                }
            }
        };
    });

    // global callbacks

    module.callbacks = {success:{}, error:{}};

    module.callbacks.error.http = function(data, status, headers, config){
        App.notify(data ? String(data) : App.i18n.get("Uuups, something went wrong..."), "danger");
    };

    App.module = module;
    App.modules.push('app');


    // helpers

    function i18n_date(format, input) {

        var d = date(format, input);

        if(App.i18n.key("@meta") && App.i18n.key("@meta").date) {

            var meta = App.i18n.key("@meta").date;

            // weekdays
            d = str_replace(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], meta.longdays, d);
            d = str_replace(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], meta.shortdays, d);

            // months
            d = str_replace(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'], meta.longmonths, d);
            d = str_replace(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], meta.shortmonths, d);
        }

        return d;
    }


    function str_replace(e,d,a,f){var b=0,c=0,g="",h="",k=0,l=0;e=[].concat(e);d=[].concat(d);var m="[object Array]"===Object.prototype.toString.call(d),n="[object Array]"===Object.prototype.toString.call(a);a=[].concat(a);f&&(this.window[f]=0);b=0;for(k=a.length;b<k;b++)if(""!==a[b])for(c=0,l=e.length;c<l;c++)g=a[b]+"",h=m?void 0!==d[c]?d[c]:"":d[0],a[b]=g.split(e[c]).join(h),f&&a[b]!==g&&(this.window[f]+=(g.length-a[b].length)/e[c].length);return n?a:a[0]};

    function date(k,l){var d,a,h="Sun Mon Tues Wednes Thurs Fri Satur January February March April May June July August September October November December".split(" "),f=/\\?(.?)/gi,g=function(b,c){return a[b]?a[b]():c},e=function(b,a){for(b=String(b);b.length<a;)b="0"+b;return b};a={d:function(){return e(a.j(),2)},D:function(){return a.l().slice(0,3)},j:function(){return d.getDate()},l:function(){return h[a.w()]+"day"},N:function(){return a.w()||7},S:function(){var b=a.j(),c=b%10;3>=c&&1==parseInt(b%
    100/10,10)&&(c=0);return["st","nd","rd"][c-1]||"th"},w:function(){return d.getDay()},z:function(){var b=new Date(a.Y(),a.n()-1,a.j()),c=new Date(a.Y(),0,1);return Math.round((b-c)/864E5)},W:function(){var b=new Date(a.Y(),a.n()-1,a.j()-a.N()+3),c=new Date(b.getFullYear(),0,4);return e(1+Math.round((b-c)/864E5/7),2)},F:function(){return h[6+a.n()]},m:function(){return e(a.n(),2)},M:function(){return a.F().slice(0,3)},n:function(){return d.getMonth()+1},t:function(){return(new Date(a.Y(),a.n(),0)).getDate()},
    L:function(){var b=a.Y();return 0===b%4&0!==b%100|0===b%400},o:function(){var b=a.n(),c=a.W();return a.Y()+(12===b&&9>c?1:1===b&&9<c?-1:0)},Y:function(){return d.getFullYear()},y:function(){return a.Y().toString().slice(-2)},a:function(){return 11<d.getHours()?"pm":"am"},A:function(){return a.a().toUpperCase()},B:function(){var a=3600*d.getUTCHours(),c=60*d.getUTCMinutes(),f=d.getUTCSeconds();return e(Math.floor((a+c+f+3600)/86.4)%1E3,3)},g:function(){return a.G()%12||12},G:function(){return d.getHours()},
    h:function(){return e(a.g(),2)},H:function(){return e(a.G(),2)},i:function(){return e(d.getMinutes(),2)},s:function(){return e(d.getSeconds(),2)},u:function(){return e(1E3*d.getMilliseconds(),6)},e:function(){throw"Not supported (see source code of date() for timezone on how to add support)";},I:function(){var b=new Date(a.Y(),0),c=Date.UTC(a.Y(),0),d=new Date(a.Y(),6),e=Date.UTC(a.Y(),6);return b-c!==d-e?1:0},O:function(){var a=d.getTimezoneOffset(),c=Math.abs(a);return(0<a?"-":"+")+e(100*Math.floor(c/
    60)+c%60,4)},P:function(){var b=a.O();return b.substr(0,3)+":"+b.substr(3,2)},T:function(){return"UTC"},Z:function(){return 60*-d.getTimezoneOffset()},c:function(){return"Y-m-d\\TH:i:sP".replace(f,g)},r:function(){return"D, d M Y H:i:s O".replace(f,g)},U:function(){return d/1E3|0}};this.date=function(a,c){d=void 0===c?new Date:c instanceof Date?new Date(c):new Date(1E3*c);return a.replace(f,g)};return this.date(k,l)};

    function strtotime(c,e){function m(a){var c=a.split(" ");a=c[0];var b=c[1].substring(0,3),e=/\d+/.test(a),f=("last"===a?-1:1)*("ago"===c[2]?-1:1);e&&(f*=parseInt(a,10));if(g.hasOwnProperty(b)&&!c[1].match(/^mon(day|\.)?$/i))return d["set"+g[b]](d["get"+g[b]]()+f);if("wee"===b)return d.setDate(d.getDate()+7*f);if("next"===a||"last"===a)c=f,b=l[b],"undefined"!==typeof b&&(b-=d.getDay(),0===b?b=7*c:0<b&&"last"===a?b-=7:0>b&&"next"===a&&(b+=7),d.setDate(d.getDate()+b));else if(!e)return!1;return!0}var a,
    h,d,l,g,k;if(!c)return null;c=c.trim().replace(/\s{2,}/g," ").replace(/[\t\r\n]/g,"").toLowerCase();if("now"===c)return null===e||isNaN(e)?(new Date).getTime()/1E3|0:e|0;if(!isNaN(a=Date.parse(c)))return a/1E3|0;if("now"===c)return(new Date).getTime()/1E3;if(!isNaN(a=Date.parse(c)))return a/1E3;if(a=c.match(/^(\d{2,4})-(\d{2})-(\d{2})(?:\s(\d{1,2}):(\d{2})(?::\d{2})?)?(?:\.(\d+)?)?$/))return h=0<=a[1]&&69>=a[1]?+a[1]+2E3:a[1],new Date(h,parseInt(a[2],10)-1,a[3],a[4]||0,a[5]||0,a[6]||0,a[7]||0)/1E3;
    d=e?new Date(1E3*e):new Date;l={sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6};g={yea:"FullYear",mon:"Month",day:"Date",hou:"Hours",min:"Minutes",sec:"Seconds"};a=c.match(RegExp("([+-]?\\d+\\s(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)|(last|next)\\s(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?))(\\sago)?",
    "gi"));if(!a)return!1;k=0;for(h=a.length;k<h;k++)if(!m(a[k]))return!1;return d.getTime()/1E3};

    function md5(n){var g=function(b,a){var d,c,e,f,g;e=b&2147483648;f=a&2147483648;d=b&1073741824;c=a&1073741824;g=(b&1073741823)+(a&1073741823);return d&c?g^2147483648^e^f:d|c?g&1073741824?g^3221225472^e^f:g^1073741824^e^f:g^e^f},h=function(b,a,d,c,e,f,h){b=g(b,g(g(a&d|~a&c,e),h));return g(b<<f|b>>>32-f,a)},k=function(b,a,d,c,e,f,h){b=g(b,g(g(a&c|d&~c,e),h));return g(b<<f|b>>>32-f,a)},m=function(b,a,c,d,e,f,h){b=g(b,g(g(a^c^d,e),h));return g(b<<f|b>>>32-f,a)},l=function(b,a,c,d,f,e,h){b=g(b,g(g(c^(a|
    ~d),f),h));return g(b<<e|b>>>32-e,a)},p=function(b){var a="",c="",d;for(d=0;3>=d;d++)c=b>>>8*d&255,c="0"+c.toString(16),a+=c.substr(c.length-2,2);return a},e=[],f,q,r,s,t,b,a,d,c;n=utf8_encode(n);e=function(b){var a,c=b.length;a=c+8;for(var d=16*((a-a%64)/64+1),e=Array(d-1),f=0,g=0;g<c;)a=(g-g%4)/4,f=8*(g%4),e[a]|=b.charCodeAt(g)<<f,g++;a=(g-g%4)/4;e[a]|=128<<8*(g%4);e[d-2]=c<<3;e[d-1]=c>>>29;return e}(n);b=1732584193;a=4023233417;d=2562383102;c=271733878;n=e.length;for(f=0;f<n;f+=16)q=b,r=a,s=d,
    t=c,b=h(b,a,d,c,e[f+0],7,3614090360),c=h(c,b,a,d,e[f+1],12,3905402710),d=h(d,c,b,a,e[f+2],17,606105819),a=h(a,d,c,b,e[f+3],22,3250441966),b=h(b,a,d,c,e[f+4],7,4118548399),c=h(c,b,a,d,e[f+5],12,1200080426),d=h(d,c,b,a,e[f+6],17,2821735955),a=h(a,d,c,b,e[f+7],22,4249261313),b=h(b,a,d,c,e[f+8],7,1770035416),c=h(c,b,a,d,e[f+9],12,2336552879),d=h(d,c,b,a,e[f+10],17,4294925233),a=h(a,d,c,b,e[f+11],22,2304563134),b=h(b,a,d,c,e[f+12],7,1804603682),c=h(c,b,a,d,e[f+13],12,4254626195),d=h(d,c,b,a,e[f+14],17,
    2792965006),a=h(a,d,c,b,e[f+15],22,1236535329),b=k(b,a,d,c,e[f+1],5,4129170786),c=k(c,b,a,d,e[f+6],9,3225465664),d=k(d,c,b,a,e[f+11],14,643717713),a=k(a,d,c,b,e[f+0],20,3921069994),b=k(b,a,d,c,e[f+5],5,3593408605),c=k(c,b,a,d,e[f+10],9,38016083),d=k(d,c,b,a,e[f+15],14,3634488961),a=k(a,d,c,b,e[f+4],20,3889429448),b=k(b,a,d,c,e[f+9],5,568446438),c=k(c,b,a,d,e[f+14],9,3275163606),d=k(d,c,b,a,e[f+3],14,4107603335),a=k(a,d,c,b,e[f+8],20,1163531501),b=k(b,a,d,c,e[f+13],5,2850285829),c=k(c,b,a,d,e[f+2],
    9,4243563512),d=k(d,c,b,a,e[f+7],14,1735328473),a=k(a,d,c,b,e[f+12],20,2368359562),b=m(b,a,d,c,e[f+5],4,4294588738),c=m(c,b,a,d,e[f+8],11,2272392833),d=m(d,c,b,a,e[f+11],16,1839030562),a=m(a,d,c,b,e[f+14],23,4259657740),b=m(b,a,d,c,e[f+1],4,2763975236),c=m(c,b,a,d,e[f+4],11,1272893353),d=m(d,c,b,a,e[f+7],16,4139469664),a=m(a,d,c,b,e[f+10],23,3200236656),b=m(b,a,d,c,e[f+13],4,681279174),c=m(c,b,a,d,e[f+0],11,3936430074),d=m(d,c,b,a,e[f+3],16,3572445317),a=m(a,d,c,b,e[f+6],23,76029189),b=m(b,a,d,c,
    e[f+9],4,3654602809),c=m(c,b,a,d,e[f+12],11,3873151461),d=m(d,c,b,a,e[f+15],16,530742520),a=m(a,d,c,b,e[f+2],23,3299628645),b=l(b,a,d,c,e[f+0],6,4096336452),c=l(c,b,a,d,e[f+7],10,1126891415),d=l(d,c,b,a,e[f+14],15,2878612391),a=l(a,d,c,b,e[f+5],21,4237533241),b=l(b,a,d,c,e[f+12],6,1700485571),c=l(c,b,a,d,e[f+3],10,2399980690),d=l(d,c,b,a,e[f+10],15,4293915773),a=l(a,d,c,b,e[f+1],21,2240044497),b=l(b,a,d,c,e[f+8],6,1873313359),c=l(c,b,a,d,e[f+15],10,4264355552),d=l(d,c,b,a,e[f+6],15,2734768916),a=
    l(a,d,c,b,e[f+13],21,1309151649),b=l(b,a,d,c,e[f+4],6,4149444226),c=l(c,b,a,d,e[f+11],10,3174756917),d=l(d,c,b,a,e[f+2],15,718787259),a=l(a,d,c,b,e[f+9],21,3951481745),b=g(b,q),a=g(a,r),d=g(d,s),c=g(c,t);return(p(b)+p(a)+p(d)+p(c)).toLowerCase()}

    function utf8_encode(n){n=(n+"").replace(/\r\n/g,"\n").replace(/\r/g,"\n");var g="",h,k,m=0;h=k=0;for(var m=n.length,l=0;l<m;l++){var p=n.charCodeAt(l),e=null;128>p?k++:e=127<p&&2048>p?String.fromCharCode(p>>6|192)+String.fromCharCode(p&63|128):String.fromCharCode(p>>12|224)+String.fromCharCode(p>>6&63|128)+String.fromCharCode(p&63|128);null!=e&&(k>h&&(g+=n.substring(h,k)),g+=e,h=k=l+1)}k>h&&(g+=n.substring(h,n.length));return g};

})(this, jQuery, jQuery(window), jQuery(document), App);