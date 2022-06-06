# Using result

As you can set an offset and limit on `FT.SEARCH` and `FT.AGGREGATE`, they are paginated commands.
The result of those commands is a page, that can contain one or more document.

---

So in this library, the result of `\MacFJA\RediSearch\Redis\Command\Search` and `\MacFJA\RediSearch\Redis\Command\Aggregate` is also a paginated object:
either a `\MacFJA\RediSearch\Redis\Response\PaginatedResponse` (for Search and Aggregate) or a `\MacFJA\RediSearch\Redis\Response\CursorResponse` (for Aggregate)

The 2 classes are representing a page.

Both `\MacFJA\RediSearch\Redis\Response\PaginatedResponse` and `\MacFJA\RediSearch\Redis\Response\CursorResponse` can be read inside a `foreach` loop or with any iterator function.
(As they implement the [`\Iterator` interface](https://www.php.net/manual/en/class.iterator.php))
What you are iterating over, are the pages (not the documents inside the page).

```php
// If your RediSearch search have 15 results in total, but you set the pagination to 10 per page:
/** @var PaginatedResponse $results */

/** @var array<SearchResponseItem> $items */
$items = $results->current();
// $items is a list of 10 SearchResponseItem

$results->next(); // To be able to class `->next()`, you need to call `->setClient()` first !

/** @var array<SearchResponseItem> $items */
$items = $results->current();
// $items is a list of 5 SearchResponseItem
```

## Get all items on all pages

```php
/** @var \MacFJA\RediSearch\Redis\Client $client */
/** @var \MacFJA\RediSearch\Redis\Command\Search $search */

/** @var \MacFJA\RediSearch\Redis\Response\PaginatedResponse $results */
$results = $client->execute($search);
$results->setClient($client);

$items = [];
foreach ($results as $pageIndex => $pageContent) {
    $items = array_merge($items, $pageContent);
}
/** @var \MacFJA\RediSearch\Redis\Response\SearchResponseItem $item */
foreach ($items as $item) {
    doSomething($item);
}
```
Be careful, this will load all items in the memory.
This will also call Redis multiple time if there are several pages.

---

If you need to avoid loading all items in the memory, you can use [`ResponseItemIterator`](https://github.com/MacFJA/php-redisearch-integration/blob/main/src/Iterator/ResponseItemIterator.php) from the sister project [macfja/redisearch-integration](https://github.com/MacFJA/php-redisearch-integration).
This class it a custom iterator that will load in memory only one page at the time.

```php
/** @var \MacFJA\RediSearch\Redis\Client $client */
/** @var \MacFJA\RediSearch\Redis\Command\Search $search */

$results = $client->execute($search);
$allItems = new \MacFJA\RediSearch\Integration\Iterator\ResponseItemIterator($results, $client);
/** @var \MacFJA\RediSearch\Redis\Response\SearchResponseItem $item */
foreach ($allItems as $item) {
    // Only one page exist in the memory
    doSomething($item);
}
```
