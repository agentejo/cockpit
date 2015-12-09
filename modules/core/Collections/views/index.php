<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Collections')</span></li>
    </ul>
</div>

<div riot-view>

    <div if="{ ready }">

        <div class="uk-margin uk-clearfix" if="{ App.Utils.count(collections) }">

            <div class="uk-form-icon uk-form uk-text-muted">

                <i class="uk-icon-filter"></i>
                <input class="uk-form-large uk-form-blank" type="text" name="txtfilter" placeholder="@lang('Filter collections...')" onkeyup="{ updatefilter }">

            </div>

            <div class="uk-float-right">

                <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/collections/collection')"><i class="uk-icon-plus-circle uk-icon-justify"></i>  @lang('Collection')</a>

            </div>

        </div>

        <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(collections) }">

            <div class="uk-width-medium-1-3 uk-animation-scale">

                <p class="uk-text-xlarge">
                    <i class="uk-icon-list"></i>
                </p>

                <h3>@lang('No Collections'). <a href="@route('/collections/collection')">Create a collection.</a></h3>

            </div>

        </div>


        <div class="uk-grid uk-grid-match uk-grid-gutter uk-grid-width-1-1 uk-grid-width-medium-1-3 uk-margin-top">

            <div class="uk-grid-margin" each="{ collection, meta in collections }" if="{ parent.infilter(meta) }">

                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-grid uk-grid-small">

                        <div data-uk-dropdown>

                            <a class="uk-icon-cog"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/collections/entries')/{collection}">@lang('Entries')</a></li>
                                    <li><a href="@route('/collections/entry')/{collection}">@lang('Add entry')</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li><a href="@route('/collections/collection')/{ collection }">@lang('Edit')</a></li>
                                    <li><a onclick="{ parent.remove }">@lang('Delete')</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li class="uk-text-truncate"><a href="@route('/collections/export')/{ meta.name }" download="{ meta.name }.collection.json">@lang('Export entries')</a></li>
                                </ul>
                            </div>
                        </div>

                        <a class="uk-text-bold uk-flex-item-1 uk-link-muted" href="@route('/collections/entries')/{collection}">{ meta.label || collection }</a>
                        <div>
                            <span class="uk-badge">{ meta.itemsCount }</span>
                        </div>
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

                this.collections = data.result;
                this.ready  = true;
                this.update();

            }.bind(this));
        });

        remove(e, collection) {

            collection = e.item.collection;

            App.ui.confirm("Are you sure?", function() {

                App.callmodule('collections:removeCollection', collection).then(function(data) {

                    App.ui.notify("Collection removed", "success");

                    delete $this.collections[collection];

                    $this.update();
                });
            });
        }

        updatefilter(e) {

        }

        infilter(collection, value, name, label) {

            if (!this.txtfilter.value) {
                return true;
            }

            value = this.txtfilter.value.toLowerCase();
            name  = [collection.name.toLowerCase(), collection.label.toLowerCase()].join(' ');

            return name.indexOf(value) !== -1;
        }

    </script>

</div>
