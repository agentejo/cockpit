(function(global, $, $win, $doc){


    $doc.on("app-init", function(){

        //auto-focus in-app search
        var txtSearch = $("#frmCockpitSearch").find('.uk-search-field');

        $doc.on("keydown", function(e) {

            //ctrl-c, ctrl-v etc.
            if (e.ctrlKey || e.altKey || e.metaKey) return;

            if (e.target.tagName && e.target.tagName.toLowerCase()=='body' && (e.keyCode>=65 && e.keyCode<=90)) {
                txtSearch.focus();
            }
        });

        // always fit navbar
        fitTopNav();
    });


    function fitTopNav() {

        $win.on('resize load', (function(){

            var navbar = $('.app-top-navbar > .app-wrapper:first'),
                menu   = navbar.find('.app-top-navbar-links:first'),
                links  = menu.children(),
                more   = $([
                            '<li class="uk-hidden" data-uk-dropdown>',
                                '<a href="#"><i class="uk-icon-plus"></i></a>',
                                '<div class="uk-dropdown uk-dropdown-flip uk-dropdown-navbar"><ul class="uk-nav uk-nav-navbar"></ul></div>',
                            '</li>'
                           ].join('')).appendTo(menu),
                list   = more.find('ul'),

                check  = function(children) {

                    for (var i=0;i<children.length;i++) {

                        if (children.eq(i).position().top > 0) {
                            return false;
                        }
                    }

                    return true;
                },

                respfn = function(){

                    if (!menu.is(':visible')) return;

                    more.addClass('uk-hidden');
                    links.removeClass("uk-hidden");

                    var children = navbar.children(':visible');

                    if (!check(children)) {

                        list.empty();
                        more.removeClass('uk-hidden');
                        links.removeClass('uk-hidden');

                        var item, link;

                        for (var i = links.length -1 ; i > -1; i--) {

                            item = $(links[i].outerHTML), link = item.find('a:first');

                            link.removeAttr('data-uk-tooltip');

                            if (!link.text().trim()) {
                                link.append('&nbsp;'+(link.attr('title') || link.data('cachedTitle')));
                            }

                            list.prepend(item);
                            links.eq(i).addClass('uk-hidden');

                            if (check(children)) break;
                        }
                    }
                };

            respfn();

            return UIkit.Utils.debounce(respfn, 50);

        })());
    }

})(this, jQuery, jQuery(window), jQuery(document));