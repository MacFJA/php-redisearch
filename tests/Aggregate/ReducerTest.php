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

use MacFJA\RediSearch\Aggregate\Reducer;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Tests\MacFJA\RediSearch\support\Assertion;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Aggregate\Reducer
 * @covers ::__construct
 * @covers ::getQueryParts
 *
 * @uses \MacFJA\RediSearch\Helper\DataHelper
 *
 * @see https://oss.redislabs.com/redisearch/Aggregations/#supported_groupby_reducers
 */
class ReducerTest extends TestCase
{
    use Assertion;

    public function testInvalidArgumentsDataType(): void
    {
        $this->expectException(RuntimeException::class);
        new Reducer('foo', [new stdClass()]);
    }

    /**
     * @covers ::count
     */
    public function testCount(): void
    {
        self::assertSameQuery('REDUCE COUNT 0', Reducer::count());
        self::assertSameQuery('REDUCE COUNT 0 AS countReducer', Reducer::count('countReducer'));
    }

    /**
     * @covers ::countDistinct
     */
    public function testCountDistinct(): void
    {
        self::assertSameQuery('REDUCE COUNT_DISTINCT 1 @foobar', Reducer::countDistinct('foobar'));
        self::assertSameQuery('REDUCE COUNT_DISTINCT 1 @foobar AS countReducer', Reducer::countDistinct('foobar', 'countReducer'));
    }

    /**
     * @covers ::countDistinctish
     */
    public function testCountDistinctish(): void
    {
        self::assertSameQuery('REDUCE COUNT_DISTINCTISH 1 @foobar', Reducer::countDistinctish('foobar'));
        self::assertSameQuery('REDUCE COUNT_DISTINCTISH 1 @foobar AS countReducer', Reducer::countDistinctish('foobar', 'countReducer'));
    }

    /**
     * @covers ::toList
     */
    public function testToList(): void
    {
        self::assertSameQuery('REDUCE TOLIST 1 @foobar', Reducer::toList('foobar'));
        self::assertSameQuery('REDUCE TOLIST 1 @foobar AS list', Reducer::toList('foobar', 'list'));
    }

    /**
     * @covers ::median
     * @covers ::quantile
     */
    public function testQuantile(): void
    {
        self::assertSameQuery('REDUCE QUANTILE 2 @foobar 0.25', Reducer::quantile('foobar', 0.25));
        self::assertSameQuery('REDUCE QUANTILE 2 @foobar 0.5 AS median', Reducer::quantile('foobar', 0.5, 'median'));
        self::assertSameQuery('REDUCE QUANTILE 2 @foobar 0.5 AS median', Reducer::median('foobar', 'median'));
    }

    /**
     * @covers ::average
     */
    public function testAverage(): void
    {
        self::assertSameQuery('REDUCE AVG 1 @foobar', Reducer::average('foobar'));
        self::assertSameQuery('REDUCE AVG 1 @foobar AS average', Reducer::average('foobar', 'average'));
    }

    /**
     * @covers ::maximum
     */
    public function testMaximum(): void
    {
        self::assertSameQuery('REDUCE MAX 1 @foobar', Reducer::maximum('foobar'));
        self::assertSameQuery('REDUCE MAX 1 @foobar AS maximum', Reducer::maximum('foobar', 'maximum'));
    }

    /**
     * @covers ::minimum
     */
    public function testMinimum(): void
    {
        self::assertSameQuery('REDUCE MIN 1 @foobar', Reducer::minimum('foobar'));
        self::assertSameQuery('REDUCE MIN 1 @foobar AS minimum', Reducer::minimum('foobar', 'minimum'));
    }

    /**
     * @covers ::sum
     */
    public function testSum(): void
    {
        self::assertSameQuery('REDUCE SUM 1 @foobar', Reducer::sum('foobar'));
        self::assertSameQuery('REDUCE SUM 1 @foobar AS sum', Reducer::sum('foobar', 'sum'));
    }

    /**
     * @covers ::firstValue
     */
    public function testFirstValue(): void
    {
        self::assertSameQuery('REDUCE FIRST_VALUE 1 @foobar', Reducer::firstValue('foobar'));
        self::assertSameQuery('REDUCE FIRST_VALUE 1 @foobar AS first', Reducer::firstValue('foobar', null, null, 'first'));
        self::assertSameQuery('REDUCE FIRST_VALUE 1 @foobar AS first', Reducer::firstValue('foobar', null, true, 'first'));
        self::assertSameQuery('REDUCE FIRST_VALUE 3 @foo BY @bar', Reducer::firstValue('foo', 'bar'));
        self::assertSameQuery('REDUCE FIRST_VALUE 4 @foo BY @bar ASC', Reducer::firstValue('foo', 'bar', true));
        self::assertSameQuery('REDUCE FIRST_VALUE 4 @foo BY @bar DESC', Reducer::firstValue('foo', 'bar', false));
        self::assertSameQuery('REDUCE FIRST_VALUE 4 @foo BY @bar DESC AS first', Reducer::firstValue('foo', 'bar', false, 'first'));
        self::assertSameQuery('REDUCE FIRST_VALUE 3 @foo BY @bar AS first', Reducer::firstValue('foo', 'bar', null, 'first'));
    }

    /**
     * @covers ::standardDeviation
     */
    public function testStandardDeviation(): void
    {
        self::assertSameQuery('REDUCE STDDEV 1 @foobar', Reducer::standardDeviation('foobar'));
        self::assertSameQuery('REDUCE STDDEV 1 @foobar AS deviation', Reducer::standardDeviation('foobar', 'deviation'));
    }
}
