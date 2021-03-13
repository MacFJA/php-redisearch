# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Shorthand for Reducer functions
- Add **Deprecated** section in `CHANGELOG.md`
- Add Redis pipeline support
- Prefix properties name with `@` in Aggregation's SortBy (keep BC)
- Add more unit tests (Aggregation options, Field creation)

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

- Wrong output of the NumericFacet ([Issue#1], [PR#2])
- Missing Composer plugin
- Missing `getSeperator` method in `TagField`
- Missing total number of result
- Fix typo in search parameter (`RETURN`)
- Fix prefixes data from index information

### Added

- Unit test on NumericFacet ([PR#2])
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

[Unreleased]: https://github.com/MacFJA/php-redisearch/compare/1.2.0...HEAD
[1.2.0]: https://github.com/MacFJA/php-redisearch/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/MacFJA/php-redisearch/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/MacFJA/php-redisearch/releases/tag/1.0.0

[Issue#1]: https://github.com/MacFJA/php-redisearch/issues/1
[PR#2]: https://github.com/MacFJA/php-redisearch/pulls/2