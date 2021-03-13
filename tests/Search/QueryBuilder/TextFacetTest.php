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

namespace Tests\MacFJA\RediSearch\Search\QueryBuilder;

use MacFJA\RediSearch\Search\Exception\NotEnoughTermsException;
use MacFJA\RediSearch\Search\QueryBuilder\PartialQuery;
use MacFJA\RediSearch\Search\QueryBuilder\TextFacet;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use PHPUnit\Framework\TestCase;
use Tests\MacFJA\RediSearch\support\Assertion;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Search\QueryBuilder\TextFacet
 * @covers ::__construct
 *
 * @uses \MacFJA\RediSearch\Helper\EscapeHelper
 * @uses \MacFJA\RediSearch\Helper\DataHelper
 * @uses \MacFJA\RediSearch\Search\QueryBuilder\OrGroup
 * @uses \MacFJA\RediSearch\Search\QueryBuilder\Word
 */
class TextFacetTest extends TestCase
{
    use Assertion;

    /**
     * @covers ::render
     */
    public function testNominal(): void
    {
        self::assertSameRender('@foobar:hello', new TextFacet('foobar', 'hello'));
        self::assertSameRender('@foobar:(hello|world)', new TextFacet('foobar', 'hello', 'world'));
        self::assertSameRender('@foobar:(hello world)', new TextFacet('foobar', 'hello world'));
        self::assertSameRender('@foobar:hello\|world', new TextFacet('foobar', 'hello|world'));
    }

    /**
     * @covers \MacFJA\RediSearch\Search\Exception\NotEnoughTermsException
     *
     * @uses \MacFJA\RediSearch\Words\Exception\NotEnoughTermsException
     */
    public function testNotEnoughValues(): void
    {
        $this->expectException(NotEnoughTermsException::class);
        $this->expectExceptionMessage('You must have at least one term');

        new TextFacet('foobar');
    }

    /**
     * @covers ::includeSpace
     */
    public function testIncludeSpace(): void
    {
        assertFalse((new TextFacet('foo', 'bar'))->includeSpace());
    }

    /**
     * @covers ::priority
     */
    public function testPriority(): void
    {
        assertSame(PartialQuery::PRIORITY_NORMAL, (new TextFacet('foo', 'bar'))->priority());
    }
}
