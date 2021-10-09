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

use MacFJA\RediSearch\Exception\NotEnoughFieldsException;
use MacFJA\RediSearch\Exception\NotEnoughTermsException;
use MacFJA\RediSearch\Query\Builder\FieldFacet;
use MacFJA\RediSearch\Query\Builder\GeoFacet;
use MacFJA\RediSearch\Query\Builder\NumericFacet;
use MacFJA\RediSearch\Query\Builder\RawElement;
use MacFJA\RediSearch\Query\Builder\TagFacet;
use MacFJA\RediSearch\Query\Builder\TextFacet;
use MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Exception\NotEnoughFieldsException
 * @covers \MacFJA\RediSearch\Exception\NotEnoughTermsException
 *
 * @covers \MacFJA\RediSearch\Query\Builder\FieldFacet
 * @covers \MacFJA\RediSearch\Query\Builder\GeoFacet
 * @covers \MacFJA\RediSearch\Query\Builder\NumericFacet
 * @covers \MacFJA\RediSearch\Query\Builder\TagFacet
 * @covers \MacFJA\RediSearch\Query\Builder\TextFacet
 *
 * @uses  \MacFJA\RediSearch\Query\Builder\RawElement
 *
 * @internal
 */
class IncompleteFacetTest extends TestCase
{
    public function testTagFacetMissingTerms(): void
    {
        $this->expectException(NotEnoughTermsException::class);
        $this->expectExceptionMessage('You must have at least one term');

        new TagFacet(['text']);
    }

    public function testTagFacetMissingFields(): void
    {
        $this->expectException(NotEnoughFieldsException::class);
        $this->expectExceptionMessage('You must have at least one field');

        new TagFacet([], 'foo');
    }

    public function testTextFacetMissingTerms(): void
    {
        $this->expectException(NotEnoughTermsException::class);
        $this->expectExceptionMessage('You must have at least one term');

        new TextFacet(['text']);
    }

    public function testTextFacetMissingFields(): void
    {
        $this->expectException(NotEnoughFieldsException::class);
        $this->expectExceptionMessage('You must have at least one field');

        new TextFacet([], 'foo');
    }

    public function testNumericFacetMissingFields(): void
    {
        $this->expectException(NotEnoughFieldsException::class);
        $this->expectExceptionMessage('You must have at least one field');

        new NumericFacet([], 0, null);
    }

    public function testGeoFacetMissingFields(): void
    {
        $this->expectException(NotEnoughFieldsException::class);
        $this->expectExceptionMessage('You must have at least one field');

        new GeoFacet([], 0, 0, 5, GeoFilterOption::UNIT_METERS);
    }

    public function testFieldFacetMissingFields(): void
    {
        $this->expectException(NotEnoughFieldsException::class);
        $this->expectExceptionMessage('You must have at least one field');

        new FieldFacet([], new RawElement(''));
    }
}
