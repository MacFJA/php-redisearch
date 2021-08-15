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

abstract class AbstractGroup implements QueryElement, QueryElementGroup
{
    /**
     * @var array<QueryElement>
     */
    protected $elements;

    /**
     * @param array<QueryElement> $elements
     */
    final public function __construct(array $elements = [])
    {
        $this->elements = $elements;
        $this->sortElement();
    }

    public function addElement(QueryElement $element): QueryElementGroup
    {
        $this->elements[] = $element;
        $this->sortElement();

        return $this;
    }

    public function priority(): int
    {
        return self::PRIORITY_NORMAL;
    }

    public function includeSpace(): bool
    {
        /** @var QueryElement $element */
        foreach ($this->elements as $element) {
            if ($element->includeSpace()) {
                return true;
            }
        }

        return false;
    }

    public static function fromStrings(string ...$words): self
    {
        $words = array_map(static function (string $word): ExactMatch {
            return new ExactMatch($word);
        }, $words);

        return new static($words);
    }

    private function sortElement(): void
    {
        usort($this->elements, static function (QueryElement $elementA, QueryElement $elementB) {
            return $elementA->priority() <=> $elementB->priority();
        });
    }
}
