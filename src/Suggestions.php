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

use function is_int;
use function is_string;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command\AbstractCommand;
use MacFJA\RediSearch\Redis\Command\SugAdd;
use MacFJA\RediSearch\Redis\Command\SugDel;
use MacFJA\RediSearch\Redis\Command\SugGet;
use MacFJA\RediSearch\Redis\Command\SugLen;
use MacFJA\RediSearch\Redis\Initializer;
use MacFJA\RediSearch\Redis\Response\SuggestionResponseItem;

/**
 * @codeCoverageIgnore
 */
class Suggestions
{
    /** @var string */
    private $dictionary;

    /** @var Client */
    private $client;

    /** @var int */
    private $length;

    /** @var string */
    private $version;

    public function __construct(string $dictionary, Client $client)
    {
        $this->dictionary = $dictionary;
        $this->client = $client;
        $this->length = (int) $client->execute((new SugLen())->setDictionary($dictionary));
        $this->version = Initializer::getRediSearchVersion($client) ?? AbstractCommand::MIN_IMPLEMENTED_VERSION;
    }

    public function add(string $suggestion, float $score, bool $increment = false, ?string $payload = null): void
    {
        $command = new SugAdd($this->version);
        $command
            ->setDictionary($this->dictionary)
            ->setScore($score)
            ->setSuggestion($suggestion)
            ->setIncrement($increment)
        ;
        if (is_string($payload)) {
            $command->setPayload($payload);
        }

        $response = $this->client->execute($command);

        if (is_int($response)) {
            $this->length = $response;
        }
    }

    /**
     * @return array<SuggestionResponseItem>
     */
    public function get(string $prefix, bool $fuzzy = false, bool $withScores = false, bool $withPayloads = false, ?int $max = null): array
    {
        $command = new SugGet($this->version);
        $command->setDictionary($this->dictionary)
            ->setWithPayloads($withPayloads)
            ->setWithScores($withScores)
            ->setPrefix($prefix)
            ->setFuzzy($fuzzy)
        ;
        if (is_int($max)) {
            $command->setMax($max);
        }

        return $this->client->execute($command) ?? [];
    }

    public function delete(string $suggestion): bool
    {
        $command = new SugDel($this->version);
        $command
            ->setDictionary($this->dictionary)
            ->setSuggestion($suggestion)
        ;
        $removed = (int) $this->client->execute($command);
        $this->length -= $removed;

        return 1 === $removed;
    }

    public function length(): int
    {
        return $this->length;
    }
}
