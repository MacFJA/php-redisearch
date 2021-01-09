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

use function array_diff;
use function array_keys;
use function array_merge;
use function array_unique;
use function count;
use MacFJA\RediSearch\Aggregate\Exception\UnknownSortDirectionException;
use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\RedisHelper;
use OutOfRangeException;
use Throwable;

class SortBy implements \MacFJA\RediSearch\PartialQuery
{
    public const SORT_ASC = 'ASC';

    public const SORT_DESC = 'DESC';

    /**
     * @psalm-var array<string,"ASC"|"DESC">
     * @phpstan-var array<string,"ASC"|"DESC">
     *
     * @var array<string,string>
     */
    private $properties = [];

    /** @var null|int */
    private $max;

    /**
     * SortBy constructor.
     *
     * @psalm-param array<string,"ASC"|"DESC"> $properties
     * @phpstab-param array<string,"ASC"|"DESC"> $properties
     *
     * @param array<string,string> $properties
     *
     * @throws Throwable
     */
    public function __construct(array $properties, ?int $max = null)
    {
        DataHelper::assertArrayOf(array_keys($properties), 'string');
        $allValues = array_unique(array_merge($properties, ['ASC', 'DESC']));
        DataHelper::assert(
            2 === count($allValues),
            new UnknownSortDirectionException(array_diff($allValues, ['ASC', 'DESC']))

        );
        DataHelper::assert(null === $max || $max > 0, new OutOfRangeException('MAX such by greater than 0'));
        $this->properties = $properties;
        $this->max = $max;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParts(): array
    {
        $query = ['SORTBY', count($this->properties) * 2];
        foreach ($this->properties as $property => $direction) {
            $query[] = $property;
            $query[] = $direction;
        }

        return RedisHelper::buildQueryNotNull($query, ['MAX' => $this->max]);
    }
}
