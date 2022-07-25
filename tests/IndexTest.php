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

namespace MacFJA\RediSearch\tests;

use MacFJA\RediSearch\Index;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command\AbstractCommand;
use MacFJA\RediSearch\Redis\Command\AliasAdd;
use MacFJA\RediSearch\Redis\Command\AliasDel;
use MacFJA\RediSearch\Redis\Command\AliasUpdate;
use MacFJA\RediSearch\Redis\Command\Alter;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption;
use MacFJA\RediSearch\Redis\Command\DropIndex;
use MacFJA\RediSearch\Redis\Command\TagVals;
use MacFJA\RediSearch\Redis\Response\InfoResponse;

/**
 * @covers \MacFJA\RediSearch\Index
 *
 * @uses \MacFJA\RediSearch\Redis\Response\ArrayResponseTrait
 * @uses \MacFJA\RediSearch\Redis\Response\InfoResponse
 * @uses \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @uses \MacFJA\RediSearch\Redis\Command\AddFieldOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Alter
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\BaseCreateFieldOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionAwareTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\WithPublicGroupedSetterTrait
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\DropIndex
 * @uses \MacFJA\RediSearch\Redis\Command\AliasAdd
 * @uses \MacFJA\RediSearch\Redis\Command\AliasUpdate
 * @uses \MacFJA\RediSearch\Redis\Command\AliasDel
 * @uses \MacFJA\RediSearch\Redis\Command\TagVals
 *
 * @internal
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    public function testAddDocumentFromArray(): void
    {
        $expectedKey = 'manualKey';
        $client = $this->createMock(Client::class);
        $client->method('executeRaw')->withConsecutive(
            ['hset', static::stringStartsWith('doc:'), 'f1', 'foo', 'f2', 'bar'],
            ['hset', $expectedKey, 'f1', 'foo', 'f2', 'bar']
        );

        $index = $this->getMockBuilder(Index::class)
            ->setConstructorArgs(['idx', $client, AbstractCommand::MIN_IMPLEMENTED_VERSION])
            ->onlyMethods(['getInfo'])
            ->getMock()
        ;
        $normalInfoResponse = new InfoResponse([
            0 => 'index_name',
            1 => 'idxt',
            2 => 'index_options',
            3 => [],
            4 => 'index_definition',
            5 => [
                0 => 'key_type',
                1 => 'HASH',
                2 => 'prefixes',
                3 => [0 => 'doc:'],
                4 => 'language_field',
                5 => '__language',
                6 => 'default_score',
                7 => '1',
                8 => 'score_field',
                9 => '__score',
                10 => 'payload_field',
                11 => '__payload',
            ],
            6 => 'fields',
            7 => [
                0 => [
                    0 => 'f1',
                    1 => 'type',
                    2 => 'TEXT',
                    3 => 'WEIGHT',
                    4 => '1',
                ],
                1 => [
                    0 => 'f2',
                    1 => 'type',
                    2 => 'TEXT',
                ],
            ],
            8 => 'num_docs',
            9 => '4',
            10 => 'max_doc_id',
            11 => '12',
            12 => 'num_terms',
            13 => '4',
            14 => 'num_records',
            15 => '30',
            16 => 'inverted_sz_mb',
            17 => '0.000171661376953125',
            18 => 'total_inverted_index_blocks',
            19 => '1801',
            20 => 'offset_vectors_sz_mb',
            21 => '2.86102294921875e-05',
            22 => 'doc_table_size_mb',
            23 => '0.000995635986328125',
            24 => 'sortable_values_size_mb',
            25 => '0',
            26 => 'key_table_size_mb',
            27 => '0.00012493133544921875',
            28 => 'records_per_doc_avg',
            29 => '7.5',
            30 => 'bytes_per_record_avg',
            31 => '6',
            32 => 'offsets_per_term_avg',
            33 => '1',
            34 => 'offset_bits_per_record_avg',
            35 => '8',
            36 => 'hash_indexing_failures',
            37 => '0',
            38 => 'indexing',
            39 => '0',
            40 => 'percent_indexed',
            41 => '1',
            42 => 'gc_stats',
            43 => [
                0 => 'bytes_collected',
                1 => '0',
                2 => 'total_ms_run',
                3 => '0',
                4 => 'total_cycles',
                5 => '0',
                6 => 'average_cycle_time_ms',
                7 => '-nan',
                8 => 'last_run_time_ms',
                9 => '0',
                10 => 'gc_numeric_trees_missed',
                11 => '0',
                12 => 'gc_blocks_denied',
                13 => '0',
            ],
            44 => 'cursor_stats',
            45 => [
                0 => 'global_idle',
                1 => 63,
                2 => 'global_total',
                3 => 63,
                4 => 'index_capacity',
                5 => 128,
                6 => 'index_total',
                7 => 63,
            ],
        ]);
        $emptyInfoResponse = new InfoResponse([]);
        $index->method('getInfo')->willReturnOnConsecutiveCalls($normalInfoResponse, $normalInfoResponse, $emptyInfoResponse, $emptyInfoResponse);

        $generatedHash = $index->addDocumentFromArray(['f1' => 'foo', 'f2' => 'bar']);
        static::assertStringStartsWith('doc:', $generatedHash);

        $insertedKey = $index->addDocumentFromArray(['f1' => 'foo', 'f2' => 'bar'], $expectedKey);
        static::assertSame($expectedKey, $insertedKey);

        $insertedKey = $index->addDocumentFromArray(['f1' => 'foo', 'f2' => 'bar'], $expectedKey);
        static::assertSame($expectedKey, $insertedKey);

        $insertedKey = $index->addDocumentFromArray(['f1' => 'foo', 'f2' => 'bar']);
        static::assertStringStartsNotWith('doc:', $insertedKey);
    }

    public function testDeleteDocument(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(static::exactly(2))->method('executeRaw')->with('del', 'foobar')->willReturnOnConsecutiveCalls(1, 0);

        $index = new Index('idx', $client, AbstractCommand::MIN_IMPLEMENTED_VERSION);
        static::assertTrue($index->deleteDocument('foobar'));
        static::assertFalse($index->deleteDocument('foobar'));
    }

    public function testAddField(): void
    {
        $client = $this->createMock(Client::class);
        $expected = (new Alter(AbstractCommand::MIN_IMPLEMENTED_VERSION))->setIndex('idx')->addTextField('f3');
        $client->expects(static::exactly(2))->method('execute')->with($expected)->willReturnOnConsecutiveCalls('OK', 0);
        $index = new Index('idx', $client, AbstractCommand::MIN_IMPLEMENTED_VERSION);

        static::assertTrue($index->addField((new TextFieldOption())->setField('f3')));
        static::assertFalse($index->addField((new TextFieldOption())->setField('f3')));
    }

    public function testDelete(): void
    {
        $client = $this->createMock(Client::class);
        $normalExpectation = (new DropIndex(AbstractCommand::MIN_IMPLEMENTED_VERSION))->setIndex('idx');
        $withDocumentExpectation = (new DropIndex(AbstractCommand::MIN_IMPLEMENTED_VERSION))->setIndex('idx')->setDeleteDocument();
        $client
            ->expects(static::exactly(3))
            ->method('execute')
            ->withConsecutive([$normalExpectation], [$withDocumentExpectation], [$normalExpectation])
            ->willReturnOnConsecutiveCalls('OK', 'OK', 0)
        ;
        $index = new Index('idx', $client, AbstractCommand::MIN_IMPLEMENTED_VERSION);

        static::assertTrue($index->delete());
        static::assertTrue($index->delete(true));
        static::assertFalse($index->delete());
    }

    public function testAliases(): void
    {
        $client = $this->createMock(Client::class);
        $addExpectation = (new AliasAdd(AbstractCommand::MIN_IMPLEMENTED_VERSION))->setIndex('idx')->setAlias('foo');
        $updateExpectation = (new AliasUpdate(AbstractCommand::MIN_IMPLEMENTED_VERSION))->setIndex('idx')->setAlias('foo');
        $deleteExpectation = (new AliasDel(AbstractCommand::MIN_IMPLEMENTED_VERSION))->setAlias('foo');
        $client
            ->expects(static::exactly(6))
            ->method('execute')
            ->withConsecutive([$addExpectation], [$addExpectation], [$updateExpectation], [$updateExpectation], [$deleteExpectation], [$deleteExpectation])
            ->willReturnOnConsecutiveCalls('OK', 0, 'OK', 0, 'OK', 0)
        ;
        $index = new Index('idx', $client, AbstractCommand::MIN_IMPLEMENTED_VERSION);

        static::assertTrue($index->addAlias('foo'));
        static::assertFalse($index->addAlias('foo'));
        static::assertTrue($index->updateAlias('foo'));
        static::assertFalse($index->updateAlias('foo'));
        static::assertTrue($index->deleteAlias('foo'));
        static::assertFalse($index->deleteAlias('foo'));
    }

    public function testGetTagValues(): void
    {
        $client = $this->createMock(Client::class);
        $expectation = (new TagVals(AbstractCommand::MIN_IMPLEMENTED_VERSION))->setIndex('idx')->setField('f1');
        $expectedTags = ['foo', 'bar'];
        $client
            ->expects(static::once())
            ->method('execute')
            ->with($expectation)
            ->willReturn($expectedTags)
        ;
        $index = new Index('idx', $client, AbstractCommand::MIN_IMPLEMENTED_VERSION);

        static::assertSame($expectedTags, $index->getTagValues('f1'));
    }
}
