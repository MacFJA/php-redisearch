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

use MacFJA\RediSearch\Search\Exception\UnknownUnitException;
use MacFJA\RediSearch\Search\GeoFilter;
use MacFJA\RediSearch\Search\QueryBuilder\GeoFacet;
use MacFJA\RediSearch\Search\QueryBuilder\PartialQuery;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use PHPUnit\Framework\TestCase;
use Tests\MacFJA\RediSearch\support\Assertion;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Search\QueryBuilder\GeoFacet
 * @covers ::__construct
 *
 * @uses \MacFJA\RediSearch\Helper\EscapeHelper
 * @uses \MacFJA\RediSearch\Helper\DataHelper
 * @uses \MacFJA\RediSearch\Search\QueryBuilder\OrGroup
 * @uses \MacFJA\RediSearch\Search\QueryBuilder\Word
 * @uses \MacFJA\RediSearch\Search\Exception\UnknownUnitException
 */
class GeoFacetTest extends TestCase
{
    use Assertion;

    /**
     * @covers ::render
     */
    public function testNominal(): void
    {
        self::assertSameRender('@foobar:[-74.044502 40.689247 40.000000 km]', new GeoFacet('foobar', -74.044502, 40.689247, 40, GeoFilter::UNIT_KILOMETERS));
    }

    public function testValidUnits(): void
    {
        $this->expectNotToPerformAssertions();
        new GeoFacet('foobar', -74.044502, 40.689247, 40, GeoFilter::UNIT_KILOMETERS);
        new GeoFacet('foobar', -74.044502, 40.689247, 40, GeoFilter::UNIT_FEET);
        new GeoFacet('foobar', -74.044502, 40.689247, 40, GeoFilter::UNIT_METERS);
        new GeoFacet('foobar', -74.044502, 40.689247, 40, GeoFilter::UNIT_MILES);
    }

    /**
     * @covers \MacFJA\RediSearch\Search\Exception\UnknownUnitException
     */
    public function testUnknownUnit(): void
    {
        $this->expectException(UnknownUnitException::class);
        $this->expectExceptionMessage('The unit "bar" is not valid.');

        new GeoFacet('foo', 0, 0, 0, 'bar');
    }

    /**
     * @covers ::includeSpace
     */
    public function testIncludeSpace(): void
    {
        assertFalse((new GeoFacet('foo', 0, 0, 0, 'm'))->includeSpace());
    }

    /**
     * @covers ::priority
     */
    public function testPriority(): void
    {
        assertSame(PartialQuery::PRIORITY_NORMAL, (new GeoFacet('foo', 0, 0, 0, 'm'))->priority());
    }
}
