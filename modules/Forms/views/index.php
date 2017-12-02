<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Forms')</span></li>
    </ul>
</div>

<div riot-view>

    <div if="{ ready }">

        <div class="uk-margin uk-clearfix" if="{ App.Utils.count(forms) }">

            <div class="uk-form-icon uk-form uk-text-muted">

                <i class="uk-icon-filter"></i>
                <input class="uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter forms...')" onkeyup="{ updatefilter }">

            </div>

            <div class="uk-float-right">

                <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/forms/form')"><i class="uk-icon-plus-circle uk-icon-justify"></i>  @lang('Form')</a>

            </div>

        </div>

        <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(forms) }">

            <div class="uk-animation-scale">

                <p>
                    <img class="uk-svg-adjust uk-text-muted" src="@url('forms:icon.svg')" width="80" height="80" alt="Forms" data-uk-svg />
                </p>
                <hr>
                <span class="uk-text-large"><strong>@lang('No forms').</strong> <a href="@route('/forms/form')">Create one</a></span>

            </div>

        </div>


        <div class="uk-grid uk-grid-match uk-grid-gutter uk-grid-width-1-1 uk-grid-width-medium-1-3 uk-grid-width-large-1-4 uk-margin-top">

            <div each="{ meta, form in forms }" show="{ infilter(meta) }">

                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-panel-teaser uk-position-relative">
                        <canvas width="600" height="350"></canvas>
                        <a href="@route('/forms/entries')/{form}" class="uk-position-cover uk-flex uk-flex-middle uk-flex-center">
                            <div class="uk-width-1-4 uk-svg-adjust" style="color:{ (meta.color) }">
                                <img riot-src="{ meta.icon ? '@url('assets:app/media/icons/')'+meta.icon : '@url('forms:icon.svg')'}" alt="icon" data-uk-svg>
                            </div>
                        </a>
                    </div>

                    <div class="uk-grid uk-grid-small">

                        <div data-uk-dropdown="delay:300">

                            <a class="uk-icon-cog" style="color:{ (meta.color) }" href="@route('/forms/form')/{ form }"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/forms/entries')/{form}">@lang('Entries')</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li><a href="@route('/forms/form')/{ form }">@lang('Edit')</a></li>
                                    <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li class="uk-text-truncate"><a href="@route('/forms/export')/{ meta.name }" download="{ meta.name }.form.json">@lang('Export entries')</a></li>
                                </ul>
                            </div>
                        </div>

                        <a class="uk-text-bold uk-flex-item-1 uk-text-center uk-link-muted" href="@route('/forms/entries')/{form}">{ meta.label || form }</a>

                        <div>
                            <span class="uk-badge" style="background-color:{ (meta.color) }">{ meta.itemsCount }</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <script type="view/script">

        var $this = this;

        this.ready  = false;
        this.forms = [];

        this.on('mount', function() {

            App.callmodule('forms:forms', true).then(function(data) {

                this.forms = data.result;
                this.ready  = true;
                this.update();

            }.bind(this));
        });

        remove(e, form) {

            form = e.item.form;

            App.ui.confirm("Are you sure?", function() {

                App.callmodule('forms:removeForm', form).then(function(data) {

                    App.ui.notify("Form removed", "success");

                    delete $this.forms[form];

                    $this.update();
                });
            });
        }

        updatefilter(e) {

        }

        infilter(form, value, name, label) {

            if (!this.refs.txtfilter.value) {
                return true;
            }

            value = this.refs.txtfilter.value.toLowerCase();
            name  = [form.name.toLowerCase(), form.label.toLowerCase()].join(' ');

            return name.indexOf(value) !== -1;
        }

    </script>

</div>
