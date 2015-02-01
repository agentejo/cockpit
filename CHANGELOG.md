# Changelog

### 0.13.0  (Feb 02, 2015)

    + Added preview for media + gallery field in collection entries list view
    + Added update_region_field api method
    + Added possibility to override paths in custom config.php
    + Added "count" api method to collections and datastore modules
    + Added pagination for form entries
    + Added App.viewpopup(url) backend js api
    + Added file based email templates @piotr-cz
    + Added extract zip files in mediamanager
    + Added cockpit.clearcache event
    + Added ContainerArray class
    ^ Updated assets
    ^ Updated language file reference @piotr-cz
    # Fixed collection media preview breaks out of the container
    # Fixed installation background size @piotr-cz
    # Fixed Insert media button in WYSIWYG editor
    # Fixed MongoDB find api

### 0.12.0  (Nov 19, 2014)

    + Added "Multiple Field" content field (repeatable fields)
    ^ Updated assets (angular, UIkit, ...)
    ^ Reworked and optimized gallery content field + gallery module
    ^ Many small improvements
    ^ Updated language file @Remigr
    ^ Updated language file @Remigr
    # Fixed undefined constant @sergeypavlenko
    # Fixed remove api for collections @sergeypavlenko
    # Fixed galleries::get_gallery_by_slug
    - Removed LESS and SCSS compilers
    - Removed Leaflet.js dependency

### 0.11.3  (Oct 1, 2014)

    # Fixed gallery pathpicker js error

### 0.11.2  (Oct 1, 2014)

    # Fixed collection link field on locale change
    # Fixed 404 error on empty string output
    # Fixed Backups download

### 0.11.1  (Sep 2, 2014)

    ^ Extracted Backups to standalone core module
    ^ Decoupled module fields from core content fields
    # Fixed Parsedown extra parameter
    # Fixed install screen

### 0.11.0  (Sep 14, 2014)

    + Added Datastore module for simple embedded data storage
    + Added uikit less sources to easily rebuild admin theme
    + Added ParsedownExtra class
    + Added multiple api tokens supporting custom url rules
    + Custom bootstrap file support (/custom/bootstrap.php)
    + Empty collection action
    ^ Add title for each image in a gallery field
    ^ Custom config.php moved to /custom/config.php
    ^ Language files are now loaded from /custom/i18n
    ^ Improved auto-updater
    # Fixed collection entries filtering for mongodb based installations

### 0.10.0  (Sep 04, 2014)

    + Added auto-updater (experimental)
    + Added location field
    + Added backup options (backup site or cockpit folder)
    + Added shorthand modules method call e.g. cockpit('regions:render', 'test');
    + Added auto slugs for collection, region and gallery names
    ^ Iimproved sluggify functionality, thanks @doctorjuta
    ^ Regions api extended
    ^ Localize option for any field
    # Check whether headers module is enabled in .htaccess (apache)

### 0.9.18  (Aug 31, 2014)

    + Added duplicate collections and regions
    + Added usage nice urls for the backend (if possible)
    + Added custom order via drag'n drop for collection entries
    + Added multilanguage support for content fields
    + Added Cross-Origin-Resource-Sharing headers to htaccess to allow extern access (e.g. mobile apps)
    + Added .htaccess to auto optimize apache based webservers
    + Added thumbnails api function
    + Added collection_populate_one api function
    ^ Updated assets + libs
    ^ Updated allow underscores in field names
    ^ Updated group settings moved to main storage (!important: group settings may get lost after update)
    ^ Updated allow using not latin letters in region & collection name field
    ^ Updated Lexy renderer refactored to service
    ^ Updated moved view caching to lexy class
    # Fixed MongoDB error on collection entries index page
    # Fixed notices in CLI mode @naumovs
    # Fixed Form disabled when not valid (form add-on)
    # Fixed cache empty assets @kinolaev
    # Fixed Galleries: Thumbnails of images added from folder aren't displayed @kinolaev
    - Removed $_SERVER['PATH_INFO'] dependency

### 0.9.17  (Jul 13, 2014)

    + Added new api function "collection_populate" to populate linked collection items
    + Added export form entries in json format
    + Added form entries data api
    + Added custom form validation
    + Added bind global "cmd + s" && "ctrl + s" to trigger save in edit views
    # Fixed running on a non-standard http port (80) breaks cockpit
    # Fixed slug option for text field
    # Fixed image thumbnail with space in filename doesn't show up
    # Fixed not correct date display in views @rpeshkov
    # Fixed disallow name fields beginning with a number @rpeshkov

### 0.9.16  (Jun 26, 2014)

    + Added slug option for collection text fields
    + Added collection can be grouped now
    + Added field label option
    # Fixed form entries list view
    # Fixed media root path didn't save properly

