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

namespace MacFJA\RediSearch\Redis\Command\SearchCommand;

trait NumericRangeTrait
{
    public static function greaterThan(string $fieldName, float $value): self
    {
        return new self($fieldName, $value, null, false);
    }

    public static function greaterThanOrEquals(string $fieldName, float $value): self
    {
        return new self($fieldName, $value, null, true);
    }

    public static function lessThan(string $fieldName, float $value): self
    {
        return new self($fieldName, null, $value, true, false);
    }

    public static function lessThanOrEquals(string $fieldName, float $value): self
    {
        return new self($fieldName, null, $value, true, true);
    }

    public static function equalsTo(string $fieldName, float $value): self
    {
        return new self($fieldName, $value, $value, true, true);
    }

    /**
     * @return string[]
     */
    private static function renderBound(?float $min, ?float $max, bool $isMinInclusive = true, bool $isMaxInclusive = true): array
    {
        $minValue = '-inf';
        $maxValue = '+inf';

        if (is_numeric($min)) {
            $minValue = (true === $isMinInclusive ? '' : '(').$min;
        }
        if (is_numeric($max)) {
            $maxValue = (true === $isMaxInclusive ? '' : '(').$max;
        }

        return [$minValue, $maxValue];
    }
}
