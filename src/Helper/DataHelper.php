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

namespace MacFJA\RedisSearch\Helper;

use function assert;
use function class_exists;
use function debug_backtrace;
use function end;
use function gettype;
use function in_array;
use function is_array;
use function is_scalar;
use function is_string;
use MacFJA\RedisSearch\Index\Exception\IndexNotFoundException;
use RuntimeException;
use function sprintf;
use function sscanf;
use function strpos;
use function strtolower;
use Throwable;

class DataHelper
{
    /**
     * @param null|string|Throwable $exception
     *
     * @throws Throwable
     */
    public static function assert(bool $condition, $exception = null, int $deep = 1): void
    {
        if (false === $condition) {
            if ($exception instanceof Throwable) {
                throw $exception;
            }
            if (is_string($exception) && class_exists($exception)) {
                $instance = new $exception();
                if ($instance instanceof Throwable) {
                    throw $instance;
                }
            }
            $trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1 + $deep);
            $trace = end($trace);
            assert(is_array($trace));

            throw new RuntimeException(sprintf(
                'The assertion of %s::%s at line %d failed.',
                $trace['class'] ?? '',
                $trace['function'] ?? '',
                $trace['line'] ?? 0
            ));
        }
    }

    /**
     * @param array<mixed>          $array
     * @param null|string|Throwable $exception
     *
     * @throws Throwable
     * @suppress PhanUndeclaredClass
     */
    public static function assertArrayOf(array $array, string $type, $exception = null): void
    {
        switch ($type) {
            case 'int':
                $type = 'integer';

                break;
            case 'bool':
                $type = 'boolean';

                break;
            case 'float':
                $type = 'double';

                break;
        }
        foreach ($array as $item) {
            if (in_array($type, ['string', 'boolean', 'integer', 'double'], true)) {
                self::assert(gettype($item) === $type, $exception, 2);
            } elseif ('scalar' === $type) {
                self::assert(is_scalar($item), $exception, 2);
            } else {
                self::assert($item instanceof $type, $exception, 2);
            }
        }
    }

    /**
     * @param array<mixed>|mixed $result
     *
     * @throws IndexNotFoundException
     */
    public static function handleRawResult($result): void
    {
        if (is_string($result) && !(false === strpos($result, ': no such index'))) {
            sscanf($result, '%s: no such index', $index);

            throw new IndexNotFoundException((string) $index);
        }
        if (is_string($result) && 'unknown index name' === strtolower($result)) {
            throw new IndexNotFoundException();
        }
    }
}
