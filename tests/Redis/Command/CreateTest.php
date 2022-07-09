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

use InvalidArgumentException;
use MacFJA\RediSearch\Redis\Command\AbstractCommand;
use MacFJA\RediSearch\Redis\Command\Create;
use MacFJA\RediSearch\Redis\Command\CreateCommand\JSONFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TagFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\VectorFieldOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @covers \MacFJA\RediSearch\Redis\Command\Create
 * @covers \MacFJA\RediSearch\Redis\Command\CreateCommand\GeoFieldOption
 * @covers \MacFJA\RediSearch\Redis\Command\CreateCommand\JSONFieldOption
 * @covers \MacFJA\RediSearch\Redis\Command\CreateCommand\NumericFieldOption
 * @covers \MacFJA\RediSearch\Redis\Command\CreateCommand\TagFieldOption
 * @covers \MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption
 * @covers \MacFJA\RediSearch\Redis\Command\CreateCommand\VectorFieldOption
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
 * @uses \MacFJA\RediSearch\Redis\Command\Option\OptionListOption
 *
 * @internal
 */
class CreateTest extends TestCase
{
    public function testGetArguments(): void
    {
        $command = new Create();
        $command->setDefaultLanguage('french')
            ->setIndex('idx')
            ->setPrefixes('document_', 'doc_')
            ->addTextField('title', false, null, null, true)
            ->addTextField('subtitle', true, null, null, false, true)
            ->addGeoField('city')
            ->addNumericField('views', true)
        ;

        static::assertSame([
            'idx',
            'PREFIX', 2,  'document_', 'doc_',
            'LANGUAGE', 'french',
            'SCHEMA',
            'title', 'TEXT', 'SORTABLE',
            'subtitle', 'TEXT', 'NOSTEM', 'NOINDEX',
            'city', 'GEO',
            'views', 'NUMERIC', 'SORTABLE',
        ], $command->getArguments());
    }

    public function testGetId(): void
    {
        $command = new Create();
        static::assertSame('FT.CREATE', $command->getId());
    }

    public function testFullOption(): void
    {
        $command = new Create(AbstractCommand::MAX_IMPLEMENTED_VERSION);
        $command
            ->addNumericField('num1')
            ->addNumericField('num2', false, true)
            ->addNumericField('num3', true, false)
            ->addNumericField('num4', true, true)
            ->addGeoField('geo1')
            ->addGeoField('geo2', true)
            ->addTextField('text1')
            ->addTextField('text2', true)
            ->addTextField('text3', false, 1.0)
            ->addTextField('text4', false, null, 'dm:en')
            ->addTextField('text5', false, null, null, true)
            ->addTextField('text6', false, null, null, false, true)
            ->addTextField('text7', true, 1.0, 'dm:fr', true, true)
            ->addTagField('tag1')
            ->addTagField('tag2', '|')
            ->addTagField('tag3', null, true)
            ->addTagField('tag4', null, false, true)
            ->addTagField('tag5', null, false, false, true)
            ->addTagField('tag6', '#', true, true, true)
            ->addVectorField('vec', VectorFieldOption::ALGORITHM_FLAT, VectorFieldOption::TYPE_FLOAT32, 10, VectorFieldOption::DISTANCE_METRIC_L2)
            ->addField(
                (new VectorFieldOption())
                    ->setField('vector_field')
                    ->setAlgorithm(VectorFieldOption::ALGORITHM_HNSW)
                    ->addHnswAttribute(VectorFieldOption::TYPE_FLOAT32, 128, VectorFieldOption::DISTANCE_METRIC_L2, 1000000, 40, 250, 20)
            )
            ->setPrefixes('doc_', 'document_')
            ->setIndex('idx')
            ->setDefaultLanguage('french')
            ->setDefaultScore(1.5)
            ->setFilter('startswith(@text1, "G")')
            ->setLanguageField('text6')
            ->setMaxTextFields(true)
            ->setNoFields(true)
            ->setNoFrequencies(true)
            ->setNoHighLight(true)
            ->setNoOffsets(true)
            ->setPayloadField('text4')
            ->setScoreField('num1')
            ->setSkipInitialScan(true)
            ->setStopWords('the', 'a')
            ->setTemporary(3600)
            ->setStructure('HASH')
        ;

        static::assertSame([
            'idx',
            'ON', 'HASH',
            'PREFIX', 2, 'doc_', 'document_',
            'FILTER', 'startswith(@text1, "G")',
            'LANGUAGE', 'french',
            'LANGUAGE_FIELD', 'text6',
            'SCORE', 1.5,
            'SCORE_FIELD', 'num1',
            'PAYLOAD_FIELD', 'text4',
            'MAXTEXTFIELDS', 'TEMPORARY', 3600, 'NOOFFSETS', 'NOHL', 'NOFIELDS', 'NOFREQS', 'SKIPINITIALSCAN',
            'STOPWORDS', 2, 'the', 'a',
            'SCHEMA',
            'num1', 'NUMERIC',
            'num2', 'NUMERIC', 'NOINDEX',
            'num3', 'NUMERIC', 'SORTABLE',
            'num4', 'NUMERIC', 'SORTABLE', 'NOINDEX',
            'geo1', 'GEO',
            'geo2', 'GEO', 'NOINDEX',
            'text1', 'TEXT',
            'text2', 'TEXT', 'NOSTEM',
            'text3', 'TEXT', 'WEIGHT', 1.0,
            'text4', 'TEXT', 'PHONETIC', 'dm:en',
            'text5', 'TEXT', 'SORTABLE',
            'text6', 'TEXT', 'NOINDEX',
            'text7', 'TEXT', 'NOSTEM', 'WEIGHT', 1.0, 'PHONETIC', 'dm:fr', 'SORTABLE', 'NOINDEX',
            'tag1', 'TAG',
            'tag2', 'TAG', 'SEPARATOR', '|',
            'tag3', 'TAG', 'SORTABLE',
            'tag4', 'TAG', 'NOINDEX',
            'tag5', 'TAG', 'CASESENSITIVE',
            'tag6', 'TAG', 'SEPARATOR', '#', 'CASESENSITIVE', 'SORTABLE', 'NOINDEX',
            'vec', 'VECTOR', 'FLAT', 6, 'TYPE', 'FLOAT32', 'DIM', 10, 'DISTANCE_METRIC', 'L2',
            'vector_field', 'VECTOR', 'HNSW', 14, 'TYPE', 'FLOAT32', 'DIM', 128, 'DISTANCE_METRIC', 'L2', 'INITIAL_CAP', 1000000, 'M', 40, 'EF_CONSTRUCTION', 250, 'EF_RUNTIME', 20,
        ], $command->getArguments());
    }

