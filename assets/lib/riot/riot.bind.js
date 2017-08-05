
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

                return _.get(tag, field);
            };

            ele.$setValue = (function() {

                return function(value, silent, field) {

                    field = field || ele.getAttribute(attr);

                    try {
                        _.set(tag, field, value);

                        if (!silent) {
                            tag.update();
                        }

                        tag.trigger('bindingupdated', [field, value]);

                        return true;

                    } catch (e) {

                        console.log(e);

                        return false;
                    }
                };

            })();


            ele.$updateValue = function(value) {};

            var nodeType = ele.nodeName.toLowerCase(),
                defaultEvt = ('oninput' in ele) && nodeType=='input' ? 'input':'change';

            if (['input', 'select', 'textarea'].indexOf(nodeType) !== -1) {

                var isCheckbox = (ele.nodeName == 'INPUT' && ele.getAttribute('type') == 'checkbox');

                ele.addEventListener(ele.getAttribute('bind-event') || defaultEvt, function() {

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
                        
                        if (document.activeElement === ele && nodeType == 'input' && !isCheckbox) {
                            return;
                        }

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
