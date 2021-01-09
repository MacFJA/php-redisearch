<?php

declare(strict_types=1);

/*
 * Copyright MacFJA
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MacFJA\RediSearch;

use function array_keys;
use function array_map;
use function array_merge;
use function array_shift;
use function assert;
use function is_int;
use function is_string;
use MacFJA\RediSearch\Aggregate\GroupBy;
use MacFJA\RediSearch\Aggregate\Reducer;
use MacFJA\RediSearch\Aggregate\Result;
use MacFJA\RediSearch\Aggregate\SortBy;
use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\RedisHelper;
use Predis\Client;
use Throwable;

class Aggregate implements Builder
{
    /** @var Client */
    private $redis;

    /** @var null|string */
    private $indexName;

    /** @var null|string */
    private $query;

    /** @var bool */
    private $verbatim = false;

    /** @var array<string> */
    private $load = [];

    /** @var array<GroupBy> */
    private $groupBy = [];

    /** @var null|SortBy */
    private $sortBy;

    /** @var array<string,string> */
    private $applies = [];

    /** @var null|int */
    private $resultOffset;

    /** @var null|int */
    private $resultLimit;

    /** @var null|string */
    private $filter;

    /**
     * Aggregate constructor.
     */
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function reset(): Builder
    {
        $this->indexName = null;
        $this->query = null;
        $this->verbatim = false;
        $this->load = [];
        $this->groupBy = [];
        $this->sortBy = null;
        $this->applies = [];
        $this->resultOffset = null;
        $this->resultLimit = null;
        $this->filter = null;

        return $this;
    }

    public function withIndexName(string $indexName): Aggregate
    {
        $this->indexName = $indexName;

        return $this;
    }

    public function withQuery(string $query): Aggregate
    {
        $this->query = $query;

        return $this;
    }

    public function withVerbatim(bool $verbatim = true): Aggregate
    {
        $this->verbatim = $verbatim;

        return $this;
    }

    /**
     * @param array<string> $load
     *
     * @throws Throwable
     *
     * @return $this
     */
    public function withLoad(array $load): Aggregate
    {
        DataHelper::assertArrayOf($load, 'string');
        $this->load = $load;

        return $this;
    }

    /**
     * @param array<GroupBy> $groupBy
     *
     * @throws Throwable
     *
     * @return $this
     */
    public function withGroupBy(array $groupBy): Aggregate
    {
        DataHelper::assertArrayOf($groupBy, GroupBy::class);
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @param array<string>  $properties
     * @param array<Reducer> $reducers
     *
     * @throws Throwable
     *
     * @return $this
     */
    public function addGroupBy(array $properties, array $reducers): self
    {
        $this->groupBy[] = new GroupBy($properties, $reducers);

        return $this;
    }

    /**
     * @param array<string,string> $properties
     * @psalm-param array<string,"ASC"|"DESC"> $properties
     * @phpstan-param array<string,"ASC"|"DESC"> $properties
     *
     * @throws Throwable
     *
     * @return $this
     */
    public function withSortBy(array $properties, ?int $max): Aggregate
    {
        $this->sortBy = new SortBy($properties, $max);

        return $this;
    }

    /**
     * @param array<string,string> $applies
     *
     * @throws Throwable
     *
     * @return $this
     */
    public function withApplies(array $applies): Aggregate
    {
        DataHelper::assertArrayOf(array_keys($applies), 'string');
        DataHelper::assertArrayOf($applies, 'string');
        $this->applies = $applies;

        return $this;
    }

    public function withResultOffset(int $resultOffset): Aggregate
    {
        $this->resultOffset = $resultOffset;

        return $this;
    }

    public function withResultLimit(int $resultLimit): Aggregate
    {
        $this->resultLimit = $resultLimit;

        return $this;
    }

    public function withFilter(string $filter): Aggregate
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return array<Result>
     */
    public function aggregate(): array
    {
        $rawResult = $this->redis->executeRaw($this->buildQuery());

        array_shift($rawResult);

        $results = array_map(function (array $document) {
            return new Result(RedisHelper::getPairs($document));
        }, $rawResult);

        $this->reset();

        return $results;
    }

    /**
     * @return array<float|int|string>
     */
    private function buildQuery(): array
    {
        assert(is_string($this->indexName));
        assert(is_string($this->query));
        $query = ['FT.AGGREGATE', $this->indexName, $this->query];
        $query = RedisHelper::buildQueryBoolean($query, ['VERBATIM' => $this->verbatim]);
        $query = RedisHelper::buildQueryList($query, ['LOAD' => $this->load]);
        $query = RedisHelper::buildQueryNotNull($query, ['FILTER' => $this->filter]);
        $query = RedisHelper::buildQueryPartial($query, array_merge([$this->sortBy], $this->groupBy));
        foreach ($this->applies as $expression => $alias) {
            $query = array_merge($query, ['APPLY', $expression, 'AS', $alias]);
        }
        if (is_int($this->resultOffset) && is_int($this->resultLimit)) {
            $query = array_merge($query, ['LIMIT', $this->resultOffset, $this->resultLimit]);
        }

        return $query;
    }
}
