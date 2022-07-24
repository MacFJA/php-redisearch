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
use function implode;
use function is_bool;
use function is_float;
use function is_int;
use function sprintf;

use NumberFormatter;

class QueryElementAttribute implements QueryElement, QueryElementDecorator
{
    /** @var QueryElement */
    private $element;

    /** @var null|float */
    private $weight;

    /** @var null|int */
    private $slop;

    /** @var null|bool */
    private $inOrder;

    /** @var null|bool */
    private $phonetic;

    public function __construct(QueryElement $element, ?float $weight = null, ?int $slop = null, ?bool $inOrder = null, ?bool $phonetic = null)
    {
        $this->element = $element;
        $this->weight = $weight;
        $this->slop = $slop;
        $this->inOrder = $inOrder;
        $this->phonetic = $phonetic;
    }

    public function render(?callable $escaper = null): string
    {
        $attributes = [];
        if (is_float($this->weight)) {
            $formatter = new NumberFormatter('en_US', NumberFormatter::PATTERN_DECIMAL, '0.0###');
            $attributes[] = sprintf('$weight: %s;', $formatter->format($this->weight));
        }
        if (is_int($this->slop)) {
            $attributes[] = sprintf('$slop: %d;', $this->slop);
        }
        if (is_bool($this->inOrder)) {
            $attributes[] = sprintf('$inorder: %s;', $this->inOrder ? 'true' : 'false');
        }
        if (is_bool($this->phonetic)) {
            $attributes[] = sprintf('$phonetic: %s;', $this->phonetic ? 'true' : 'false');
        }

        if (0 === count($attributes)) {
            return $this->element->render($escaper);
        }

        $attributesString = implode(' ', $attributes);

        return sprintf(
            '%s => { %s }',
            EncapsulationGroup::simple($this->element)->render($escaper),
            $attributesString
        );
    }

    public function includeSpace(): bool
    {
        return true;
    }

    public function priority(): int
    {
        return $this->element->priority();
    }
}
