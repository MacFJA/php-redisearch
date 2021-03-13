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

namespace Tests\MacFJA\RediSearch\Index\Builder;

use MacFJA\RediSearch\Index\Builder\NumericField;
use PHPUnit\Framework\TestCase;
use Tests\MacFJA\RediSearch\support\Assertion;

/**
 * @covers \MacFJA\RediSearch\Index\Builder\NumericField
 *
 * @uses \MacFJA\RediSearch\Index\Builder\AbstractField
 * @uses \MacFJA\RediSearch\Helper\RedisHelper
 */
class NumericFieldTest extends TestCase
{
    use Assertion;

    public function testNominal(): void
    {
        self::assertSameQuery('age NUMERIC SORTABLE', new NumericField('age', true));
        self::assertSameQuery('weight NUMERIC NOINDEX', new NumericField('weight', false, true));
        self::assertSameQuery('speed NUMERIC SORTABLE NOINDEX', new NumericField('speed', true, true));
    }

    public function testGetType(): void
    {
        self::assertSame('NUMERIC', (new NumericField('foobar'))->getType());
    }
}
