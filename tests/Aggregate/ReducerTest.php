<?php

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

/**
 * @covers \MacFJA\RediSearch\Aggregate\Reducer::getQueryParts()
 * @covers \MacFJA\RediSearch\Aggregate\Reducer::__construct()
 * @uses \MacFJA\RediSearch\Helper\DataHelper
 */
class ReducerTest extends TestCase
{
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::count
     */
    public function testCount(): void
    {
        self::assertReducerQuery('REDUCE COUNT 0', Reducer::count());
        self::assertReducerQuery('REDUCE COUNT 0 AS countReducer', Reducer::count('countReducer'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::countDistinct
     */
    public function testCountDistinct(): void
    {
        self::assertReducerQuery('REDUCE COUNT_DISTINCT 1 @foobar', Reducer::countDistinct('foobar'));
        self::assertReducerQuery('REDUCE COUNT_DISTINCT 1 @foobar AS countReducer', Reducer::countDistinct('foobar', 'countReducer'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::countDistinctish
     */
    public function testCountDistinctish(): void
    {
        self::assertReducerQuery('REDUCE COUNT_DISTINCTISH 1 @foobar', Reducer::countDistinctish('foobar'));
        self::assertReducerQuery('REDUCE COUNT_DISTINCTISH 1 @foobar AS countReducer', Reducer::countDistinctish('foobar', 'countReducer'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::toList
     */
    public function testToList(): void
    {
        self::assertReducerQuery('REDUCE TOLIST 1 @foobar', Reducer::toList('foobar'));
        self::assertReducerQuery('REDUCE TOLIST 1 @foobar AS list', Reducer::toList('foobar', 'list'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::quantile
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::median
     */
    public function testQuantile(): void
    {
        self::assertReducerQuery('REDUCE QUANTILE 2 @foobar 0.25', Reducer::quantile('foobar', 0.25));
        self::assertReducerQuery('REDUCE QUANTILE 2 @foobar 0.5 AS median', Reducer::quantile('foobar', 0.5, 'median'));
        self::assertReducerQuery('REDUCE QUANTILE 2 @foobar 0.5 AS median', Reducer::median('foobar', 'median'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::average
     */
    public function testAverage(): void
    {
        self::assertReducerQuery('REDUCE AVG 1 @foobar', Reducer::average('foobar'));
        self::assertReducerQuery('REDUCE AVG 1 @foobar AS average', Reducer::average('foobar', 'average'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::maximum
     */
    public function testMaximum(): void
    {
        self::assertReducerQuery('REDUCE MAX 1 @foobar', Reducer::maximum('foobar'));
        self::assertReducerQuery('REDUCE MAX 1 @foobar AS maximum', Reducer::maximum('foobar', 'maximum'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::minimum
     */
    public function testMinimum(): void
    {
        self::assertReducerQuery('REDUCE MIN 1 @foobar', Reducer::minimum('foobar'));
        self::assertReducerQuery('REDUCE MIN 1 @foobar AS minimum', Reducer::minimum('foobar', 'minimum'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::sum
     */
    public function testSum(): void
    {
        self::assertReducerQuery('REDUCE SUM 1 @foobar', Reducer::sum('foobar'));
        self::assertReducerQuery('REDUCE SUM 1 @foobar AS sum', Reducer::sum('foobar', 'sum'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::firstValue
     */
    public function testFirstValue(): void
    {
        self::assertReducerQuery('REDUCE FIRST_VALUE 1 @foobar', Reducer::firstValue('foobar'));
        self::assertReducerQuery('REDUCE FIRST_VALUE 1 @foobar AS first', Reducer::firstValue('foobar', null, null, 'first'));
        self::assertReducerQuery('REDUCE FIRST_VALUE 1 @foobar AS first', Reducer::firstValue('foobar', null, true, 'first'));
        self::assertReducerQuery('REDUCE FIRST_VALUE 3 @foo BY @bar', Reducer::firstValue('foo', 'bar'));
        self::assertReducerQuery('REDUCE FIRST_VALUE 4 @foo BY @bar ASC', Reducer::firstValue('foo', 'bar', true));
        self::assertReducerQuery('REDUCE FIRST_VALUE 4 @foo BY @bar DESC', Reducer::firstValue('foo', 'bar', false));
        self::assertReducerQuery('REDUCE FIRST_VALUE 4 @foo BY @bar DESC AS first', Reducer::firstValue('foo', 'bar', false, 'first'));
        self::assertReducerQuery('REDUCE FIRST_VALUE 3 @foo BY @bar AS first', Reducer::firstValue('foo', 'bar', null, 'first'));
    }
    /**
     * @covers \MacFJA\RediSearch\Aggregate\Reducer::standardDeviation
     */
    public function testStandardDeviation(): void
    {
        self::assertReducerQuery('REDUCE STDDEV 1 @foobar', Reducer::standardDeviation('foobar'));
        self::assertReducerQuery('REDUCE STDDEV 1 @foobar AS deviation', Reducer::standardDeviation('foobar', 'deviation'));
    }

    private static function assertReducerQuery(string $expected, Reducer $reducer): void
    {
        self::assertSame($expected, implode(' ', $reducer->getQueryParts()));
    }
}