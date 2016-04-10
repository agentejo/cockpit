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
                <input class="uk-form-large uk-form-blank" type="text" name="txtfilter" placeholder="@lang('Filter regions...')" onkeyup="{ updatefilter }">

            </div>

            <div class="uk-float-right">

                <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/regions/region')"><i class="uk-icon-plus-circle uk-icon-justify"></i>  @lang('Region')</a>

            </div>

        </div>

        <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(regions) }">

            <div class="uk-width-medium-1-3 uk-animation-scale">

                <p class="uk-text-xlarge">
                    <i class="uk-icon-th"></i>
                </p>

                <h3>@lang('No regions'). <a href="@route('/regions/region')">Create a region.</a></h3>

            </div>

        </div>


        <div class="uk-grid uk-grid-match uk-grid-gutter uk-grid-width-1-1 uk-grid-width-medium-1-3 uk-margin-top">

            <div class="uk-grid-margin" each="{ region, meta in regions }" if="{ parent.infilter(meta) }">

                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-grid uk-grid-small">

                        <div data-uk-dropdown>

                            <a class="uk-icon-cog"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/regions/form')/{ region }">@lang('Form')</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li><a href="@route('/regions/region')/{ region }">@lang('Edit')</a></li>
                                    <li><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                                </ul>
                            </div>
                        </div>

                        <a class="uk-text-bold uk-flex-item-1 uk-link-muted" href="@route('/regions/form')/{region}">{ meta.label || region }</a>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <script type="view/script">

        var $this = this;

        this.ready  = false;
        this.regions = [];

        this.on('mount', function() {

            App.callmodule('regions:regions', true).then(function(data) {

                this.regions = data.result;
                this.ready  = true;
                this.update();

            }.bind(this));
        });

        remove(e, region) {

            region = e.item.region;

            App.ui.confirm("Are you sure?", function() {

                App.callmodule('regions:removeRegion', region).then(function(data) {

                    App.ui.notify("Region removed", "success");

                    delete $this.regions[region];

                    $this.update();
                });
            });
        }

        updatefilter(e) {

        }

        infilter(region, value, name, label) {

            if (!this.txtfilter.value) {
                return true;
            }

            value = this.txtfilter.value.toLowerCase();
            name  = [region.name.toLowerCase(), region.label.toLowerCase()].join(' ');

            return name.indexOf(value) !== -1;
        }

    </script>

</div>
