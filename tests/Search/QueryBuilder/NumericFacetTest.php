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

use MacFJA\RediSearch\Search\QueryBuilder\NumericFacet;
use MacFJA\RediSearch\Search\QueryBuilder\PartialQuery;
use PHPUnit\Framework\TestCase;
use Tests\MacFJA\RediSearch\support\Assertion;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Search\QueryBuilder\NumericFacet
 * @covers ::__construct
 * @covers ::render
 *
 * @uses \MacFJA\RediSearch\Helper\EscapeHelper
 *
 * @see: https://oss.redislabs.com/redisearch/Query_Syntax/#mapping_common_sql_predicates_to_redisearch
 * WHERE num BETWEEN 10 AND 20	    @num:[10 20]
 * WHERE num >= 10	                @num:[10 +inf]
 * WHERE num > 10	                @num:[(10 +inf]
 * WHERE num < 10	                @num:[-inf (10]
 * WHERE num <= 10	                @num:[-inf 10]
 * WHERE num < 10 OR num > 20	    @num:[-inf (10] | @num:[(20 +inf]
 */
class NumericFacetTest extends TestCase
{
    use Assertion;

    public function testNominal(): void
    {
        self::assertSameRender('@foo:[0 10]', new NumericFacet('foo', 0, 10));
        self::assertSameRender('@foo:[0 10]', new NumericFacet('foo', 0, 10, true, true));
        self::assertSameRender('@foo:[(0 10]', new NumericFacet('foo', 0, 10, false));
        self::assertSameRender('@foo:[0 (10]', new NumericFacet('foo', 0, 10, true, false));
        self::assertSameRender('@foo:[(0 (10]', new NumericFacet('foo', 0, 10, false, false));
    }

    /**
     * @covers ::greaterThan
     */
    public function testGreaterThan()
    {
        $facet = NumericFacet::greaterThan('num', 10);
        self::assertSameRender('@num:[(10 +inf]', $facet);
    }

    /**
     * @covers ::greaterThanOrEquals
     */
    public function testGreaterThanOrEquals()
    {
        $facet = NumericFacet::greaterThanOrEquals('num', 10);
        self::assertSameRender('@num:[10 +inf]', $facet);
    }

    /**
     * @covers ::lessThan
     */
    public function testLessThan()
    {
        $facet = NumericFacet::lessThan('num', 10);
        self::assertSameRender('@num:[-inf (10]', $facet);
    }

    /**
     * @covers ::lessThanOrEquals
     */
    public function testLessThanOrEquals()
    {
        $facet = NumericFacet::lessThanOrEquals('num', 10);
        self::assertSameRender('@num:[-inf 10]', $facet);
    }

    /**
     * @covers ::equalsTo
     */
    public function testEqualsTo()
    {
        $facet = NumericFacet::equalsTo('num', 10);
        self::assertSameRender('@num:[10 10]', $facet);
    }

    /**
     * @covers ::includeSpace
     */
    public function testIncludeSpace(): void
    {
        self::assertFalse((new NumericFacet('foobar', null, null))->includeSpace());
    }

    /**
     * @covers ::priority
     */
    public function testPriority(): void
    {
        self::assertSame(PartialQuery::PRIORITY_NORMAL, (new NumericFacet('foobar', null, null))->priority());
    }
}
