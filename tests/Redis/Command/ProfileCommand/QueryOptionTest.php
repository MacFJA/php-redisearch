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

namespace MacFJA\RediSearch\tests\Redis\Command\ProfileCommand;

use MacFJA\RediSearch\Redis\Command\Aggregate;
use MacFJA\RediSearch\Redis\Command\ProfileCommand\QueryOption;
use MacFJA\RediSearch\Redis\Command\Search;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\ProfileCommand\QueryOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Search
 * @uses \MacFJA\RediSearch\Redis\Command\Aggregate
 * @uses \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NumberedOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\HighlightOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\LimitOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\SortByOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\SummarizeOption
 * @uses \MacFJA\RediSearch\Redis\Command\AggregateCommand\WithCursor
 *
 * @internal
 */
class QueryOptionTest extends TestCase
{
    public function testGetOptionData(): void
    {
        $command = (new Search())->setIndex('idx')->setQuery('*');
        static::assertSame([
            'command' => $command,
        ], (new QueryOption())->setCommand($command)->getOptionData());
    }

    public function testIsSearch(): void
    {
        $search = (new Search())->setIndex('idx')->setQuery('*');
        $aggregate = (new Aggregate())->setIndex('idx')->setQuery('*');
        static::assertTrue((new QueryOption())->setCommand($search)->isSearch());
        static::assertFalse((new QueryOption())->setCommand($aggregate)->isSearch());
    }
}
