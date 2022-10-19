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

use MacFJA\RediSearch\Query\Builder;
use MacFJA\RediSearch\Query\Builder\AndGroup;
use MacFJA\RediSearch\Query\Builder\EncapsulationGroup;
use MacFJA\RediSearch\Query\Builder\ExactMatch;
use MacFJA\RediSearch\Query\Builder\FieldFacet;
use MacFJA\RediSearch\Query\Builder\Fuzzy;
use MacFJA\RediSearch\Query\Builder\GeoFacet;
use MacFJA\RediSearch\Query\Builder\Negation;
use MacFJA\RediSearch\Query\Builder\NumericFacet;
use MacFJA\RediSearch\Query\Builder\Optional;
use MacFJA\RediSearch\Query\Builder\OrGroup;
use MacFJA\RediSearch\Query\Builder\Prefix;
use MacFJA\RediSearch\Query\Builder\QueryElementAttribute;
use MacFJA\RediSearch\Query\Builder\RawElement;
use MacFJA\RediSearch\Query\Builder\TagFacet;
use MacFJA\RediSearch\Query\Builder\TextFacet;
use MacFJA\RediSearch\Query\Builder\Word;
use MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Query\Builder
 * @covers \MacFJA\RediSearch\Query\Builder\AbstractGroup
 * @covers \MacFJA\RediSearch\Query\Builder\AndGroup
 * @covers \MacFJA\RediSearch\Query\Builder\EncapsulationGroup
 * @covers \MacFJA\RediSearch\Query\Builder\ExactMatch
 * @covers \MacFJA\RediSearch\Query\Builder\FieldFacet
 * @covers \MacFJA\RediSearch\Query\Builder\Fuzzy
 * @covers \MacFJA\RediSearch\Query\Builder\GeoFacet
 * @covers \MacFJA\RediSearch\Query\Builder\Negation
 * @covers \MacFJA\RediSearch\Query\Builder\NumericFacet
 * @covers \MacFJA\RediSearch\Query\Builder\Optional
 * @covers \MacFJA\RediSearch\Query\Builder\OrGroup
 * @covers \MacFJA\RediSearch\Query\Builder\Prefix
 * @covers \MacFJA\RediSearch\Query\Builder\QueryElement::priority
 * @covers \MacFJA\RediSearch\Query\Builder\QueryElementAttribute
 * @covers \MacFJA\RediSearch\Query\Builder\QueryElementVector
 * @covers \MacFJA\RediSearch\Query\Builder\RawElement
 * @covers \MacFJA\RediSearch\Query\Builder\TagFacet
 * @covers \MacFJA\RediSearch\Query\Builder\TextFacet
 * @covers \MacFJA\RediSearch\Query\Builder\Word
 *
 * @uses \MacFJA\RediSearch\Query\Escaper
 *
 * @internal
 */
class IncludeSpaceTest extends TestCase
{
    public function testGroupIncludeSpace(): void
    {
        $andGroup = new AndGroup();
        $andGroup2 = new AndGroup([new Word('foo bar')]);
        $andGroup3 = new AndGroup([new Word('foo bar'), new Word('baz')]);
        $encapsulationGroup = new EncapsulationGroup('(', ')', new RawElement(''));
        $encapsulationGroup2 = new EncapsulationGroup('(', ')', new Word('foo'), true);
        $encapsulationGroup3 = new EncapsulationGroup('(', ')', new Word('foo bar'), true);
        $orGroup = new OrGroup();
        $orGroup2 = new OrGroup([new Word('foo bar')]);
        $orGroup3 = new OrGroup([new Word('foo bar'), new Word('baz')]);
        $orGroup4 = new OrGroup([new Word('foo')]);
        $builderGroup = new Builder();

        static::assertFalse($andGroup->includeSpace());
        static::assertTrue($andGroup2->includeSpace());
        static::assertTrue($andGroup3->includeSpace());
        static::assertFalse($encapsulationGroup->includeSpace());
        static::assertFalse($encapsulationGroup2->includeSpace());
        static::assertFalse($encapsulationGroup3->includeSpace());
        static::assertFalse($orGroup->includeSpace());
        static::assertTrue($orGroup2->includeSpace());
        static::assertTrue($orGroup3->includeSpace());
        static::assertFalse($orGroup4->includeSpace());
        static::assertFalse($builderGroup->includeSpace());
    }

    public function testFacetIncludeSpace(): void
    {
        $field = new FieldFacet(['foo'], new RawElement(''));
        $geo = new GeoFacet(['foo'], 0, 0, 0, GeoFilterOption::UNIT_METERS);
        $numeric = new NumericFacet(['foo'], 0, 0);
        $tag = new TagFacet(['foo'], '');
        $text = new TextFacet(['foo'], '');
        $text2 = new TextFacet(['foo'], 'bar baz');
        $text3 = new TextFacet(['foo'], 'bar', 'baz');

        static::assertFalse($field->includeSpace());
        static::assertFalse($geo->includeSpace());
        static::assertFalse($numeric->includeSpace());
        static::assertFalse($tag->includeSpace());
        static::assertFalse($text->includeSpace());
        static::assertFalse($text2->includeSpace());
        static::assertFalse($text3->includeSpace());
    }

    public function testModifierIncludeSpace(): void
    {
        $exactMatch = new ExactMatch('foo bar');
        $fuzzy = new Fuzzy('foo bar');
        $negation = new Negation(new RawElement('foo bar'));
        $optional = new Optional(new RawElement('foo bar'));
        $prefix = new Prefix('foo bar');
        $attribute = new QueryElementAttribute(new RawElement('foo bar'));
        $vector = new Builder\QueryElementVector(new RawElement('foo bar'), 10, 'vec', 'BLOB');
        $raw = new RawElement('foo bar');
        $word = new Word('foo bar');

        static::assertFalse($exactMatch->includeSpace());
        static::assertTrue($fuzzy->includeSpace());
        static::assertFalse($negation->includeSpace());
        static::assertFalse($optional->includeSpace());
        static::assertFalse($prefix->includeSpace());
        static::assertTrue($attribute->includeSpace());
        static::assertFalse($vector->includeSpace());
        static::assertTrue($raw->includeSpace());
        static::assertTrue($word->includeSpace());
    }
}