    public function testNoStopWords(): void
    {
        $command = new Create();
        $command->setIndex('idx')
            ->addTextField('foo')
            ->setNoStopWords()
        ;

        static::assertSame(['idx', 'STOPWORDS', 0, 'SCHEMA', 'foo', 'TEXT'], $command->getArguments());
    }

    public function testUnNormalizedForm(): void
    {
        $command = new Create('2.0.0');
        $command->setIndex('idx');

        $textField = new TextFieldOption();
        $textField->setField('foo')
            ->setSortable(true)
            ->setUnNormalizedSortable(true)
        ;

        $command->addField($textField);

        static::assertSame(['idx', 'SCHEMA', 'foo', 'TEXT', 'SORTABLE'], $command->getArguments());

        // Change version
        $command->setRediSearchVersion('2.0.12');
        static::assertSame(['idx', 'SCHEMA', 'foo', 'TEXT', 'SORTABLE', 'UNF'], $command->getArguments());

        // Remove sorting
        $textField->setSortable(false);
        static::assertSame(['idx', 'SCHEMA', 'foo', 'TEXT'], $command->getArguments());
    }

    public function testJSONStructure(): void
    {
        $expected = [
            'userIdx', 'ON', 'JSON', 'SCHEMA', '$.user.name', 'AS', 'name', 'TEXT', '$.user.tag', 'AS', 'country', 'TAG',
        ];
        $command = new Create('2.2.0');
        $command
            ->setIndex('userIdx')
            ->setStructure('JSON')
            ->addField(new JSONFieldOption('$.user.name', (new TextFieldOption())->setField('name')))
            ->addField(new JSONFieldOption('$.user.tag', (new TagFieldOption())->setField('country')))
        ;

        static::assertSame($expected, $command->getArguments());
    }

    public function testJSONStructureShortHand(): void
    {
        $expected = [
            'userIdx', 'ON', 'JSON', 'SCHEMA', '$.user.name', 'AS', 'name', 'TEXT', '$.user.tag', 'AS', 'country', 'TAG',
        ];
        $command = new Create('2.2.0');
        $command
            ->setIndex('userIdx')
            ->setStructure('JSON')
            ->addJSONField('$.user.name', (new TextFieldOption())->setField('name'))
            ->addJSONField('$.user.tag', (new TagFieldOption())->setField('country'))
        ;

        static::assertSame($expected, $command->getArguments());
    }

    public function testJSONStructureShortHand2(): void
    {
        $expected = [
            'userIdx', 'ON', 'JSON', 'SCHEMA', '$.user.name', 'AS', 'name', 'TEXT', '$.user.tag', 'AS', 'country', 'TAG',
        ];
        $command = new Create('2.2.0');
        $command
            ->setIndex('userIdx')
            ->setStructure('JSON')
            ->addJSONTextField('$.user.name', 'name')
            ->addJSONTagField('$.user.tag', 'country')
        ;

        static::assertSame($expected, $command->getArguments());
    }

    public function testJSONStructureWrongVersion(): void
    {
        $command = new Create('2.0.0');
        $command
            ->setIndex('userIdx')
            ->setStructure('JSON')
            ->addJSONTextField('$.user.name', 'name')
            ->addJSONTagField('$.user.tag', 'country')
        ;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing command option: fields');
        $command->getArguments();
    }
}
