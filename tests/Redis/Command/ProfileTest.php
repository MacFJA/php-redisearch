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
use MacFJA\RediSearch\Redis\Command\Profile;
use MacFJA\RediSearch\Redis\Command\Search;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\AbstractCommand
 *
 * @covers \MacFJA\RediSearch\Redis\Command\Profile
 * @covers \MacFJA\RediSearch\Redis\Command\ProfileCommand\QueryOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NumberedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Search
 * @uses \MacFJA\RediSearch\Redis\Command\Aggregate
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\HighlightOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\LimitOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\SortByOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\SummarizeOption
 * @uses \MacFJA\RediSearch\Redis\Command\AggregateCommand\SortByOption
 * @uses \MacFJA\RediSearch\Redis\Command\AggregateCommand\WithCursor
 *
 * @internal
 */
class ProfileTest extends TestCase
{
    public function testGetId(): void
    {
        $command = new Profile();
        static::assertSame('FT.PROFILE', $command->getId());
    }

    public function testFullOption(): void
    {
        $aggregate = new Aggregate(Profile::MAX_IMPLEMENTED_VERSION);
        $aggregate->setQuery('@foo:bar')->setIndex('idx');
        $command = new Profile(Profile::MAX_IMPLEMENTED_VERSION);
        $command
            ->setIndex('idx')
            ->setTypeAggregate()
            ->setLimited()
            ->setQuery($aggregate)
        ;

        static::assertSame([
            'idx', 'AGGREGATE', 'LIMITED', 'QUERY', '@foo:bar',
        ], $command->getArguments());
    }

    public function testSearch(): void
    {
        $search = new Search('2.2.0');
        $search->setIndex('idx')->setQuery('@foo:bar');
        $command = new Profile('2.2.0');
        $command
            ->setIndex('idx')
            ->setTypeSearch()
            ->setQuery($search)
        ;

        static::assertSame([
            'idx', 'SEARCH', 'QUERY', '@foo:bar',
        ], $command->getArguments());
    }

    public function testAutoFill(): void
    {
        $search = new Search('2.2.0');
        $search->setIndex('idx')->setQuery('@foo:bar');
        $command = new Profile('2.2.0');
        $command
            ->setQuery($search)
        ;

        static::assertSame([
            'idx', 'SEARCH', 'QUERY', '@foo:bar',
        ], $command->getArguments());
    }

    public function testOverride(): void
    {
        $search = new Search('2.2.0');
        $search->setIndex('idx')->setQuery('@foo:bar');
        $command = new Profile('2.2.0');
        $command
            ->setQuery($search)
            ->setIndex('idx2')
            ->setTypeAggregate()
        ;

        static::assertSame([
            'idx2', 'AGGREGATE', 'QUERY', '@foo:bar',
        ], $command->getArguments());
    }
}
