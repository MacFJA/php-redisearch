# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased v2.x]

### Added

- Documentation on paginated responses
- Throw custom exception is client is missing
- Add compatibility for Predis version 2.x
- (dev) Add `make clean` to remove all generated files

### Fixed

- Missing default values on highlight option ([Issue#38])

### Changed

- Rework paginated responses
- Add warning for Predis commands initialization
- (dev) Remove deprecated `--no-suggest` option of Composer in the `Makefile`

### Deprecated

- `\MacFJA\RediSearch\Redis\Response\AggregateResponseItem::getValue` (use `\MacFJA\RediSearch\Redis\Response\AggregateResponseItem::getFieldValue` instead)

## [2.1.2]

### Fixed

- Allow Phpredis raw command to have 0 arguments ([Issue#39])

### Changed

- (dev) Update version and rules of PHP-CS-Fixer and PHPStan

## [2.1.1]

### Fixed

- Summarize Search option enabled by default (without possibility to disable it) ([Issue#26])
- Highlight Search option enabled by default (without possibility to disable it) ([Issue#26])
- Fix error when parsing response from Search with NOCONTENT flag
- Make PaginatedResponse `valid` iterator function work

## [2.1.0]

### Added

- Support from JSON document (require RediSearch 2.2)
- (dev) GitHub Actions
- Protection against division by 0

### Fixed

- (dev) Code coverage with XDebug 3
- (dev) Defined list of allowed Composer plugins
- Fix `PaginatedResponse` index (now start at 0, and have a linear progression)

## [2.0.2]

### Added

- `reset` method for builder (`\MacFJA\RediSearch\IndexBuilder` and `\MacFJA\RediSearch\Query\Builder`)
- Allow PHP 8.0
- (dev) Allow `podman` (in addition to `docker`) as container runner for integration test

### Changed

- `APPLY` option of Aggregate command is now dependent of the last inserted option (if the option have a meaning for `APPLY`)
- (dev) Update `phpstan` (from `0.12` to `1.2`)

### Fixed

- Fix the `APPLY` option of the Aggregate command

## [2.0.1]

### Fixed

- (doc) Fix GPS coordinate (should be longitude first)
- `AmpRedisClient` pipeline not sequential

## [2.0.0]

### Added

- Allow multiple connector for Redis ([PR#17])
- Allow specific feature of RediSearch depending of its version ([Issue#6])
- Add more language ([Issue#5])
- Add more language ([Issue#5])
- Add `SORTABLE` flag on `GEO` field ([Issue#5])
- Add `CASESENSITIVE` flag on fields ([Issue#5])
- Add `UNF` flag on fields ([Issue#5])

### Changed

- Remove hard dependency on Predis ([Issue#16])
- Full rework of the library

## [Unreleased v1.x]

## [1.4.0]

### Added

- Implementation of SLOP parameter ([PR#8])

### Fixed

- Correctly handle Search result with NoContent flag ([Issue#9], [Issue#10], [PR#13])
- Don't double % in fuzzy search ([Issue#11], [PR#14])
- Fix exact match not used inside text facet ([Issue#12], [PR#15])
- (dev) Fix missing `@uses` in Unit Test

## [1.3.0]

### Added

- Shorthand for Reducer functions
- Add **Deprecated** section in `CHANGELOG.md`
- Add Redis pipeline support
- Prefix properties name with `@` in Aggregation's SortBy (keep BC)
- Add more unit tests (Aggregation options, Field creation, DataHelper, RedisHelper)
- (dev) Add mutation testing

### Changed

- Allow `GROUPBY` with no properties
- Allow `GROUPBY` with no reducers
- (dev) Replace `php-parallel-lint` lib.
- (dev) Add Php-Cs-Fixer on `tests/`
- (dev) Improve Makefile

### Fixed

- Fix namespace of tests
- Aggregation result can now be an array
- Fix Index builder options not preserved ([PR#3])
- Aggregation SortBy allow 0 (not limit) as value for Max
- Fix helper removing _"nullish"_ values (`array_filter` is too lax by default)
- Fix order in fields creation not being correct
- Don't allow empty facet in search query

### Deprecated

- `MacFJA\RediSearch\Aggregate\Exception\NotEnoughPropertiesException`
- `MacFJA\RediSearch\Aggregate\Exception\NotEnoughReducersException`

## [1.2.0]

### Fixed

- Bad copy/paste in the [README](README.md) file
- Allow `null` value in Aggregate result
- Use correct namespace in the [README](README.md) file

### Added

- Detection of syntax error from Redis response
- Allow multiple level of fuzziness
- Escape values in the query builder

## [1.1.0]

### Fixed

- Wrong output of the NumericFacet ([Issue#2], [PR#1])
- Missing Composer plugin
- Missing `getSeperator` method in `TagField`
- Missing total number of result
- Fix typo in search parameter (`RETURN`)
- Fix prefixes data from index information

### Added

- Unit test on NumericFacet ([PR#1])
- Index stats can return fields as object.
- Fields implements Comparable interface
- Paginate results.
- Syntax error detection in search.

### Changed

- Rename `Index::addFromArray` to `Index::addDocumentFromArray` for consistency (Depreciation, keeping BC)
- Rename namespace from `MacFJA\RedisSearch` to `MacFJA\RediSearch`(Depreciation, keeping BC)
- Uniformize Builder interface (Deprecation, keeping BC)

### Deprecated

- The whole namespace `MacFJA\RedisSearch` (replaced by `MacFJA\RediSearch`)
- `MacFJA\RediSearch\Aggregate::aggregate`
- `MacFJA\RediSearch\Index::addFromArray`
- `MacFJA\RediSearch\Index\Builder::create`
- `MacFJA\RediSearch\Search::search`

## [1.0.0] - 2020-12-05

First version

[Unreleased v2.x]: https://github.com/MacFJA/php-redisearch/compare/2.1.2...HEAD
[2.1.2]: https://github.com/MacFJA/php-redisearch/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/MacFJA/php-redisearch/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/MacFJA/php-redisearch/compare/2.0.2...2.1.0
[2.0.2]: https://github.com/MacFJA/php-redisearch/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/MacFJA/php-redisearch/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/MacFJA/php-redisearch/releases/tag/2.0.0
[Unreleased v1.x]: https://github.com/MacFJA/php-redisearch/compare/1.4.0...1.x
[1.4.0]: https://github.com/MacFJA/php-redisearch/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/MacFJA/php-redisearch/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/MacFJA/php-redisearch/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/MacFJA/php-redisearch/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/MacFJA/php-redisearch/releases/tag/1.0.0

[Issue#2]: https://github.com/MacFJA/php-redisearch/issues/2
[Issue#5]: https://github.com/MacFJA/php-redisearch/issues/5
[Issue#6]: https://github.com/MacFJA/php-redisearch/issues/6
[Issue#9]: https://github.com/MacFJA/php-redisearch/issues/9
[Issue#10]: https://github.com/MacFJA/php-redisearch/issues/10
[Issue#11]: https://github.com/MacFJA/php-redisearch/issues/11
[Issue#12]: https://github.com/MacFJA/php-redisearch/issues/12
[Issue#16]: https://github.com/MacFJA/php-redisearch/issues/16
[Issue#26]: https://github.com/MacFJA/php-redisearch/issues/26
[Issue#38]: https://github.com/MacFJA/php-redisearch/issues/38
[Issue#39]: https://github.com/MacFJA/php-redisearch/issues/39
[PR#1]: https://github.com/MacFJA/php-redisearch/pull/1
[PR#3]: https://github.com/MacFJA/php-redisearch/pull/3
[PR#8]: https://github.com/MacFJA/php-redisearch/pull/8
[PR#13]: https://github.com/MacFJA/php-redisearch/pull/13
[PR#14]: https://github.com/MacFJA/php-redisearch/pull/14
[PR#15]: https://github.com/MacFJA/php-redisearch/pull/15
[PR#17]: https://github.com/MacFJA/php-redisearch/pull/17
