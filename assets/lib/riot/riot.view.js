// riot inline views
(function(riot, d){

    if (!riot || !riot.compile) {
        return;
    }

    d.writeln('<style>[riot-view]{display:none}</style>');

    riot.util.initViews = (function(views, view, vid, tag, ele, i) {

        return function(root) {

            root  = root || d;
            views = root.querySelectorAll('[riot-view]');

            for (i=0;i<views.length;i++) {

                view = views[i];
                vid  = viewuid();
                tag  = ("<"+vid+">\n" + view.innerHTML + "\n</"+vid+">").replace(' type="view/script"', '');
                ele  = d.createElement(view.tagName);

                copyattrs(view, ele);
                riot.compile(tag);

                view.parentNode.insertBefore(ele, view);
                view.parentNode.removeChild(view);
                riot.mount(ele, vid);
            }
        };

    })();

    d.addEventListener('DOMContentLoaded', function(event) {
        riot.compile(riot.util.initViews);
    });

    function copyattrs(src, target) {

        for (var i = 0, atts = src.attributes, n = atts.length; i < n; i++) {
            if (atts[i].name == 'riot-view') continue;
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
