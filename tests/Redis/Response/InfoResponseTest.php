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

namespace MacFJA\RediSearch\tests\Redis\Response;

use BadMethodCallException;
use InvalidArgumentException;
use MacFJA\RediSearch\Redis\Command\CreateCommand\GeoFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\NumericFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TagFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption;
use MacFJA\RediSearch\Redis\Command\Info;
use MacFJA\RediSearch\Redis\Response\InfoResponse;
use PHPUnit\Framework\TestCase;
use Predis\Response\Status;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\AbstractCommand::parseResponse
 * @covers \MacFJA\RediSearch\Redis\Command\Info
 * @covers \MacFJA\RediSearch\Redis\Response\InfoResponse
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\AbstractCommand::__construct
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\GeoFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\NumericFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\TagFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionAwareTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 *
 * @internal
 */
class InfoResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $rawResponse = [
            0 => new Status('index_name'),
            1 => new Status('idxt'),
            2 => new Status('index_options'),
            3 => [],
            4 => new Status('index_definition'),
            5 => [
                0 => new Status('key_type'),
                1 => new Status('HASH'),
                2 => new Status('prefixes'),
                3 => [0 => new Status('test')],
                4 => new Status('language_field'),
                5 => new Status('__language'),
                6 => new Status('default_score'),
                7 => '1',
                8 => new Status('score_field'),
                9 => new Status('__score'),
                10 => new Status('payload_field'),
                11 => new Status('__payload'),
            ],
            6 => new Status('fields'),
            7 => [
                0 => [
                    0 => new Status('text'),
                    1 => new Status('type'),
                    2 => new Status('TEXT'),
                    3 => new Status('WEIGHT'),
                    4 => '1',
                ],
                1 => [
                    0 => new Status('labels'),
                    1 => new Status('type'),
                    2 => new Status('TAG'),
                ],
                2 => [
                    0 => new Status('place'),
                    1 => new Status('type'),
                    2 => new Status('GEO'),
                ],
                3 => [
                    0 => new Status('count'),
                    1 => new Status('type'),
                    2 => new Status('NUMERIC'),
                    3 => new Status('NOINDEX'),
                ],
            ],
            8 => new Status('num_docs'),
            9 => '4',
            10 => new Status('max_doc_id'),
            11 => '12',
            12 => new Status('num_terms'),
            13 => '4',
            14 => new Status('num_records'),
            15 => '30',
            16 => new Status('inverted_sz_mb'),
            17 => '0.000171661376953125',
            18 => new Status('total_inverted_index_blocks'),
            19 => '1801',
            20 => new Status('offset_vectors_sz_mb'),
            21 => '2.86102294921875e-05',
            22 => new Status('doc_table_size_mb'),
            23 => '0.000995635986328125',
            24 => new Status('sortable_values_size_mb'),
            25 => '0',
            26 => new Status('key_table_size_mb'),
            27 => '0.00012493133544921875',
            28 => new Status('records_per_doc_avg'),
            29 => '7.5',
            30 => new Status('bytes_per_record_avg'),
            31 => '6',
            32 => new Status('offsets_per_term_avg'),
            33 => '1',
            34 => new Status('offset_bits_per_record_avg'),
            35 => '8',
            36 => new Status('hash_indexing_failures'),
            37 => '0',
            38 => new Status('indexing'),
            39 => '0',
            40 => new Status('percent_indexed'),
            41 => '1',
            42 => new Status('gc_stats'),
            43 => [
                0 => new Status('bytes_collected'),
                1 => '0',
                2 => new Status('total_ms_run'),
                3 => '0',
                4 => new Status('total_cycles'),
                5 => '0',
                6 => new Status('average_cycle_time_ms'),
                7 => '-nan',
                8 => new Status('last_run_time_ms'),
                9 => '0',
                10 => new Status('gc_numeric_trees_missed'),
                11 => '0',
                12 => new Status('gc_blocks_denied'),
                13 => '0',
            ],
            44 => new Status('cursor_stats'),
            45 => [
                0 => new Status('global_idle'),
                1 => 63,
                2 => new Status('global_total'),
                3 => 63,
                4 => new Status('index_capacity'),
                5 => 128,
                6 => new Status('index_total'),
                7 => 63,
            ],
        ];
        $command = new Info();

        /** @var InfoResponse $parsed */
        $parsed = $command->parseResponse($rawResponse);

        static::assertSame('idxt', $parsed->getIndexName());
        static::assertSame(4, $parsed->getNumDocs());
        static::assertSame(12, $parsed->getMaxDocId());
        static::assertSame(4, $parsed->getNumTerms());
        static::assertSame(30, $parsed->getNumRecords());
        static::assertSame(0.000171661376953125, $parsed->getInvertedSzMb());
        static::assertSame(1801, $parsed->getTotalInvertedIndexBlocks());
        static::assertSame(2.86102294921875e-05, $parsed->getOffsetVectorsSzMb());
        static::assertSame(0.000995635986328125, $parsed->getDocTableSizeMb());
        static::assertSame(0.0, $parsed->getSortableValuesSizeMb());
        static::assertSame(0.00012493133544921875, $parsed->getKeyTableSizeMb());
        static::assertSame(7.5, $parsed->getRecordsPerDocAvg());
        static::assertSame(6.0, $parsed->getBytesPerRecordAvg());
        static::assertSame(1.0, $parsed->getOffsetsPerTermAvg());
        static::assertSame(8.0, $parsed->getOffsetBitsPerRecordAvg());
        static::assertSame(0, $parsed->getHashIndexingFailures());
        static::assertFalse($parsed->getIndexing());
        static::assertSame(1.0, $parsed->getPercentIndexed());

        $this->doTestGetFieldsAsOption($parsed);
        $this->doTestGetIndexDefinition($parsed);
    }

    public function doTestGetFieldsAsOption(InfoResponse $parsed): void
    {
        $response = $parsed->getFieldsAsOption();

        static::assertSame(['text', 'TEXT', 'WEIGHT', '1'], $response[0]->render());
        static::assertInstanceOf(TextFieldOption::class, $response[0]);
        static::assertSame(['labels', 'TAG'], $response[1]->render());
        static::assertInstanceOf(TagFieldOption::class, $response[1]);
        static::assertSame(['place', 'GEO'], $response[2]->render());
        static::assertInstanceOf(GeoFieldOption::class, $response[2]);
        static::assertSame(['count', 'NUMERIC', 'NOINDEX'], $response[3]->render());
        static::assertInstanceOf(NumericFieldOption::class, $response[3]);
    }

    public function doTestGetIndexDefinition(InfoResponse $parsed): void
    {
        static::assertEquals(
            [
                'key_type' => new Status('HASH'),
                'prefixes' => [0 => new Status('test')],
                'language_field' => new Status('__language'),
                'default_score' => '1',
                'score_field' => new Status('__score'),
                'payload_field' => new Status('__payload'),
            ],
            $parsed->getIndexDefinition()
        );

        static::assertSame(
            'HASH',
            (string) $parsed->getIndexDefinition('key_type')
        );
        static::assertSame(
            1.0,
            (float) $parsed->getIndexDefinition('default_score')
        );
    }

    public function testUnknownKey(): void
    {
        $response = new InfoResponse([]);
        $this->expectException(InvalidArgumentException::class);

        $response->getHelloWorld(); // @phpstan-ignore-line
    }

    public function testUnknownBadNaming1(): void
    {
        $response = new InfoResponse([]);
        $this->expectException(BadMethodCallException::class);

        $response->gethelloWorld(); // @phpstan-ignore-line
    }

    public function testUnknownBadNaming2(): void
    {
        $response = new InfoResponse([]);
        $this->expectException(BadMethodCallException::class);

        $response->gotHelloWorld(); // @phpstan-ignore-line
    }

    public function testNotANumber(): void
    {
        $response = new InfoResponse(['num_docs', '-nan']);

        static::assertNull($response->getNumDocs());
    }

    public function testDataCasting(): void
    {
        $response = new InfoResponse([
            'records_per_doc_avg', '1.5',
            'max_doc_id', '50',
            'index_name', 'idx',
            'indexing', '1',
            'non_existent_string', 'hello',
            'non_existent_float', 1.2,
            'non_existent_int', 1,
        ]);

        static::assertSame(1.5, $response->getRecordsPerDocAvg());
        static::assertSame(50, $response->getMaxDocId());
        static::assertSame('idx', $response->getIndexName());
        static::assertTrue($response->getIndexing());
        // @phpstan-ignore-next-line
        static::assertSame('hello', $response->getNonExistentString());
        // @phpstan-ignore-next-line
        static::assertSame(1.2, $response->getNonExistentFloat());
        // @phpstan-ignore-next-line
        static::assertSame(1, $response->getNonExistentInt());
    }

    public function testMissingIndexDefinition(): void
    {
        $response = new InfoResponse([]);
        static::assertNull($response->getIndexDefinition('foobar'));
        static::assertSame([], $response->getIndexDefinition());
        static::assertEmpty($response->getIndexDefinition());
    }
}
