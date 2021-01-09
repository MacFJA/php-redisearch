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

namespace MacFJA\RediSearch\Words;

use function array_keys;
use function assert;
use function count;
use function is_string;
use function key;
use MacFJA\RediSearch\Helper\DataHelper;
use function reset;
use Throwable;

class SpellingResult
{
    /** @var string */
    private $searched;

    /** @var array<string, float> */
    private $suggestions = [];

    /**
     * SpellingResult constructor.
     *
     * @param array<string, float> $suggestions
     *
     * @throws Throwable
     */
    public function __construct(string $searched, array $suggestions)
    {
        DataHelper::assert(count($suggestions) > 0);
        DataHelper::assertArrayOf(array_keys($suggestions), 'string');
        DataHelper::assertArrayOf($suggestions, 'double');
        $this->searched = $searched;
        $this->suggestions = $suggestions;
    }

    public function getSearched(): string
    {
        return $this->searched;
    }

    /**
     * @return array<string, float>
     */
    public function getSuggestions(): array
    {
        return $this->suggestions;
    }

    public function getBestSuggestion(): string
    {
        assert(count($this->suggestions) > 0);
        reset($this->suggestions);
        $best = key($this->suggestions);
        assert(is_string($best));

        return $best;
    }
}
