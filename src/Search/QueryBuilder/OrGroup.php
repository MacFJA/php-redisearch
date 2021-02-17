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
use function implode;
use function sprintf;
use function substr;
use function usort;

class OrGroup implements GroupPartialQuery
{
    /** @var array<PartialQuery> */
    private $expressions = [];

    public function render(): string
    {
        usort($this->expressions, function (PartialQuery $queryA, PartialQuery $queryB) {
            return $queryA->priority() <=> $queryB->priority();
        });
        $terms = array_map(function (PartialQuery $query): string {
            if ($query->includeSpace()) {
                return sprintf('(%s)', $query->render());
            }

            return $query->render();
        }, $this->expressions);

        return sprintf('(%s)', implode('|', $terms));
    }

    public function includeSpace(): bool
    {
        return false;
    }

    public function addExpression(PartialQuery $expression): void
    {
        $this->expressions[] = $expression;
    }

    public function priority(): int
    {
        return self::PRIORITY_NORMAL;
    }

    public static function renderNoParentheses(PartialQuery ...$queries): string
    {
        if (0 === count($queries)) {
            return '';
        }

        $group = new self();
        foreach ($queries as $query) {
            $group->addExpression($query);
        }
        $rendered = $group->render();

        return substr($rendered, 1, -1) ?: $rendered;
    }
}
