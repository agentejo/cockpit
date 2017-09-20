<cp-search>

    <div ref="autocomplete" class="uk-autocomplete uk-form uk-form-icon app-search">

        <style>
            cp-search .uk-dropdown {
                min-width: 25vw;
            }
        </style>

        <i class="uk-icon-search"></i>
        <input class="uk-width-1-1 uk-form-blank" type="text" placeholder="{ App.i18n.get('Search for anything...') }">

    </div>

    <script>

        this.on('mount', function(){

            var txtSearch = App.$("input[type='text']", this.refs.autocomplete);

            UIkit.autocomplete(this.refs.autocomplete, {
                source: App.route('/cockpit/search'),
                template: '<ul class="uk-nav uk-nav-autocomplete uk-autocomplete-results">{{~items}}<li data-value="" data-url="{{$item.url}}"><a><i class="uk-icon-{{ ($item.icon || "cube") }}"></i> {{$item.title}}</a></li>{{/items}}</ul>'
            });

            UIkit.$doc.on("keydown", function(e) {
                
                //ctrl-c, ctrl-v etc.
                if (e.ctrlKey || e.altKey || e.metaKey) return;

                if (e.target.tagName && e.target.tagName.toLowerCase()=='body' && (e.keyCode>=65 && e.keyCode<=90)) {
                    txtSearch.focus();
                }
            });

            // bind global command
            Mousetrap.bindGlobal(['alt+f'], function(e) {

                e.preventDefault();
                txtSearch.focus();
                return false;
            });

        });

        App.$(this.root).on("selectitem.uk.autocomplete", function(e, data) {

            if (data.url) {
                location.href = data.url;
            }
        });

    </script>

</cp-search>
