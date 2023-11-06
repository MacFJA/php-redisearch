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

use InvalidArgumentException;
use MacFJA\RediSearch\Query\Builder;
use MacFJA\RediSearch\Redis\Command\Search;
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
 * @covers \MacFJA\RediSearch\Query\Builder\QueryElementVector
 * @covers \MacFJA\RediSearch\Query\Builder\RawElement
 * @covers \MacFJA\RediSearch\Query\Builder\TagFacet
 * @covers \MacFJA\RediSearch\Query\Builder\TextFacet
 * @covers \MacFJA\RediSearch\Query\Builder\Word
 * @covers \MacFJA\RediSearch\Query\Escaper
 * @covers \MacFJA\RediSearch\Redis\Command\Search::addParam
 *
 * @uses \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @uses \MacFJA\RediSearch\Redis\Command\LanguageOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionAwareTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NumberedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Search
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\HighlightOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\LimitOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\SortByOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\SummarizeOption
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
     * @see https://oss.redis.com/redisearch/Query_Syntax/#prefix_matching
     */
    public function testDocPrefixExample1(): void
    {
        $expected = 'hel* world';
        $builder = new Builder();

        $builder
            ->addElement(new Builder\Prefix('hel'))
            ->addString('world')
        ;

        static::assertSame($expected, $builder->render());
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

        $builder->addElement(Builder\OrGroup::fromStrings('hello', 'world'));

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

    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample1(): void
    {
        // $expected = 'idx "*=>[KNN 100 @vec $BLOB]" PARAMS 2 BLOB "\12\a9\f5\6c"';
        // Data escaping is partially handled by the Redis lib, + this lib put extra safety ()
        $expected = 'idx (*)=>[KNN 100 @vec $BLOB] PARAMS 2 BLOB \12\a9\f5\6c';
        $builder = new Builder();
        $search = new Search('2.4.0');

        $builder
            ->addElement(
                new Builder\QueryElementVector(new Builder\RawElement('*'), 100, 'vec', 'BLOB')
            )
        ;
        $search
            ->setIndex('idx')
            ->setQuery($builder->render())
            ->addParam('BLOB', '\12\a9\f5\6c')
        ;

        static::assertSame($expected, implode(' ', $search->getArguments()));
    }

    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample12(): void
    {
        // $expected = 'idx "*=>[KNN 100 @vec $BLOB]" PARAMS 2 BLOB "\12\a9\f5\6c"';
        // Data escaping is partially handled by the Redis lib, + this lib put extra safety ()
        $expected = 'idx (*)=>[KNN 100 @vec $BLOB] PARAMS 2 BLOB \12\a9\f5\6c';
        $builder = new Builder();
        $search = new Search('2.4.0');

        $builder
            ->addElement(
                new Builder\QueryElementVector(new Builder\RawElement('*'), 100, '@vec', '$BLOB')
            )
        ;
        $search
            ->setIndex('idx')
            ->setQuery($builder->render())
            ->addParam('BLOB', '\12\a9\f5\6c')
        ;

        static::assertSame($expected, implode(' ', $search->getArguments()));
    }

    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample2(): void
    {
        // $expected = 'idx "*=>[KNN 100 @vec $BLOB]" PARAMS 2 BLOB "\12\a9\f5\6c" SORTBY __vec_score';
        // Data escaping is partially handled by the Redis lib, + this lib put extra safety ()
        $expected = 'idx (*)=>[KNN 100 @vec $BLOB] PARAMS 2 BLOB \12\a9\f5\6c SORTBY __vec_score';
        $builder = new Builder();
        $search = new Search('2.4.0');

        $builder
            ->addElement(
                new Builder\QueryElementVector(new Builder\RawElement('*'), 100, 'vec', 'BLOB')
            )
        ;
        $search
            ->setIndex('idx')
            ->setQuery($builder->render())
            ->addParam('BLOB', '\12\a9\f5\6c')
            ->setSortBy('__vec_score')
        ;

        static::assertSame($expected, implode(' ', $search->getArguments()));
    }

    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample3(): void
    {
        // $expected = 'idx "*=>[KNN $K @vec $BLOB EF_RUNTIME $EF]" PARAMS 6 BLOB "\12\a9\f5\6c" K 10 EF 150';
        // Data escaping is partially handled by the Redis lib, + this lib put extra safety ()
        $expected = 'idx (*)=>[KNN $K @vec $BLOB EF_RUNTIME $EF] PARAMS 6 BLOB \12\a9\f5\6c K 10 EF 150';
        $builder = new Builder();
        $search = new Search('2.4.0');

        $vector = new Builder\QueryElementVector(new Builder\RawElement('*'), 'K', 'vec', 'BLOB');
        $vector->addParameter('EF_RUNTIME', '$EF');
        $builder->addElement($vector);
        $search
            ->setIndex('idx')
            ->setQuery($builder->render())
            ->addParam('BLOB', '\12\a9\f5\6c')
            ->addParam('K', 10)
            ->addParam('EF', 150)
        ;

        static::assertSame($expected, implode(' ', $search->getArguments()));
    }

    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample32(): void
    {
        // $expected = 'idx "*=>[KNN $K @vec $BLOB EF_RUNTIME $EF]" PARAMS 6 BLOB "\12\a9\f5\6c" K 10 EF 150';
        // Data escaping is partially handled by the Redis lib, + this lib put extra safety ()
        $expected = 'idx (*)=>[KNN $K @vec $BLOB EF_RUNTIME $EF] PARAMS 6 BLOB \12\a9\f5\6c K 10 EF 150';
        $builder = new Builder();
        $search = new Search('2.4.0');

        $vector = new Builder\QueryElementVector(new Builder\RawElement('*'), '$K', '@vec', '$BLOB');
        $vector->addParameter('EF_RUNTIME', '$EF');
        $builder->addElement($vector);
        $search
            ->setIndex('idx')
            ->setQuery($builder->render())
            ->addParam('BLOB', '\12\a9\f5\6c')
            ->addParam('K', 10)
            ->addParam('EF', 150)
        ;

        static::assertSame($expected, implode(' ', $search->getArguments()));
    }

    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample33Error(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $vector = new Builder\QueryElementVector(new Builder\RawElement('*'), '$K', '@vec', '$BLOB');
        $vector->addParameter('EF_RUNTIME', 'EF');
    }

    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample4(): void
    {
        // $expected = 'idx "*=>[KNN $K @vec $BLOB AS my_scores]" PARAMS 4 BLOB "\12\a9\f5\6c" K 10 SORTBY my_scores';
        // Data escaping is partially handled by the Redis lib, + this lib put extra safety ()
        $expected = 'idx (*)=>[KNN $K @vec $BLOB AS my_scores] PARAMS 4 BLOB \12\a9\f5\6c K 10 SORTBY my_scores';
        $builder = new Builder();
        $search = new Search('2.4.0');

        $builder
            ->addElement(
                new Builder\QueryElementVector(new Builder\RawElement('*'), 'K', 'vec', 'BLOB', 'my_scores')
            )
        ;
        $search
            ->setIndex('idx')
            ->setQuery($builder->render())
            ->addParam('BLOB', '\12\a9\f5\6c')
            ->addParam('K', 10)
            ->setSortBy('my_scores')
        ;

        static::assertSame($expected, implode(' ', $search->getArguments()));
    }

    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample5(): void
    {
        // $expected = 'idx "(@title:Dune @num:[2020 2022])=>[KNN $K @vec $BLOB AS my_scores]" PARAMS 4 BLOB "\12\a9\f5\6c" K 10 SORTBY my_scores';
        // Data escaping is partially handled by the Redis lib
        $expected = 'idx (@title:Dune @num:[2020 2022])=>[KNN $K @vec $BLOB AS my_scores] PARAMS 4 BLOB \12\a9\f5\6c K 10 SORTBY my_scores';
        $builder = new Builder();
        $search = new Search('2.4.0');

        $group = new Builder\AndGroup([
            new Builder\TextFacet(['title'], 'Dune'),
            new Builder\NumericFacet(['num'], 2020, 2022),
        ]);
        $builder
            ->addElement(
                new Builder\QueryElementVector($group, 'K', 'vec', 'BLOB', 'my_scores')
            )
        ;
        $search
            ->setIndex('idx')
            ->setQuery($builder->render())
            ->addParam('BLOB', '\12\a9\f5\6c')
            ->addParam('K', 10)
            ->setSortBy('my_scores')
        ;

        static::assertSame($expected, implode(' ', $search->getArguments()));
    }

    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample6(): void
    {
        // $expected = 'idx "(@type:{shirt} ~@color:{blue})=>[KNN $K @vec $BLOB AS my_scores]" PARAMS 4 BLOB "\12\a9\f5\6c" K 10 SORTBY my_scores';
        // Data escaping is partially handled by the Redis lib
        $expected = 'idx (@type:{shirt} ~@color:{blue})=>[KNN $K @vec $BLOB AS my_scores] PARAMS 4 BLOB \12\a9\f5\6c K 10 SORTBY my_scores';
        $builder = new Builder();
        $search = new Search('2.4.0');

        $group = new Builder\AndGroup([
            new Builder\TagFacet(['type'], 'shirt'),
            new Builder\Optional(new Builder\TagFacet(['color'], 'blue')),
        ]);
        $builder
            ->addElement(
                new Builder\QueryElementVector($group, 'K', 'vec', 'BLOB', 'my_scores')
            )
        ;
        $search
            ->setIndex('idx')
            ->setQuery($builder->render())
            ->addParam('BLOB', '\12\a9\f5\6c')
            ->addParam('K', 10)
            ->setSortBy('my_scores')
        ;

        static::assertSame($expected, implode(' ', $search->getArguments()));
    }
}
