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
                <input class="uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter collections...')" onkeyup="{ updatefilter }">

            </div>

            @hasaccess?('collections', 'create')
            <div class="uk-float-right">
                <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/collections/collection')"><i class="uk-icon-plus-circle uk-icon-justify"></i>  @lang('Collection')</a>
            </div>
            @end

        </div>

        <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(collections) }">

            <div class="uk-animation-scale">

                <p>
                    <img class="uk-svg-adjust uk-text-muted" src="@url('collections:icon.svg')" width="80" height="80" alt="Collections" data-uk-svg />
                </p>
                <hr>
                <span class="uk-text-large"><strong>@lang('No Collections').</strong>
                @hasaccess?('collections', 'create')
                <a href="@route('/collections/collection')">@lang('Create one')</a></span>
                @end
            </div>

        </div>


        <div class="uk-grid uk-grid-match uk-grid-gutter uk-grid-width-1-1 uk-grid-width-medium-1-3 uk-grid-width-large-1-4 uk-margin-top">

            <div each="{ meta, collection in collections }" show="{ infilter(meta) }">

                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-panel-teaser uk-position-relative">
                        <canvas width="600" height="350"></canvas>
                        <a href="@route('/collections/entries')/{collection}" class="uk-position-cover uk-flex uk-flex-middle uk-flex-center">
                            <div class="uk-width-1-4 uk-svg-adjust" style="color:{ (meta.color) }">
                                <img riot-src="{ meta.icon ? '@url('assets:app/media/icons/')'+meta.icon : '@url('collections:icon.svg')'}" alt="icon" data-uk-svg>
                            </div>
                        </a>
                    </div>

                    <div class="uk-grid uk-grid-small">

                        <div data-uk-dropdown="delay:300">

                            <a class="uk-icon-cog" style="color:{ (meta.color) }" href="@route('/collections/collection')/{ collection }" if="{ meta.allowed.edit }"></a>
                            <a class="uk-icon-cog" style="color:{ (meta.color) }" if="{ !meta.allowed.edit }"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/collections/entries')/{collection}">@lang('Entries')</a></li>
                                    <li><a href="@route('/collections/entry')/{collection}" if="{ meta.allowed.entries_create }">@lang('Add entry')</a></li>
                                    <li if="{ meta.allowed.edit || meta.allowed.delete }" class="uk-nav-divider"></li>
                                    <li if="{ meta.allowed.edit }"><a href="@route('/collections/collection')/{ collection }">@lang('Edit')</a></li>
                                    @hasaccess?('collections', 'delete')
                                    <li class="uk-nav-item-danger" if="{ meta.allowed.delete }"><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                                    @end
                                    <li class="uk-nav-divider" if="{ meta.allowed.edit }"></li>
                                    <li class="uk-text-truncate" if="{ meta.allowed.edit }"><a href="@route('/collections/export')/{ meta.name }" download="{ meta.name }.collection.json">@lang('Export entries')</a></li>
                                    <li class="uk-text-truncate" if="{ meta.allowed.edit }"><a href="@route('/collections/import/collection')/{ meta.name }">@lang('Import entries')</a></li>
                                </ul>
                            </div>
                        </div>

                        <a class="uk-text-bold uk-flex-item-1 uk-text-center uk-link-muted" href="@route('/collections/entries')/{collection}">{ meta.label || collection }</a>
                        <div>
                            <span class="uk-badge" riot-style="background-color:{ (meta.color) }">{ meta.itemsCount }</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <script type="view/script">

        var $this = this;

        this.ready  = true;
        this.collections = {{ json_encode($collections) }};

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

            if (!this.refs.txtfilter.value) {
                return true;
            }

            value = this.refs.txtfilter.value.toLowerCase();
            name  = [collection.name.toLowerCase(), collection.label.toLowerCase()].join(' ');

            return name.indexOf(value) !== -1;
        }

    </script>

</div>
