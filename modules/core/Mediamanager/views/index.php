{{ $app->assets(['assets:vendor/codemirror/lib/codemirror.js','assets:vendor/codemirror/lib/codemirror.css','assets:vendor/codemirror/theme/pastel-on-dark.css'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/codemirror/mode/xml/xml.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/htmlmixed/htmlmixed.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/javascript/javascript.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/css/css.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/codemirror/mode/clike/clike.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/codemirror/addon/edit/matchbrackets.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/codemirror/addon/selection/active-line.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:vendor/ajaxupload.js'], $app['cockpit/version']) }}
{{ $app->assets(['assets:vendor/loadie/jquery.loadie.js', 'assets:vendor/loadie/loadie.css'], $app['cockpit/version']) }}
{{ $app->assets(['mediamanager:assets/js/index.js'], $app['cockpit/version']) }}

{{ $app->assets(['assets:angular/directives/mediapreview.js'], $app['cockpit/version']) }}


<div class="app-wrapper" data-ng-controller="mediamanager" ng-cloak>

    <div class="uk-navbar">
        <span class="uk-navbar-brand">@lang('Mediamanager')</span>
        <ul class="uk-navbar-nav">
            <li class="uk-parent" data-uk-dropdown>
                <a><i class="uk-icon-star"></i>&nbsp; @lang('Bookmarks')</a>
                <div class="uk-dropdown uk-dropdown-navbar">

                    <ul id="mmbookmarks" class="uk-nav uk-nav-navbar uk-nav-parent-icon" ng-show="(bookmarks.folders.length || bookmarks.files.length)">

                        <li class="uk-nav-header" ng-if="bookmarks.folders.length">@lang('Folders')</li>
                        <li ng-repeat="folder in bookmarks.folders" ng-if="bookmarks.folders.length">
                            <a data-idx="@@ $index @@" data-group="folders" href="#@@ folder.path @@" ng-click="updatepath(folder.path)" draggable="true"><i class="uk-icon-folder-o"></i> @@ folder.name @@</a>
                        </li>

                        <li class="uk-nav-header" ng-if="bookmarks.files.length">@lang('Files')</li>
                        <li ng-repeat="file in bookmarks.files" ng-if="bookmarks.files.length">
                            <a data-idx="@@ $index @@" data-group="files" ng-click="open(file)" draggable="true"><i class="uk-icon-file-o"></i> @@ file.name @@</a>
                        </li>


                    </ul>

                    <div class="uk-text-muted" ng-show="(!bookmarks.folders.length && !bookmarks.files.length)">
                        @lang('You have nothing bookmarked.')
                    </div>
                </div>
            </li>
            <li><a href="" ng-click="action('createfolder')"><i class="uk-icon-plus-circle"></i>&nbsp; @lang('Folder')</a></li>
            <li><a href="" ng-click="action('createfile')"><i class="uk-icon-plus-circle"></i>&nbsp; @lang('File')</a></li>
            <li class="media-upload-button">
                <a><i class="uk-icon-upload"></i>&nbsp; </a>
                <form id="frmMediaUpload" action="">
                    <input type="file" name="files[]" onchange="jQuery(this.form).trigger('submit')">
                </form>
            </li>
        </ul>
    </div>
    <br>
    <div class="app-panel">

        <div class="uk-panel app-panel-box docked">
            <ul class="uk-breadcrumb">
                <li ng-click="updatepath('/')"><a href="#/" title="Change dir to root"><i class="uk-icon-home"></i></a></li>
                <li ng-repeat="crumb in breadcrumbs"><a href="#@@ crumb.path @@" ng-click="updatepath(crumb.path)" title="Change dir to @@ crumb.name @@">@@ crumb.name @@</a></li>
            </ul>
        </div>

        <div class="uk-navbar uk-margin-large-bottom">

            <div class="uk-navbar-content">
                <div class="uk-button-group">
                    <button class="uk-button" data-ng-class="mode=='table' ? 'uk-button-primary':''" data-ng-click="(mode='table')" title="@lang('Table mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-list-alt"></i></button>
                    <button class="uk-button" data-ng-class="mode=='list' ? 'uk-button-primary':''" data-ng-click="(mode='list')" title="@lang('List mode')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th"></i></button>
                </div>
            </div>

            <div class="uk-navbar-content">
                <span class="uk-alert uk-alert-warning" data-ng-show="dir && (dir.folders.length && viewfilter=='files')"><span class="uk-icon-bolt"></span> @lang('Folders hidden via filter'): <strong>@@ dir.folders.length @@</strong></span>
                <span class="uk-alert uk-alert-warning" data-ng-show="dir && (dir.files.length && viewfilter=='folders')"><span class="uk-icon-bolt"></span> @lang('Files hidden via filter'): <strong>@@ dir.files.length @@</strong></span>
            </div>
            <div class="uk-navbar-flip">
                <div class="uk-navbar-content uk-form">
                    <div class="uk-form-icon uk-hidden-small">
                        <i class="uk-icon-filter"></i>
                        <input type="text" placeholder="@lang('Filter by name...')" data-ng-model="namefilter">
                    </div>
                    <div class="uk-button-group">
                        <button class="uk-button" data-ng-class="viewfilter=='all' ? 'uk-button-primary':''" data-ng-click="(viewfilter='all')" title="@lang('Show files + directories')" data-uk-tooltip="{pos:'bottom'}">@lang('All')</button>
                        <button class="uk-button" data-ng-class="viewfilter=='folders' ? 'uk-button-primary':''" data-ng-click="(viewfilter='folders')" title="@lang('Show only directories')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-folder-o"></i> <span class="uk-text-small">@@dir.folders.length@@</span></button>
                        <button class="uk-button" data-ng-class="viewfilter=='files' ? 'uk-button-primary':''" data-ng-click="(viewfilter='files')" title="@lang('Show only files')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-file-o"></i> <span class="uk-text-small">@@dir.files.length@@</span></button>
                    </div>
                </div>
            </div>
        </div>

        <ul class="uk-clearfix media-dir" data-ng-show="mode=='list' && dir && (dir.folders.length || dir.files.length)">
            <li class="uk-width-medium-1-5 uk-width-1-1 uk-float-left" ng-repeat="folder in dir.folders" data-type="folder" data-ng-hide="(viewfilter=='files' || !matchName(folder.name))">
                <div>
                    <div class="mm-type">
                        <i class="uk-icon-folder-o"></i>
                        <div class="mm-actions">
                            <ul class="uk-subnav uk-subnav-line">
                                <li><a ng-click="addBookmark(folder)" title="@lang('Bookmark folder')"><i class="uk-icon-star"></i></a></li>
                                <li><a ng-click="action('rename', folder)" title="@lang('Rename folder')"><i class="uk-icon-text-width"></i></a></li>
                                <li><a ng-click="action('remove', folder)" title="@lang('Delete folder')"><i class="uk-icon-minus-circle"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="uk-text-truncate mm-caption" title="@@ folder.name @@"><a href="#@@ folder.path @@" ng-click="updatepath(folder.path)">@@ folder.name @@</a></div>
                </div>
            </li>
            <li class="uk-width-medium-1-5 uk-width-1-1 uk-float-left" ng-repeat="file in dir.files" data-ng-hide="(viewfilter=='folders' || !matchName(file.name))">
                <div>
                    <div class="mm-type">
                        <i class="uk-icon-file-o" media-preview="@@ file.url @@"></i>
                        <div class="mm-actions">
                            <ul class="uk-subnav uk-subnav-line">
                                <li><a ng-click="addBookmark(file)" title="@lang('Bookmark file')"><i class="uk-icon-star"></i></a></li>
                                <li><a ng-click="action('rename', file)" title="@lang('Rename file')"><i class="uk-icon-text-width"></i></a></li>
                                <li><a ng-click="action('download', file)" title="@lang('Download file')"><i class="uk-icon-paperclip"></i></a></li>
                                <li><a ng-click="action('remove', file)" title="@lang('Delete file')"><i class="uk-icon-minus-circle"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="uk-text-truncate mm-caption" title="@@ file.name @@"><a ng-click="open(file)">@@ file.name @@</a></div>
                </div>
            </li>
        </ul>


        <table class="uk-table uk-table-hover media-table" data-ng-show="mode=='table' && dir && (dir.folders.length || dir.files.length)">
            <thead>
                <tr>
                    <th width="20"></th>
                    <th>@lang('Name')</th>
                    <th class="uk-text-right" width="100">@lang('Size')</th>
                    <th class="uk-text-right" width="150">@lang('Last modified')</th>
                    <th class="uk-text-right" width="50">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="folder in dir.folders" data-type="folder" data-ng-hide="(viewfilter=='files' || !matchName(folder.name))">
                   <td><i class="uk-icon-folder-o"></i></td>
                   <td><div class="uk-text-truncate" title="@@ folder.name @@"><a href="#@@ folder.path @@" ng-click="updatepath(folder.path)">@@ folder.name @@</a></div></td>
                   <td>&nbsp;</td>
                   <td>&nbsp;</td>
                   <td class="uk-text-right">
                       <div class="mm-actions" data-uk-dropdown="{mode:'click'}">
                           <i class="uk-icon-bars"></i>
                           <div class="uk-dropdown uk-dropdown-flip uk-text-left">
                               <ul class="uk-nav uk-nav-dropdown uk-nav-parent-icon">
                                   <li class="uk-nav-header uk-text-truncate"><i class="uk-icon-folder-o"></i> @@ folder.name @@</li>
                                   <li><a ng-click="addBookmark(folder)" title="@lang('Bookmark folder')"><i class="uk-icon-star"></i> @lang('Bookmark folder')</a></li>
                                   <li><a ng-click="action('rename', folder)" title="@lang('Rename folder')"><i class="uk-icon-text-width"></i> @lang('Rename folder')</a></li>
                                   <li><a ng-click="action('remove', folder)" title="@lang('Delete folder')"><i class="uk-icon-minus-circle"></i> @lang('Delete folder')</a></li>
                               </ul>
                           </div>
                       </div>
                   </td>
                </tr>

                <tr ng-repeat="file in dir.files" data-type="folder" data-ng-hide="(viewfilter=='folders' || !matchName(file.name))">
                   <td><i class="uk-icon-file-o" media-preview="@@ file.url @@"></i></td>
                   <td><div class="uk-text-truncate" title="@@ file.name @@"><a ng-click="open(file)">@@ file.name @@</a></div></td>
                   <td class="uk-text-right">@@ file.size @@</td>
                   <td class="uk-text-right">@@ file.lastmodified @@</td>
                   <td class="uk-text-right">
                       <div class="mm-actions" data-uk-dropdown="{mode:'click'}">
                           <i class="uk-icon-bars"></i>
                           <div class="uk-dropdown uk-dropdown-flip uk-text-left">
                               <ul class="uk-nav uk-nav-dropdown uk-nav-parent-icon">
                                   <li class="uk-nav-header uk-text-truncate"><i class="uk-icon-file-o"></i> @@ file.name @@</li>
                                   <li><a ng-click="addBookmark(file)" title="@lang('Bookmark file')"><i class="uk-icon-star"></i> @lang('Bookmark file')</a></li>
                                   <li><a ng-click="action('rename', file)" title="@lang('Rename file')"><i class="uk-icon-text-width"></i> @lang('Rename file')</a></li>
                                   <li><a ng-click="action('download', file)" title="@lang('Download file')"><i class="uk-icon-paperclip"></i> @lang('Download file')</a></li>
                                   <li><a ng-click="action('remove', file)" title="@lang('Delete file')"><i class="uk-icon-minus-circle"></i> @lang('Delete file')</a></li>
                               </ul>
                           </div>
                       </div>
                   </td>
                </tr>
            </tbody>
        </table>

        <div class="uk-margin uk-text-center" data-ng-show="dir && (!dir.folders.length && !dir.files.length)">
            <h2><i class="uk-icon-folder-o-open-o"></i></h2>
            <p class="uk-text-large">
                @lang('This folder is empty.')
            </p>
        </div>

    </div>

</div>

<div id="mm-image-preview" class="uk-modal">
    <div class="uk-modal-dialog">
        <div class="modal-content uk-text-center"></div>
    </div>
</div>

<div id="mm-editor">
    <nav class="uk-navbar">
        <div class="uk-navbar-content">
            <i class="uk-icon-pencil"></i> &nbsp; <strong class="uk-text-small filename"></strong>
        </div>
        <ul class="uk-navbar-nav">
            <li><a data-editor-action="save" title="@lang('Save file')" data-uk-tooltip><i class="uk-icon-save"></i></a></li>
        </ul>
        <div class="uk-navbar-flip">
            <ul class="uk-navbar-nav">
                <li><a data-editor-action="close" title="@lang('Close file')" data-uk-tooltip><i class="uk-icon-times"></i></a></li>
            </ul>
        </div>
    </nav>
    <textarea></textarea>
</div>

<style>

    .app-panel a {
        cursor: pointer;
    }

    .media-dir {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .media-dir > li > div {
        padding: 10px;
        min-height: 100px;
    }

    .media-dir .mm-type {
        position: relative;
        text-align: center;
        padding: 15px;
        height: 70px;
    }

    .media-dir .mm-type > i {
        font-size: 40px;
    }

    .media-dir .mm-type .mm-actions {
        display: none;
        position: absolute;
        top: 45%;
        left: 0;
        right: 0;
    }

    .media-dir .mm-type:hover .mm-actions {
        display: block;
    }

    .media-dir .mm-type .mm-actions > ul {
        display: inline-block;
        background: #eee;
        background: rgba(0,0,0,0.75);
        padding: 5px 20px;
        border-radius: 3px;
    }

    .media-dir .mm-type .mm-actions a {
        color: #fff;
        cursor: pointer;
        font-size: 11px;
    }

    table.uk-table .uk-subnav {
        padding: 0;
        margin: 0;
    }

    .media-dir .mm-caption {
        text-align: center;
    }

    .media-upload-button {
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    .media-upload-button form {
        opacity: 0;
        position: absolute;
        padding: 0;
        margin: 0;
        top:0;
        left:0;
        font-size: 500px;
    }
    .media-upload-button * { cursor: pointer; }

    .media-dir .uk-icon-folder-o, .media-table .uk-icon-folder-o {
        color: #999;
    }

    .mm-actions { cursor: pointer; }

    .media-url-preview {
        background-repeat:no-repeat;
        background-position: 50% 50%;
        background-size:contain;
    }

    .media-dir .media-url-preview {
        height: 35px;
        width: 35px;
        display: inline-block;
    }

    .media-table .media-url-preview {
        height: 14px;
        width: 14px;
    }


    /* editor */

    #mm-editor {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.3);
        border: 10px rgba(0,0,0,0.3) solid;
        z-index: 100;
    }

    #mm-editor .uk-navbar {
        background: #f7f7f7;
        border-radius: 3px 3px 0 0;
    }

    #mm-editor .CodeMirror {
        border: none;
        border-radius:  0 0 3px 3px;
    }

    #mm-editor a { cursor: pointer; }

</style>