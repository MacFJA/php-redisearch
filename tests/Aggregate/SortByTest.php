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

namespace Tests\MacFJA\RediSearch\Aggregate;

use MacFJA\RediSearch\Aggregate\Exception\UnknownSortDirectionException;
use MacFJA\RediSearch\Aggregate\SortBy;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Tests\MacFJA\RediSearch\support\Assertion;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Aggregate\SortBy
 * @covers ::__construct
 * @covers ::getQueryParts
 * @covers \MacFJA\RediSearch\Aggregate\Exception\UnknownSortDirectionException
 *
 * @uses \MacFJA\RediSearch\Helper\DataHelper
 * @uses \MacFJA\RediSearch\Helper\RedisHelper
 *
 * @see https://oss.redislabs.com/redisearch/Aggregations/#aggregate_request_format
 */
class SortByTest extends TestCase
{
    use Assertion;

    public function testNominal(): void
    {
        self::assertSameQuery(
            'SORTBY 4 @name ASC @age DESC MAX 20',
            new SortBy(['name' => SortBy::SORT_ASC, 'age' => SortBy::SORT_DESC], 20)
        );
        self::assertSameQuery(
            'SORTBY 2 @name ASC', new SortBy(['name' => SortBy::SORT_ASC]));
    }

    public function testNoProperties(): void
    {
        self::assertSameQuery('SORTBY 0', new SortBy([]));
        self::assertSameQuery('SORTBY 0 MAX 2', new SortBy([], 2));
        self::assertSameQuery('SORTBY 0 MAX 0', new SortBy([], 0));
    }

    public function testInvalidMax(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('MAX such by greater or equals to 0');
        new SortBy([], -1);
    }

    public function testInvalidSort(): void
    {
        $this->expectException(UnknownSortDirectionException::class);
        $this->expectExceptionMessage('Sort By direction can only be "ASC" or "DESC". Provided: UP, DOWN');
        new SortBy(['foo' => 'UP', 'bar' => 'DOWN']);
    }
}
