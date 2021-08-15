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

namespace MacFJA\RediSearch\Query;

use function preg_quote;
use function preg_replace;
use function sprintf;
use function str_split;

class Escaper
{
    private const NOT_ESCAPED_CHAR_REGEX_PATTERN = '/(?<!\\\\)((?:\\\\\\\\)*)(%s)/';

    private const CHAR_AT_WORD_BEGIN_REGEX_PATTERN = '/(^|\s+)(%s)/';

    public static function escapeFieldName(string $text): string
    {
        $text = self::escapeCommon($text);

        return self::escapeCharAtWordBegin($text, '-');
    }

    public static function escapeWord(string $text): string
    {
        return self::escapeCommon($text);
    }

    public static function escapeExactMatch(string $text): string
    {
        return self::escapeCommon($text);
    }

    public static function escapeFuzzy(string $text): string
    {
        return self::escapeCommon($text);
    }

    public static function escapeNegation(string $text): string
    {
        $text = self::escapeCommon($text);
        $text = self::escapeCharAtWordBegin($text, '-');

        for ($number = 0; $number < 10; ++$number) {
            $text = self::escapeCharAtWordBegin($text, (string) $number);
        }

        return $text;
    }

    public static function escapeOptional(string $text): string
    {
        return self::escapeCommon($text);
    }

    public static function escapeNothing(string $text): string
    {
        return $text;
    }

    /**
     * @codeCoverageIgnore
     */
    private static function escapeCharAtWordBegin(string $inText, string $char): string
    {
        return preg_replace(
            sprintf(self::CHAR_AT_WORD_BEGIN_REGEX_PATTERN, preg_quote($char, '/')),
            '$1\\\\$2',
            $inText
        ) ?? $inText;
    }

    /**
     * @codeCoverageIgnore
     */
    private static function escapeChar(string $inText, string $char): string
    {
        return preg_replace(
            sprintf(self::NOT_ESCAPED_CHAR_REGEX_PATTERN, preg_quote($char, '/')),
            '$1\\\\$2',
            $inText
        ) ?? $inText;
    }

    /**
     * @codeCoverageIgnore
     */
    private static function escapeCommon(string $inText): string
    {
        $anywhere = str_split(',.<>{}[]"\':;!@#$%^&*()-+=~|');

        foreach ($anywhere as $char) {
            $inText = self::escapeChar($inText, $char);
        }

        return self::unescapeNumber($inText);
    }

    /**
     * @codeCoverageIgnore
     */
    private static function unescapeNumber(string $inText): string
    {
        return preg_replace('/(?:\\s|^)\\\\(-\\d)/', '$1', $inText) ?? $inText;
    }
}
