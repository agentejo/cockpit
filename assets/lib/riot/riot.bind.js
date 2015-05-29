
/**
 * simple two way data-binding for riot
 */

(function(riot){

    riot.util.bind = function(tag, namespace) {

        var root = tag.root,
            attr = (namespace ? namespace+'-':'')+'bind',
            attrSelector = '['+attr+']';

        function update() {

            Array.prototype.forEach.call(root.querySelectorAll(attrSelector), function(ele) {

                var value = null;

                if (!ele.$boundTo) {
                    init(ele);
                }

                if (ele.$boundTo !== tag ) {
                    return;
                }

                 try {

                    value = (new Function('tag', 'return tag.'+ele.getAttribute(attr)+';'))(tag);

                } catch(e) {}


                if (JSON.stringify(ele.$value) !== JSON.stringify(value)) {
                    ele.$value = value;
                    ele.$updateValue(value);
                }
            });
        }


        function init(ele) {

            ele.$boundTo = tag;

            ele.$setValue = (function(fn, body) {

                body = 'try{ tag.'+ele.getAttribute(attr)+' = val;tag.update(); return true;}catch(e){ return false; }';

                fn = new Function('tag', 'val', body);

                return function(value) {
                    return fn(tag, value);
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
                        body = 'input.value = val;';
                    }

                    fn = new Function('input', 'val', 'try{'+body+'}catch(e){}');

                    return function(value) {
                        fn(ele, value);
                    };

                })();

            } else {

                if (ele._tag) {

                    ele._tag.$setValue = ele.$setValue;
                    ele._tag.$boundTo  = tag;

                    ele.$updateValue = function(value) {

                        if (ele._tag && ele._tag.$updateValue) {

                            ele._tag.$updateValue.apply(ele._tag, [value]);
                        }
                    };
                }

            }
        }

        // init values
        tag.on('mount updated bind', function() {
            update();
        });
    };

})(riot);
