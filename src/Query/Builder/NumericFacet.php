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

namespace MacFJA\RediSearch\Query\Builder;

use function is_array;

use MacFJA\RediSearch\Exception\NotEnoughFieldsException;
use MacFJA\RediSearch\Redis\Command\SearchCommand\NumericRangeTrait;

class NumericFacet extends FieldFacet
{
    use NumericRangeTrait;

    /**
     * @param array<string>|string $fields
     */
    public function __construct($fields, ?float $min, ?float $max, bool $isMinInclusive = true, bool $isMaxInclusive = true)
    {
        if (empty($fields)) {
            throw new NotEnoughFieldsException();
        }
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        $bounds = self::renderBound($min, $max, $isMinInclusive, $isMaxInclusive);
        $group = new EncapsulationGroup('[', ']', AndGroup::fromStrings(...$bounds));
        parent::__construct($fields, $group);
    }
}
