<?php

namespace Tests\MacFJA\RediSearch\Search\QueryBuilder;

use MacFJA\RediSearch\Search\QueryBuilder\NumericFacet;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Search\QueryBuilder\NumericFacet
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
        $this->assertSame('@num:[(10 +inf]', $facet->render());
    }

    /**
     * @covers ::greaterThanOrEquals
     */
    public function testGreaterThanOrEquals()
    {
        $facet = NumericFacet::greaterThanOrEquals('num', 10);
        $this->assertSame('@num:[10 +inf]', $facet->render());
    }

    /**
     * @covers ::lessThan
     */
    public function testLessThan()
    {
        $facet = NumericFacet::lessThan('num', 10);
        $this->assertSame('@num:[-inf (10]', $facet->render());
    }

    /**
     * @covers ::lessThanOrEquals
     */
    public function testLessThanOrEquals()
    {
        $facet = NumericFacet::lessThanOrEquals('num', 10);
        $this->assertSame('@num:[-inf 10]', $facet->render());
    }

    /**
     * @covers ::equalsTo
     */
    public function testEqualsTo()
    {
        $facet = NumericFacet::equalsTo('num', 10);
        $this->assertSame('@num:[10 10]', $facet->render());
    }
}
