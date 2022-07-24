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

use function count;

use MacFJA\RediSearch\Exception\NotEnoughFieldsException;
use MacFJA\RediSearch\Query\Escaper;

class FieldFacet implements QueryElement
{
    /** @var array<string> */
    private $fields;

    /** @var QueryElement */
    private $element;

    /**
     * @param array<string> $fields
     */
    public function __construct(array $fields, QueryElement $element)
    {
        if (0 === count($fields)) {
            throw new NotEnoughFieldsException();
        }
        $this->fields = array_map([Escaper::class, 'escapeFieldName'], $fields);
        $this->element = $element;
    }

    public function render(?callable $escaper = null): string
    {
        return sprintf(
            '@%s:%s',
            OrGroup::fromStrings(...$this->fields)->render(),
            EncapsulationGroup::simple($this->element)->render()
        );
    }

    public function priority(): int
    {
        return self::PRIORITY_NORMAL;
    }

    public function includeSpace(): bool
    {
        return false;
    }
}
