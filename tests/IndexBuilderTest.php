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

use BadMethodCallException;
use InvalidArgumentException;
use MacFJA\RediSearch\IndexBuilder;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command\Create;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\VectorFieldOption;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \MacFJA\RediSearch\IndexBuilder
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Create
 * @uses \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NumberedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\OptionListOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\BaseCreateFieldOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\GeoFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\NumericFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\TagFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\JSONFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\VectorFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionAwareTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\WithPublicGroupedSetterTrait
 *
 * @group default
 *
 * @internal
 */
class IndexBuilderTest extends TestCase
{
    /**
     * @return array<Create|IndexBuilder>
     *
     * @medium
     */
    public function testAllSetter(): array
    {
        $createCommand = new Create();

        $builder = new IndexBuilder();

        $builder->setIndex('city');
        static::assertNotEquals($createCommand, $builder->getCommand());
        $createCommand->setIndex('city');
        static::assertEquals($createCommand, $builder->getCommand());

        $resultInstance = $builder->setPrefixes(['city-', 'c-']);
        $createCommand->setPrefixes('city-', 'c-');
        static::assertSame($builder, $resultInstance);

        $builder->setStopWords(['hello', 'world']);
        $createCommand->setStopWords('hello', 'world');

        $builder->setSkipInitialScan(true);
        $createCommand->setSkipInitialScan(true);

        $builder->setNoFrequencies(true);
        $createCommand->setNoFrequencies(true);

        $builder->setNoFields(true);
        $createCommand->setNoFields(true);

        $builder->setNoHighLight(true);
        $createCommand->setNoHighLight(true);

        $builder->setTemporary(20);
        $createCommand->setTemporary(20);

        $builder->setNoOffsets(true);
        $createCommand->setNoOffsets(true);

        $builder->setDefaultLanguage('en');
        $createCommand->setDefaultLanguage('en');

        $builder->setDefaultScore(1.3);
        $createCommand->setDefaultScore(1.3);

        $builder->setMaxTextFields(true);
        $createCommand->setMaxTextFields(true);

        $builder->setPayloadField('payload');
        $createCommand->setPayloadField('payload');

        $builder->setScoreField('score');
        $createCommand->setScoreField('score');

        $builder->setLanguageField('lang');
        $createCommand->setLanguageField('lang');

        $builder->setFilter('startswith(@name, "G")');
        $createCommand->setFilter('startswith(@name, "G")');

        $builder->setStructure('JSON');
        $createCommand->setStructure('JSON');

        $builder->setFields([(new TextFieldOption())->setField('name')]);
        $createCommand->addTextField('name');
        static::assertEquals($createCommand, $builder->getCommand());

        return [$builder, $createCommand];
    }

    /**
     * @medium
     */
    public function testAllWith(): void
    {
        $createCommand = new Create();

        $builder = new IndexBuilder();

        $builder->setIndex('city');
        $createCommand->setIndex('city');

        $oldBuilder = $builder;
        $builder = $builder->withPrefixes(['city-', 'c-']);
        static::assertNotEquals($createCommand, $builder->getCommand());
        $createCommand->setPrefixes('city-', 'c-');
        static::assertEquals($createCommand, $builder->getCommand());
        static::assertNotSame($oldBuilder, $builder);

        $builder = $builder->withStopWords(['hello', 'world']);
        $createCommand->setStopWords('hello', 'world');

        $builder = $builder->withSkipInitialScan(true);
        $createCommand->setSkipInitialScan(true);

        $builder = $builder->withNoFrequencies(true);
        $createCommand->setNoFrequencies(true);

        $builder = $builder->withNoFields(true);
        $createCommand->setNoFields(true);

        $builder = $builder->withNoHighLight(true);
        $createCommand->setNoHighLight(true);

        $builder = $builder->withTemporary(20);
        $createCommand->setTemporary(20);

        $builder = $builder->withNoOffsets(true);
        $createCommand->setNoOffsets(true);

        $builder = $builder->withDefaultLanguage('en');
        $createCommand->setDefaultLanguage('en');

        $builder = $builder->withDefaultScore(1.3);
        $createCommand->setDefaultScore(1.3);

        $builder = $builder->withMaxTextFields(true);
        $createCommand->setMaxTextFields(true);

        $builder = $builder->withPayloadField('payload');
        $createCommand->setPayloadField('payload');

        $builder = $builder->withScoreField('score');
        $createCommand->setScoreField('score');

        $builder = $builder->withLanguageField('lang');
        $createCommand->setLanguageField('lang');

        $builder = $builder->withFilter('startswith(@name, "G")');
        $createCommand->setFilter('startswith(@name, "G")');

        $builder = $builder->withStructure('JSON');
        $createCommand->setStructure('JSON');

        $builder = $builder->withFields([(new TextFieldOption())->setField('name')]);
        $createCommand->addTextField('name');
        static::assertEquals($createCommand, $builder->getCommand());
    }

    public function testNoStopWords(): void
    {
        $createCommand = new Create();

        $builder = new IndexBuilder();

        $builder->setIndex('city');
        $createCommand->setIndex('city');

        $builder->setNoStopWords();
        $createCommand->setNoStopWords();

        static::assertEquals($createCommand, $builder->getCommand());
    }

