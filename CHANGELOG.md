# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- new event: `collections.admin._filterMongo.before`
- new event: `collections.admin._filterMongo.field`
- cyrillic slug support
- `CHANGELOG.md` file
- webp support in assets manager
- text color picker plugin to wysiwyg editor
- disable `convert_urls` option for the wysiwyg field

### Changed

- return `null` instead of empty string on rest api calls
- minimum PHP version `7.3.0`

### Deprecated

### Fixed

- mispelling in `csrf` tokens (renamend "csfr" to "csrf")
- missing UIkit i18n for password field and and placeholder
- default langauge for "admin" user during app install

### Security

### Removed

- `$fn`, `$func`, `$f` as MongoLite filter (use `$where` as an alternative)


## [0.11.2] - 2020-09-14

### Changed

- update UIkit (see: [#1327](https://github.com/agentejo/cockpit/issues/1327))

### Fixed

- wrong user props on new password view

### Security

- fixed possible security issue for login
- don't allow callable strings for $func (MongoLite) (see: [comments](https://github.com/agentejo/cockpit/commit/33e7199575631ba1f74cba6b16b10c820bec59af#comments))

## [0.11.1] - 2020-09-09

### Changed

- update cache helper
- throw exception instead of calling app stop on field condition fail

### Fixed

- return nulled singleton data instead of 404
- missing quotes for database queries (MongoLite and SQLite)
- missing options in `cp-field` tag
- fix mongo driver update method

## [0.11.0] - 2020-07-25

### Added

- select field supports source url for remote entries
- field-select add multiple support
- field-multipleselect: add remote source option
- collection entry linked usage overview panel
- default sort + filter option for collection-link fields
- folder upload support for finder and assets
- group feature for select items
- titles on overview items
- new event: `cockpit.webhook`
- new event: `cockpit.assetsfolders.find.before`
- new option: `"restrict"` to layout field
- new option: `"filter"` param for `/api/collections/entry/*`
- new shortcut: `root://` to content preview urls #1311
- new method: `lib/MongoHybrid/Mongo::renameCollection()`
- new component: `field-collectionlinkselect.tag`
- new cli script: `renamecollection.php`
- rest api: `/api/collections/entry/{collection}/{id}` rest api to retrieve single collection item
- rest api: `/api/cockpit/asset/{id}` api entry point

### Changed

- code cleanup and ui improvements
- improved mongo query in admin ui for collection items and accounts
- update `.htaccess` rewrite rules
- remove `uniqid()` usage for resource `_id` generation (use just name by default)

### Deprecated

- `/api/collections/get` in favour of `/api/collections/entries`

### Fixed

- mongo update api usage
- lodamore in collection-link dialog
- sort option for field-collectionlinkselect
- mongolite queries for null values
- allow relative urls for content preview (see: [#1312](https://github.com/agentejo/cockpit/pull/1312))

### Security

- reflected XSS in login panel (see: [#1310](https://github.com/agentejo/cockpit/issues/1310))

[unreleased]: https://github.com/agentejo/cockpit/compare/0.11.2...HEAD
[0.11.2]: https://github.com/agentejo/cockpit/compare/0.11.1...0.11.2
[0.11.1]: https://github.com/agentejo/cockpit/compare/0.11.0...0.11.1
[0.11.0]: https://github.com/agentejo/cockpit/compare/0.10.2...0.11.0
