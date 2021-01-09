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
use function array_column;
use function array_combine;
use function array_map;
use function array_merge;
use function assert;
use function count;
use function is_array;
use function is_bool;
use function is_string;
use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\RedisHelper;
use MacFJA\RediSearch\Words\Exception\NotEnoughTermsException;
use MacFJA\RediSearch\Words\SpellingResult;
use MacFJA\RediSearch\Words\SynonymResult;
use Predis\Client;
use Throwable;

class Words
{
    /** @var Client */
    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param array<string> $synonyms
     */
    public function addSynonym(string $index, string $for, array $synonyms, bool $skipInitialScan = false): bool
    {
        DataHelper::assert(count($synonyms) > 0);
        DataHelper::assertArrayOf($synonyms, 'string');
        $query = ['FT.SYNUPDATE', $index, $for];
        $query = RedisHelper::buildQueryBoolean($query, ['SKIPINITIALSCAN' => $skipInitialScan]);
        $query = array_merge($query, $synonyms);

        return 'OK' === $this->redis->executeRaw($query);
    }

    /**
     * @return array<SynonymResult>
     */
    public function dumpSynonyms(string $index): array
    {
        $result = $this->redis->executeRaw(['FT.SYNDUMP', $index]);
        DataHelper::handleRawResult($result);

        return array_map(function (array $item): SynonymResult {
            return new SynonymResult($item[0], $item[1]);
        }, array_chunk($result, 2));
    }

    /**
     * @throws Throwable
     *
     * @return array<SpellingResult>
     */
    public function getSpellCheck(string $index, string $query, ?int $distance = null): array
    {
        return $this->internalSpellCheck($index, $query, $distance);
    }

    /**
     * @param array<string> $terms
     *
     * @throws Throwable
     *
     * @return array<SpellingResult>
     */
    public function getSpellCheckInclude(string $index, string $query, string $dictionary, array $terms = [], ?int $distance = null): array
    {
        DataHelper::assertArrayOf($terms, 'string');

        return $this->internalSpellCheck($index, $query, $distance, true, $dictionary, $terms);
    }

    /**
     * @param array<string> $terms
     *
     * @throws Throwable
     *
     * @return array<SpellingResult>
     */
    public function getSpellCheckExclude(string $index, string $query, string $dictionary, array $terms = [], ?int $distance = null): array
    {
        DataHelper::assertArrayOf($terms, 'string');

        return $this->internalSpellCheck($index, $query, $distance, false, $dictionary, $terms);
    }

    /**
     * @param array<string> $terms
     *
     * @throws NotEnoughTermsException
     */
    public function addToDictionary(string $dictionary, array $terms): int
    {
        DataHelper::assert(count($terms) < 1, NotEnoughTermsException::class);
        $query = array_merge(['FT.DICTADD', $dictionary], $terms);

        return $this->redis->executeRaw($query);
    }

    /**
     * @param array<string> $terms
     *
     * @throws NotEnoughTermsException
     */
    public function removeFromDictionary(string $dictionary, array $terms): int
    {
        DataHelper::assert(count($terms) < 1, NotEnoughTermsException::class);
        DataHelper::assertArrayOf($terms, 'string');
        $query = array_merge(['FT.DICTDEL', $dictionary], $terms);

        return $this->redis->executeRaw($query);
    }

    /**
     * @return array<string>
     */
    public function dumpDictionary(string $dictionary): array
    {
        return $this->redis->executeRaw(['FT.DICTDUMP', $dictionary]);
    }

    /**
     * @param array<string> $terms
     *
     * @throws Throwable
     *
     * @return array<SpellingResult>
     */
    private function internalSpellCheck(string $index, string $query, ?int $distance = null, ?bool $withInclude = null, ?string $dictionary = null, array $terms = []): array
    {
        $redisQuery = ['FT.SPELLCHECK', $index, $query];
        $redisQuery = RedisHelper::buildQueryNotNull($redisQuery, ['DISTANCE' => $distance]);
        if (is_bool($withInclude) && is_string($dictionary)) {
            $redisQuery[] = 'TERMS';
            $redisQuery[] = true === $withInclude ? 'INCLUDE' : 'EXCLUDE';
            $redisQuery[] = $dictionary;
            $redisQuery = array_merge($redisQuery, $terms);
        }

        $result = $this->redis->executeRaw($redisQuery);

        return array_map(function (array $term): SpellingResult {
            $searched = $term[1];
            $suggestions = array_combine(
                array_column($term[2], 1),
                array_column($term[2], 0)
            );
            assert(is_array($suggestions));

            return new SpellingResult($searched, $suggestions);
        }, $result);
    }
}
