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

namespace MacFJA\RediSearch\tests\Query\Builder;

use MacFJA\RediSearch\Exception\OutOfRangeLevenshteinDistanceException;
use MacFJA\RediSearch\Query\Builder\Fuzzy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Exception\OutOfRangeLevenshteinDistanceException
 * @covers \MacFJA\RediSearch\Query\Builder\Fuzzy
 *
 * @internal
 */
class FuzzyTest extends TestCase
{
    public function testTooLow(): void
    {
        $this->expectException(OutOfRangeLevenshteinDistanceException::class);
        $this->expectExceptionMessage('The Levenshtein distance should be between 1 and 3 (0 provided)');
        new Fuzzy('hello', 0);
    }

    public function testTooHigh(): void
    {
        $this->expectException(OutOfRangeLevenshteinDistanceException::class);
        $this->expectExceptionMessage('The Levenshtein distance should be between 1 and 3 (4 provided)');
        new Fuzzy('hello', 4);
    }

    public function testSpace(): void
    {
        $element = new Fuzzy('hello');
        static::assertFalse($element->includeSpace());

        $element = new Fuzzy('hello world');
        static::assertTrue($element->includeSpace());
    }
}
