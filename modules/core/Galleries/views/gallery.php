@start('header')

    {{ $app->assets(['assets:vendor/uikit/js/components/sortable.min.js'], $app['cockpit/version']) }}
    {{ $app->assets(['assets:vendor/uikit/js/components/nestable.min.js'], $app['cockpit/version']) }}

    {{ $app->assets(['mediamanager:assets/pathpicker.js'], $app['cockpit/version']) }}

    {{ $app->assets(['galleries:assets/galleries.js','galleries:assets/js/gallery.js'], $app['cockpit/version']) }}

    @trigger('cockpit.content.fields.sources')

    <style>

        #images-list .uk-thumbnail {
            position: relative;
        }

        .images-list-actions {
            position: absolute;
            left: 0;
            width: 100%;
            top: 50%;
            -webkit-transform: translateY(-50%);
            transform: translateY(-50%);
        }
    </style>

@end('header')

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
                            <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="gallery.name" required>
                            <div class="uk-margin-top">
                                <input class="uk-width-1-1 uk-form-blank uk-text-muted" type="text" data-ng-model="gallery.slug" app-slug="gallery.name" placeholder="@lang('Slug...')" title="slug" data-uk-tooltip="{pos:'left'}">
                            </div>
                        </div>

                        <div class="uk-form-row">

                            <div data-ng-show="!managefields">

                                <ul id="images-list" class="uk-grid uk-sortable" data-uk-grid-match="{target:'.uk-thumbnail'}" data-uk-sortable>
                                    <li class="uk-width-1-2 uk-width-medium-1-5 uk-grid-margin" data-ng-repeat="image in gallery.images" draggable="true">
                                        <div class="uk-thumbnail uk-width-1-1 uk-text-center uk-visible-hover">
                                            <div class="uk-text-center" style="background: #fff url(@route('/mediamanager/thumbnail')/@@ image.path|base64 @@/200/200) 50% 50% no-repeat;background-size:contain;height:140px;">

                                                <div class="images-list-actions uk-hidden">
                                                    <div class="uk-button-group">
                                                        <button type="button" class="uk-button uk-button-small uk-button-primary" data-ng-click="showMeta($index)"><i class="uk-icon-pencil"></i></button>
                                                        <button type="button" class="uk-button uk-button-small uk-button-danger" data-ng-click="removeImage($index)"><i class="uk-icon-trash-o"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>


                                <div class="uk-text-center uk-margin-large-top uk-margin-large-bottom" data-ng-show="gallery && !gallery.images.length">
                                    <h2 class="uk-text-muted"><i class="uk-icon-th"></i></h2>
                                    <p class="uk-text-muted uk-text-large">
                                        @lang('You don\'t have any images in this gallery.')
                                    </p>
                                </div>

                            </div>

                            <div data-ng-show="managefields">

                                <ul id="manage-fields-list" class="uk-nestable" data-uk-nestable="{maxDepth:1}">
                                     <li class="uk-nestable-list-item" data-ng-repeat="field in gallery.fields">
                                        <div class="uk-nestable-item uk-nestable-item-table">

                                            <div class="uk-grid uk-grid-small">
                                                <div class="uk-width-3-4">
                                                    <div class="uk-nestable-handle"></div>
                                                    <input class="uk-width-2-3 uk-form-blank" type="text" data-ng-model="field.name" placeholder="@lang('Field name')" pattern="[a-zA-Z0-9]+" required>
                                                </div>
                                                <div class="uk-width-1-4 uk-text-right">
                                                    <a ng-click="toggleOptions($index)"><i class="uk-icon-cog"></i></a>
                                                    <a data-ng-click="removefield(field)" class="uk-close"></a>
                                                </div>
                                            </div>
                                            <div id="options-field-@@ $index @@" class="app-panel uk-margin-small-top uk-hidden">
                                                <div class="uk-grid uk-grid-small">
                                                    <div class="uk-width-1-2">

                                                        <label class="uk-text-small">@lang('Field type')</label>
                                                        <select class="uk-width-1-1" data-ng-model="field.type" title="@lang('Field type')" ng-options="f.name as f.label for f in contentfields"></select>
                                                    </div>
                                                    <div class="uk-width-1-2">
                                                        <label class="uk-text-small">@lang('Field label')</label>
                                                        <input class="uk-width-1-1" type="text" data-ng-model="field.label" placeholder="@lang('Field label')">
                                                    </div>

                                                    <div class="uk-width-1-1 uk-grid-margin">

                                                        <strong class="uk-text-small">Extra options</strong>
                                                        <hr>
                                                        <div class="uk-form uk-form-horizontal">

                                                            <div class="uk-form-row" data-ng-if="field.type=='select'">
                                                                <label class="uk-form-label">@lang('Options')</label>
                                                                <div class="uk-form-controls">
                                                                    <input class="uk-form-blank" type="text" data-ng-model="field.options" ng-list placeholder="@lang('options...')" title="@lang('Separate different options by comma')" data-uk-tooltip>
                                                                </div>
                                                            </div>

                                                            <div class="uk-form-row" data-ng-if="field.type=='media'">
                                                                <label class="uk-form-label">@lang('Extensions')</label>
                                                                <div class="uk-form-controls">
                                                                    <input class="uk-form-blank" type="text" data-ng-model="field.allowed" placeholder="*.*" title="@lang('Allowed media types')" data-uk-tooltip>
                                                                </div>
                                                            </div>

                                                            <div class="uk-form-row" data-ng-if="field.type=='code'">
                                                                <label class="uk-form-label">@lang('Syntax')</label>
                                                                <div class="uk-form-controls">
                                                                    <select data-ng-model="field.syntax" title="@lang('Code syntax')" data-uk-tooltip>
                                                                        <option value="text">Text</option>
                                                                        <option value="css">CSS</option>
                                                                        <option value="htmlmixed">Html</option>
                                                                        <option value="javascript">Javascript</option>
                                                                        <option value="markdown">Markdown</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            @trigger('cockpit.content.fields.settings')

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

                        <button type="button" class="uk-button" data-ng-class="managefields ? 'uk-button-success':'uk-button-primary'" data-ng-click="switchFieldsForm(managefields)" title="@lang('Manage meta fields')">
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
                <div class="uk-form-row" data-ng-repeat="field in gallery.fields">

                    <label class="uk-text-small">@@ (field.label || field.name) | uppercase @@</label>

                    <contentfield options="@@ field @@" ng-model="$parent.metaimage.data[field.name]"></contentfield>
                </div>
            </div>

            <button class="uk-button uk-modal-close">@lang('Close')</button>
        </div>
    </div>

</div>
