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

namespace MacFJA\RedisSearch;

use function array_keys;
use function array_map;
use function array_merge;
use function is_string;
use MacFJA\RedisSearch\Helper\DataHelper;
use MacFJA\RedisSearch\Helper\RedisHelper;
use MacFJA\RedisSearch\Index\Builder\Field;
use MacFJA\RedisSearch\Index\InfoResult;
use Predis\Client;
use Throwable;
use function trigger_error;
use function uniqid;

class Index
{
    /** @var string */
    private $indexKey;

    /** @var Client */
    private $redis;

    /**
     * Index constructor.
     */
    public function __construct(string $indexKey, Client $redis)
    {
        $this->indexKey = $indexKey;
        $this->redis = $redis;
    }

    /**
     * @param array<string,float|int|string> $properties
     *
     * @throws Throwable
     *
     * @deprecated Use `addDocumentFromArray` instead
     * @see Index::addDocumentFromArray()
     */
    public function addFromArray(array $properties, ?string $hash = null): string
    {
        trigger_error('The method '.__METHOD__.' is deprecated. Use \MacFJA\RedisSearch\Index::addDocumentFromArray instead.', \E_USER_DEPRECATED);

        return $this->addDocumentFromArray($properties, $hash);
    }

    /**
     * @param array<string,float|int|string> $properties
     *
     * @throws Throwable
     */
    public function addDocumentFromArray(array $properties, ?string $hash = null): string
    {
        DataHelper::assertArrayOf(array_keys($properties), 'string');
        DataHelper::assertArrayOf($properties, 'scalar');
        $documentId = is_string($hash) ? $hash : uniqid();
        $query = ['HSET', $documentId];
        foreach ($properties as $name => $value) {
            $query[] = $name;
            $query[] = $value;
        }

        $this->redis->executeRaw($query);

        return $documentId;
    }

    public function deleteDocument(string $hash): bool
    {
        $count = $this->redis->del($hash);

        return 1 === $count;
    }

    public function addField(Field $field): bool
    {
        $result = $this->redis->executeRaw(array_merge([
            'FT.ALTER',
            $this->indexKey,
            'SCHEMA',
            'ADD',
        ], $field->getQueryParts()));

        return 'OK' === $result;
    }

    public function delete(bool $withDocuments = false): bool
    {
        $query = ['FT.DROPINDEX', $this->indexKey];
        if (true === $withDocuments) {
            $query[] = 'DD';
        }
        $result = $this->redis->executeRaw($query);

        return 'OK' === $result;
    }

    public function addAlias(string $alias): bool
    {
        return $this->redis->executeRaw(['FT.ALIASADD', $alias, $this->indexKey]);
    }

    public function updateAlias(string $alias): bool
    {
        return $this->redis->executeRaw(['FT.ALIASUPDATE', $alias, $this->indexKey]);
    }

    public function deleteAlias(string $alias): bool
    {
        return $this->redis->executeRaw(['FT.ALIASDEL', $alias]);
    }

    /**
     * @return array<string>
     */
    public function getTagValues(string $fieldName): array
    {
        return $this->redis->executeRaw(['FT.TAGVALS', $this->indexKey, $fieldName]);
    }

    public function getStats(): InfoResult
    {
        $result = $this->redis->executeRaw(['FT.INFO', $this->indexKey]);
        DataHelper::handleRawResult($result);

        $stats = RedisHelper::getPairs($result);

        return new InfoResult(
            (string) $stats['index_name'],
            array_map('strval', (array) ($stats['index_options'] ?? [])),
            array_map('strval', (array) ($stats['index_definition'] ?? [])),
            (array) ($stats['fields'] ?? []),
            (int) $stats['num_docs'],
            (int) $stats['max_doc_id'],
            (int) $stats['num_terms'],
            (float) $stats['bytes_per_record_avg'],
            (float) $stats['inverted_sz_mb'],
            (int) ($stats['total_inverted_index_blocks'] ?? 0),
            '1' === $stats['indexing'],
            (int) ($stats['hash_indexing_failures'] ?? 0),
            (float) ($stats['percent_indexed'] ?? 1),
            array_map('strval', (array) ($stats['stopwords_list'] ?? []))
        );
    }

    /**
     * @return array<string>
     */
    public static function getIndexes(Client $redis): array
    {
        return $redis->executeRaw(['FT._LIST']);
    }
}
