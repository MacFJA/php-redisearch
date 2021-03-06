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

namespace Tests\MacFJA\RediSearch\support;

use function implode;
use function is_subclass_of;
use MacFJA\RediSearch\PartialQuery;
use MacFJA\RediSearch\Search\QueryBuilder\PartialQuery as RenderablePartialQuery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

trait Assertion
{
    private static function assertSameQuery(string $expected, PartialQuery $partialQuery): void
    {
        if (!is_subclass_of(self::class, TestCase::class)) {
            throw new RuntimeException();
        }
        self::assertSame($expected, implode(' ', $partialQuery->getQueryParts()));
    }

    private static function assertSameRender(string $expected, RenderablePartialQuery $partialQuery): void
    {
        if (!is_subclass_of(self::class, TestCase::class)) {
            throw new RuntimeException();
        }
        self::assertSame($expected, $partialQuery->render());
    }
}
