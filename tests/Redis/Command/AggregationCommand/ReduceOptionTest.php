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

namespace MacFJA\RediSearch\tests\Redis\Command\AggregationCommand;

use MacFJA\RediSearch\Redis\Command\AggregateCommand\ReduceOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\AggregateCommand\ReduceOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 *
 * @internal
 */
class ReduceOptionTest extends TestCase
{
    public function testShorthands(): void
    {
        static::assertSame(['REDUCE', 'COUNT', 0, 'AS', 'count'], ReduceOption::count('count')->render());
        static::assertSame(['REDUCE', 'COUNT_DISTINCT', 1, '@name', 'AS', 'distinct'], ReduceOption::countDistinct('name', 'distinct')->render());
        static::assertSame(['REDUCE', 'COUNT_DISTINCTISH', 1, '@city', 'AS', 'cities_count'], ReduceOption::countDistinctish('city', 'cities_count')->render());
        static::assertSame(['REDUCE', 'SUM', 1, '@delta', 'AS', 'total'], ReduceOption::sum('delta', 'total')->render());
        static::assertSame(['REDUCE', 'MIN', 1, '@distance', 'AS', 'shortest'], ReduceOption::minimum('distance', 'shortest')->render());
        static::assertSame(['REDUCE', 'MAX', 1, '@distance', 'AS', 'longest'], ReduceOption::maximum('distance', 'longest')->render());
        static::assertSame(['REDUCE', 'AVG', 1, '@age', 'AS', 'avg'], ReduceOption::average('age', 'avg')->render());
        static::assertSame(['REDUCE', 'STDDEV', 1, '@fare', 'AS', 'deviation'], ReduceOption::standardDeviation('fare', 'deviation')->render());
        static::assertSame(['REDUCE', 'QUANTILE', 2, '@num', 0.25, 'AS', 'quarter'], ReduceOption::quantile('num', 0.25, 'quarter')->render());
        static::assertSame(['REDUCE', 'QUANTILE', 2, '@num', 0.5, 'AS', 'median'], ReduceOption::median('num', 'median')->render());
        static::assertSame(['REDUCE', 'TOLIST', 1, '@year', 'AS', 'years'], ReduceOption::toList('year', 'years')->render());
        static::assertSame(['REDUCE', 'FIRST_VALUE', 4, '@name', 'BY', '@age', 'DESC', 'AS', 'n'], ReduceOption::firstValue('name', 'age', false, 'n')->render());
    }

    public function testGetOptionData(): void
    {
        static::assertSame([
            'function' => 'FIRST_VALUE',
            'arguments' => ['@name', 'BY', '@age', 'DESC'],
            'alias' => 'n',
        ], ReduceOption::firstValue('name', 'age', false, 'n')->getOptionData());
    }
}
