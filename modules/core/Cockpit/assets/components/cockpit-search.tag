<cockpit-search>

    <div name="autocomplete" class="uk-autocomplete uk-form-icon uk-form app-search">

        <style>
            cockpit-search .uk-dropdown {
                min-width: 25vw;
            }
        </style>

        <i class="uk-icon-search"></i>
        <input class="uk-width-1-1 uk-form-blank" type="text">

        <script>

            this.on('mount', function(){

                UIkit.autocomplete(this.autocomplete, {
                    source: App.route('/cockpit/search'),
                    template: '<ul class="uk-nav uk-nav-autocomplete uk-autocomplete-results">{{~items}}<li data-value="" data-url="{{$item.url}}"><a><i class="uk-icon-{{ ($item.icon || "cube") }}"></i> {{$item.title}}</a></li>{{/items}}</ul>'
                });
            });

            App.$(this.root).on("selectitem.uk.autocomplete", function(e, data) {

                if (data.url) {
                    location.href = data.url;
                }
            });

        </script>

    </div>

</cockpit-search>
