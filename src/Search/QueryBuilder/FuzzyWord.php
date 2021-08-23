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

use MacFJA\RediSearch\Helper\EscapeHelper;
use MacFJA\RediSearch\Search\Exception\OutOfRangeLevenshteinDistanceException;
use function sprintf;
use function str_repeat;

class FuzzyWord implements PartialQuery
{
    /** @var string */
    private $word;

    /** @var int */
    private $levenshteinDistance;

    public function __construct(string $word, int $levenshteinDistance = 1)
    {
        if ($levenshteinDistance < 1 || $levenshteinDistance > 3) {
            throw new OutOfRangeLevenshteinDistanceException($levenshteinDistance);
        }
        $this->word = $word;
        $this->levenshteinDistance = $levenshteinDistance;
    }

    public function render(): string
    {
        return sprintf('%1$s%2$s%1$s', str_repeat('%', $this->levenshteinDistance), EscapeHelper::escapeFuzzy($this->word));
    }

    public function includeSpace(): bool
    {
        return false;
    }

    public function priority(): int
    {
        return self::PRIORITY_BEFORE;
    }
}
