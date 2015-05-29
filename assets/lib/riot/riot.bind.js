
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

            ele.$setValue = function(value) {

                try {
                    (new Function('tag', 'val', 'tag.'+ele.getAttribute(attr)+' = val;tag.update(); '))(tag, value);
                } catch(e) {}
            };


            ele.$updateValue = function(value) {};


            if (['input', 'select', 'textarea'].indexOf(ele.nodeName.toLowerCase()) !== -1) {

                ele.addEventListener(ele.getAttribute('bind-event') || 'change', function() {

                    try {

                        if (ele.getAttribute('type') == 'checkbox') {
                            ele.$setValue(ele.checked);
                        } else {
                            ele.$setValue(ele.value);
                        }

                    } catch(e) {}

                }, false);

                ele.$updateValue = function(value) {

                    try {

                        if (ele.getAttribute('type') == 'checkbox') {
                            (new Function('input', 'val', 'input.checked = val ? true:false;'))(ele, value);
                        } else {
                            (new Function('input', 'val', 'input.value = val;'))(ele, value);
                        }

                    } catch(e) {}
                };


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
