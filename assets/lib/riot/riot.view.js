// riot inline views
(function(riot, d){

    if (!riot || !riot.compile) {
        return;
    }

    // small DOM pimping

    document.find = document.querySelectorAll.bind(document)

    HTMLElement.prototype.find = function(selector){
        return this.querySelectorAll(selector);
    };

    NodeList.prototype.forEach = Array.prototype.forEach;

    Node.prototype.on = window.on = function (name, delegate, fn) {

        if (arguments.length !== 3) {
            return this.addEventListener(name, arguments[1]);
        }

        return this.addEventListener(name, function (e) {
            if(e.target.matches(delegate)){
                return fn.apply(e.target, arguments);
            }
        });
    };

    // hide [riot-view]
    (function(style) {
        style.innerText = '[riot-view]{display:none}';
        d.head.appendChild(style);
    })(d.createElement('style'));

    riot.util.initViews = (function(views, view, vid, tag, ele, i) {

        return function(root, opts, clb) {

            root  = root || d;
            opts  = opts || {};
            views = root.querySelectorAll('[riot-view]');

            for (i=0;i<views.length;i++) {

                view = views[i];
                vid  = viewuid();
                tag  = ("<"+vid+">\n" + view.innerHTML + "\n</"+vid+">").replace(' type="view/script"', '');
                ele  = d.createElement(view.tagName.toLowerCase() == 'script' ? 'div':view.tagName);

                copyattrs(view, ele);
                riot.compile(tag);

                view.parentNode.insertBefore(ele, view);
                view.parentNode.removeChild(view);
                riot.mount(ele, vid, opts);
            }

            if (clb) clb();
        };

    })();

    d.addEventListener('DOMContentLoaded', function(event) {
        riot.compile(riot.util.initViews);
    });

    function copyattrs(src, target) {

        for (var i = 0, atts = src.attributes, n = atts.length; i < n; i++) {
            if (['riot-view', 'type'].indexOf(atts[i].name) !== -1) continue;
            target.setAttribute(atts[i].name, atts[i].value);
        }
    }

    function viewuid() {

        return 'view-xxxxxxxx'.replace(/[x]/g, function(c) {
            var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        });
    }

})(riot, document);


// riot auto mount
(function(riot, d){

    if (!riot || !riot.compile) {
        return;
    }

    riot.util.autoMount = (function(elements) {

        return function(root) {

            root     = root || d;
            elements = root.querySelectorAll('[riot-mount]');

            for (i=0;i<elements.length;i++) {
                riot.mount(elements[i], '*');
                elements[i].removeAttribute('riot-mount');
            }
        };

    })();

    d.addEventListener('DOMContentLoaded', function(event) {
        riot.util.autoMount();
    });

})(riot, document);
