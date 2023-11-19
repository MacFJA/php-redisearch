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

use function array_key_exists;
use function count;
use function gettype;
use function is_int;
use function is_string;
use function strlen;

use InvalidArgumentException;

class QueryElementVector implements QueryElementDecorator
{
    private const PARAMETERS = [
        'EF_RUNTIME' => 'integer',
    ];

    /** @var QueryElement */
    private $element;

    /** @var int|string */
    private $number;

    /** @var string */
    private $field;

    /** @var string */
    private $blob;

    /** @var null|string */
    private $scoreAlias;

    /** @var array<string,int|string> */
    private $parameters = [];

    /**
     * @param int|scalar|string $number
     */
    public function __construct(QueryElement $element, $number, string $field, string $blob, ?string $scoreAlias = null)
    {
        if (!is_string($number) && !is_int($number)) {
            throw new InvalidArgumentException();
        }
        $this->element = $element;
        $this->number = $number;
        $this->field = $field;
        $this->blob = $blob;
        $this->scoreAlias = $scoreAlias;
    }

    public function render(?callable $escaper = null): string
    {
        return sprintf(
            '(%s)=>[KNN %s @%s $%s%s%s]',
            $this->element->render($escaper),
            $this->getRenderedNumber(),
            $this->getRenderedField(),
            $this->getRenderedBlob(),
            $this->getRenderedAttributes(),
            $this->getRenderedAlias()
        );
    }

    /**
     * @param int|string $value
     *
     * @return $this
     */
    public function addParameter(string $name, $value): self
    {
        if (!array_key_exists($name, self::PARAMETERS)) {
            throw new InvalidArgumentException();
        }
        if (
            !(gettype($value) === self::PARAMETERS[$name])
            && (is_string($value) && (strlen($value) < 2 || !(0 === strpos($value, '$'))))
        ) {
            throw new InvalidArgumentException();
        }
        $this->parameters[$name] = $value;

        return $this;
    }

    public function priority(): int
    {
        return self::PRIORITY_BEFORE;
    }

    public function includeSpace(): bool
    {
        return false;
    }

    private function getRenderedNumber(): string
    {
        return is_string($this->number) ? ('$'.ltrim($this->number, '$')) : ((string) $this->number);
    }

    private function getRenderedField(): string
    {
        return ltrim($this->field, '@');
    }

    private function getRenderedBlob(): string
    {
        return ltrim($this->blob, '$');
    }

    private function getRenderedAlias(): string
    {
        if (!is_string($this->scoreAlias)) {
            return '';
        }

        return ' AS '.$this->scoreAlias;
    }

    private function getRenderedAttributes(): string
    {
        if (0 === count($this->parameters)) {
            return '';
        }

        return ' '.implode(' ', array_map(static function ($key, $value) {
            return $key.' '.$value;
        }, array_keys($this->parameters), array_values($this->parameters)));
    }
}
