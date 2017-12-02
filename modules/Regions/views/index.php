<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Regions')</span></li>
    </ul>
</div>

<div riot-view>

    <div if="{ ready }">

        <div class="uk-margin uk-clearfix" if="{ App.Utils.count(regions) }">

            <div class="uk-form-icon uk-form uk-text-muted">

                <i class="uk-icon-filter"></i>
                <input class="uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter regions...')" onkeyup="{ updatefilter }">

            </div>

            @hasaccess?('regions', 'create')
            <div class="uk-float-right">
                <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/regions/region')"><i class="uk-icon-plus-circle uk-icon-justify"></i>  @lang('Region')</a>
            </div>
            @endif

        </div>

        <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(regions) }">

            <div class="uk-animation-scale">

                <p>
                    <img class="uk-svg-adjust uk-text-muted" src="@url('regions:icon.svg')" width="80" height="80" alt="Regions" data-uk-svg />
                </p>
                <hr>
                <span class="uk-text-large"><strong>@lang('No regions').</strong>

                @hasaccess?('regions', 'create')
                <a href="@route('/regions/region')">Create one</a></span>
                @end

            </div>

        </div>


        <div class="uk-grid uk-grid-match uk-grid-gutter uk-grid-width-1-1 uk-grid-width-medium-1-3 uk-grid-width-large-1-4 uk-margin-top">

            <div each="{ meta, region in regions }" show="{ infilter(meta) }">

                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-panel-teaser uk-position-relative">
                        <canvas width="600" height="350"></canvas>
                        <a href="@route('/regions/form')/{ region }" class="uk-position-cover uk-flex uk-flex-middle uk-flex-center">
                            <div class="uk-width-1-4 uk-svg-adjust" style="color:{ (meta.color) }">
                                <img riot-src="{ meta.icon ? '@url('assets:app/media/icons/')'+meta.icon : '@url('regions:icon.svg')'}" alt="icon" data-uk-svg>
                            </div>
                        </a>
                    </div>

                    <div class="uk-grid uk-grid-small">

                        <div data-uk-dropdown="delay:300">

                            <a class="uk-icon-cog" style="color: { (meta.color) }" href="@route('/regions/region')/{ region }" if="{ meta.allowed.region_edit }"></a>
                            <a class="uk-icon-cog" style="color: { (meta.color) }" if="{ !meta.allowed.region_edit }"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/regions/form')/{ region }">@lang('Form')</a></li>
                                    <li if="{ meta.allowed.region_edit }" class="uk-nav-divider"></li>
                                    <li if="{ meta.allowed.region_edit }"><a href="@route('/regions/region')/{ region }">@lang('Edit')</a></li>
                                    @hasaccess?('regions', 'delete')
                                    <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ this.parent.remove }">@lang('Delete')</a></li>
                                    @end
                                </ul>
                            </div>
                        </div>
                        <div class="uk-flex-item-1 uk-text-center">
                            <a class="uk-text-bold uk-link-muted" href="@route('/regions/form')/{region}">{ meta.label || region }</a>
                        </div>
                        <div>&nbsp;</div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <script type="view/script">

        var $this = this;

        this.ready  = true;
        this.regions = {{ json_encode($regions) }};

        remove(e, region) {

            region = e.item.region;

            App.ui.confirm("Are you sure?", function() {

                App.request('/regions/remove_region/'+region, {nc:Math.random()}).then(function(data) {

                    App.ui.notify("Region removed", "success");

                    delete $this.regions[region];

                    $this.update();
                });
            });
        }

        updatefilter(e) {

        }

        infilter(region, value, name, label) {

            if (!this.refs.txtfilter.value) {
                return true;
            }

            value = this.refs.txtfilter.value.toLowerCase();
            name  = [region.name.toLowerCase(), region.label.toLowerCase()].join(' ');

            return name.indexOf(value) !== -1;
        }

    </script>

</div>
