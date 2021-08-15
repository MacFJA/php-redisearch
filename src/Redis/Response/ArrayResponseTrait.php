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

use function assert;
use function count;
use function is_array;

trait ArrayResponseTrait
{
    /**
     * @param array<float|int|mixed|string> $notKeyedArray
     *
     * @return array<string,mixed>
     */
    private static function getPairs(array $notKeyedArray): array
    {
        if (count($notKeyedArray) % 2 > 0) {
            array_pop($notKeyedArray);
        }
        $entries = array_chunk($notKeyedArray, 2);
        $keyed = array_combine(array_column($entries, 0), array_column($entries, 1));
        assert(is_array($keyed));

        return $keyed;
    }

    /**
     * @param array<array|mixed> $notKeyedArray
     *
     * @return null|array<mixed>|bool|float|int|string
     */
    private static function getValue(array $notKeyedArray, string $key)
    {
        while (count($notKeyedArray) >= 2) {
            $shifted = (string) array_shift($notKeyedArray);
            if ($shifted === $key) {
                return array_shift($notKeyedArray);
            }
            array_shift($notKeyedArray);
        }

        return null;
    }

    /**
     * @param array<array|mixed> $notKeyedArray
     *
     * @return null|bool|float|int|string
     */
    private static function getNextValue(array $notKeyedArray, string $key)
    {
        while (count($notKeyedArray) >= 2) {
            $shifted = (string) array_shift($notKeyedArray);
            if ($shifted === $key) {
                return array_shift($notKeyedArray);
            }
        }

        return null;
    }

    /**
     * @param array<float|int|mixed|string> $notKeyedArray
     *
     * @return array<string>
     */
    private static function getKeys(array $notKeyedArray): array
    {
        $keys = array_filter(array_values($notKeyedArray), static function (int $key): bool {
            return 0 === $key % 2;
        }, ARRAY_FILTER_USE_KEY);

        return array_map(static function ($key): string {
            return (string) $key;
        }, $keys);
    }
}
