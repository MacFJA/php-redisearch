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

namespace MacFJA\RediSearch\Redis\Response;

use function count;

/**
 * @codeCoverageIgnore Simple value object
 */
class SpellCheckResponseItem
{
    /** @var string */
    private $term;
    /** @var array<string,float> */
    private $suggestions;

    /**
     * @param array<string,float> $suggestions
     */
    public function __construct(string $term, array $suggestions)
    {
        $this->term = $term;
        $this->suggestions = $suggestions;
    }

    public function getMisspelled(): string
    {
        return $this->term;
    }

    /**
     * @return array<string, float>
     */
    public function getSuggestions(bool $normalized = false): array
    {
        if (false === $normalized) {
            return $this->suggestions;
        }
        $highest = max($this->suggestions);

        if ($highest <= 0) {
            return [];
        }

        return array_map(static function (float $score) use ($highest) {
            return $score / $highest;
        }, $this->suggestions);
    }

    public function getTopSuggestion(): ?string
    {
        if (0 === count($this->suggestions)) {
            return null;
        }

        $local = $this->suggestions;
        arsort($local);
        reset($local);

        return key($local);
    }
}