    public function testAllAdd(): void
    {
        $createCommand = new Create();

        $builder = new IndexBuilder();

        $builder->setIndex('city');
        $createCommand->setIndex('city');

        $oldBuilder = $builder;
        $builder = $builder->addPrefixes('city-', 'c-');
        static::assertNotEquals($createCommand, $builder->getCommand());
        $createCommand->setPrefixes('city-', 'c-');
        static::assertEquals($createCommand, $builder->getCommand());
        static::assertSame($oldBuilder, $builder);

        $builder->addStopWords('hello', 'world');
        $createCommand->setStopWords('hello', 'world');

        $builder->addFields((new TextFieldOption())->setField('name'));
        $createCommand->addTextField('name');

        $builder->addField((new TextFieldOption())->setField('country'));
        $createCommand->addTextField('country');

        $builder->addTextField('continent');
        $createCommand->addTextField('continent');

        $builder->addNumericField('population');
        $createCommand->addNumericField('population');

        $builder->addGeoField('gps');
        $createCommand->addGeoField('gps');

        $builder->addTagField('languages');
        $createCommand->addTagField('languages');
        static::assertEquals($createCommand, $builder->getCommand());
    }

    public function testAllJsonAdd(): void
    {
        $createCommand = new Create();

        $builder = new IndexBuilder();

        $builder->setIndex('city');
        $createCommand->setIndex('city');

        $oldBuilder = $builder;
        $builder = $builder->addPrefixes('city-', 'c-');
        static::assertNotEquals($createCommand, $builder->getCommand());
        $createCommand->setPrefixes('city-', 'c-');
        static::assertEquals($createCommand, $builder->getCommand());
        static::assertSame($oldBuilder, $builder);

        $builder->addStopWords('hello', 'world');
        $createCommand->setStopWords('hello', 'world');

        $builder->addJSONField('$.city.name', (new TextFieldOption())->setField('name'));
        $createCommand->addJSONTextField('$.city.name', 'name');

        $builder->addJSONField('$.city.country', (new TextFieldOption())->setField('country'));
        $createCommand->addJSONTextField('$.city.country', 'country');

        $builder->addJSONTextField('$.city.continent', 'continent');
        $createCommand->addJSONTextField('$.city.continent', 'continent');

        $builder->addJSONNumericField('$.city.population', 'population');
        $createCommand->addJSONNumericField('$.city.population', 'population');

        $builder->addJSONGeoField('$.city.gps', 'gps');
        $createCommand->addJSONGeoField('$.city.gps', 'gps');

        $builder->addJSONTagField('$.city.languages', 'languages');
        $createCommand->addJSONTagField('$.city.languages', 'languages');

        $builder->addJSONVectorField('$.city.vec', 'vec', VectorFieldOption::ALGORITHM_FLAT, VectorFieldOption::TYPE_FLOAT32, 10, VectorFieldOption::DISTANCE_METRIC_L2);
        $createCommand->addJSONVectorField('$.city.vec', 'vec', VectorFieldOption::ALGORITHM_FLAT, VectorFieldOption::TYPE_FLOAT32, 10, VectorFieldOption::DISTANCE_METRIC_L2);
        static::assertEquals($createCommand, $builder->getCommand());
    }

    /**
     * @medium
     */
    public function testAllWithAdded(): void
    {
        $createCommand = new Create();

        $builder = new IndexBuilder();

        $builder->setIndex('city');
        $createCommand->setIndex('city');
        static::assertEquals($createCommand, $builder->getCommand());

        $oldBuilder = $builder;
        $builder = $builder->withAddedPrefixes('city-', 'c-');
        $createCommand->setPrefixes('city-', 'c-');
        static::assertEquals($createCommand, $builder->getCommand());
        static::assertNotSame($oldBuilder, $builder);

        $builder = $builder->withAddedStopWords('hello', 'world');
        $createCommand->setStopWords('hello', 'world');
        static::assertEquals($createCommand, $builder->getCommand());

        $builder = $builder->withAddedFields((new TextFieldOption())->setField('name'));
        $createCommand->addTextField('name');
        static::assertEquals($createCommand, $builder->getCommand());

        $builder = $builder->withAddedTextField('continent');
        $createCommand->addTextField('continent');
        static::assertEquals($createCommand, $builder->getCommand());

        $builder = $builder->withAddedNumericField('population');
        $createCommand->addNumericField('population');
        static::assertEquals($createCommand, $builder->getCommand());

        $builder = $builder->withAddedGeoField('gps');
        $createCommand->addGeoField('gps');
        static::assertEquals($createCommand, $builder->getCommand());

        $builder = $builder->withAddedTagField('languages');
        $createCommand->addTagField('languages');
        static::assertEquals($createCommand, $builder->getCommand());
    }

    public function testUnexistingMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call undefined foobar method');

        $builder = new IndexBuilder();
        // @phpstan-ignore-next-line
        $builder->foobar();
    }

    public function testUnexistingMethod2(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call undefined setHelloWorld method');

        $builder = new IndexBuilder();
        // @phpstan-ignore-next-line
        $builder->setHelloWorld();
    }

    public function testUnexistingMethodAdd(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call undefined addHelloWorld method');

        $builder = new IndexBuilder();
        // @phpstan-ignore-next-line
        $builder->addHelloWorld();
    }

    public function testInvalidAdd(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('addFields method need at least one parameter');

        $builder = new IndexBuilder();
        $builder->addFields();
    }

    public function testMissingIndex(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The index name is missing');

        $builder = new IndexBuilder();
        $builder->getCommand();
    }

    public function testCreate(): void
    {
        /**
         * @var IndexBuilder $builder
         * @var Create       $createCommand
         */
        [$builder, $createCommand] = $this->testAllSetter();
        $client = $this->createMock(Client::class);
        $client->expects(static::once())->method('execute')->with($createCommand);
        $builder->create($client);
    }
}
