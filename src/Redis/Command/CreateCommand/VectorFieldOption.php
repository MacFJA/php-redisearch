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

namespace MacFJA\RediSearch\Redis\Command\CreateCommand;

use function in_array;
use InvalidArgumentException;
use function is_int;
use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption;
use MacFJA\RediSearch\Redis\Command\Option\GroupedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\Option\OptionListOption;
use MacFJA\RediSearch\Redis\Command\Option\WithPublicGroupedSetterTrait;
use OutOfRangeException;
use RuntimeException;

/**
 * @method VectorFieldOption setField(string $name)
 * @method VectorFieldOption setNoIndex(bool $active)
 * @method VectorFieldOption setAlgorithm(string $algorithm)
 */
class VectorFieldOption extends GroupedOption implements CreateCommandFieldOption
{
    use BaseCreateFieldOptionTrait;
    use WithPublicGroupedSetterTrait;

    public const ALGORITHM_FLAT = 'FLAT';
    public const ALGORITHM_HNSW = 'HNSW';
    public const TYPE_FLOAT32 = 'FLOAT32';
    public const DISTANCE_METRIC_L2 = 'L2';
    public const DISTANCE_METRIC_IP = 'IP';
    public const DISTANCE_METRIC_COSINE = 'COSINE';
    private const TYPES = [self::TYPE_FLOAT32];
    private const DISTANCE_METRICS = [self::DISTANCE_METRIC_IP, self::DISTANCE_METRIC_COSINE, self::DISTANCE_METRIC_L2];

    public function __construct()
    {
        parent::__construct($this->getConstructorOptions('VECTOR', [
            'algorithm' => CustomValidatorOption::allowedValues(new NamelessOption(null, '>=2.4.0'), [self::ALGORITHM_FLAT, self::ALGORITHM_HNSW]),
            'count' => CustomValidatorOption::isNumeric(new NamelessOption(null, '>=2.4.0')),
            'attributes' => new OptionListOption('>=2.4.0'),
        ]), ['field', 'algorithm', 'count', 'type'], ['type'], '>=2.4.0');
    }

    public function getFieldName(): string
    {
        return $this->getDataOfOption('field');
    }

    public function addFlatAttribute(string $type, int $dimension, string $distanceMetric, ?int $initialCapacity = null, ?int $blockSize = null): self
    {
        if (!(self::ALGORITHM_FLAT === $this->getDataOfOption('algorithm'))) {
            throw new RuntimeException();
        }

        return $this->addAttribute($type, $dimension, $distanceMetric, $initialCapacity, $blockSize);
    }

    public function addHnswAttribute(string $type, int $dimension, string $distanceMetric, ?int $initialCapacity = null, ?int $maxEdge = null, ?int $efConstruction = null, ?int $efRuntime = null): self
    {
        if (!(self::ALGORITHM_HNSW === $this->getDataOfOption('algorithm'))) {
            throw new RuntimeException();
        }

        return $this->addAttribute($type, $dimension, $distanceMetric, $initialCapacity, null, $maxEdge, $efConstruction, $efRuntime);
    }

    public function addAttribute(string $type, int $dimension, string $distanceMetric, ?int $initialCapacity = null, ?int $blockSize = null, ?int $maxEdge = null, ?int $efConstruction = null, ?int $efRuntime = null): self
    {
        $algorithm = $this->getDataOfOption('algorithm');

        if (self::ALGORITHM_FLAT === $algorithm) {
            $efRuntime = null;
            $efConstruction = null;
            $maxEdge = null;
        }
        if (self::ALGORITHM_HNSW === $algorithm) {
            $blockSize = null;
        }

        $this->validateArguments($type, $dimension, $distanceMetric, $initialCapacity, $blockSize, $maxEdge, $efConstruction, $efRuntime);

        $attribute = new GroupedOption([
            'type' => new NamedOption('TYPE', $type, '>=2.4.0'),
            'dim' => new NamedOption('DIM', $dimension, '>=2.4.0'),
            'distance_metric' => new NamedOption('DISTANCE_METRIC', $distanceMetric, '>=2.4.0'),
            'initial_cap' => new NamedOption('INITIAL_CAP', $initialCapacity, '>=2.4.0'),
            'block_size' => new NamedOption('BLOCK_SIZE', $blockSize, '>=2.4.0'),
            'm' => new NamedOption('M', $maxEdge, '>=2.4.0'),
            'ef_construction' => new NamedOption('EF_CONSTRUCTION', $efConstruction, '>=2.4.0'),
            'ef_runtime' => new NamedOption('EF_RUNTIME', $efRuntime, '>=2.4.0'),
        ], ['type', 'dim', 'distance_metric'], ['type', 'dim', 'distance_metric', 'initial_cap', 'block_size', 'm', 'ef_construction', 'ef_runtime']);

        /** @var array<GroupedOption> $options */
        $options = $this->getDataOfOption('attributes');
        $options[] = $attribute;
        $this->setDataOfOption('attributes', ...$options);

        $countAddition = 6
            + (is_int($initialCapacity) ? 2 : 0)
            + (is_int($blockSize) ? 2 : 0)
            + (is_int($maxEdge) ? 2 : 0)
            + (is_int($efConstruction) ? 2 : 0)
            + (is_int($efRuntime) ? 2 : 0)
        ;

        /** @var int $count */
        $count = $this->getDataOfOption('count') ?? 0;
        $count += $countAddition;
        $this->setDataOfOption('count', $count);

        return $this;
    }

    /**
     * @return string[]
     */
    protected function publicSetter(): array
    {
        return ['field', 'algorithm', 'no_index'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) -- Dynamic variable name
     */
    private function validateArguments(string $type, int $dimension, string $distanceMetric, ?int $initialCapacity = null, ?int $blockSize = null, ?int $maxEdge = null, ?int $efConstruction = null, ?int $efRuntime = null): void
    {
        $atLeastOne = ['dimension', 'initialCapacity', 'blockSize', 'efConstruction', 'efRuntime'];

        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not a known type (expect one of %s)',
                $type,
                implode(', ', self::TYPES)
            ));
        }

        foreach ($atLeastOne as $varName) {
            if (is_int(${$varName}) && ${$varName} < 1) {
                throw new OutOfRangeException(sprintf('$%s must be greater than 0', $varName));
            }
        }

        if (!in_array($distanceMetric, self::DISTANCE_METRICS, true)) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not a known distance metric (expect one of %s)',
                $distanceMetric,
                implode(', ', self::DISTANCE_METRICS)
            ));
        }

        if (is_int($maxEdge) && $maxEdge < 0) {
            throw new OutOfRangeException('$maxEdge must be a positive number');
        }
    }
}
