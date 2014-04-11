{{ $app->assets(['forms:assets/forms.js','forms:assets/js/form.js'], $app['cockpit/version']) }}

<div data-ng-controller="form" data-id="{{ $id }}" ng-cloak>

    <h1>
        <a href="@route("/forms")">@lang('Forms')</a> /
        <span class="uk-text-muted" ng-show="!form.name">@lang('Form')</span>
        <span ng-show="form.name">@@ form.name @@</span>
    </h1>


    <form class="uk-form" data-ng-submit="save()" data-ng-show="form">

        <div class="uk-grid" data-uk-grid-margin>

            <div class="uk-width-medium-1-2">

                <div class="app-panel">

                    <div class="uk-form-row">
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="form.name" pattern="[a-zA-Z0-9\s]+" required>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">Email</label>
                        <input class="uk-width-1-1 uk-form-large" type="email" placeholder="@lang('Email form data to this adress')" data-ng-model="form.email">

                        <div class="uk-alert">
                            @lang('Leave the field empty if you don\'t want to recieve any form data via email.')
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <input type="checkbox" data-ng-model="form.entry"> @lang('Save form data')
                    </div>

                    <div class="uk-form-row">
                        <button type="submit" class="uk-button uk-button-primary uk-button-large">@lang('Save form')</button>
                        <a href="@route('/forms')">@lang('Cancel')</a>
                    </div>

                </div>
            </div>

            <div class="uk-width-medium-1-2">

                <div class="uk-margin" ng-show="form.name">
                    <strong>@lang('Form snippet example'):</strong>

<pre><code><strong>&lt;?php form('@@form.name@@'); ?&gt;</strong>
    &lt;p&gt;
        &lt;label&gt;Name&lt;/label&gt;
        &lt;input type="text" name="<i>form</i>[name]"/&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;label&gt;Message&lt;/label&gt;
        &lt;textarea name="<i>form</i>[message]"&gt;&lt;/textarea&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;button type="submit"&gt;Send&lt;/button&gt;
    &lt;/p&gt;
&lt;/form&gt;</code></pre>

<div class="uk-alert uk-alert-info">
    <i class="uk-icon-exclamation-circle"></i>
    @lang('It is important to prefix the form fields with <strong>form[...]</strong>.')
</div>

                </div>
            </div>
        </div>

    </form>
</div>