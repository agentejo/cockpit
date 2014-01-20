{{ $app->assets(['galleries:assets/galleries.js','galleries:assets/js/gallery.js']) }}
{{ $app->assets(['mediamanager:assets/pathpicker.directive.js']) }}

<div data-ng-controller="gallery" data-id="{{ $id }}">

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">
          <a href="@route("/galleries")">@lang('Galleries')</a> / @lang('Gallery')
        </span>
    </nav>


    <form class="uk-form" data-ng-submit="save()" data-ng-show="gallery">

            <div class="uk-grid">

                <div class="uk-width-medium-4-5">

                    <div class="app-panel">

                        <div class="uk-form-row">
                            <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="gallery.name"  pattern="[a-zA-Z0-9]+" required>
                        </div>

                        <div class="uk-form-row">
                            <button type="submit" class="uk-button uk-button-primary uk-button-large">@lang('Save gallery')</button>
                            <a href="@route('/galleries')">@lang('Cancel')</a>
                        </div>
                    </div>
                </div>
            </div>
    </form>


</div>