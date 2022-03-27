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

namespace MacFJA\RediSearch\tests\Redis\Command\Option;

use Generator;
use MacFJA\RediSearch\Redis\Command\Option\NumberedOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\Option\NumberedOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 *
 * @internal
 */
class NumberedOptionTest extends TestCase
{
    use BaseOptionTestTrait;

    public function testGetSetArguments(): void
    {
        $option = new NumberedOption('FOO');
        static::assertNull($option->getArguments());
        $option->setArguments([]);
        static::assertIsArray($option->getArguments());
        static::assertEquals([], $option->getArguments());
        $option->setArguments(['foo', 'bar', 'foobar']);
        static::assertIsArray($option->getArguments());
        static::assertEquals(['foo', 'bar', 'foobar'], $option->getArguments());

        $option = new NumberedOption('FOO', ['foo']);
        static::assertIsArray($option->getArguments());
        static::assertEquals(['foo'], $option->getArguments());
    }

    public function testGetName(): void
    {
        $option = new NumberedOption('FOO');
        static::assertEquals('FOO', $option->getName());
        $option = new NumberedOption('BAR');
        static::assertEquals('BAR', $option->getName());
    }

    public function dataProvider(string $testName): Generator
    {
        $noArgumentsOption = new NumberedOption('FOO');
        $emptyArgumentsOption = new NumberedOption('FOO', []);
        $withOneArgumentOption = new NumberedOption('FOO', ['bar']);
        $withMultipleArgumentOption = new NumberedOption('FOO', ['bar', 'foobar']);

        switch ($testName) {
            case 'testGetOptionData':
                yield [$noArgumentsOption, null];

                yield [$emptyArgumentsOption, []];

                yield [$withOneArgumentOption, ['bar']];

                yield [$withMultipleArgumentOption, ['bar', 'foobar']];

                break;

            case 'testIsValid':
                yield [$noArgumentsOption, false];

                yield [$emptyArgumentsOption, true];

                yield [$withOneArgumentOption, true];

                yield [$withMultipleArgumentOption, true];

                break;

            case 'testRender':
                yield [$noArgumentsOption, []];

                yield [$emptyArgumentsOption, ['FOO', 0]];

                yield [$withOneArgumentOption, ['FOO', 1, 'bar']];

                yield [$withMultipleArgumentOption, ['FOO', 2, 'bar', 'foobar']];

                break;
        }
    }
}
