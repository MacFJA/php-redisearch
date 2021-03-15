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

use MacFJA\RediSearch\Aggregate\GroupBy;
use MacFJA\RediSearch\Aggregate\Reducer;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\MacFJA\RediSearch\support\Assertion;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Aggregate\GroupBy
 * @covers ::__construct
 * @covers ::getQueryParts
 *
 * @uses \MacFJA\RediSearch\Helper\DataHelper
 * @uses \MacFJA\RediSearch\Helper\RedisHelper
 * @uses \MacFJA\RediSearch\Aggregate\Reducer
 *
 * @see https://oss.redislabs.com/redisearch/Aggregations/#aggregate_request_format
 */
class GroupByTest extends TestCase
{
    use Assertion;

    public function testNominal(): void
    {
        self::assertSameQuery(
            'GROUPBY 1 @name REDUCE COUNT 0',
            new GroupBy(['name'], [Reducer::count()])
        );
        self::assertSameQuery(
            'GROUPBY 2 @name @age REDUCE COUNT 0 AS _count REDUCE AVG 1 @age AS _average',
            new GroupBy(['name', 'age'], [Reducer::count('_count'), Reducer::average('age', '_average')])
        );
    }

    public function testNoProperties(): void
    {
        self::assertSameQuery(
            'GROUPBY 0 REDUCE COUNT 0',
            new GroupBy([], [Reducer::count()])
        );
        self::assertSameQuery(
            'GROUPBY 0 REDUCE COUNT 0 AS _count REDUCE AVG 1 @age AS _average',
            new GroupBy([], [Reducer::count('_count'), Reducer::average('age', '_average')])
        );
    }

    public function testNoReducer(): void
    {
        self::assertSameQuery(
            'GROUPBY 1 @name',
            new GroupBy(['name'], [])
        );
        self::assertSameQuery(
            'GROUPBY 2 @name @age',
            new GroupBy(['name', 'age'], [])
        );
    }

    public function testInvalidPropertiesDataType(): void
    {
        $this->expectException(RuntimeException::class);
        new GroupBy([1, false], []);
    }

    public function testInvalidReducerDataType(): void
    {
        $this->expectException(RuntimeException::class);
        new GroupBy([], ['foo', 1, false]);
    }
}
