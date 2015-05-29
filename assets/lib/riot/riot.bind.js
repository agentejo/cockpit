// riot enhancments
(function(riot, $){


    /**
     * simple two way data-binding for riot
     */

    riot.util.bind = function(tag, namespace) {

        var bind = this,
            root = this.root,
            attr = (namespace ? namespace+'-':'')+'bind',
            attrSelector = '['+attr+']',
            $root = $(tag.root).attr('bind-root', true);

        function update() {

            $root.find(attrSelector).each(function() {

                var value = null;

                if (!this.$boundTo) {
                    init(this);
                }

                if (this.$boundTo !== tag ) {
                    return;
                }

                 try {

                    value = (new Function('tag', 'return tag.'+this.getAttribute(attr)+';'))(tag);

                } catch(e) {}


                if (JSON.stringify(this.$value) !== JSON.stringify(value)) {
                    this.$value = value;
                    this.$updateValue(value);
                }
            });
        }


        function init(ele) {

            var $ele = $(ele);

            ele.$boundTo = tag;

            ele.$setValue = function(value) {

                try {
                    (new Function('tag', 'val', 'tag.'+ele.getAttribute(attr)+' = val;tag.update(); '))(tag, value);
                } catch(e) {}
            };


            ele.$updateValue = function(value) {};


            if ($ele.is(':input')) {

                $ele.on($ele.attr('bind-event') || 'change', function() {

                    try {

                        if ($ele.is('[type="checkbox"]')) {
                            ele.$setValue($ele.prop("checked"));
                        } else {
                            ele.$setValue($ele.val());
                        }

                    } catch(e) {}

                });

                ele.$updateValue = function(value) {

                    try {

                        if ($ele.is('[type="checkbox"]')) {
                            (new Function('input', 'val', 'input.prop("checked", val);'))($ele, value);
                        } else {
                            (new Function('input', 'val', 'input.val(val);'))($ele, value);
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

})(riot, jQuery);
