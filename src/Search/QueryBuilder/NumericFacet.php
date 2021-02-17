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

namespace MacFJA\RediSearch\Search\QueryBuilder;

use function is_numeric;
use MacFJA\RediSearch\Helper\EscapeHelper;
use function sprintf;

class NumericFacet implements PartialQuery
{
    /** @var string */
    private $fieldName;

    /** @var null|float */
    private $min;

    /** @var null|float */
    private $max;

    /** @var bool */
    private $isMaxInclusive;

    /** @var bool */
    private $isMinInclusive;

    public function __construct(string $fieldName, ?float $min, ?float $max, bool $isMinInclusive = true, bool $isMaxInclusive = true)
    {
        $this->fieldName = $fieldName;
        $this->min = $min;
        $this->max = $max;
        $this->isMaxInclusive = $isMaxInclusive;
        $this->isMinInclusive = $isMinInclusive;
    }

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

    public function render(): string
    {
        $min = '-inf';
        $max = '+inf';

        if (is_numeric($this->min)) {
            $min = ($this->isMinInclusive ? '' : '(').$this->min;
        }
        if (is_numeric($this->max)) {
            $max = ($this->isMaxInclusive ? '' : '(').$this->max;
        }

        return sprintf('@%s:[%s %s]', EscapeHelper::escapeFieldName($this->fieldName), $min, $max);
    }

    public function includeSpace(): bool
    {
        return false;
    }

    public function priority(): int
    {
        return self::PRIORITY_NORMAL;
    }
}
