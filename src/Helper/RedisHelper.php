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

use function array_chunk;
use function array_column;
use function array_combine;
use function array_filter;
use function array_keys;
use function array_merge;
use function array_shift;
use function assert;
use function count;
use function is_array;
use MacFJA\RediSearch\PartialQuery;

class RedisHelper
{
    /**
     * [
     *   'REDISEARCHKEYWORD' => $phpBooleanValue
     * ].
     *
     * @param array<float|int|string> $query
     * @param array<string,bool>      $data
     *
     * @return array<float|int|string>
     */
    public static function buildQueryBoolean(array $query, array $data): array
    {
        return array_merge($query, array_keys(array_filter($data, function ($item) {
            return $item;
        })));
    }

    /**
     * [
     *   'REDISEARCHKEYWORD' => $phpArray
     * ].
     *
     * @param array<float|int|string> $query
     * @param array<string,array>     $data
     *
     * @return array<float|int|string>
     */
    public static function buildQueryList(array $query, array $data): array
    {
        foreach ($data as $queryWord => $list) {
            if (count($list) > 0) {
                $query[] = $queryWord;
                $query[] = count($list);
                $query = array_merge($query, $list);
            }
        }

        return $query;
    }

    /**
     * @param array<float|int|string>   $query
     * @param array<string, null|mixed> $data
     *
     * @return array<float|int|string>
     */
    public static function buildQueryNotNull($query, array $data): array
    {
        foreach (array_filter($data) as $queryWord => $value) {
            $query[] = $queryWord;
            $query[] = $value;
        }

        return $query;
    }

    /**
     * @param array<float|int|string>  $query
     * @param array<null|PartialQuery> $partials
     *
     * @return array<float|int|string>
     */
    public static function buildQueryPartial(array $query, array $partials): array
    {
        /** @var PartialQuery $partial */
        foreach (array_filter($partials) as $partial) {
            $query = array_merge($query, $partial->getQueryParts());
        }

        return $query;
    }

    /**
     * @param array<float|int|mixed|string> $notKeyedArray
     *
     * @return array<string,mixed>
     */
    public static function getPairs(array $notKeyedArray): array
    {
        $entries = array_chunk($notKeyedArray, 2);
        $keyed = array_combine(array_column($entries, 0), array_column($entries, 1));
        assert(is_array($keyed));

        return $keyed;
    }

    /**
     * @param array<float|int|string> $notKeyedArray
     */
    public static function getValue(array $notKeyedArray, string $key): ?string
    {
        while (count($notKeyedArray) > 0) {
            $shifted = array_shift($notKeyedArray);
            if ($shifted === $key && count($notKeyedArray) > 0) {
                return (string) array_shift($notKeyedArray);
            }
        }

        return null;
    }
}
