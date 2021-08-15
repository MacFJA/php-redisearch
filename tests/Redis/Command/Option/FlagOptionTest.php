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
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 *
 * @internal
 */
class FlagOptionTest extends TestCase
{
    use BaseOptionTestTrait;

    public function testGetSetActive(): void
    {
        $option = new FlagOption('FOO', true);
        static::assertTrue($option->isActive());
        $option->setActive(true);
        static::assertTrue($option->isActive());
        $option->setActive(false);
        static::assertFalse($option->isActive());
        $option = new FlagOption('BAR', false);
        static::assertFalse($option->isActive());
        $option = new FlagOption('FOOBAR');
        static::assertFalse($option->isActive());
    }

    public function testGetName(): void
    {
        $option = new FlagOption('FOO');
        static::assertEquals('FOO', $option->getName());
        $option = new FlagOption('BAR');
        static::assertEquals('BAR', $option->getName());
    }

    /**
     * @return Generator<array>
     */
    public function dataProvider(string $testName): Generator
    {
        $defaultValueFlag = new FlagOption('FOO');
        $notActiveFlag = new FlagOption('FOO', false);
        $activeFlag = new FlagOption('BAR', true);

        switch ($testName) {
            case 'testGetOptionData':
                yield [$defaultValueFlag, false];
                yield [$notActiveFlag, false];
                yield [$activeFlag, true];

                break;

            case 'testIsValid':
                yield [$defaultValueFlag, true];
                yield [$notActiveFlag, true];
                yield [$activeFlag, true];

                break;

            case 'testRender':
                yield [$defaultValueFlag, []];
                yield [$notActiveFlag, []];
                yield [$activeFlag, ['BAR']];

                break;

            default:
                return;
        }
    }
}
