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

namespace MacFJA\RediSearch\tests\Redis\Command\SearchCommand;

use MacFJA\RediSearch\Redis\Command\SearchCommand\FilterOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\SearchCommand\FilterOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 *
 * @internal
 */
class FilterOptionTest extends TestCase
{
    public function testShorthands(): void
    {
        static::assertSame(['FILTER', 'num1', '(100', '+inf'], FilterOption::greaterThan('num1', 100)->render());
        static::assertSame(['FILTER', 'num1', '100', '100'], FilterOption::equalsTo('num1', 100)->render());
        static::assertSame(['FILTER', 'num1', '100', '+inf'], FilterOption::greaterThanOrEquals('num1', 100)->render());
        static::assertSame(['FILTER', 'num1', '-inf', '(100'], FilterOption::lessThan('num1', 100)->render());
        static::assertSame(['FILTER', 'num1', '-inf', '100'], FilterOption::lessThanOrEquals('num1', 100)->render());
    }

    public function testGetOptionData(): void
    {
        static::assertSame([
            'min' => 100.0,
            'max' => null,
            'isMinInclusive' => false,
            'isMaxInclusive' => true,
        ], FilterOption::greaterThan('num1', 100)->getOptionData());
    }
}
