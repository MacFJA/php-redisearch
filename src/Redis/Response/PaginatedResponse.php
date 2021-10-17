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

namespace MacFJA\RediSearch\Redis\Response;

use function count;
use Countable;
use function is_int;
use Iterator;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command\PaginatedCommand;
use MacFJA\RediSearch\Redis\Response;

/**
 * @implements Iterator<int,AggregateResponseItem[]|SearchResponseItem[]>
 */
class PaginatedResponse implements Response, Iterator, Countable
{
    /** @var array<AggregateResponseItem>|array<SearchResponseItem> */
    private $items;
    /** @var PaginatedCommand */
    private $lastCommand;
    /** @var int */
    private $totalCount;
    /** @var Client */
    private $client;

    /** @var null|int */
    private $requestedOffset;
    /** @var null|int */
    private $requestedSize;

    /**
     * @param AggregateResponseItem[]|SearchResponseItem[] $items
     */
    public function __construct(PaginatedCommand $command, int $totalCount, array $items)
    {
        $this->totalCount = $totalCount;
        $this->items = $items;
        $this->lastCommand = $command;
    }

    public function setClient(Client $client): PaginatedResponse
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return array<AggregateResponseItem>|array<SearchResponseItem>
     */
    public function current()
    {
        if ($this->requestedOffset === ($this->lastCommand->getOffset() ?? 0) && ($this->requestedSize === $this->getPageSize())) {
            $this->requestedOffset = null;
            $this->requestedSize = null;
        }
        if (is_int($this->requestedOffset) && is_int($this->requestedSize)) {
            $this->updateWithLimit($this->requestedOffset, $this->requestedSize);
            $this->requestedOffset = null;
            $this->requestedSize = null;
        }

        return $this->items;
    }

    public function next(): void
    {
        $this->requestedOffset = ($this->lastCommand->getOffset() ?? 0) + $this->getPageSize();
        $this->requestedSize = $this->getPageSize();
    }

    public function getPageSize(): int
    {
        return $this->lastCommand->getSize() ?? count($this->items);
    }

    public function getPageCount(): int
    {
        return (int) ceil($this->totalCount / $this->getPageSize());
    }

    public function key()
    {
        if (0 === $this->getPageSize()) {
            return 0;
        }

        return (int) ceil(($this->lastCommand->getOffset() ?? 0) / $this->getPageSize()) + 1;
    }

    public function valid()
    {
        return $this->requestedOffset >= 0
            && $this->requestedSize > 0
            && $this->requestedOffset < $this->totalCount;
    }

    public function rewind(): void
    {
        $this->requestedOffset = 0;
        $this->requestedSize = $this->getPageSize();
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function count()
    {
        return $this->totalCount;
    }

    private function updateWithLimit(int $offset, int $size): void
    {
        /** @var PaginatedCommand $nextCommand */
        $nextCommand = clone $this->lastCommand;
        $nextCommand->setLimit($offset, $size);

        /** @var PaginatedResponse $paginated */
        $paginated = $this->client->execute($nextCommand);
        $this->lastCommand = $nextCommand;
        $this->totalCount = $paginated->totalCount;
        $this->items = $paginated->items;
    }
}
