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

use BadMethodCallException;
use Generator;
use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\GroupedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\Option\NumberedOption;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Rules\StringVal;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @covers \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionAwareTrait
 * @covers \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @covers \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NumberedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 *
 * @internal
 */
class GroupedOptionTest extends TestCase
{
    use BaseOptionTestTrait;
    use TestIsCompatibleTrait;

    public function testGetSetDataOfOption(): void
    {
        $option = new GroupedOption([
            'flag' => new FlagOption('FOO'),
            'nameless' => new NamelessOption(),
            'decorated' => new CustomValidatorOption(new NamelessOption(), new StringVal()),
            'named' => new NamedOption('BAR'),
            'list' => new NumberedOption('list'),
        ], []);

        static::assertEquals(false, $option->getDataOfOption('flag'));
        static::assertEquals(null, $option->getDataOfOption('nameless'));
        static::assertEquals(null, $option->getDataOfOption('decorated'));
        static::assertEquals(null, $option->getDataOfOption('named'));
        static::assertEquals(null, $option->getDataOfOption('list'));

        $option->setDataOfOption('flag', true);
        static::assertEquals(true, $option->getDataOfOption('flag'));

        $option->setDataOfOption('nameless', 20);
        static::assertEquals(20, $option->getDataOfOption('nameless'));

        $option->setDataOfOption('decorated', 'foo');
        static::assertEquals('foo', $option->getDataOfOption('decorated'));

        $option->setDataOfOption('list', ['foo', 'bar']);
        static::assertEquals(['foo', 'bar'], $option->getDataOfOption('list'));
    }

    public function testGetOption(): void
    {
        $option = new GroupedOption([
            'flag' => new FlagOption('FOO'),
            'nameless' => new NamelessOption(),
            'named' => new NamedOption('BAR'),
        ], []);

        static::assertEquals(new FlagOption('FOO'), $option->getOption('flag'));
        static::assertEquals(new NamelessOption(), $option->getOption('nameless'));
        static::assertEquals(new NamedOption('BAR'), $option->getOption('named'));
    }

    public function testGetOptionsName(): void
    {
        $option = new GroupedOption([
            'flag' => new FlagOption('FOO'),
            'nameless' => new NamelessOption(),
            'named' => new NamedOption('BAR'),
        ], []);
        static::assertEquals(['flag', 'nameless', 'named'], $option->getOptionsName());
    }

    public function testSetUnknownOptionData(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectErrorMessage('There is no option for foobar');

        $option = new GroupedOption([], []);
        $option->setDataOfOption('foobar', 'bar');
    }

    public function testSetLockOptionData(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectErrorMessage('The option lock can\'t be modified');

        $option = new GroupedOption(['lock' => new NamelessOption('foo')], [], ['lock']);
        $option->setDataOfOption('lock', 'bar');
    }

    public function dataProvider(string $testName): Generator
    {
        $emptyGroup = new GroupedOption([], []);
        $noRequiredGroup = new GroupedOption(['foo' => new NamelessOption()], []);
        $emptyRequiredGroup = new GroupedOption([], ['foo']);
        $noValueRequireGroup = new GroupedOption(['foo' => new NamelessOption()], ['foo']);
        $valueRequireGroup = new GroupedOption(['foo' => new NamelessOption('FOO')], ['foo']);
        $valueRequireGroup2 = new GroupedOption(['foo' => new NamelessOption(0)], ['foo']);
        $valueLockGroup = new GroupedOption(['foo' => new NamelessOption('FOO')], [], ['foo']);
        $lowVersionGroup = new GroupedOption([], [], [], '>=1.0.0');
        $highVersionGroup = new GroupedOption([], [], [], '>=2.0.0');

        switch ($testName) {
            case 'testGetOptionData':
                yield [$emptyGroup, []];
                yield [$noRequiredGroup, ['foo' => new NamelessOption()]];
                yield [$emptyRequiredGroup, []];
                yield [$noValueRequireGroup, ['foo' => new NamelessOption()]];
                yield [$valueRequireGroup, ['foo' => new NamelessOption('FOO')]];
                yield [$valueRequireGroup2, ['foo' => new NamelessOption(0)]];
                yield [$valueLockGroup, ['foo' => new NamelessOption('FOO')]];

                break;

            case 'testIsValid':
                yield [$emptyGroup, true];
                yield [$noRequiredGroup, true];
                yield [$emptyRequiredGroup, false];
                yield [$noValueRequireGroup, false];
                yield [$valueRequireGroup, true];
                yield [$valueRequireGroup2, true];
                yield [$valueLockGroup, true];

                break;

            case 'testRender':
                yield [$emptyGroup, []];
                yield [$noRequiredGroup, []];
                yield [$emptyRequiredGroup, []];
                yield [$noValueRequireGroup, []];
                yield [$valueRequireGroup, ['FOO']];
                yield [$valueRequireGroup2, [0]];
                yield [$valueLockGroup, ['FOO']];

                break;

            case 'testIsCompatible':
                yield [$emptyGroup, '1.0.0', true];
                yield [$emptyGroup, '1.5.0', true];
                yield [$emptyGroup, '2.0.0', true];
                yield [$emptyGroup, '2.5.0', true];

                yield [$lowVersionGroup, '1.0.0', true];
                yield [$lowVersionGroup, '1.5.0', true];
                yield [$lowVersionGroup, '2.0.0', true];
                yield [$lowVersionGroup, '2.5.0', true];

                yield [$highVersionGroup, '1.0.0', false];
                yield [$highVersionGroup, '1.5.0', false];
                yield [$highVersionGroup, '2.0.0', true];
                yield [$highVersionGroup, '2.5.0', true];

                break;

            default:
                return;
        }
    }
}
