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

namespace MacFJA\RediSearch\Redis\Command\AggregateCommand;

use function count;
use function is_bool;
use function is_string;
use MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption;
use MacFJA\RediSearch\Redis\Command\PrefixFieldName;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ReduceOption extends AbstractCommandOption
{
    use PrefixFieldName;

    /** @var string */
    private $function;

    /** @var array<float|int|string> */
    private $arguments = [];

    /** @var null|string */
    private $name;

    /**
     * @param array<float|int|string> $arguments
     */
    public function __construct(string $function, array $arguments = [], ?string $name = null, ?string $versionConstraint = null)
    {
        $this->function = $function;
        $this->arguments = $arguments;
        $this->name = $name;
        parent::__construct($versionConstraint);
    }

    public function isValid(): bool
    {
        return true;
    }

    /**
     * @return array<string,null|array<float|int|string>|string>
     */
    public function getOptionData()
    {
        return [
            'function' => $this->function,
            'arguments' => $this->arguments,
            'alias' => $this->name,
        ];
    }

    public static function count(?string $name = null): self
    {
        return new self('COUNT', [], $name, '>=2.0.0');
    }

    public static function countDistinct(string $property, ?string $name = null): self
    {
        return new self('COUNT_DISTINCT', [self::prefixField($property)], $name, '>=2.0.0');
    }

    public static function countDistinctish(string $property, ?string $name = null): self
    {
        return new self('COUNT_DISTINCTISH', [self::prefixField($property)], $name, '>=2.0.0');
    }

    public static function sum(string $property, ?string $name = null): self
    {
        return new self('SUM', [self::prefixField($property)], $name, '>=2.0.0');
    }

    public static function minimum(string $property, ?string $name = null): self
    {
        return new self('MIN', [self::prefixField($property)], $name, '>=2.0.0');
    }

    public static function maximum(string $property, ?string $name = null): self
    {
        return new self('MAX', [self::prefixField($property)], $name, '>=2.0.0');
    }

    public static function average(string $property, ?string $name = null): self
    {
        return new self('AVG', [self::prefixField($property)], $name, '>=2.0.0');
    }

    public static function standardDeviation(string $property, ?string $name = null): self
    {
        return new self('STDDEV', [self::prefixField($property)], $name, '>=2.0.0');
    }

    public static function quantile(string $property, float $quantile, ?string $name = null): self
    {
        return new self('QUANTILE', [self::prefixField($property), $quantile], $name, '>=2.0.0');
    }

    public static function median(string $property, ?string $name = null): self
    {
        return new self('QUANTILE', [self::prefixField($property), 0.5], $name, '>=2.0.0');
    }

    public static function toList(string $property, ?string $name = null): self
    {
        return new self('TOLIST', [self::prefixField($property)], $name, '>=2.0.0');
    }

    public static function firstValue(string $property, ?string $sortByProperty = null, ?bool $ascending = null, ?string $name = null): self
    {
        $arguments = [self::prefixField($property)];
        if (is_string($sortByProperty)) {
            $arguments[] = 'BY';
            $arguments[] = '@'.$sortByProperty;
            if (is_bool($ascending)) {
                $arguments[] = true === $ascending ? 'ASC' : 'DESC';
            }
        }

        return new self('FIRST_VALUE', $arguments, $name, '>=2.0.0');
    }

    protected function doRender(?string $version): array
    {
        $alias = is_string($this->name) ? ['AS', $this->name] : [];

        return array_merge(['REDUCE', $this->function, count($this->arguments)], $this->arguments, $alias);
    }
}
