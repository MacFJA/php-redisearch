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

namespace MacFJA\RediSearch;

use function array_chunk;
use function array_map;
use function array_shift;
use function assert;
use function count;
use function in_array;
use InvalidArgumentException;
use function is_string;
use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\PipelineItem;
use MacFJA\RediSearch\Helper\RedisHelper;
use MacFJA\RediSearch\Suggestion\Result;
use Predis\Client;

/**
 * @property-read int $length
 */
class Suggestions
{
    /** @var string */
    private $dictionaryKey;

    /** @var ?int */
    private $length;

    /** @var Client */
    private $redis;

    public function __construct(string $dictionaryKey, Client $redis)
    {
        $this->dictionaryKey = $dictionaryKey;
        $this->redis = $redis;
    }

    /**
     * @return int|void
     */
    public function __get(string $name)
    {
        if ('length' === $name) {
            return $this->length ?? $this->length();
        }

        return;
    }

    public function __isset(string $name)
    {
        return in_array($name, ['length'], true);
    }

    public function add(string $suggestion, float $score, bool $increment = false, ?string $payload = null): void
    {
        $command = ['FT.SUGADD', $this->dictionaryKey, $suggestion, $score];
        $command = RedisHelper::buildQueryBoolean($command, ['INCR' => $increment]);
        $command = RedisHelper::buildQueryNotNull($command, ['PAYLOAD' => $payload]);
        /** @phan-suppress-next-line PhanAccessReadOnlyMagicProperty */
        $this->length = $this->redis->executeRaw($command);
    }

    /**
     * @return array<Result>
     */
    public function get(string $prefix, bool $fuzzy = false, bool $withScores = false, bool $withPayloads = false, ?int $max = null): array
    {
        $request = $this->pipeableGet($prefix, $fuzzy, $withScores, $withPayloads, $max);
        $result = $this->redis->executeCommand($request->getCommand());

        return $request->transform($result);
    }

    public function pipeableGet(string $prefix, bool $fuzzy = false, bool $withScores = false, bool $withPayloads = false, ?int $max = null): PipelineItem
    {
        $command = ['FT.SUGGET', $this->dictionaryKey, $prefix];
        $command = RedisHelper::buildQueryBoolean($command, [
            'FUZZY' => $fuzzy,
            'WITHSCORES' => $withScores,
            'WITHPAYLOADS' => $withPayloads,
        ]);
        $command = RedisHelper::buildQueryNotNull($command, [
            'MAX' => $max,
        ]);

        return PipelineItem::createFromRaw(
            $command,
            function ($rawResult, array $context) {
                if (null === $rawResult) {
                    return [];
                }

                return self::listFromRawRedis($rawResult, $context['payloads'], $context['scores']);
            },
            ['payloads' => $withPayloads, 'scores' => $withScores]
        );
    }

    public function delete(string $suggestion): bool
    {
        $removed = (int) $this->redis->executeRaw(['FT.SUGDEL', $this->dictionaryKey, $suggestion]);
        /** @phan-suppress-next-line PhanAccessReadOnlyMagicProperty */
        $this->length -= $removed;

        return 1 === $removed;
    }

    public function length(): int
    {
        /** @phan-suppress-next-line PhanAccessReadOnlyMagicProperty */
        $this->length = (int) $this->redis->executeRaw(['FT.SUGLEN', $this->dictionaryKey]);

        return $this->length;
    }

    /**
     * @param array<float|string> $data
     *
     * @return array<Result>
     */
    private static function listFromRawRedis(array $data, bool $withPayload = true, bool $withScore = false): array
    {
        $size = 1;
        if (true === $withPayload) {
            $size++;
        }
        if (true === $withScore) {
            $size++;
        }
        $suggestions = array_chunk($data, $size, false);

        return array_map(function (array $grouped) use ($withPayload, $withScore) {
            return self::fromRawRedis($grouped, $withPayload, $withScore);
        }, $suggestions);
    }

    /**
     * @param array<float|string> $data
     */
    private static function fromRawRedis(array $data, bool $withPayload = true, bool $withScore = false): Result
    {
        $expectedSize = 1 + (true === $withPayload ? 1 : 0) + (true === $withScore ? 1 : 0);
        if (!(count($data) === $expectedSize)) {
            throw new InvalidArgumentException();
        }

        $value = array_shift($data);
        $score = null;
        $payload = null;

        if (true === $withScore) {
            $score = (float) array_shift($data);
        }
        if (true === $withPayload) {
            $payload = array_shift($data);
        }

        DataHelper::assert(is_string($payload) || null === $payload);
        assert(is_string($payload) || null === $payload);

        return new Result((string) $value, $score, $payload);
    }
}
