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

namespace MacFJA\RediSearch\tests\Redis\Command;

use MacFJA\RediSearch\Redis\Command\Aggregate;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\GroupByOption;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\ReduceOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\AbstractCommand
 *
 * @covers \MacFJA\RediSearch\Redis\Command\Aggregate
 * @covers \MacFJA\RediSearch\Redis\Command\AggregateCommand\ApplyOption
 * @covers \MacFJA\RediSearch\Redis\Command\AggregateCommand\GroupByOption
 * @covers \MacFJA\RediSearch\Redis\Command\AggregateCommand\LimitOption
 * @covers \MacFJA\RediSearch\Redis\Command\AggregateCommand\ReduceOption
 * @covers \MacFJA\RediSearch\Redis\Command\AggregateCommand\SortByOption
 *
 * @covers \MacFJA\RediSearch\Redis\Command\AggregateCommand\WithCursor
 *
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\LimitOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionAwareTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NumberedOption
 *
 * @internal
 */
class AggregateTest extends TestCase
{
    public function testGetId(): void
    {
        $command = new Aggregate();
        static::assertSame('FT.AGGREGATE', $command->getId());
    }

    public function testGetIndex(): void
    {
        $command = new Aggregate();
        $command->setIndex('idx');
        static::assertSame('idx', $command->getIndex());
    }

    public function testMultiReduce(): void
    {
        $group = new GroupByOption(['@text1']);
        $group
            ->addReducer(ReduceOption::countDistinct('user_id'))
            ->addReducer(ReduceOption::average('num1', 'average'))
        ;
        $command = new Aggregate();
        $command
            ->setIndex('idx')
            ->setQuery('@text1:"hello world"')
            ->addGroupBy($group)
        ;

        static::assertSame([
            'idx',
            '@text1:"hello world"',
            'GROUPBY', 1, '@text1', 'REDUCE', 'COUNT_DISTINCT', 1, '@user_id', 'REDUCE', 'AVG', 1, '@num1', 'AS', 'average',
        ], $command->getArguments());
    }

    public function testWithCursor(): void
    {
        $command = new Aggregate();
        $command
            ->setIndex('idx')
            ->setQuery('@text1:"hello world"')
            ->setWithCursor(20, 30)
        ;

        static::assertSame([
            'idx',
            '@text1:"hello world"',
            'WITHCURSOR', 'COUNT', 20, 'MAXIDLE', 30,
        ], $command->getArguments());

        $command->setWithoutCursor();
        static::assertSame([
            'idx',
            '@text1:"hello world"',
        ], $command->getArguments());
    }

    public function testSizeOptions(): void
    {
        $command = new Aggregate();
        static::assertNull($command->getSize());
        static::assertNull($command->getOffset());

        $command->setLimit(0, 10);
        static::assertSame(0, $command->getOffset());
        static::assertSame(10, $command->getSize());
    }

    public function testFullOption(): void
    {
        $command = new Aggregate();
        $command
            ->setIndex('idx')
            ->setQuery('@text1:"hello world"')
            ->setVerbatim()
            ->setLoad('@geo1', '@tag1')
            ->addGroupBy(new GroupByOption(['@text1'], [ReduceOption::countDistinct('user_id'), ReduceOption::average('num1', 'average')]))
            ->addFilter("@name=='foo' && @age < 20")
            ->addSortBy('@text1')
            ->addSortBy('@num1', 'DESC')
            ->setSortByMax(10)
            ->setLimit(12, 35)
            ->addApply('@timestamp - (@timestamp % 86400)', 'day')
            ->setWithCursor(20, 30)
            ;

        static::assertSame([
            'idx',
            '@text1:"hello world"',
            'VERBATIM',
            'LOAD', 2, '@geo1', '@tag1',
            'GROUPBY', 1, '@text1', 'REDUCE', 'COUNT_DISTINCT', 1, '@user_id', 'REDUCE', 'AVG', 1, '@num1', 'AS', 'average',
            'APPLY', '@timestamp - (@timestamp % 86400)',  'AS', 'day',
            'SORTBY', 3, '@text1', '@num1', 'DESC', 'MAX', 10,
            'LIMIT', 12, 35,
            'FILTER', "@name=='foo' && @age < 20",
            'WITHCURSOR', 'COUNT', 20, 'MAXIDLE', 30,
        ], $command->getArguments());
    }

    public function testApplyOrder(): void
    {
        $command = new Aggregate();
        $command
            ->setIndex('idx')
            ->setQuery('@text1:"hello world"')
            ->setVerbatim()
            ->setLoad('@geo1', '@tag1')
            ->addApply('split(@tag1)', 'tag2')
            ->addGroupBy(new GroupByOption(['@text1'], [ReduceOption::countDistinct('user_id'), ReduceOption::average('num1', 'average')]))
            ->addFilter("@name=='foo' && @age < 20")
            ->addApply('@age - 1', 'age')
            ->addSortBy('@text1')
            ->addSortBy('@num1', 'DESC')
            ->setSortByMax(10)
            ->setLimit(12, 35)
            ->setWithCursor(20, 30)
        ;

        static::assertSame([
            'idx',
            '@text1:"hello world"',
            'VERBATIM',
            'LOAD', 2, '@geo1', '@tag1',
            'APPLY', 'split(@tag1)',  'AS', 'tag2',
            'GROUPBY', 1, '@text1', 'REDUCE', 'COUNT_DISTINCT', 1, '@user_id', 'REDUCE', 'AVG', 1, '@num1', 'AS', 'average',
            'APPLY', '@age - 1',  'AS', 'age',
            'SORTBY', 3, '@text1', '@num1', 'DESC', 'MAX', 10,
            'LIMIT', 12, 35,
            'FILTER', "@name=='foo' && @age < 20",
            'WITHCURSOR', 'COUNT', 20, 'MAXIDLE', 30,
        ], $command->getArguments());
    }
}
