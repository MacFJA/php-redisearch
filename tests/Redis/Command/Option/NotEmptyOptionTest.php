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
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption;
use MacFJA\RediSearch\Redis\Command\Option\NumberedOption;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @covers \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @covers \MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NumberedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 *
 * @internal
 */
class NotEmptyOptionTest extends \PHPUnit\Framework\TestCase
{
    use BaseOptionTestTrait;
    use TestIsCompatibleTrait;

    public function testIsValid2(): void
    {
        $option = new NotEmptyOption(new NamedOption('FOO'));
        static::assertEquals(null, $option->getValue());
        static::assertFalse($option->isValid());
        static::assertSame([], $option->render());
        $option->setValue('bar');
        static::assertEquals('bar', $option->getValue());
        static::assertTrue($option->isValid());
        static::assertSame(['FOO', 'bar'], $option->render());

        $option->setValue('');
        static::assertEquals('', $option->getValue());
        static::assertFalse($option->isValid());
        static::assertSame([], $option->render());
    }

    public function dataProvider(string $testName): Generator
    {
        $namelessOption = new NotEmptyOption(new NamelessOption('foo'));
        $namedOption = new NotEmptyOption(new NamedOption('FOO', 'bar'));
        $numberedOption = new NotEmptyOption(new NumberedOption('FOO', ['bar', 'foobar']));
        $emptyNamelessOption = new NotEmptyOption(new NamelessOption());
        $emptyNamedOption = new NotEmptyOption(new NamedOption('FOO'));
        $emptyNumberedOption = new NotEmptyOption(new NumberedOption('FOO', []));
        $nullNumberedOption = new NotEmptyOption(new NumberedOption('FOO'));
        $trueFlagOption = new NotEmptyOption(new FlagOption('FOO', true));
        $falseFlagOption = new NotEmptyOption(new FlagOption('FOO'));
        $lowOptionOption = new NotEmptyOption(new NamelessOption('bar', '>=1.0.0'));
        $highOptionOption = new NotEmptyOption(new NamelessOption('bar', '>=2.0.0'));

        switch ($testName) {
            case 'testGetOptionData':
                yield [$namelessOption, 'foo'];
                yield [$namedOption, 'bar'];
                yield [$numberedOption, ['bar', 'foobar']];
                yield [$emptyNamelessOption, null];
                yield [$emptyNamedOption, null];
                yield [$emptyNumberedOption, []];
                yield [$nullNumberedOption, null];
                yield [$trueFlagOption, true];
                yield [$falseFlagOption, false];

                break;

            case 'testIsValid':
                yield [$namelessOption, true];
                yield [$namedOption, true];
                yield [$numberedOption, true];
                yield [$emptyNamelessOption, false];
                yield [$emptyNamedOption, false];
                yield [$emptyNumberedOption, false];
                yield [$trueFlagOption, true];
                yield [$falseFlagOption, false];

                break;

            case 'testRender':
                yield [$namelessOption, ['foo']];
                yield [$namedOption, ['FOO', 'bar']];
                yield [$numberedOption, ['FOO', 2, 'bar', 'foobar']];
                yield [$emptyNamelessOption, []];
                yield [$emptyNamedOption, []];
                yield [$emptyNumberedOption, []];
                yield [$trueFlagOption, ['FOO']];
                yield [$falseFlagOption, []];

                break;

            case 'testIsCompatible':
                yield [$namelessOption, '1.0.0', true];
                yield [$namelessOption, '1.5.0', true];
                yield [$namelessOption, '2.0.0', true];
                yield [$namelessOption, '2.5.0', true];
                yield [$namelessOption, 'foo', false];

                yield [$lowOptionOption, '1.0.0', true];
                yield [$lowOptionOption, '1.5.0', true];
                yield [$lowOptionOption, '2.0.0', true];
                yield [$lowOptionOption, '2.5.0', true];
                yield [$lowOptionOption, 'foo', false];

                yield [$highOptionOption, '1.0.0', false];
                yield [$highOptionOption, '1.5.0', false];
                yield [$highOptionOption, '2.0.0', true];
                yield [$highOptionOption, '2.5.0', true];
                yield [$highOptionOption, 'foo', false];

                break;

            default:
                return;
        }
    }
}
