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

use function array_map;
use function count;
use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\EscapeHelper;
use MacFJA\RediSearch\Search\Exception\NotEnoughTermsException;
use function reset;
use function sprintf;
use function strpos;

class TextFacet implements PartialQuery
{
    private const WITH_SPACE_PATTERN = '@%s:(%s)';

    private const WITHOUT_SPACE_PATTERN = '@%s:%s';

    /** @var string */
    private $field;

    /** @var array<string> */
    private $orValues;

    public function __construct(string $field, string ...$orValues)
    {
        DataHelper::assert(count($orValues) > 0, NotEnoughTermsException::class);
        $this->field = $field;
        $this->orValues = $orValues;
    }

    public function render(): string
    {
        if (count($this->orValues) > 1) {
            $terms = OrGroup::renderNoParentheses(...array_map(function (string $orValue) {
                return new ExactMatch($orValue);
            }, $this->orValues));

            return sprintf(self::WITH_SPACE_PATTERN, EscapeHelper::escapeFieldName($this->field), $terms);
        }

        $value = reset($this->orValues) ?: '';
        if (false === strpos($value, ' ')) {
            return sprintf(self::WITHOUT_SPACE_PATTERN, EscapeHelper::escapeFieldName($this->field), EscapeHelper::escapeWord($value));
        }

        return sprintf(self::WITH_SPACE_PATTERN, EscapeHelper::escapeFieldName($this->field), EscapeHelper::escapeWord($value));
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
