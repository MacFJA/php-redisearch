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

namespace MacFJA\RediSearch\Redis\Command;

use function assert;
use function count;
use function is_array;
use function is_int;
use MacFJA\RediSearch\Exception\UnexpectedServerResponseException;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Response\AggregateResponseItem;
use MacFJA\RediSearch\Redis\Response\ArrayResponseTrait;
use MacFJA\RediSearch\Redis\Response\CursorResponse;

class CursorRead extends AbstractCommand
{
    use ArrayResponseTrait;

    public function __construct(string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION)
    {
        parent::__construct([
            'type' => new FlagOption('READ', true, '>=2.0.0'),
            'index' => new NamelessOption(null, '>=2.0.0'),
            'cursor' => new NamelessOption(null, '>=2.0.0'),
            'count' => new NamedOption('COUNT', null, '>=2.0.0'),
        ], $rediSearchVersion);
    }

    public function setIndex(string $index): self
    {
        $this->options['index']->setValue($index);

        return $this;
    }

    public function setCursorId(int $cursor): self
    {
        $this->options['cursor']->setValue($cursor);

        return $this;
    }

    public function setCount(int $count): self
    {
        $this->options['count']->setValue($count);

        return $this;
    }

    public function setNoCount(): self
    {
        $this->options['count']->setValue(null);

        return $this;
    }

    public function getId(): string
    {
        return 'FT.CURSOR';
    }

    /**
     * @param array<array<mixed>|int> $data
     */
    public static function transformResponse(array $data, ?int $count, string $index, string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION): CursorResponse
    {
        [$rawResult, $cursorId] = $data;

        assert(is_array($rawResult));
        assert(is_int($cursorId));

        $totalCount = array_shift($rawResult);
        $items = array_map(static function (array $document) {
            return new AggregateResponseItem(self::getPairs($document));
        }, $rawResult);

        $size = $count ?? count($items);

        return new CursorResponse($cursorId, $totalCount, $items, $size, $index, $rediSearchVersion);
    }

    /**
     * @param array|mixed $data
     *
     * @return CursorResponse
     */
    public function parseResponse($data)
    {
        if (!is_array($data)) {
            throw new UnexpectedServerResponseException($data);
        }

        return self::transformResponse(
            $data,
            $this->options['count']->getValue(),
            $this->options['index']->getValue(),
            $this->getRediSearchVersion()
        );
    }

    protected function getRequiredOptions(): array
    {
        return ['type', 'index', 'cursor'];
    }
}
