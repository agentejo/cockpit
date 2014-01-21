{{ $app->assets(['assets:vendor/nativesortable.js']) }}

{{ $app->assets(['galleries:assets/galleries.js','galleries:assets/js/gallery.js']) }}
{{ $app->assets(['mediamanager:assets/pathpicker.directive.js']) }}

<div data-ng-controller="gallery" data-id="{{ $id }}">

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">
          <a href="@route("/galleries")">@lang('Galleries')</a> / @lang('Gallery')
        </span>
        <ul class="uk-navbar-nav">
            <li data-uk-dropdown>
                <a title="@lang('Add images to gallery')" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-plus-circle"></i></a>
                <div class="uk-dropdown uk-dropdown-navbar">
                    <ul class="uk-nav uk-nav-navbar">
                        <li><a href="#" ng-click="selectImage()">@lang('Select single image')</a></li>
                        <li><a href="#" ng-click="importFromFolder()">@lang('Import images from folder')</a></li>
                    </ul>
                </div>
            </li>

        </ul>
    </nav>


    <form class="uk-form" data-ng-submit="save()" data-ng-show="gallery">

            <div class="uk-grid">

                <div class="uk-width-medium-4-5">

                    <div class="app-panel">

                        <div class="uk-form-row">
                            <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="gallery.name"  pattern="[a-zA-Z0-9]+" required>
                        </div>

                        <div class="uk-form-row">

                            <div id="images-list" class="uk-grid" data-uk-grid-match="{target:'.uk-thumbnail'}">
                                <div class="uk-width-medium-1-3 uk-grid-margin" data-ng-repeat="image in gallery.images" draggable="true">
                                    <div class="uk-thumbnail uk-width-1-1 uk-text-center">
                                        <div class="uk-text-center" style="background: #fff url(@@ imgurl(image) @@) 50% 50% no-repeat;background-size:contain;height:275px;">
                                            <button type="button" class="uk-button uk-button-danger" data-ng-click="removeImage($index)"><i class="uk-icon-trash-o"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="uk-alert" data-ng-show="!gallery.images.length">
                                @lang('No images.')
                            </div>
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

<style>

    #images-list > div {
        cursor: move;
        transform: scale(1.0);
        -webkit-transition: -webkit-transform 0.2s ease-out;
        transition: transform 0.2s ease-out;
    }

    #images-list .sortable-dragging {
        opacity: .25;
        -webkit-transform: scale(0.8);
        transform: scale(0.8);
    }

    #images-list .sortable-over {
           opacity: .25;
        }


</style>