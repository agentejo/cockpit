@start('header')

    {{ $app->assets(['forms:assets/forms.js','forms:assets/js/form.js'], $app['cockpit/version']) }}

@end('header')

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
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="form.name" required>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">Email</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Email form data to this address')" data-ng-model="form.email">

                        <div class="uk-alert">
                            @lang('Leave the email field empty if you don\'t want to recieve any form data via email.')
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <input type="checkbox" data-ng-model="form.entry"> @lang('Save form data')
                    </div>

                    <div class="uk-form-row">

                        <div class="uk-button-group">
                            <button type="submit" class="uk-button uk-button-primary uk-button-large">@lang('Save form')</button>
                            <a href="@route('/forms/entries')/@@ form._id @@" class="uk-button uk-button-large" data-ng-show="form._id"><i class="uk-icon-list"></i> @lang('Goto entries')</a>
                        </div>
                        &nbsp;
                        <a href="@route('/forms')">@lang('Cancel')</a>
                    </div>

                </div>
            </div>

            <div class="uk-width-medium-1-2">

                <div class="uk-margin" ng-show="form.name">
                    <strong>@lang('Form snippet example'):</strong>

<highlightcode>&lt;?php form('@@form.name@@'); ?&gt;
    &lt;p&gt;
        &lt;label&gt;Name&lt;/label&gt;
        &lt;input type="text" name="<i>form</i>[name]" required&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;label&gt;Message&lt;/label&gt;
        &lt;textarea name="<i>form</i>[message]" required&gt;&lt;/textarea&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;button type="submit"&gt;Send&lt;/button&gt;
    &lt;/p&gt;
&lt;/form&gt;</highlightcode>

<div class="uk-alert uk-alert-info">
    <i class="uk-icon-exclamation-circle"></i>
    @lang('It is important to prefix the form fields with <strong>form[...]</strong>.')
</div>

                </div>
            </div>
        </div>

    </form>
</div>
