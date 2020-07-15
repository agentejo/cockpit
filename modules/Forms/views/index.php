<script>
    window.__forms = {{ json_encode($forms) }};
</script>

<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Forms')</span></li>
    </ul>
</div>

<div riot-view>

    <div>

        <div class="uk-margin uk-clearfix" if="{ App.Utils.count(forms) }">

            <div class="uk-form-icon uk-form uk-text-muted">

                <i class="uk-icon-filter"></i>
                <input class="uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter forms...')" aria-label="@lang('Filter forms...')" onkeyup="{ updatefilter }">

            </div>

            <div class="uk-float-right">

                <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/forms/form')">@lang('Add Form')</a>

            </div>

        </div>

        <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(forms) }">

            <div class="uk-animation-scale">

                <p>
                    <img class="uk-svg-adjust uk-text-muted" src="@url('forms:icon.svg')" width="80" height="80" alt="Forms" data-uk-svg />
                </p>
                <hr>
                <span class="uk-text-large"><strong>@lang('No forms').</strong> <a href="@route('/forms/form')">@lang('Create one')</a></span>

            </div>

        </div>


        <div class="uk-grid uk-grid-match uk-grid-gutter uk-grid-width-1-1 uk-grid-width-medium-1-3 uk-grid-width-large-1-4 uk-grid-width-xlarge-1-5 uk-margin-top">

            <div each="{ form, idx in forms }" show="{ infilter(form.meta) }">

                <div class="uk-panel uk-panel-box uk-panel-card uk-panel-card-hover">

                    <div class="uk-panel-teaser uk-position-relative">
                        <canvas width="600" height="350"></canvas>
                        <a aria-label="{ form.label }" href="@route('/forms/entries')/{form.name}" class="uk-position-cover uk-flex uk-flex-middle uk-flex-center">
                            <div class="uk-width-1-4 uk-svg-adjust" style="color:{ (form.meta.color) }">
                                <img riot-src="{ form.meta.icon ? '@url('assets:app/media/icons/')'+form.meta.icon : '@url('forms:icon.svg')'}" alt="icon" data-uk-svg>
                            </div>
                        </a>
                    </div>

                    <div class="uk-grid uk-grid-small">

                        <div data-uk-dropdown="mode:'click'">

                            <a aria-label="@lang('Edit form')" class="uk-icon-cog" style="color:{ (form.meta.color) }"></a>

                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li class="uk-nav-header">@lang('Actions')</li>
                                    <li><a href="@route('/forms/entries')/{form.name}">@lang('Entries')</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li><a href="@route('/forms/form')/{ form.name }">@lang('Edit')</a></li>
                                    <li class="uk-nav-item-danger"><a class="uk-dropdown-close" onclick="{ parent.remove }">@lang('Delete')</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li class="uk-text-truncate"><a href="@route('/forms/export')/{ form.meta.name }" download="{ form.meta.name }.form.json">@lang('Export entries')</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="uk-flex-item-1 uk-text-center uk-text-truncate">
                            <a class="uk-text-bold uk-link-muted" href="@route('/forms/entries')/{form.name}" title="{ form.label }">{ form.label }</a>
                        </div>
                        <div>
                            <span class="uk-badge" style="background-color:{ (form.meta.color) }">{ form.meta.itemsCount }</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <script type="view/script">

        var $this = this;

        this.forms = window.__forms;

        remove(e, form) {

            form = e.item.form;

            App.ui.confirm('Are you sure?', function() {

                App.callmodule('forms:removeForm', form.name).then(function(data) {

                    App.ui.notify('Form removed', 'success');
                    $this.forms.splice(e.item.idx, 1);
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
