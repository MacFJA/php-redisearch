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

namespace MacFJA\RediSearch\tests\Query;

use function count;
use MacFJA\RediSearch\Query\Escaper;

/**
 * @covers \MacFJA\RediSearch\Query\Escaper
 *
 * @internal
 */
class EscaperTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_TO_FIXTURE = [
        'testEscapeFieldName' => 'field name',
        'testEscapeWord' => 'word',
        'testEscapeExactMatch' => 'exact match',
        'testEscapeFuzzy' => 'fuzzy',
        'testEscapeNegation' => 'negation',
        'testEscapeOptional' => 'optional',
        'testEscapeNothing' => 'input',
    ];
    /**
     * @var array<array<string>>
     */
    private $dataset = [];

    /**
     * @dataProvider dataProvider
     */
    public function testEscapeNothing(string $input, string $escaped): void
    {
        static::assertSame($escaped, Escaper::escapeNothing($input));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testEscapeFieldName(string $input, string $escaped): void
    {
        static::assertSame($escaped, Escaper::escapeFieldName($input));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testEscapeWord(string $input, string $escaped): void
    {
        static::assertSame($escaped, Escaper::escapeWord($input));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testEscapeExactMatch(string $input, string $escaped): void
    {
        static::assertSame($escaped, Escaper::escapeExactMatch($input));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testEscapeFuzzy(string $input, string $escaped): void
    {
        static::assertSame($escaped, Escaper::escapeFuzzy($input));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testEscapeNegation(string $input, string $escaped): void
    {
        static::assertSame($escaped, Escaper::escapeNegation($input));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testEscapeOptional(string $input, string $escaped): void
    {
        static::assertSame($escaped, Escaper::escapeOptional($input));
    }

    /**
     * @return array<array<string>>
     */
    public function dataProvider(string $testName): array
    {
        $this->buildDataset();

        return array_map(static function ($row) use ($testName) {
            return [$row['input'], $row[self::TEST_TO_FIXTURE[$testName]]];
        }, $this->dataset);
    }

    private function buildDataset(): void
    {
        if (count($this->dataset) > 0) {
            return;
        }

        $datasetPath = __DIR__.'/../fixtures/Query/EscaperFixture.csv';

        if (!file_exists($datasetPath)) {
            static::markTestSkipped('Unable to find fixture data');
        }
        $handle = fopen($datasetPath, 'rb');

        if (false === $handle) {
            static::markTestSkipped('Unable to load fixture data');
        }

        $header = fgetcsv($handle) ?: [];
        fgetcsv($handle); // Description
        while (($row = fgetcsv($handle, 0, ',', '"', '"')) !== false) {
            $this->dataset[] = array_combine($header, $row ?: []) ?: [];
        }
        fclose($handle);

        if (0 === count($this->dataset)) {
            static::markTestSkipped('Unable to load fixture data');
        }
    }
}
