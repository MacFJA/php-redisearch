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

namespace MacFJA\RediSearch\Aggregate;

use function count;
use MacFJA\RediSearch\Aggregate\Exception\NotEnoughPropertiesException;
use MacFJA\RediSearch\Aggregate\Exception\NotEnoughReducersException;
use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\RedisHelper;
use MacFJA\RediSearch\PartialQuery;
use Throwable;

class GroupBy implements PartialQuery
{
    /** @var array<string> */
    private $properties = [];

    /** @var array<Reducer> */
    private $reducers = [];

    /**
     * GroupBy constructor.
     *
     * @param array<string>  $properties
     * @param array<Reducer> $reducers
     *
     * @throws Throwable
     */
    public function __construct(array $properties, array $reducers)
    {
        DataHelper::assert(count($properties) > 0, NotEnoughPropertiesException::class);
        DataHelper::assert(count($reducers) > 0, NotEnoughReducersException::class);
        DataHelper::assertArrayOf($properties, 'string');
        DataHelper::assertArrayOf($reducers, Reducer::class);

        $this->properties = $properties;
        $this->reducers = $reducers;
    }

    public function getQueryParts(): array
    {
        $query = RedisHelper::buildQueryList([], ['GROUPBY' => $this->properties]);

        return RedisHelper::buildQueryPartial($query, $this->reducers);
    }
}
