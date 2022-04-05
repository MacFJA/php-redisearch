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

use MacFJA\RediSearch\Redis\Command\AbstractCommand;
use MacFJA\RediSearch\Redis\Command\Search;
use MacFJA\RediSearch\Redis\Command\SearchCommand\FilterOption;
use MacFJA\RediSearch\Redis\Response\PaginatedResponse;
use MacFJA\RediSearch\Redis\Response\SearchResponseItem;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \MacFJA\RediSearch\Redis\Command\AbstractCommand
 *
 * @covers \MacFJA\RediSearch\Redis\Command\Search
 * @covers \MacFJA\RediSearch\Redis\Command\SearchCommand\FilterOption
 * @covers \MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption
 * @covers \MacFJA\RediSearch\Redis\Command\SearchCommand\HighlightOption
 * @covers \MacFJA\RediSearch\Redis\Command\SearchCommand\LimitOption
 * @covers \MacFJA\RediSearch\Redis\Command\SearchCommand\SortByOption
 *
 * @covers \MacFJA\RediSearch\Redis\Command\SearchCommand\SummarizeOption
 *
 * @covers \MacFJA\RediSearch\Redis\Response\PaginatedResponse
 *
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
class SearchTest extends TestCase
{
    public function testGetId(): void
    {
        $command = new Search();
        static::assertSame('FT.SEARCH', $command->getId());
    }

    public function testGetIndex(): void
    {
        $command = new Search();
        $command->setIndex('idx');
        static::assertSame('idx', $command->getIndex());
    }

    public function testSizeOptions(): void
    {
        $command = new Search();
        static::assertNull($command->getSize());
        static::assertNull($command->getOffset());

        $command->setLimit(0, 10);
        static::assertSame(0, $command->getOffset());
        static::assertSame(10, $command->getSize());
    }

    public function testFullOption(): void
    {
        $command = new Search(AbstractCommand::MAX_IMPLEMENTED_VERSION);
        $command
            ->setIndex('idx')
            ->setQuery('@text1:"hello world"')
            ->setNoContent()
            ->setNoStopWords()
            ->setVerbatim()
            ->setWithSortKeys()
            ->setWithPayloads()
            ->setWithScores()
            ->addFilter(new FilterOption('@num1', 10, 20))
            ->setGeoFilter('@geo1', 37.37767452939122, -122.06451753037919, 50, 'm')
            ->setInKeys('@text1', '@text2')
            ->setInFields('@text4')
            ->setReturn('@text3', '@text4', '@text5')
            ->setSummarize(['@text1', '@text3'], 2, 4, ' ... ')
            ->setHighlight(['@text4'], '<em>', '</em>')
            ->setSlop(2)
            ->setInOrder()
            ->setLanguage('english')
            ->setExpander('my_expander')
            ->setScorer('my_scorer')
            ->setExplainScore()
            ->setPayload('foobar')
            ->setSortBy('@num1', 'ASC')
            ->setLimit(12, 10)
        ;

        static::assertSame([
            'idx',
            '@text1:"hello world"',
            'NOCONTENT',
            'VERBATIM',
            'NOSTOPWORDS',
            'WITHSCORES',
            'WITHPAYLOADS',
            'WITHSORTKEYS',
            'FILTER', '@num1', '10', '20',
            'GEOFILTER', '@geo1', 37.37767452939122, -122.06451753037919, 50.0, 'm',
            'INKEYS', 2, '@text1', '@text2',
            'INFIELDS', 1, '@text4',
            'RETURN', 3, '@text3', '@text4', '@text5',
            'SUMMARIZE', 'FIELDS', 2, '@text1', '@text3', 'FRAGS', 2, 'LEN', 4, 'SEPARATOR', ' ... ',
            'HIGHLIGHT', 'FIELDS', 1, '@text4', 'TAGS', '<em>', '</em>',
            'SLOP', 2, 'INORDER',
            'LANGUAGE', 'english',
            'EXPANDER', 'my_expander',
            'SCORER', 'my_scorer',
            'EXPLAINSCORE',
            'PAYLOAD', 'foobar',
            'SORTBY', '@num1', 'ASC',
            'LIMIT', 12, 10,
        ], $command->getArguments());
    }

    public function testNoSummarizeAndNoHighlight(): void
    {
        $command = new Search(AbstractCommand::MAX_IMPLEMENTED_VERSION);
        $command
            ->setIndex('idx')
            ->setQuery('@text1:"hello world"')
            ->setLimit(12, 10)
        ;

        static::assertSame([
            'idx',
            '@text1:"hello world"',
            'LIMIT', 12, 10,
        ], $command->getArguments());
    }

    public function testParsingResponseNoContent(): void
    {
        $redisResponse = [
            3,
            'hash_1',
            'hash_2',
            'hash_3',
        ];

        $command = new Search(AbstractCommand::MAX_IMPLEMENTED_VERSION);
        $command->setQuery('*');
        $command->setNoContent();

        $expectedResponse = new PaginatedResponse($command, 3, [
            new SearchResponseItem('hash_1'),
            new SearchResponseItem('hash_2'),
            new SearchResponseItem('hash_3'),
        ]);

        $actualResponse = $command->parseResponse($redisResponse);

        static::assertEquals($expectedResponse, $actualResponse);
    }

    public function testParsingResponseScore(): void
    {
        $redisResponse = [
            3,
            'hash_1',
            '0.741',
            ['field', '1'],
            'hash_2',
            '0.654',
            ['field', '2'],
            'hash_3',
            '0.258',
            ['field', '3'],
        ];

        $command = new Search(AbstractCommand::MAX_IMPLEMENTED_VERSION);
        $command->setQuery('*');
        $command->setWithScores();

        $expectedResponse = new PaginatedResponse($command, 3, [
            new SearchResponseItem('hash_1', ['field' => '1'], 0.741),
            new SearchResponseItem('hash_2', ['field' => '2'], 0.654),
            new SearchResponseItem('hash_3', ['field' => '3'], 0.258),
        ]);

        $actualResponse = $command->parseResponse($redisResponse);

        static::assertEquals($expectedResponse, $actualResponse);
    }

    public function testParsingResponseScoreAndPayload(): void
    {
        $redisResponse = [
            3,
            'hash_1',
            '0.741',
            null,
            ['field', '1'],
            'hash_2',
            '0.654',
            null,
            ['field', '2', 'other', 'field'],
            'hash_3',
            '0.258',
            'third',
            ['field', '3'],
        ];

        $command = new Search(AbstractCommand::MAX_IMPLEMENTED_VERSION);
        $command->setQuery('*');
        $command->setWithScores();
        $command->setWithPayloads();

        $expectedResponse = new PaginatedResponse($command, 3, [
            new SearchResponseItem('hash_1', ['field' => '1'], 0.741),
            new SearchResponseItem('hash_2', ['field' => '2', 'other' => 'field'], 0.654),
            new SearchResponseItem('hash_3', ['field' => '3'], 0.258, 'third'),
        ]);

        $actualResponse = $command->parseResponse($redisResponse);

        static::assertEquals($expectedResponse, $actualResponse);
    }
}
