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
use Iterator;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command\AbstractCommand;
use MacFJA\RediSearch\Redis\Command\CursorRead;
use MacFJA\RediSearch\Redis\Response;

/**
 * @implements Iterator<int,AggregateResponseItem[]>
 */
class CursorResponse implements Response, Iterator
{
    /** @var array<AggregateResponseItem> */
    private $items;

    /** @var int */
    private $totalCount;

    /** @var Client */
    private $client;

    /** @var bool */
    private $doNext = false;

    /** @var int */
    private $cursorId;

    /** @var int */
    private $size;

    /** @var int */
    private $offset = 0;

    /** @var string */
    private $index;

    /** @var string */
    private $redisVersion;

    /**
     * @param array<AggregateResponseItem> $items
     */
    public function __construct(int $cursorId, int $totalCount, array $items, int $size, string $index, string $redisVersion = AbstractCommand::MIN_IMPLEMENTED_VERSION)
    {
        $this->totalCount = $totalCount;
        $this->items = $items;
        $this->size = $size;
        $this->index = $index;
        $this->redisVersion = $redisVersion;
        $this->cursorId = $cursorId;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return array<AggregateResponseItem>
     */
    public function current()
    {
        if ($this->doNext) {
            $cursorRead = new CursorRead($this->redisVersion);
            $cursorRead->setIndex($this->index);
            $cursorRead->setCursorId($this->cursorId);
            $cursorRead->setCount($this->getPageSize());

            /** @var CursorResponse $next */
            $next = $this->client->execute($cursorRead);
            $this->cursorId = $next->cursorId;
            $this->offset += count($this->items);
            $this->items = $next->items;
            $this->doNext = false;
            // Don't update totalCount as Cursor always return 0
        }

        return $this->items;
    }

    public function next(): void
    {
        $this->doNext = true;
    }

    public function getPageSize(): int
    {
        return $this->size;
    }

    public function getPageCount(): int
    {
        if (0 === $this->size) {
            return 0;
        }

        return (int) ceil($this->totalCount / $this->size);
    }

    public function key()
    {
        if (0 === $this->size) {
            return 0;
        }

        return (int) ceil(($this->offset) / $this->size) + 1;
    }

    public function valid()
    {
        return $this->offset + count($this->items) < $this->totalCount && $this->cursorId > 0;
    }

    public function rewind(): void
    {
        // Do nothing
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
