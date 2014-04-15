# Changelog

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