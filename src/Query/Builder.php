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

namespace MacFJA\RediSearch\Query;

use MacFJA\RediSearch\Query\Builder\AndGroup;
use MacFJA\RediSearch\Query\Builder\ExactMatch;
use MacFJA\RediSearch\Query\Builder\GeoFacet;
use MacFJA\RediSearch\Query\Builder\NumericFacet;
use MacFJA\RediSearch\Query\Builder\QueryElement;
use MacFJA\RediSearch\Query\Builder\QueryElementGroup;
use MacFJA\RediSearch\Query\Builder\TagFacet;
use MacFJA\RediSearch\Query\Builder\TextFacet;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Builder implements QueryElement, QueryElementGroup
{
    /** @var AndGroup */
    private $group;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): self
    {
        $this->group = new AndGroup();

        return $this;
    }

    public function addTextFacet(string $field, string ...$values): self
    {
        $this->group->addElement(
            new TextFacet([$field], ...$values)
        );

        return $this;
    }

    public function addTagFacet(string $tagName, string ...$values): self
    {
        $this->group->addElement(
            new TagFacet([$tagName], ...$values)
        );

        return $this;
    }

    public function addNumericFacet(string $fieldName, ?float $min, ?float $max): self
    {
        $this->group->addElement(
            new NumericFacet([$fieldName], $min, $max)
        );

        return $this;
    }

    public function addGeoFacet(string $fieldName, float $lon, float $lat, float $radius, string $unit): self
    {
        $this->group->addElement(
            new GeoFacet([$fieldName], $lon, $lat, $radius, $unit)
        );

        return $this;
    }

    public function addString(string ...$strings): self
    {
        foreach ($strings as $string) {
            $this->group->addElement(new ExactMatch($string));
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addElement(QueryElement $element): QueryElementGroup
    {
        $this->group->addElement($element);

        return $this;
    }

    public function render(?callable $escaper = null): string
    {
        $rendered = $this->group->render(null);
        $this->reset();

        return $rendered;
    }

    public function priority(): int
    {
        return self::PRIORITY_BEFORE;
    }

    public function includeSpace(): bool
    {
        return false;
    }
}
