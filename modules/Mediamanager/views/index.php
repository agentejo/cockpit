{{ $app->assets(['assets:vendor/ajaxupload.js']) }}
{{ $app->assets(['mediamanager:assets/js/index.js']) }}

<div class="app-wrapper" data-ng-controller="mediamanager">

    <nav class="uk-navbar uk-margin-large-bottom">
        <ul class="uk-navbar-nav">
            <span class="uk-navbar-brand">Mediamanager</span>
            <li><a href="" class="uk-icon-plus-sign" ng-click="action('createfolder')">&nbsp; Folder</a></li>
        </ul>

        <div class="uk-navbar-flip">
            <div class="uk-navbar-content uk-form">
                <div class="uk-form-icon">
                    <i class="uk-icon-eye-open"></i>
                    <input type="text" placeholder="Filter by name..." data-ng-model="namefilter">
                </div>
                <div class="uk-button-group">
                    <button class="uk-button" data-ng-class="viewfilter=='all' ? 'uk-active':''" data-ng-click="(viewfilter='all')" title="Show files + directories" data-uk-tooltip="{pos:'bottom'}">All</button>
                    <button class="uk-button" data-ng-class="viewfilter=='folders' ? 'uk-active':''" data-ng-click="(viewfilter='folders')" title="Show only directories" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-folder-close"></i> <span class="uk-text-small">@@dir.folders.length@@</span></button>
                    <button class="uk-button" data-ng-class="viewfilter=='files' ? 'uk-active':''" data-ng-click="(viewfilter='files')" title="Show only files" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-file"></i> <span class="uk-text-small">@@dir.files.length@@</span></button>
                </div>
            </div>
        </div>
    </nav>

    <div class="uk-margin uk-panel uk-panel-box">
        <ul class="uk-breadcrumb">
            <li ng-click="updatepath('/')"><a href="#/" title="Change dir to root"><i class="uk-icon-home"></i></a></li>
            <li ng-repeat="crumb in breadcrumbs"><a href="#@@ crumb.path @@" ng-click="updatepath(crumb.path)" title="Change dir to @@ crumb.name @@">@@ crumb.name @@</a></li>
        </ul>
    </div>

    <div class="uk-alert uk-alert-warning uk-margin" data-ng-show="dir && (dir.folders.length && viewfilter=='files')">@@ dir.folders.length @@ folders are hidden via filter</div>
    <div class="uk-alert uk-alert-warning uk-margin" data-ng-show="dir && (dir.files.length && viewfilter=='folders')">@@ dir.files.length @@ files are hidden via filter</div>

    <div class="app-panel">
        
        <ul class="uk-grid media-dir" data-ng-show="dir && (dir.folders.length || dir.files.length)">
            <li class="uk-width-medium-1-4 uk-grid-margin uk-visible-hover" ng-repeat="folder in dir.folders" data-type="folder" data-ng-hide="(viewfilter=='files' || !matchName(folder.name))">
                <div class="uk-panel">
                    <div class="uk-button-group uk-hidden">
                        <button class="uk-button" title="Rename folder"><i class="uk-icon-text-width" ng-click="action('rename', folder)"></i></button>
                        <button class="uk-button" title="Delete folder"><i class="uk-icon-minus-sign" ng-click="action('remove', folder)"></i></button>
                    </div>
                    <div class="mm-type">
                        <i class="uk-icon-folder-close"></i>
                    </div>
                    <div class="uk-text-truncate mm-caption" title="@@ folder.name @@"><a href="#@@ folder.path @@" ng-click="updatepath(folder.path)">@@ folder.name @@</a></div>
                </div>
            </li>
            <li class="uk-width-medium-1-4 uk-grid-margin uk-visible-hover" ng-repeat="file in dir.files" data-ng-hide="(viewfilter=='folders' || !matchName(file.name))">
                <div class="uk-panel">
                    <div class="uk-button-group uk-hidden">
                        <button class="uk-button" title="Rename file"><i class="uk-icon-text-width" ng-click="action('rename', file)"></i></button>
                        <button class="uk-button" title="Download file"><i class="uk-icon-paper-clip" ng-click="action('download', file)"></i></button>
                        <button class="uk-button" title="Delete file"><i class="uk-icon-minus-sign" ng-click="action('remove', file)"></i></button>
                    </div>
                    <div class="mm-type">
                        <i class="uk-icon-file"></i>
                    </div>
                    <div class="uk-text-truncate mm-caption" title="@@ file.name @@" ng-click="open(file)">@@ file.name @@</div>
                </div>
            </li>
        </ul>

        <div class="uk-margin uk-text-center" data-ng-show="dir && (!dir.folders.length && !dir.files.length)">
            <h2><i class="uk-icon-folder-open-alt"></i></h2>
            <p class="uk-text-large">
                This folder is empty.
            </p>
        </div>

    </div>

</div>

<style>

    .media-dir > li > .uk-panel {
        position: relative;
        padding: 10px;
        border: 1px #eee dotted;
        min-height: 100px;
    }

    .media-dir > li .uk-button-group {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .media-dir .mm-type {
        text-align: center;
        font-size: 40px;
        padding: 15px;
    }
    .media-dir .mm-caption {
        text-align: center;
    }

</style>