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

class EncapsulationGroup implements QueryElement, QueryElementDecorator
{
    /** @var string */
    private $open;

    /** @var string */
    private $close;

    /** @var bool */
    private $omitIfNoSpace;

    /** @var QueryElement */
    private $element;

    public function __construct(string $open, string $close, QueryElement $element, bool $omitIfNoSpace = false)
    {
        $this->open = $open;
        $this->close = $close;
        $this->omitIfNoSpace = $omitIfNoSpace;
        $this->element = $element;
    }

    public function render(?callable $escaper = null): string
    {
        if ($this->omitIfNoSpace && !$this->element->includeSpace()) {
            return $this->element->render($escaper);
        }

        return sprintf('%s%s%s', $this->open, $this->element->render($escaper), $this->close);
    }

    public function priority(): int
    {
        return self::PRIORITY_NORMAL;
    }

    public function includeSpace(): bool
    {
        return false;
    }

    public static function simple(QueryElement $element): self
    {
        return new self('(', ')', $element, true);
    }
}
