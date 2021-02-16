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
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Search\QueryBuilder\NumericFacet
 * @covers ::render
 *
 * @uses \MacFJA\RediSearch\Helper\EscapeHelper
 * @uses \MacFJA\RediSearch\Search\QueryBuilder\NumericFacet
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
    /**
     * @covers ::greaterThan
     */
    public function testGreaterThan()
    {
        $facet = NumericFacet::greaterThan('num', 10);
        self::assertSame('@num:[(10 +inf]', $facet->render());
    }

    /**
     * @covers ::greaterThanOrEquals
     */
    public function testGreaterThanOrEquals()
    {
        $facet = NumericFacet::greaterThanOrEquals('num', 10);
        self::assertSame('@num:[10 +inf]', $facet->render());
    }

    /**
     * @covers ::lessThan
     */
    public function testLessThan()
    {
        $facet = NumericFacet::lessThan('num', 10);
        self::assertSame('@num:[-inf (10]', $facet->render());
    }

    /**
     * @covers ::lessThanOrEquals
     */
    public function testLessThanOrEquals()
    {
        $facet = NumericFacet::lessThanOrEquals('num', 10);
        self::assertSame('@num:[-inf 10]', $facet->render());
    }

    /**
     * @covers ::equalsTo
     */
    public function testEqualsTo()
    {
        $facet = NumericFacet::equalsTo('num', 10);
        self::assertSame('@num:[10 10]', $facet->render());
    }
}
