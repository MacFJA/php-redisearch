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
use MacFJA\RediSearch\Query\Builder\QueryElement;
use MacFJA\RediSearch\Query\Builder\QueryElementAttribute;
use MacFJA\RediSearch\Query\Builder\RawElement;
use MacFJA\RediSearch\Query\Builder\TagFacet;
use MacFJA\RediSearch\Query\Builder\TextFacet;
use MacFJA\RediSearch\Query\Builder\Word;
use MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption;

/**
 * @covers \MacFJA\RediSearch\Query\Builder
 *
 * @covers \MacFJA\RediSearch\Query\Builder\AbstractGroup
 * @covers \MacFJA\RediSearch\Query\Builder\AndGroup
 * @covers \MacFJA\RediSearch\Query\Builder\EncapsulationGroup
 * @covers \MacFJA\RediSearch\Query\Builder\ExactMatch
 * @covers \MacFJA\RediSearch\Query\Builder\FieldFacet
 *
 * @covers \MacFJA\RediSearch\Query\Builder\Fuzzy
 * @covers \MacFJA\RediSearch\Query\Builder\GeoFacet
 * @covers \MacFJA\RediSearch\Query\Builder\Negation
 * @covers \MacFJA\RediSearch\Query\Builder\NumericFacet
 * @covers \MacFJA\RediSearch\Query\Builder\Optional
 *
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
class PriorityTest extends \PHPUnit\Framework\TestCase
{
    public function testGroupPriority(): void
    {
        $andGroup = new AndGroup();
        $encapsulationGroup = new EncapsulationGroup('(', ')', new RawElement(''));
        $orGroup = new OrGroup();
        $builderGroup = new Builder();

        static::assertSame(QueryElement::PRIORITY_NORMAL, $andGroup->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $encapsulationGroup->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $orGroup->priority());
        static::assertSame(QueryElement::PRIORITY_BEFORE, $builderGroup->priority());
    }

    public function testFacetPriority(): void
    {
        $field = new FieldFacet(['foo'], new RawElement(''));
        $geo = new GeoFacet(['foo'], 0, 0, 0, GeoFilterOption::UNIT_METERS);
        $numeric = new NumericFacet(['foo'], 0, 0);
        $tag = new TagFacet(['foo'], '');
        $text = new TextFacet(['foo'], '');

        static::assertSame(QueryElement::PRIORITY_NORMAL, $field->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $geo->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $numeric->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $tag->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $text->priority());
    }

    public function testModifierPriority(): void
    {
        $exactMatch = new ExactMatch('foo');
        $fuzzy = new Fuzzy('foo');
        $negation = new Negation(new RawElement('foo'));
        $optional = new Optional(new RawElement('foo'));
        $prefix = new Prefix('foo');
        $attribute = new QueryElementAttribute(new RawElement('foo'));
        $vector = new Builder\QueryElementVector(new RawElement('foo'), 10, 'vec', 'BLOB');
        $raw = new RawElement('foo');
        $word = new Word('foo');

        static::assertSame(QueryElement::PRIORITY_NORMAL, $exactMatch->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $fuzzy->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $negation->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $optional->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $prefix->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $attribute->priority());
        static::assertSame(QueryElement::PRIORITY_BEFORE, $vector->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $raw->priority());
        static::assertSame(QueryElement::PRIORITY_NORMAL, $word->priority());
    }
}
