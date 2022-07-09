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

namespace MacFJA\RediSearch\tests\Redis\Command\CreateCommand;

use InvalidArgumentException;
use MacFJA\RediSearch\Redis\Command\CreateCommand\VectorFieldOption;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
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
 * @uses \MacFJA\RediSearch\Redis\Command\Option\OptionListOption
 *
 * @internal
 */
class VectorFieldOptionTest extends TestCase
{
    public function testGetFieldName(): void
    {
        $option = new VectorFieldOption();
        $option->setField('foo');

        static::assertSame('foo', $option->getFieldName());
    }

    public function testAddFlatAttributeWrongAlgorithm(): void
    {
        $this->expectException(RuntimeException::class);
        $option = new VectorFieldOption();
        $option->setAlgorithm(VectorFieldOption::ALGORITHM_HNSW);
        $option->addFlatAttribute(VectorFieldOption::TYPE_FLOAT32, 10, VectorFieldOption::DISTANCE_METRIC_IP);
    }

    public function testAddFlatAttribute(): void
    {
        $option = new VectorFieldOption();
        $option->setField('vec');
        $option->setAlgorithm(VectorFieldOption::ALGORITHM_FLAT);
        $option->addFlatAttribute(VectorFieldOption::TYPE_FLOAT32, 10, VectorFieldOption::DISTANCE_METRIC_IP);
        static::assertSame(['vec', 'VECTOR', 'FLAT',  6, 'TYPE', 'FLOAT32', 'DIM', 10, 'DISTANCE_METRIC', 'IP'], $option->render('2.4.0'));

        $option = new VectorFieldOption();
        $option->setField('vec');
        $option->setAlgorithm(VectorFieldOption::ALGORITHM_FLAT);
        $option->addFlatAttribute(VectorFieldOption::TYPE_FLOAT32, 128, VectorFieldOption::DISTANCE_METRIC_COSINE, 1000);
        static::assertSame(['vec', 'VECTOR', 'FLAT', 8, 'TYPE', 'FLOAT32', 'DIM', 128, 'DISTANCE_METRIC', 'COSINE', 'INITIAL_CAP', 1000], $option->render('2.4.0'));

        $option = new VectorFieldOption();
        $option->setField('vec');
        $option->setAlgorithm(VectorFieldOption::ALGORITHM_FLAT);
        $option->addFlatAttribute(VectorFieldOption::TYPE_FLOAT32, 128, VectorFieldOption::DISTANCE_METRIC_COSINE, 1000, 10);
        static::assertSame(['vec', 'VECTOR', 'FLAT', 10, 'TYPE', 'FLOAT32', 'DIM', 128, 'DISTANCE_METRIC', 'COSINE', 'INITIAL_CAP', 1000, 'BLOCK_SIZE', 10], $option->render('2.4.0'));
    }

    public function testAddHnswAttributeWrongAlgorithm(): void
    {
        $this->expectException(RuntimeException::class);
        $option = new VectorFieldOption();
        $option->setAlgorithm(VectorFieldOption::ALGORITHM_FLAT);
        $option->addHnswAttribute(VectorFieldOption::TYPE_FLOAT32, 10, VectorFieldOption::DISTANCE_METRIC_L2);
    }

    public function testAddHnswAttribute(): void
    {
        $option = new VectorFieldOption();
        $option->setField('vec');
        $option->setAlgorithm(VectorFieldOption::ALGORITHM_HNSW);
        $option->addHnswAttribute(VectorFieldOption::TYPE_FLOAT32, 10, VectorFieldOption::DISTANCE_METRIC_IP);
        static::assertSame(['vec', 'VECTOR', 'HNSW',  6, 'TYPE', 'FLOAT32', 'DIM', 10, 'DISTANCE_METRIC', 'IP'], $option->render('2.4.0'));

        $option = new VectorFieldOption();
        $option->setField('vec');
        $option->setAlgorithm(VectorFieldOption::ALGORITHM_HNSW);
        $option->addHnswAttribute(
            VectorFieldOption::TYPE_FLOAT32,
            10,
            VectorFieldOption::DISTANCE_METRIC_IP,
            1000,
            10,
            500,
            50
        );
        static::assertSame(['vec', 'VECTOR', 'HNSW',  14, 'TYPE', 'FLOAT32', 'DIM', 10, 'DISTANCE_METRIC', 'IP', 'INITIAL_CAP', 1000, 'M', 10, 'EF_CONSTRUCTION', 500, 'EF_RUNTIME', 50], $option->render('2.4.0'));
    }

    /**
     * @phpstan-param class-string<\Throwable> $expectedErrorClass
     *
     * @dataProvider validateArgumentDataProvider
     */
    public function testValidateArguments(string $expectedErrorClass, string $algo, string $type, int $dimension, string $distanceMetric, ?int $initialCapacity, ?int $blockSize, ?int $maxEdge, ?int $efConstruction, ?int $efRuntime): void
    {
        $this->expectException($expectedErrorClass);
        $option = new VectorFieldOption();
        $option->setField('vec');
        $option->setAlgorithm($algo);
        $option->addAttribute($type, $dimension, $distanceMetric, $initialCapacity, $blockSize, $maxEdge, $efConstruction, $efRuntime);
    }

    /**
     * @return array<array<int|string>>
     */
    public function validateArgumentDataProvider(): array
    {
        $rangeDefault = [
            0 => OutOfRangeException::class, // $expectedErrorClass
            1 => VectorFieldOption::ALGORITHM_HNSW, // $algo
            2 => VectorFieldOption::TYPE_FLOAT32, // $type
            3 => 10, // $dimension
            4 => VectorFieldOption::DISTANCE_METRIC_L2, // $distanceMetric
            5 => 100, // $initialCapacity
            6 => 10, // $blockSize
            7 => 20, // $maxEdge
            8 => 200, // $efConstruction
            9 => 100, // $efRuntime
        ];
        $invalidDefault = [
            0 => InvalidArgumentException::class, // $expectedErrorClass
            1 => VectorFieldOption::ALGORITHM_HNSW, // $algo
            2 => VectorFieldOption::TYPE_FLOAT32, // $type
            3 => 10, // $dimension
            4 => VectorFieldOption::DISTANCE_METRIC_L2, // $distanceMetric
            5 => 100, // $initialCapacity
            6 => 10, // $blockSize
            7 => 20, // $maxEdge
            8 => 200, // $efConstruction
            9 => 100, // $efRuntime
        ];

        $sort = static function (array $source): array {
            ksort($source);

            return $source;
        };

        return [
            $sort([2 => 'foo'] + $invalidDefault),
            $sort([3 => 0] + $rangeDefault),
            $sort([4 => 'bar'] + $invalidDefault),
            $sort([5 => 0] + $rangeDefault),
            $sort([6 => 0, 1 => VectorFieldOption::ALGORITHM_FLAT] + $rangeDefault),
            $sort([7 => -1] + $rangeDefault),
            $sort([8 => 0] + $rangeDefault),
            $sort([9 => 0] + $rangeDefault),
        ];
    }
}
