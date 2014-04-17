{{ $app->assets(['assets:vendor/nativesortable.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/uikit/js/addons/sortable.min.js'], $app['cockpit/version']) }}

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
                        <li class="uk-nav-header">@lang("Import")</li>
                        <li><a href="#" ng-click="selectImage()"><i class="uk-icon-reply"></i> @lang('Single image')</a></li>
                        <li><a href="#" ng-click="importFromFolder()"><i class="uk-icon-reply-all"></i> @lang('Images from folder')</a></li>
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
                            <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="gallery.name" pattern="[a-zA-Z0-9\s]+" required>
                        </div>

                        <div class="uk-form-row">

                            <div data-ng-show="!managefields">

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


                                <div class="uk-text-center uk-margin-large-top uk-margin-large-bottom" data-ng-show="gallery && !gallery.images.length">
                                    <h2><i class="uk-icon-th"></i></h2>
                                    <p class="uk-text-large">
                                        @lang('You don\'t have any images in this gallery.')
                                    </p>
                                </div>

                            </div>

                            <div data-ng-show="managefields">

                                <ul id="manage-fields-list" class="uk-sortable" data-uk-sortable="{maxDepth:1}">
                                     <li data-ng-repeat="field in gallery.fields">
                                        <div class="uk-sortable-item uk-sortable-item-table">
                                           <div class="uk-sortable-handle"></div>
                                           <input type="text" data-ng-model="field.name" placeholder="Field name" pattern="[a-zA-Z0-9]+" required>
                                           <select data-ng-model="field.type" title="@lang('Field type')" data-uk-tooltip>
                                               <option value="text">Text</option>
                                               <option value="html">Html</option>
                                               <option value="select">Select</option>
                                               <option value="boolean">Boolean</option>
                                               <option value="media">Media</option>
                                           </select>

                                           <input type="text" data-ng-if="field.type=='select'" data-ng-model="field.options" ng-list placeholder="@lang('options...')">
                                           <input type="text" data-ng-if="field.type=='media'" data-ng-model="field.allowed" placeholder="*.*" title="@lang('Allowed media types')" data-uk-tooltip>

                                           <a data-ng-click="removefield(field)" class="uk-close"></a>
                                        </div>
                                     </li>
                                </ul>

                                <button data-ng-click="addfield()" type="button" class="uk-button uk-button-success"><i class="uk-icon-plus-circle" title="@lang('Add field')"></i></button>
                            </div>

                        </div>


                        <div class="uk-form-row">
                            <button type="submit" class="uk-button uk-button-primary uk-button-large">@lang('Save gallery')</button> &nbsp;
                            <a href="@route('/galleries')">@lang('Cancel')</a>
                        </div>
                    </div>
                </div>

                <div class="uk-width-medium-1-5">

                    <div class="uk-form-row">
                        <label><strong>@lang("Group")</strong></label>
                        <div class="uk-form-controls uk-margin-small-top">
                            <div class="uk-form-select">
                                <i class="uk-icon-sitemap uk-margin-small-right"></i>
                                <a>@@ gallery.group || '- @lang("No group") -' @@</a>
                                <select class="uk-width-1-1 uk-margin-small-top" data-ng-model="gallery.group">
                                    <option ng-repeat="group in groups" value="@@ group @@">@@ group @@</option>
                                    <option value="">- @lang("No group") -</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <strong>@lang("Meta fields")</strong>

                        <div class="uk-margin-small-top">
                            <ul class="uk-list uk-list-line uk-text-muted" ng-show="gallery.fields.length">
                                 <li data-ng-repeat="field in gallery.fields">
                                    <i class="uk-icon-chain"></i> @@ field.name @@
                                 </li>
                            </ul>

                            <p class="uk-text-muted" ng-show="!gallery.fields.length">
                                @lang('No meta fields defined.')
                            </p>
                        </div>

                        <button type="button" class="uk-button" data-ng-class="managefields ? 'uk-button-success':'uk-button-primary'" data-ng-click="(managefields = !managefields)" title="@lang('Manage meta fields')">
                            <span ng-show="!managefields"><i class="uk-icon-cog"></i></span>
                            <span ng-show="managefields"><i class="uk-icon-check"></i></span>
                        </button>
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

                    <div data-ng-switch-when="select">
                        <select class="uk-width-1-1 uk-form-large" data-ng-model="$parent.metaimage.data[field.name]" data-ng-init="fieldindex=$index">
                            <option value="@@ option @@" data-ng-repeat="option in (field.options || [])" data-ng-selected="($parent.metaimage.data[field.name]==option)">@@ option @@</option>
                        </select>
                    </div>

                    <div data-ng-switch-when="media">
                        <input type="text" media-path-picker="@@ field.allowed || '*' @@" data-ng-model="$parent.metaimage.data[field.name]">
                    </div>

                    <div data-ng-switch-when="boolean">
                        <input type="checkbox" data-ng-model="$parent.metaimage.data[field.name]">
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