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

namespace MacFJA\RediSearch\Search;

use function array_map;
use function implode;
use MacFJA\RediSearch\Search\QueryBuilder\AndGroup;
use MacFJA\RediSearch\Search\QueryBuilder\GroupPartialQuery;
use MacFJA\RediSearch\Search\QueryBuilder\NumericFacet;
use MacFJA\RediSearch\Search\QueryBuilder\OrGroup;
use MacFJA\RediSearch\Search\QueryBuilder\PartialQuery;
use MacFJA\RediSearch\Search\QueryBuilder\TagFacet;
use MacFJA\RediSearch\Search\QueryBuilder\TextFacet;
use MacFJA\RediSearch\Search\QueryBuilder\Word;
use function strlen;

class QueryBuilder
{
    /** @var GroupPartialQuery */
    private $group;

    /** @var array<QueryBuilder> */
    private $queryBuilders = [];

    /**
     * QueryBuilder constructor.
     */
    private function __construct(bool $isOrGroup)
    {
        if (true === $isOrGroup) {
            $this->group = new OrGroup();

            return;
        }
        $this->group = new AndGroup();
    }

    public function addTagFacet(string $tagName, string ...$values): self
    {
        $this->group->addExpression(
            new TagFacet($tagName, ...$values)
        );

        return $this;
    }

    public function addTextFacet(string $fieldName, string ...$values): self
    {
        $this->group->addExpression(
            new TextFacet($fieldName, ...$values)
        );

        return $this;
    }

    public function addNumericFacet(string $fieldName, ?float $min, ?float $max): self
    {
        $this->group->addExpression(
            new NumericFacet($fieldName, $min, $max)
        );

        return $this;
    }

    public function addString(string ...$string): self
    {
        $this->group->addExpression(new Word(...$string));

        return $this;
    }

    public function addQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $this->queryBuilders[] = $queryBuilder;

        return $this;
    }

    public function addExpression(PartialQuery $expression): self
    {
        $this->group->addExpression($expression);

        return $this;
    }

    public function render(): string
    {
        $otherQueries = implode(' ', array_map(
            function (QueryBuilder $queryBuilder): string {
                return $queryBuilder->render();
            }, $this->queryBuilders
        ));

        return $this->group->render().(strlen($otherQueries) > 0 ? ' '.$otherQueries : '');
    }

    public static function createOrGroup(): self
    {
        return new self(true);
    }

    public static function createAndGroup(): self
    {
        return new self(false);
    }

    public static function create(): self
    {
        return new self(false);
    }
}
