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

namespace MacFJA\RediSearch\tests\Redis\Command\CreateCommand;

use MacFJA\RediSearch\Exception\InvalidJSONPathException;
use MacFJA\RediSearch\Redis\Command\CreateCommand\JSONFieldOption;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MacFJA\RediSearch\Exception\InvalidJSONPathException
 * @covers \MacFJA\RediSearch\Redis\Command\CreateCommand\JSONFieldOption
 */
class JSONFieldOptionTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testIsPathValid(string $input, bool $expected): void
    {
        static::assertEquals($expected, JSONFieldOption::isPathValid($input));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIsPathValidException(string $input, bool $expected): void
    {
        $this->expectException(InvalidJSONPathException::class);
        static::assertEquals($expected, JSONFieldOption::isPathValid($input));
    }

    /**
     * @return array<string, array<bool|string>>
     */
    public function dataProvider(string $testName): array
    {
        if ('testIsPathValid' === $testName) {
            return [
                '2nd element of root' => ['$.[1]', true],
                '2nd element' => ['$[1]', true],
                'multilevel' => ['$.basic.dict.new_child_1', true],
                'empty 2' => [' ', false],
                'empty' => ['', false],
                'not close 2' => ['$[', false],
                'missing dollar' => ['.basic', false],
            ];
        }

        return [
            'not close 1' => ['$(', false],
        ];
    }
}
