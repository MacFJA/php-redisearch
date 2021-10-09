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

namespace MacFJA\RediSearch\tests\Query;

use MacFJA\RediSearch\Query\Builder;
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
 * @covers \MacFJA\RediSearch\Query\Builder\QueryElementAttribute
 * @covers \MacFJA\RediSearch\Query\Builder\RawElement
 * @covers \MacFJA\RediSearch\Query\Builder\TagFacet
 * @covers \MacFJA\RediSearch\Query\Builder\TextFacet
 * @covers \MacFJA\RediSearch\Query\Builder\Word
 * @covers \MacFJA\RediSearch\Query\Escaper
 *
 * @internal
 */
class BuilderTest extends TestCase
{
    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#numeric_filters_in_query
     */
    public function testDocNumericExample1(): void
    {
        $expected = '@price:[100 200]';

        $builder = new Builder();

        $builder->addNumericFacet('price', 100, 200);

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#numeric_filters_in_query
     */
    public function testDocNumericExample2(): void
    {
        $expected = '@title:foo -@price:[100 100]';

        $builder = new Builder();

        $builder
            ->addTextFacet('title', 'foo')
            ->addElement(
                new Builder\Negation(
                    Builder\NumericFacet::equalsTo('price', 100)
                )
            )
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * https://oss.redislabs.com/redisearch/Query_Syntax/#pure_negative_queries.
     */
    public function testDocNegativeExample1(): void
    {
        $expected = '-hello';
        $builder = new Builder();

        $builder->addElement(new Builder\Negation(new Builder\Word('hello')));

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#field_modifiers
     */
    public function testDocModifierExample1(): void
    {
        $expected = '@country:korea @engine:(diesel|hybrid) @class:suv';
        $builder = new Builder();

        $builder->addTextFacet('country', 'korea')
            ->addTextFacet('engine', 'diesel', 'hybrid')
            ->addTextFacet('class', 'suv')
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#field_modifiers
     */
    public function testDocModifierExample2(): void
    {
        $expected = '@title|body:(hello world) @url|image:mydomain';
        $builder = new Builder();

        $builder
            ->addElement(
                new Builder\FieldFacet(
                    ['title', 'body'],
                    Builder\EncapsulationGroup::simple(Builder\AndGroup::fromStrings('hello', 'world'))
                )
            )
            ->addElement(new Builder\TextFacet(
                ['url', 'image'],
                'mydomain'
            ))
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#tag_filters
     */
    public function testDocTagExample1(): void
    {
        $expected = '@cities:{ New York | Los Angeles | Barcelona }';
        $expected = str_replace(['{ ', ' | ', ' }'], ['{', '|', '}'], $expected);
        $builder = new Builder();

        $builder
            ->addTagFacet('cities', 'New York', 'Los Angeles', 'Barcelona')
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#tag_filters
     */
    public function testDocTagExample2(): void
    {
        $expected = '@cities:{ New York } @cities:{Los Angeles} @cities:{ Barcelona }';
        $expected = str_replace(['{ ', ' | ', ' }'], ['{', '|', '}'], $expected);
        $builder = new Builder();

        $builder
            ->addTagFacet('cities', 'New York')
            ->addTagFacet('cities', 'Los Angeles')
            ->addTagFacet('cities', 'Barcelona')
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#geo_filters_in_query
     */
    public function testDocGeoExample1(): void
    {
        $expected = 'chinese restaurant @location:[-122.41 37.77 5 km]';
        $builder = new Builder();

        $builder
            ->addString('chinese')
            ->addString('restaurant')
            ->addGeoFacet('location', -122.41, 37.77, 5, 'km')
        ;

        static::assertSame($expected, str_replace(
            ['.000000 ', '0000 '],
            [' ', ' '],
            $builder->render()
        ));
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#fuzzy_matching
     */
    public function testDocFuzzyExample1(): void
    {
        $expected = '%hello% world';
        $builder = new Builder();

        $builder
            ->addElement(new Builder\Fuzzy('hello'))
            ->addString('world')
            ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#fuzzy_matching
     */
    public function testDocFuzzyExample2(): void
    {
        $expected = '%%hello%%';
        $builder = new Builder();

        $builder
            ->addElement(new Builder\Fuzzy('hello', 2))
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#query_attributes
     */
    public function testDocAttributeExample1(): void
    {
        $expected = '(foo bar) => { $weight: 2.0; $slop: 1; $inorder: true; }';
        $builder = new Builder();

        $builder
            ->addElement(
                new Builder\QueryElementAttribute(
                    Builder\AndGroup::fromStrings('foo', 'bar'),
                    2.0,
                    1,
                    true
                )
            )
        ;

        static::assertSame($expected, str_replace(
            [' })', '(('],
            [' }', '('],
            $builder->render()
        ));
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#query_attributes
     */
    public function testDocAttributeExample2(): void
    {
        $expected = '~(bar baz) => { $weight: 0.5; }';
        $builder = new Builder();

        $builder
            ->addElement(
                new Builder\QueryElementAttribute(
                    new Builder\Optional(Builder\AndGroup::fromStrings('bar', 'baz')),
                    0.5
                )
            )
        ;

        static::assertSame($expected, str_replace(
            [' })', '(~('],
            [' }', '~('],
            $builder->render()
        ));
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#query_attributes
     */
    public function testDocAttributeExample2bis(): void
    {
        $expected = '~(bar baz) => { $weight: 0.5; }';
        $builder = new Builder();

        $builder
            ->addElement(
                new Builder\Optional(
                    new Builder\QueryElementAttribute(
                        Builder\AndGroup::fromStrings('foo', 'bar'),
                        0.5
                    )
                )
            )
        ;

        static::assertNotSame($expected, str_replace(
            [' })', '(~('],
            [' }', '~('],
            $builder->render()
        ));
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample1(): void
    {
        $expected = 'hello world';
        $builder = new Builder();

        $builder
            ->addString('hello')
            ->addString('world')
        ;

        static::assertSame($expected, $builder->render());

        $builder = new Builder();

        $builder
            ->addElement(Builder\AndGroup::fromStrings('hello', 'world'))
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample2(): void
    {
        $expected = '"hello world"';
        $builder = new Builder();

        $builder
            ->addElement(new Builder\ExactMatch('hello world'))
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample3(): void
    {
        $expected = 'hello|world';
        $builder = new Builder();

        $builder->addElement(Builder\OrGroup::fromStrings('hello', 'world'))
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample4(): void
    {
        $expected = 'hello -world';
        $builder = new Builder();

        $builder
            ->addString('hello')
            ->addElement(new Builder\Negation(new Builder\Word('world')))
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample5(): void
    {
        $expected = '(hello|halo) (world|werld)';
        $builder = new Builder();

        $builder
            ->addElement(Builder\OrGroup::fromStrings('hello', 'halo'))
            ->addElement(Builder\OrGroup::fromStrings('world', 'werld'))
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample6(): void
    {
        $expected = 'hello -(world|werld)';
        $builder = new Builder();

        $builder
            ->addString('hello')
            ->addElement(
                new Builder\Negation(
                    new Builder\EncapsulationGroup(
                        '(',
                        ')',
                        Builder\OrGroup::fromStrings('world', 'werld')
                    )
                )
            )
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample7(): void
    {
        $expected = '(barack|barrack) obama';
        $builder = new Builder();

        $builder
            ->addElement(Builder\OrGroup::fromStrings('barack', 'barrack'))
            ->addString('obama')
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample8(): void
    {
        $expected = 'obama ~barack ~michelle';
        $builder = new Builder();

        $builder
            ->addString('obama')
            ->addElement(new Builder\Optional(new Builder\Word('barack')))
            ->addElement(new Builder\Optional(new Builder\Word('michelle')))
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample9(): void
    {
        $expected = '@title:"barack obama" @job:president';
        $builder = new Builder();

        $builder
            ->addTextFacet('title', 'barack obama')
            ->addTextFacet('job', 'president')
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample10(): void
    {
        $expected = '@title:hello world @body:(foo bar) @category:(articles|biographies)';
        $builder = new Builder();

        $builder
            ->addTextFacet('title', 'hello')
            ->addString('world')
            ->addElement(new Builder\FieldFacet(['body'], Builder\AndGroup::fromStrings('foo', 'bar')))
            ->addTextFacet('category', 'articles', 'biographies')
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample12(): void
    {
        $expected = '@name:tv @price:[200 500]';
        $builder = new Builder();

        $builder
            ->addTextFacet('name', 'tv')
            ->addNumericFacet('price', 200, 500)
        ;

        static::assertSame($expected, $builder->render());
    }

    /**
     * @see https://oss.redislabs.com/redisearch/Query_Syntax/#a_few_query_examples
     */
    public function testDocExample13(): void
    {
        $expected = '@age:[(18 +inf]';
        $builder = new Builder();

        $builder
            ->addElement(Builder\NumericFacet::greaterThan('age', 18))
        ;

        static::assertSame($expected, $builder->render());
    }
}
