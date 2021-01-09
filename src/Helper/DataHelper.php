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

use function assert;
use function class_exists;
use function debug_backtrace;
use function end;
use function gettype;
use function in_array;
use function is_array;
use function is_scalar;
use function is_string;
use MacFJA\RediSearch\Index\Exception\IndexNotFoundException;
use RuntimeException;
use function settype;
use function sprintf;
use function sscanf;
use function strpos;
use function strtolower;
use Throwable;
use UnexpectedValueException;

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
        $type = self::getPhpType($type);
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

    /**
     * @param null|bool|float|int|string $raw
     *
     * @return null|bool|float|int|string
     */
    public static function nullOrCast($raw, string $cast)
    {
        if (is_scalar($raw)) {
            settype($raw, self::getPhpType($cast, true));
        }

        return $raw;
    }

    private static function getPhpType(string $type, bool $exceptionOnUnknown = false): string
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return 'integer';
            case 'bool':
            case 'boolean':
                return 'boolean';
            case 'float':
            case 'double':
                return 'double';
            case 'string':
                return 'string';
        }
        if (true === $exceptionOnUnknown) {
            throw new UnexpectedValueException(sprintf('The type "%s" is unknown', $type));
        }

        return $type;
    }
}
