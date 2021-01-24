# PHP RediSearch

[MacFJA/redisearch](https://packagist.org/packages/macfja/redisearch) is a PHP Client for [RediSearch](https://oss.redislabs.com/redisearch/).

The implemented API is for RediSearch 2.0

## Installation

```
composer require macfja/redisearch
```

## Usage

### Create a new index

```php
$client = new \Predis\Client(/* ... */);
$builder = new \MacFJA\RedisSearch\Index\Builder($client);

// Field can be create in advance
$address = new \MacFJA\RedisSearch\Index\Builder\GeoField('address');

$builder
    ->withName('person')
    ->addField($address)
    // Or field can be create "inline"
    ->addTextField('lastname', false, null, null, true)
    ->addTextField('firstname')
    ->addNumericField('age')
    ->create();
```

### Add a document

```php
$client = new \Predis\Client(/* ... */);
$index = new \MacFJA\RedisSearch\Index('person', $client);
$index->addFromArray([
    'firstname' => 'Joe',
    'lastname' => 'Doe',
    'age' => 30,
    'address' => '40.689247,-74.044502'
]);
```

### Search

```php
$client = new \Predis\Client(/* ... */);
$search = new \MacFJA\RedisSearch\Search($client);

$results = $search
    ->withIndex('person')
    ->withQuery('Doe')
    ->withHighlight(['lastname'])
    ->withScores()
    ->search();
```

#### Create a search query

```php
use \MacFJA\RedisSearch\Search\QueryBuilder\NumericFacet;
use \MacFJA\RedisSearch\Search\QueryBuilder\Negation;
use \MacFJA\RedisSearch\Search\QueryBuilder\GeoFacet;
use \MacFJA\RedisSearch\Search\QueryBuilder\Optional;
use \MacFJA\RedisSearch\Search\QueryBuilder\Word;
use \MacFJA\RedisSearch\Search\GeoFilter;

$queryBuilder = \MacFJA\RedisSearch\Search\QueryBuilder::create();
$query = $queryBuilder
    ->addExpression(NumericFacet::greaterThan('age', 17))
    ->addString('Doe')
    ->addExpression(
        new Negation(
            new GeoFacet('address', 40.589247, -74.044502, 40, GeoFilter::UNIT_KILOMETERS)
        )
    )
    ->addExpression(new Optional(new Word('John')))
    ->render();

// The value of $query is:
// Doe ~John @age:[(17 +inf] -@address:[40.589247 -74.044502 40.000000 km]
```

## Similar projects

- [ethanhann/redisearch-php](https://packagist.org/packages/ethanhann/redisearch-php) - Abandoned
- [front/redisearch](https://packagist.org/packages/front/redisearch) - Partial fork of `ethanhann/redisearch-php`
- [ashokgit/redisearch-php](https://packagist.org/packages/ashokgit/redisearch-php) - Fork of `ethanhann/redisearch-php`

## Contributing

You can contribute to the library.
To do so, you have Github issues to:
 - ask your question
 - request any change (typo, bad code, new feature etc.)
 - and much more...

You also have PR to:
 - suggest a correction
 - suggest a new feature
 - and much more... 
 
See [CONTRIBUTING](CONTRIBUTING.md) for more information.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.