### 0.9.15  (Jun 11, 2014)

    + Added mode option to thumbnail function ['crop', 'resize', 'best_fit']
    + Added allow videos to be selected in gallery field
    + Added download/export collection entries as json export
    + Added region field to link regions in collections
    + Added batch remove for any list in table view
    ^ Updated form api: using formdata object when possible
    ^ Updated vendor assets + scripts
    # Fixed shortcut api functions url_to / path_to
    # Fixed success message on upload max filesize error
    # Fixed gallery field: removing media
    # Fixed login history
    # Fixed media upload button not working in firefox
    # Fixed hide form success and error messages initially


### 0.9.14  (Apr 16, 2014)

    + Added batch delete (media manager)
    + Added fuzzy search for any file in the media folder (media manager)
    + Added define allowed file pattern for media field
    ^ Clear/Empty media field after selection #79
    # Fixed :splat routes
    # Fixed nginx compatibility


### 0.9.13  (Apr 10, 2014)

    ^ Autofit topnav to screensize
    ^ Assets updated
    ^ Updated Html field
    ^ UX improvements
    # Fixed form mailer @davidgenetic
    # Fixed saving sortfield (collections)

### 0.9.12  (Apr 03, 2014)

    + Added tags field
    + Added entries pagination (load more)
    + Replaced Less parser lib to support less 1.7.0
    + Added pathToUrl to Cockpit.js
    ^ Updated vendor + assets libs
    # Fixed entries sorting

### 0.9.11  (Mar 22, 2014)

    + image gallery field for collections
    + composer support added (install cockpit via composer)
    ^ assets + vendors updated
    ^ image previews in media picker
    ^ added functionality for date picker + time picker fields
    ^ language file updated + i18n improved @StevenDevooght
    # MongoLite boolean query fix @StevenDevooght
    # missing html5 input input types (form add-on / serialize)

### 0.9.10  (Mar 12, 2014)

    ^ more assets cleanup + update
    ^ media manager list view updated
    + manage meta fields for each gallery
    # media manager empty folder fixed

### 0.9.9  (Mar 06, 2014)

    ^ more assets cleanup
    ^ mediapicker reworked

### 0.9.8  (Mar 06, 2014)

    ^ assets cleanup
    + autocomplete feature for code fields
    + markdown field added
    # Fixed get_registry api call

### 0.9.7  (Mar 02, 2014)

    # Fixed login after first install

### 0.9.6  (Mar 01, 2014)

    + Activity logger added
    # Fixed import images from folder (gallery addon)

### 0.9.5  (Mar 01, 2014)

    ^ dashboard reworked
    ^ more code cleanup

### 0.9.4  (Feb 27, 2014)

    + grouping for regions and galleries
    + batch remove for collection and form entries
    ^ minor updates


### 0.9.3 (Feb 21, 2014)

    ^ minor fixes
    ^ vendor assets updated


### 0.9.2 (Feb 19, 2014)

    + set media root path for each acl group
    + table view modes for regions, collections, galleries and forms
    + experimental mongodb support as main storage
    + required field option in collections @davidgenetic

### 0.9.1 (Feb 09, 2014)

    + Added global registry (key/value) storage
    + Added caching of Lexy based views when possible (tmp folder must be writable)
    - Removed jQuery dependency for form addon

### 0.9.0 (Feb 07, 2014)

    + REST api (+ js lib)

### 0.8.1 (Feb 04, 2014)

    + custom folder
    # Fixed codearea fields height
    ^ Updated assets + vendor libs

### 0.8.0 (Jan 30, 2014)

    + Added global in-app search for region, forms, collections, galleries and mediamanger bookmarks
    # Fixed default values for collection entries
    # Fixed boolean field type (collections, regions) @davidgenetic

### 0.7.4 (Jan 25, 2014)

    ^ Language file template
    ^ Assets updated
    + Image previews in mediamanager

### 0.7.3 (Jan 22, 2014)

    # Fixed media picker
    + Added gallery add-on

### 0.7.2 (Jan 16, 2014)

    + Added (simple) version history with rollback for regions and collection entries

### 0.7.1 (Jan 13, 2014)

    # Fixed PHP 5.4 rference error

### 0.7.0 (Jan 13, 2014)

    + Clean cache + database from settings/info
    + Added (simple) ACL functionality
    # Fixed collections entry bug

### 0.6.0 (Jan 11, 2014)

    ^ Language files moved to https://github.com/aheinze/cockpit-i18n
    + Site backup feature
    + Forms add-on

### 0.5.0 (Jan 08, 2014)

    * Initial release
