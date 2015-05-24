<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Collections')</span></li>
    </ul>
</div>



<div riot-view>

    <div if="{ ready }">

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-middle" if="{ !App.Utils.count(collections) }">

            <div class="uk-animation-fade">

                <p class="uk-text-xlarge">
                    <i class="uk-icon-database"></i>
                </p>

                <hr>

                No Collections. <a href="@route('/collections/collection')">Create a Collection.</a>

            </div>

        </div>

        <div data-uk-margin if="{ App.Utils.count(collections) }">

            <a class="uk-button uk-button-primary" href="@route('/collections/collection')" title="@lang('Create a Collection')"><i class="uk-icon-plus"></i></a>

        </div>

        <div class="uk-grid uk-grid-match uk-grid-width-1-1 uk-grid-width-medium-1-3" if="{ App.Utils.count(collections) }">

            <div class="uk-grid-margin" each="{ collection, meta in collections }">

                <div class="uk-panel uk-panel-box">

                    <div class="uk-grid uk-grid-small">

                        <div data-uk-dropdown="\{mode:'click'\}">

                            <a class="uk-icon-bars"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/collections/entries')/{collection}">@lang('Entries')</a></li>
                                    <li><a href="@route('/collections/entry')/{collection}">@lang('Add entry')</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li><a href="@route('/collections/collection')/{ collection }">@lang('Edit')</a></li>
                                    <li><a onclick="{ parent.remove }">@lang('Delete')</a></li>
                                </ul>
                            </div>
                        </div>

                        <a class="uk-text-bold uk-flex-item-1 uk-link-muted" href="@route('/collections/entries')/{collection}">{ meta.label || collection }</a>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <script type="view/script">

        var $this = this;

        this.ready  = false;
        this.collections = [];

        this.on('mount', function() {

            App.callmodule('collections:collections', true).then(function(data) {

                $this.collections = data.result;
                $this.ready  = true;
                $this.update();
            });
        });

        remove(e, collection) {

            collection = e.item.collection;

            App.UI.confirm("Are you sure?", function() {

                App.callmodule('collections:removeCollection', collection).then(function(data) {

                    App.UI.notify("Collection removed", "success");

                    delete $this.collections[collection];

                    $this.update();
                });
            });
        }

    </script>

</div>
