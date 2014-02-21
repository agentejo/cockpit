{{ $app->assets(['assets:vendor/nativesortable.js'], $app['cockpit/version']) }}

{{ $app->assets(['galleries:assets/galleries.js','galleries:assets/js/gallery.js'], $app['cockpit/version']) }}
{{ $app->assets(['mediamanager:assets/pathpicker.directive.js'], $app['cockpit/version']) }}

<div data-ng-controller="gallery" data-id="{{ $id }}" ng-cloak>

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-navbar-brand">
            <a href="@route("/galleries")">@lang('Galleries')</a> /
            <span class="uk-text-muted" ng-show="!gallery.name">@lang('Gallery')</span>
            <span ng-show="gallery.name">@@ gallery.name @@</span>
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
                            <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="gallery.name" required>
                        </div>

                        <div class="uk-form-row">

                            <div id="images-list" class="uk-grid" data-uk-grid-match="{target:'.uk-thumbnail'}">
                                <div class="uk-width-1-2 uk-width-medium-1-5 uk-grid-margin" data-ng-repeat="image in gallery.images" draggable="true">
                                    <div class="uk-thumbnail uk-width-1-1 uk-text-center uk-visible-hover">
                                        <div class="uk-text-center" style="background: #fff url(@@ imgurl(image) @@) 50% 50% no-repeat;background-size:contain;height:140px;">

                                            <div class="images-list-actions uk-hidden">
                                                <div class="uk-button-group">
                                                    <button type="button" class="uk-button uk-button-small uk-button-primary" data-ng-click="showMeta($index)"><i class="uk-icon-pencil"></i></button>
                                                    <button type="button" class="uk-button uk-button-small uk-button-danger" data-ng-click="removeImage($index)"><i class="uk-icon-trash-o"></i></button>
                                                </div>
                                            </div>
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


    <div id="meta-dialog" class="uk-modal">
        <div class="uk-modal-dialog">
            <a class="uk-modal-close uk-close"></a>
            <h3>@lang('Meta')</h3>

            <div class="uk-form uk-margin">
                <div class="uk-form-row" data-ng-repeat="field in gallery.fields" data-ng-switch="field.type">

                    <label class="uk-text-small">@@ field.name | uppercase @@</label>

                    <div data-ng-switch-when="html">
                        <textarea class="uk-width-1-1 uk-form-large" data-ng-model="$parent.metaimage.data[field.name]"></textarea>
                    </div>

                    <div data-ng-switch-when="code">
                        <textarea codearea="{mode:'@@field.syntax@@'}" class="uk-width-1-1 uk-form-large" data-ng-model="$parent.metaimage.data[field.name]"></textarea>
                    </div>

                    <div data-ng-switch-when="wysiwyg">
                        <textarea wysiwyg="{document_base_url:'{{ $app->pathToUrl('site:') }}'}" class="uk-width-1-1 uk-form-large" data-ng-model="$parent.metaimage.data[field.name]"></textarea>
                    </div>

                    <div data-ng-switch-when="select">
                        <select class="uk-width-1-1 uk-form-large" data-ng-model="$parent.metaimage.data[field.name]" data-ng-init="fieldindex=$index">
                            <option value="@@ option @@" data-ng-repeat="option in (field.options || [])" data-ng-selected="($parent.metaimage.data[field.name]==option)">@@ option @@</option>
                        </select>
                    </div>

                    <div data-ng-switch-when="media">
                        <input type="text" media-path-picker data-ng-model="$parent.metaimage.data[field.name]">
                    </div>

                    <div data-ng-switch-default>
                        <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="$parent.metaimage.data[field.name]">
                    </div>
                </div>
            </div>

            <button class="uk-button uk-modal-close">@lang('Close')</button>
        </div>
    </div>

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

    #images-list .uk-thumbnail {
        position: relative;
    }

    .images-list-actions {
        position: absolute;
        width: 100%;
        top: 50%;
        -webkit-transform: translateY(-50%);
        transform: translateY(-50%);
    }


</style>