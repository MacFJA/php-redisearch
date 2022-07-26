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

use InvalidArgumentException;
use MacFJA\RediSearch\Query\Builder;
use MacFJA\RediSearch\Redis\Command\Search;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Query\Builder\QueryElementVector
 *
 * @uses \MacFJA\RediSearch\Query\Builder
 * @uses \MacFJA\RediSearch\Query\Builder\AbstractGroup
 * @uses \MacFJA\RediSearch\Query\Builder\AndGroup
 * @uses \MacFJA\RediSearch\Query\Builder\EncapsulationGroup
 * @uses \MacFJA\RediSearch\Query\Builder\ExactMatch
 * @uses \MacFJA\RediSearch\Query\Builder\FieldFacet
 * @uses \MacFJA\RediSearch\Query\Builder\Optional
 * @uses \MacFJA\RediSearch\Query\Builder\OrGroup
 * @uses \MacFJA\RediSearch\Query\Builder\RawElement
 * @uses \MacFJA\RediSearch\Query\Builder\TagFacet
 * @uses \MacFJA\RediSearch\Query\Builder\Word
 * @uses \MacFJA\RediSearch\Query\Escaper
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
class QueryElementVectorTest extends TestCase
{
    /**
     * @see https://redis.io/docs/stack/search/reference/vectors/#examples-for-querying-vector-fields
     */
    public function testVectorDocExample(): void
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

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $element = new Builder\RawElement('*');
        new Builder\QueryElementVector($element, false, 'vec', 'BLOB');
    }

    public function testInvalidParameterName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $element = new Builder\RawElement('*');
        $vector = new Builder\QueryElementVector($element, 10, 'vec', 'BLOB');
        $vector->addParameter('foo', 'bar');
    }

    public function testInvalidParameterValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $element = new Builder\RawElement('*');
        $vector = new Builder\QueryElementVector($element, 10, 'vec', 'BLOB');
        $vector->addParameter('EF_RUNTIME', 'bar');
    }
}
