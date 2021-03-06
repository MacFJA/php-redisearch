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

use function ctype_digit;
use function sprintf;
use function substr;

class Negation implements PartialQuery
{
    private const WITH_SPACE_PATTERN = '-(%s)';

    private const WITHOUT_SPACE_PATTERN = '-%s';

    /** @var PartialQuery */
    private $expression;

    public function __construct(PartialQuery $expression)
    {
        $this->expression = $expression;
    }

    public function render(): string
    {
        $withParentheses = $this->expression->includeSpace() || ctype_digit(substr($this->expression->render(), 0, 1));

        return sprintf(
            true === $withParentheses ? self::WITH_SPACE_PATTERN : self::WITHOUT_SPACE_PATTERN,
            $this->expression->render()
        );
    }

    public function includeSpace(): bool
    {
        return false;
    }

    public function priority(): int
    {
        return $this->expression->priority();
    }
}
