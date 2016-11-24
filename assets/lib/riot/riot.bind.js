
/**
 * simple two way data-binding for riot
 */

(function(riot, global){

    riot.util.bind = function(tag, namespace) {

        var root = tag.root,
            attr = (namespace ? namespace+'-':'')+'bind',
            attrSelector = '['+attr+']';

        function update() {

            var field;

            Array.prototype.forEach.call(root.querySelectorAll(attrSelector), function(ele) {

                field = ele.getAttribute(attr);

                if (!ele.$boundTo) {
                    init(ele);
                }

                if (ele.$boundTo !== tag ) {
                    return;
                }

                var value = ele.$getValue();

                if (JSON.stringify(ele.$value) !== JSON.stringify(value)) {
                    ele.$value = value;
                    ele.$updateValue(value, field);
                }
            });
        }


        function init(ele) {

            ele.$boundTo = tag;

            ele.$getValue = function(field) {

                field = field || ele.getAttribute(attr);

                var value = null;

                if (ele.$boundTo !== tag ) {
                    return;
                }

                try {
                    value = (new Function('tag', 'return tag.'+field+';'))(tag);
                } catch(e) {}

                return value;
            };

            ele.$setValue = (function(fn, body) {

                var field, segments, cache = {};

                return function(value, silent, field) {

                    field = field || ele.getAttribute(attr);

                    if (!cache[field]) {

                        segments = field.split('.');

                        var current = tag;

                        try {

                            for (var i = 0;i<segments.length;i++) {

                                if (segments[i].indexOf('[') != -1) break;

                                if (current[segments[i]] === undefined ) {

                                    if (segments[ i + 1 ]) {
                                        current[segments[i]] = {};
                                    } else {
                                        current[segments[i]] = null;
                                    }
                                }

                                current = current[segments[i]];
                            }

                        }catch(e){}

                        cache[field] = true;
                    }

                    body = 'try{ tag.'+field+' = val; if(!silent) { tag.update(); } tag.trigger("bindingupdated", ["'+field+'", val]);return true;}catch(e){ console.log(e);return false; }';

                    fn = new Function('tag', 'val', 'silent', body);

                    return fn(tag, value, silent);
                };

            })();


            ele.$updateValue = function(value) {};


            if (['input', 'select', 'textarea'].indexOf(ele.nodeName.toLowerCase()) !== -1) {

                var isCheckbox = (ele.nodeName == 'INPUT' && ele.getAttribute('type') == 'checkbox');

                ele.addEventListener(ele.getAttribute('bind-event') || 'change', function() {

                    try {

                        if (isCheckbox) {
                            ele.$setValue(ele.checked);
                        } else {
                            ele.$setValue(ele.value);
                        }

                    } catch(e) {}

                }, false);

                ele.$updateValue = (function(fn, body) {

                    if (isCheckbox) {
                        body = 'input.checked = val ? true:false;';
                    } else {
                        body = 'input.value = val || "";';
                    }

                    fn = new Function('input', 'val', 'try{'+body+'}catch(e){}');

                    return function(value) {
                        fn(ele, value);
                    };

                })();

            } else {

                if (ele._tag) {

                    ele._tag.$getValue = ele.$getValue;
                    ele._tag.$setValue = ele.$setValue;
                    ele._tag.$boundTo  = tag;

                    ele.$updateValue = function(value, field) {

                        if (ele._tag.$updateValue) {

                            ele._tag.$updateValue.apply(ele._tag, arguments);
                        }
                    };

                    if (ele._tag.$initBind) {
                        ele._tag.$initBind.apply(ele._tag, [tag]);
                    }

                }
            }
        }

        // init values
        tag.on('mount', function() {
            update();
        });

        tag.on('updated', function() {
            update();
        });

        tag.on('bind', function() {
            update();
        });


        tag.$bindUpdate = function() {
            update();
        };
    };

    var Mixin = {
        init: function() {
            riot.util.bind(this);
        }
    };

    global.RiotBindMixin = Mixin;

})(riot, this);
