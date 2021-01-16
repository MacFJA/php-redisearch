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

namespace MacFJA\RediSearch\Helper;

use function ceil;
use function count;

/**
 * @template T
 */
abstract class PaginatedResult
{
    /** @var int */
    private $totalCount;

    /** @var array<T> */
    private $items;

    /** @var int */
    private $offset;

    /** @var int */
    private $pageSize;

    /**
     * @param array<T> $items
     */
    public function __construct(int $totalCount, array $items, int $offset, int $pageSize)
    {
        $this->totalCount = $totalCount;
        $this->items = $items;
        $this->offset = $offset;
        $this->pageSize = $pageSize;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return array<T>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getPageNumber(): int
    {
        if (0 === $this->pageSize) {
            return 0;
        }

        return (int) ceil($this->getOffset() / $this->pageSize) + 1;
    }

    public function havePreviousPage(): bool
    {
        return $this->offset > 0;
    }

    public function haveNextPage(): bool
    {
        return $this->offset + count($this->items) < $this->totalCount;
    }

    public function getPageCount(): int
    {
        if (0 === $this->pageSize) {
            return 0;
        }

        return (int) ceil($this->totalCount / $this->pageSize);
    }
}
