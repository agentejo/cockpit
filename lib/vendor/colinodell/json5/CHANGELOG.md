# Changelog

All notable changes to `colinodell/json5` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [Unreleased][unreleased]

## [2.1.0] - 2019-03-28
### Added
 - Added `.phpstorm.meta.php` for better code completion
 - Added several tiny micro-optimizations

### Removed
 - Removed support for PHP 5.4 and 5.5

## [2.0.0] - 2018-09-20
### Added
 - Added a polyfill for class `\JsonException` (added in PHP 7.3)
 - Added a polyfill for constant `JSON_THROW_ON_ERROR`
### Changed
 - The `SyntaxError` class now extends from `\JsonException`

## [1.0.5] - 2018-09-20
### Fixed
 - Fixed exceptions not being thrown for incomplete objects/arrays

## [1.0.4] - 2018-01-14
### Changed
 - Modified the internal pointer and string manipulations to use bytes instead of characters for better performance (#4)

## [1.0.3] - 2018-01-14
### Fixed
 - Fixed check for PHP 7+

## [1.0.2] - 2018-01-14
This release contains massive performance improvements of 98% or more, especially for larger JSON inputs!

### Added
 - On PHP 7.x: parser will try using `json_decode()` first in case normal JSON is given, since this function is much faster (#1)

### Fixed
 - Fixed multiple performance issues (#1)
 - Fixed bug where `JSON_OBJECT_AS_ARRAY` was improperly taking priority over `assoc` in some cases

## [1.0.1] - 2017-11-11
### Removed
 - Removed accidentally-public constant

## 1.0.0 - 2017-11-11
### Added
 - Initial commit

[unreleased]: https://github.com/colinodell/json5/compare/v2.1.0...HEAD
[2.1.0]: https://github.com/colinodell/json5/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/colinodell/json5/compare/v1.0.5...v2.0.0
[1.0.5]: https://github.com/colinodell/json5/compare/v1.0.4...v1.0.5
[1.0.4]: https://github.com/colinodell/json5/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/colinodell/json5/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/colinodell/json5/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/colinodell/json5/compare/v1.0.0...v1.0.1
