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

namespace MacFJA\RediSearch\Aggregate;

use function count;
use function is_bool;
use function is_string;
use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\PartialQuery;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Reducer implements PartialQuery
{
    /** @var string */
    private $function;

    /** @var array<float|int|string> */
    private $arguments = [];

    /** @var null|string */
    private $name;

    /**
     * @param array<float|int|string> $arguments
     *
     * @throws Throwable
     */
    public function __construct(string $function, array $arguments = [], ?string $name = null)
    {
        DataHelper::assertArrayOf($arguments, 'scalar');
        $this->function = $function;
        $this->arguments = $arguments;
        $this->name = $name;
    }

    public function getQueryParts(): array
    {
        $query = ['REDUCE', $this->function, count($this->arguments)];
        foreach ($this->arguments as $argument) {
            $query[] = $argument;
        }
        if (is_string($this->name)) {
            $query[] = 'AS';
            $query[] = $this->name;
        }

        return $query;
    }

    public static function count(?string $name = null): self
    {
        return new self('COUNT', [], $name);
    }

    public static function countDistinct(string $property, ?string $name = null): self
    {
        return new self('COUNT_DISTINCT', ['@'.$property], $name);
    }

    public static function countDistinctish(string $property, ?string $name = null): self
    {
        return new self('COUNT_DISTINCTISH', ['@'.$property], $name);
    }

    public static function sum(string $property, ?string $name = null): self
    {
        return new self('SUM', ['@'.$property], $name);
    }

    public static function minimum(string $property, ?string $name = null): self
    {
        return new self('MIN', ['@'.$property], $name);
    }

    public static function maximum(string $property, ?string $name = null): self
    {
        return new self('MAX', ['@'.$property], $name);
    }

    public static function average(string $property, ?string $name = null): self
    {
        return new self('AVG', ['@'.$property], $name);
    }

    public static function standardDeviation(string $property, ?string $name = null): self
    {
        return new self('STDDEV', ['@'.$property], $name);
    }

    public static function quantile(string $property, float $quantile, ?string $name = null): self
    {
        return new self('QUANTILE', ['@'.$property, $quantile], $name);
    }

    public static function median(string $property, ?string $name = null): self
    {
        return new self('QUANTILE', ['@'.$property, 0.5], $name);
    }

    public static function toList(string $property, ?string $name = null): self
    {
        return new self('TOLIST', ['@'.$property], $name);
    }

    public static function firstValue(string $property, ?string $sortByProperty = null, ?bool $ascending = null, ?string $name = null): self
    {
        $arguments = ['@'.$property];
        if (is_string($sortByProperty)) {
            $arguments[] = 'BY';
            $arguments[] = '@'.$sortByProperty;
            if (is_bool($ascending)) {
                $arguments[] = true === $ascending ? 'ASC' : 'DESC';
            }
        }

        return new self('FIRST_VALUE', $arguments, $name);
    }
}
