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
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\Option\OptionListOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\Option\OptionListOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 *
 * @internal
 */
class OptionListOptionTest extends TestCase
{
    use BaseOptionTestTrait;

    public function testAddOption(): void
    {
        $emptyArgumentsOption = new OptionListOption();
        $emptyArgumentsOption->addOption(new NamelessOption());
        static::assertEquals([new NamelessOption()], $emptyArgumentsOption->getOptionData());

        $withOneArgumentOption = new OptionListOption();
        $withOneArgumentOption->addOption(new NamelessOption('foo'));
        static::assertEquals([new NamelessOption('foo')], $withOneArgumentOption->getOptionData());

        $withMultipleArgumentOption = new OptionListOption();
        $withMultipleArgumentOption->addOption(new NamelessOption('foo'));
        static::assertEquals([new NamelessOption('foo')], $withMultipleArgumentOption->getOptionData());
        $withMultipleArgumentOption->addOption(new NamelessOption('bar'));
        static::assertEquals([new NamelessOption('foo'), new NamelessOption('bar')], $withMultipleArgumentOption->getOptionData());
        $withMultipleArgumentOption->addOption(new NamedOption('foobar', 'baz'));
        static::assertEquals([new NamelessOption('foo'), new NamelessOption('bar'), new NamedOption('foobar', 'baz')], $withMultipleArgumentOption->getOptionData());
    }

    public function testSetValue(): void
    {
        $emptyArgumentsOption = new OptionListOption();
        $emptyArgumentsOption->setValue(new NamelessOption());
        static::assertEquals([new NamelessOption()], $emptyArgumentsOption->getOptionData());

        $withOneArgumentOption = new OptionListOption();
        $withOneArgumentOption->setValue(new NamelessOption('foo'));
        static::assertEquals([new NamelessOption('foo')], $withOneArgumentOption->getOptionData());
        $withOneArgumentOption->setValue(new NamelessOption('bar'));
        static::assertEquals([new NamelessOption('bar')], $withOneArgumentOption->getOptionData());

        $withMultipleArgumentOption = new OptionListOption();
        $withMultipleArgumentOption->setValue(new NamelessOption('foo'), new NamelessOption('bar'), new NamedOption('foobar', 'baz'));
        static::assertEquals([new NamelessOption('foo'), new NamelessOption('bar'), new NamedOption('foobar', 'baz')], $withMultipleArgumentOption->getOptionData());
    }

    public function dataProvider(string $testName): Generator
    {
        $noArgumentsOption = new OptionListOption();
        $emptyArgumentsOption = new OptionListOption();
        $emptyArgumentsOption->addOption(new NamelessOption());
        $withOneArgumentOption = new OptionListOption();
        $withOneArgumentOption->addOption(new NamelessOption('foo'));
        $withMultipleArgumentOption = new OptionListOption();
        $withMultipleArgumentOption->addOption(new NamelessOption('foo'));
        $withMultipleArgumentOption->addOption(new NamelessOption('bar'));
        $withMultipleArgumentOption->addOption(new NamedOption('foobar', 'baz'));

        switch ($testName) {
            case 'testGetOptionData':
                yield [$noArgumentsOption, []];

                yield [$emptyArgumentsOption, [new NamelessOption()]];

                yield [$withOneArgumentOption, [new NamelessOption('foo')]];

                yield [$withMultipleArgumentOption, [new NamelessOption('foo'), new NamelessOption('bar'), new NamedOption('foobar', 'baz')]];

                break;

            case 'testIsValid':
                yield [$noArgumentsOption, true];

                yield [$emptyArgumentsOption, true];

                yield [$withOneArgumentOption, true];

                yield [$withMultipleArgumentOption, true];

                break;

            case 'testRender':
                yield [$noArgumentsOption, []];

                yield [$emptyArgumentsOption, []];

                yield [$withOneArgumentOption, ['foo']];

                yield [$withMultipleArgumentOption, ['foo', 'bar', 'foobar', 'baz']];

                break;
        }
    }
}
