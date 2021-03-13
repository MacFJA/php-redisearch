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

namespace MacFJA\RediSearch\Index\Builder;

use function array_merge;
use function implode;
use MacFJA\RediSearch\Helper\RedisHelper;
use SGH\Comparable\ComparatorException;

abstract class AbstractField implements Field
{
    /** @var string */
    private $name;

    /** @var bool */
    private $sortable = false;

    /** @var bool */
    private $noIndex = false;

    public function __construct(string $name, bool $sortable = false, bool $noIndex = false)
    {
        $this->name = $name;
        $this->sortable = $sortable;
        $this->noIndex = $noIndex;
    }

    public function isNoStem(): bool
    {
        return false;
    }

    public function getPhonetic(): ?string
    {
        return null;
    }

    public function getWeight(): ?float
    {
        return null;
    }

    public function getSeparator(): ?string
    {
        return null;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isNoIndex(): bool
    {
        return $this->noIndex;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQueryParts(): array
    {
        $query = array_merge(
            [$this->name, $this->getType()],
            $this->getAdditionalQueryParts()
        );

        return RedisHelper::buildQueryBoolean($query, [
            'SORTABLE' => $this->sortable,
            'NOINDEX' => $this->noIndex,
        ]);
    }

    public function compareTo($object)
    {
        if (!($object instanceof Field)) {
            throw new ComparatorException();
        }

        return implode(' ', $object->getQueryParts()) <=> implode(' ', $this->getQueryParts());
    }

    /**
     * @return array<float|int|string>
     */
    protected function getAdditionalQueryParts(): array
    {
        return [];
    }
}
