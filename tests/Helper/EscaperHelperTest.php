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

namespace Tests\MacFJA\RediSearch\Helper;

use function array_combine;
use function array_map;
use function fclose;
use function fgetcsv;
use function fopen;
use MacFJA\RediSearch\Helper\EscapeHelper;
use PHPUnit\Framework\TestCase;

class EscaperHelperTest extends TestCase
{
    private $fixtures;

    /**
     * @covers \MacFJA\RediSearch\Helper\EscapeHelper::escapeFieldName
     *
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeChar()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeCommon()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeCharAtWordBegin()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::unescapeNumber()
     * @dataProvider dataProvider
     */
    public function testEscapeFieldName(string $input, string $output): void
    {
        if (empty($output)) {
            self::markTestSkipped('not transformation for '.$input);
        }
        self::assertSame($output, EscapeHelper::escapeFieldName($input));
    }

    /**
     * @covers \MacFJA\RediSearch\Helper\EscapeHelper::escapeWord
     *
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeChar()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeCommon()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::unescapeNumber()
     * @dataProvider dataProvider
     */
    public function testEscapeWord(string $input, string $output): void
    {
        if (empty($output)) {
            self::markTestSkipped('not transformation for '.$input);
        }
        self::assertSame($output, EscapeHelper::escapeWord($input));
    }

    /**
     * @covers \MacFJA\RediSearch\Helper\EscapeHelper::escapeFuzzy
     *
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeChar()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeCommon()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::unescapeNumber()
     *
     * @dataProvider dataProvider
     */
    public function testEscapeFuzzy(string $input, string $output): void
    {
        if (empty($output)) {
            self::markTestSkipped('not transformation for '.$input);
        }
        self::assertSame($output, EscapeHelper::escapeFuzzy($input));
    }

    /**
     * @covers \MacFJA\RediSearch\Helper\EscapeHelper::escapeExactMatch
     *
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeChar()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeCommon()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::unescapeNumber()
     *
     * @dataProvider dataProvider
     */
    public function testEscapeExactMatch(string $input, string $output): void
    {
        if (empty($output)) {
            self::markTestSkipped('not transformation for '.$input);
        }
        self::assertSame($output, EscapeHelper::escapeExactMatch($input));
    }

    /**
     * @covers \MacFJA\RediSearch\Helper\EscapeHelper::escapeNegation
     *
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeChar()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeCommon()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeCharAtWordBegin()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::unescapeNumber()
     *
     * @dataProvider dataProvider
     */
    public function testEscapeNegation(string $input, string $output): void
    {
        if (empty($output)) {
            self::markTestSkipped('not transformation for '.$input);
        }
        self::assertSame($output, EscapeHelper::escapeNegation($input));
    }

    /**
     * @covers \MacFJA\RediSearch\Helper\EscapeHelper::escapeOptional
     *
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeChar()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::escapeCommon()
     * @uses \MacFJA\RediSearch\Helper\EscapeHelper::unescapeNumber()
     *
     * @dataProvider dataProvider
     */
    public function testEscapeOptional(string $input, string $output): void
    {
        if (empty($output)) {
            self::markTestSkipped('not transformation for '.$input);
        }
        self::assertSame($output, EscapeHelper::escapeOptional($input));
    }

    public function dataProvider(string $testName): array
    {
        switch ($testName) {
            case 'testEscapeFieldName':
                return $this->getFixture('field name');
            case 'testEscapeWord':
                return $this->getFixture('word');
            case 'testEscapeFuzzy':
                return $this->getFixture('fuzzy');
            case 'testEscapeExactMatch':
                return $this->getFixture('exact match');
            case 'testEscapeNegation':
                return $this->getFixture('negation');
            case 'testEscapeOptional':
                return $this->getFixture('optional');
        }

        return [];
    }

    private function getFixture(string $columnName): array
    {
        if (null === $this->fixtures) {
            $handle = fopen(__DIR__.'/../fixtures/Helper/EscaperHelperFixture.csv', 'rb');
            $header = fgetcsv($handle);
            fgetcsv($handle); // Description
            $this->fixtures = [];
            while (($row = fgetcsv($handle, 0, ',', '"', '"')) !== false) {
                $this->fixtures[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return array_map(function (array $row) use ($columnName) {
            return [$row['input'], $row[$columnName]];
        }, $this->fixtures);
    }
}
