# PHP RediSearch

[MacFJA/redisearch](https://packagist.org/packages/macfja/redisearch) is a PHP Client for [RediSearch](https://oss.redislabs.com/redisearch/).

The implemented API is for RediSearch 2.0

## Installation

```
composer require macfja/redisearch
```

## Usage

### Get a Redis client

This lib can use several connector for Redis:
 - [Predis](https://github.com/predis/predis/wiki) - Pure PHP implementation
 - [Phpredis](https://github.com/phpredis/phpredis) - PHP extension
 - [Phpiredis](https://github.com/nrk/phpiredis) - PHP extension depending on [hiredis](https://github.com/redis/hiredis)
 - [Amp\Redis](https://github.com/amphp/redis) - Pure PHP Async implementation
 - [cheprasov/php-redis-client](https://github.com/cheprasov/php-redis-client) - Pure PHP implementation
 - [Credis](https://github.com/colinmollenhour/credis) - Pure PHP implementation
 - [Rediska](https://github.com/Shumkov/Rediska) - Pure PHP implementation
 - [Redisent](https://github.com/jdp/redisent) - Pure PHP implementation
 - [TinyRedis](https://github.com/ptrofimov/tinyredisclient) - Pure PHP implementation

You can pick the connector depending of your need.

```php
$clientFacade = new \MacFJA\RediSearch\Redis\Client\ClientFacade();

// With Predis
$client = $clientFacade->getClient(new \Predis\Client(/* ... */));

// With Phpredis extension
$client = $clientFacade->getClient(new \Redis([/* ... */]));

// With Phpiredis extension
$client = $clientFacade->getClient(phpiredis_connect($host));

// With Amp\Redis
$client = $clientFacade->getClient(new \Amp\Redis\Redis(new RemoteExecutor(Config::fromUri(/* ... */))));

// With Cheprasov
$client = $clientFacade->getClient(new \RedisClient\Client\Version\RedisClient6x0([/* ... */]));

// With Rediska
$client = $clientFacade->getClient(new \Rediska(['servers' => [[/* ... */]]]));

// With Redisent
$client = $clientFacade->getClient(new \redisent\Redis(/* ... */));

// With TinyRedisClient
$client = $clientFacade->getClient(new \TinyRedisClient(/* ... */));

// With Credis
$client = $clientFacade->getClient(new \Credis_Client(/* ... */));
```

You can add your own implementation, all you need is to implement the interface `\MacFJA\RediSearch\Redis\Client` and add it to the client facace with:
```php
$clientFacade = new \MacFJA\RediSearch\Redis\Client\ClientFacade();
$clientFacade->addFactory(\MyVendor\MyPackage\MyRedisClient::class);
```

### Create a new index

```php
$client = /* ... */;
$builder = new \MacFJA\RediSearch\IndexBuilder();

// Field can be create in advance
$address = (new \MacFJA\RediSearch\Redis\Command\CreateCommand\GeoFieldOption())
    ->setField('address');

$builder
    ->setIndex('person')
    ->addField($address)
    // Or field can be create "inline"
    ->addTextField('lastname', false, null, null, true)
    ->addTextField('firstname')
    ->addNumericField('age')
    ->create($client);
```

The builder can also be used with `withXxx` and `withAddedXxx` instead of `setXxx` and `addXxx`.
This will give you a new instance of the builder with the configured data.

### Add a document

```php
$client = /* ... */;
$index = new \MacFJA\RediSearch\Index('person', $client);
$index->addDocumentFromArray([
    'firstname' => 'Joe',
    'lastname' => 'Doe',
    'age' => 30,
    'address' => '40.689247,-74.044502'
]);
```

### Search

```php
$client = /* ... */;
$search = new \MacFJA\RediSearch\Redis\Command\Search();

$search
    ->setIndex('person')
    ->setQuery('Doe')
    ->setHighlight(['lastname'])
    ->setWithScores();
$results = $client->execute($search);
```

#### Create a search query

```php
use MacFJA\RediSearch\Query\Builder\GeoFacet;
use MacFJA\RediSearch\Query\Builder\Negation;
use MacFJA\RediSearch\Query\Builder\NumericFacet;
use MacFJA\RediSearch\Query\Builder\Optional;
use MacFJA\RediSearch\Query\Builder\Word;
use MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption;

$queryBuilder = new \MacFJA\RediSearch\Query\Builder();
$query = $queryBuilder
    ->addElement(NumericFacet::greaterThan('age', 17))
    ->addString('Doe')
    ->addElement(
        new Negation(
            new GeoFacet(['address'], 40.589247, -74.044502, 40, GeoFilterOption::UNIT_KILOMETERS)
        )
    )
    ->addElement(new Optional(new Word('John')))
    ->render();

// The value of $query is:
// @age:[(17 +inf] Doe -@address:[40.589247 -74.044502 40.000000 km] ~John
```

### Pipeline

```php
use MacFJA\RediSearch\Redis\Command\Aggregate;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\GroupByOption;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\ReduceOption;
use MacFJA\RediSearch\Redis\Command\Search;
use MacFJA\RediSearch\Redis\Command\SugGet;

$client = /* ... */;

$query = '@age:[(17 +inf] %john%';
$search = new Search();
$search->setIndex('people')
    ->setQuery($query);

$stats = new Aggregate();
$stats->setIndex('people')
    ->setQuery($query)
    ->addGroupBy(new GroupByOption([], [
        ReduceOption::average('age', 'avg'),
        ReduceOption::maximum('age', 'oldest')
    ]));

$aggregate = new Aggregate();
$aggregate->setIndex('people')
    ->setQuery($query)
    ->addGroupBy(new GroupByOption(['lastname'], [ReduceOption::count('count')]));

$suggestion = new SugGet();
$suggestion->setDictionary('names')
    ->setPrefix('john')
    ->setFuzzy();

$result = $client->pipeline($search, $stats, $aggregate, $suggestion);

// $result[0] is the search result
// $result[1] is the first aggregation result
// $result[2] is the second aggregation result
// $result[3] is the suggestion result
```

### Use Predis shorthand syntax

```php
$client = new \Predis\Client(/* ... */);
\MacFJA\RediSearch\Redis\Initializer::registerCommands($client->getProfile());

$client->ftsearch('people', '@age:[(17 +inf] %john%');
// But you will have raw Redis output.
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