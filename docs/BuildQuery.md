# Building a Query

To help create RediSearch, the library offer a builder.

It all start with the class `\MacFJA\RediSearch\Query\Builder`. From there you can add groups and query elements.

---

For example, for search all people named `John`, and preferably with the last name `Doe` that work as an accountant in the 10 km around Washington DC, and the resume contain text `cake` the request will be:
```php
use MacFJA\RediSearch\Query\Builder;
use MacFJA\RediSearch\Query\Builder\GeoFacet;
use MacFJA\RediSearch\Query\Builder\TextFacet;
use MacFJA\RediSearch\Query\Builder\Optional;
use MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption;

$queryBuilder = new Builder();
$query = $queryBuilder
    ->addTextFacet('firstname', 'John') // people named `John`
    ->addElement(new Optional(new TextFacet(['lastname'], 'Doe'))) // preferably with the last name `Doe`
    ->addTextFacet('job', 'accountant') // work as an accountant
    ->addGeoFacet('address', -77.0365427, 38.8950368, 10, GeoFilterOption::UNIT_KILOMETERS) // in the 10 km around Washington DC
    ->addString('cake') // the resume contain text `cake`
    ->render();

// @firstname:John ~@lastname:Doe @job:accountant @address:[-77.0365427 38.8950368 10 km] cake
```
